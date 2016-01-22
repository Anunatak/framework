<?php

namespace Anunatak\Framework\Http;

use Anunatak\Framework\Framework;

class Controller {

	/**
	 * Holds the Framework
	 */
	protected $framework;

	/**
	 * Holds the Request
	 */
	protected $request;

	/**
	 * Holds the Router
	 */
	protected $router;

	/**
	 * Path to the assets
	 * @var string
	 */
	private $assets_path;

	/**
	 * The type of controller
	 * @var string
	 */
	protected $type = 'public';

	/**
	 * Settings for the admin page
	 * @var array
	 */
	protected $admin_page = array();

	/**
	 * Admin sub pages
	 * @var array
	 */
	protected $admin_sub_pages = array();

	/**
	 * Admin pages
	 * @var array
	 */
	protected $admin_pages = array();

	/**
	 * Scripts
	 * @var array
	 */
	protected $scripts = array();

	/**
	 * Styles
	 * @var array
	 */
	protected $styles = array();

	/**
	 * Holds the Twig instance
	 * @var null
	 */
	protected $twig = null;

	/**
	 * Set up the controller
	 * @param mixed $router Router or null
	 */
	public function __construct($router = null, Framework $framework)
	{
		$this->framework   = $framework;
		$this->request     = $framework->make('request');
		$this->assets_path = $this->framework->getUrl() . 'public/';
		$this->router      = $router;

		if($this->type === 'admin') {
			add_action('admin_menu', array($this, 'adminPage'));
		}
		elseif($this->type === 'public') {
			$this->publicPage();
		}

		$this->twig = $this->framework->make('twig');
	}

	/**
	 * Sets up the admin page
	 * @return void
	 */
	public function adminPage()
	{
		if($this->admin_page) {
			$admin_page = $this->addAdminPage($this->admin_page);
			if($this->admin_sub_pages) {
				foreach($this->admin_sub_pages as $page) {
					$this->addAdminPage($page, $admin_page);
				}
			}
		}
	}

	/**
	 * Adds a new admin page
	 * @param array $options Admin page options
	 * @param string $parent The parent slug
	 */
	public function addAdminPage($options, $parent = null) {
		$options = array_merge( array(
			'page_title' => '', // the title of the settings page
			'menu_title' => '', // the title of the settings menu item
			'capability' => 'edit_posts', // the required capability
			'menu_slug'  => '', // the menu slug
			'icon_url'   => '', // the icon
			'position'   => 30, // the menu position
			'content'    => '' // the content of the page.
		), $options );

		if(!$parent) {
			$page = \add_menu_page(
				$options['page_title'],
				$options['menu_title'],
				$options['capability'],
				$this->framework->getSlug() . '-'. $options['menu_slug'],
				array($this, 'render'),
				$options['icon_url'],
				$options['position']
			);
		} else {
			$parent = $this->framework->getSlug() . '-' . $parent;
			$page = \add_submenu_page(
				$parent,
				$options['page_title'],
				$options['menu_title'],
				$options['capability'],
				$this->framework->getSlug() . '-'. $options['menu_slug'],
				array($this, 'render')
			);
		}

		add_action('admin_print_scripts-'. $page, array($this, 'scripts'));
		add_action('admin_print_styles-'. $page, array($this, 'styles'));
		$this->admin_pages[$options['menu_slug']] = $options;
		return $options['menu_slug'];
	}

	/**
	 * Sets up the public page actions
	 * @return void
	 */
	public function publicPage()
	{
		add_action('wp_enqueue_scripts', array($this, 'scripts'));
		add_action('wp_print_styles', array($this, 'styles'));

	}

	/**
	 * Load scripts
	 * @return void
	 */
	public function scripts() {
		if($this->scripts) {
			foreach($this->scripts as $script => $deps) {
				wp_enqueue_script( $this->framework->getSlug() . '-' . $script, $this->assets_path . 'js/'. $script .'.js', $deps, $this->framework->getVersion() );
			}
		}
	}

	/**
	 * Load styles
	 * @return void
	 */
	public function styles() {
		if($this->styles) {
			foreach($this->styles as $style) {
				wp_enqueue_style( $this->framework->getSlug() . '-' . $style, $this->assets_path . 'js/'. $script .'.js', array(), $this->framework->getVersion() );
			}
		}
	}

	/**
	 * Render the admin page
	 * @return mixed
	 */
	public function render() {
		$page = str_replace( $this->framework->getSlug() . '-', '', isset($_REQUEST['page']) ? $_REQUEST['page'] : '');
		$options = isset($this->admin_pages[$page]) ? $this->admin_pages[$page] : array();

		if(is_array($options['content'])) {
			return $this->renderApi($options['content']);
		} elseif(is_object($options['content']) && ($options['content'] instanceof Closure)) {
			return $this->renderFunction($options['content']);
		} elseif(is_string($options['content']) && $this->isViewPath($options['content'])) {
			return $this->renderFile($options['content']);
		} else {
			return $this->renderContent($options['content']);
		}
	}

	/**
	 * Render a file
	 * @return mixed
	 */
	public function renderContent($content) {
		echo '<div class="wrap">'. $content . '</div>';
	}

	/**
	 * Render a file
	 * @return mixed
	 */
	public function renderFile($content) {
		return $this->view($content, true);
	}

	/**
	 * Render a closure
	 * @return mixed
	 */
	public function renderFunction($content) {
		return call_user_func($content);
	}

	/**
	 * Render a settings API
	 * @return mixed
	 */
	public function renderApi($content) {
		return '';
	}

	/**
	 * Check if given string is a view
	 * @param  string  $file The file name
	 * @return boolean
	 */
	public function isViewPath($file) {
		$path = $this->framework->getUrl() . 'resources/views/';
		$file = $path . $file . '.php';
		if(file_exists($file)) {
			return true;
		}
		return false;
	}

	/**
	 * Gets a view from the resource folder
	 * @param  string $file View to get
	 * @return [type]       [description]
	 */
	public function view($file, $data = array(), $echo = false) {
		$path = $this->framework->getPath() . 'resources/views/';
		$filename = $path . $file;

		if(!file_exists($filename)) {
			$view =  __( 'View does not exist.', $this->framework->getTextDomain() );
		}
		else {
			$view = $this->twig->render($file, $data);
		}

		if($echo) {
			echo $view;
		}
		else {
			return $view;
		}
	}

}