<?php

namespace Anunatak\Framework;

class Controller {

	/**
	 * Path to the assets
	 * @var string
	 */
	private $assets_path;

	/**
	 * The type of controller
	 * @var string
	 */
	protected $type;

	/**
	 * Settings for the admin page
	 * @var array
	 */
	protected $admin_pages = array();

	/**
	 * Admin sub pages
	 * @var array
	 */
	protected $admin_sub_pages = array();

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
	 * Set up the controller
	 * @param mixed $router Router or null
	 */
	public function __construct($router = null)
	{
		$this->assets_path = Framework::getUrl() . 'resources/assets/';
		if($this->type === 'admin') {
			add_action('admin_menu', array($this, 'admin_page'));
		}
		elseif($this->type === 'public') {
			$this->public_page();
		}
	}

	/**
	 * Sets up the admin page
	 * @return void
	 */
	public function admin_page()
	{
		if($this->admin_page) {
			$admin_page = \add_menu_page( $this->admin_page['page_title'], $this->admin_page['menu_title'], $this->admin_page['capability'], $this->admin_page['menu_slug'], array($this, 'render'), $this->admin_page['icon_url'], $this->admin_page['position'] );
			add_action('admin_print_scripts-'. $admin_page, array($this, 'scripts'));
			add_action('admin_print_styles-'. $admin_page, array($this, 'styles'));
		}
	}

	/**
	 * Sets up the public page actions
	 * @return void
	 */
	public function public_page()
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
				wp_enqueue_script( Framework::getSlug() . '-' . $script, $this->assets_path . 'js/'. $script .'.js', $deps, Framework::getVersion() );
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
				wp_enqueue_style( Framework::getSlug() . '-' . $style, $this->assets_path . 'js/'. $script .'.js', array(), Framework::getVersion() );
			}
		}
	}

	/**
	 * Render the admin page
	 * @return mixed
	 */
	public function render() {
		return '';
	}

	/**
	 * Gets a view from the resource folder
	 * @param  string $file View to get
	 * @return [type]       [description]
	 */
	public function view($file, $echo = true) {
		$path = Framework::getUrl() . 'resources/views/';
		$file = $path . $file . '.php';

		if(!file_exists($file)) {
			$view =  __( 'View does not exist.', Framework::getTextDomain() );
		}
		else {
			ob_start();
			include $file;
			$view = ob_get_clean();
		}

		if($echo) {
			echo $view;
		}
		else {
			return $view;
		}
	}

}