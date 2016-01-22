<?php

namespace Anunatak\Framework;

use Pimple\Container as PimpleContainer;

/**
 * The main container class
 */
class Container {

	/**
	 * Holds the framework
	 */
	protected $framwork;

	/**
	 * Holds the container
	 * @var Pimple\Container
	 */
	protected $container;

	/**
	 * Sets up the enviroment
	 */
	public function __construct( Framework $framework ) {
		$this->framework = $framework;
		$this->createContainer();
		$this->registerModules();
	}

	/**
	 * Creates the container
	 * @return void
	 */
	protected function createContainer() {
		$this->container = new PimpleContainer();
	}

	/**
	 * Registers all modules
	 * @return void
	 */
	public function registerModules() {
		$container = $this;
		$this->container['twig_loader'] = function($c) use ($container) {
			return $container->registerTwigLoader($c);
		};;
		$this->container['twig'] = function($c) use ($container) {
			return $container->registerTwigEnviroment($c);
		};;
		$this->container['admin_router'] = function($c) use ($container) {
			return $container->registerAdminRouter($c);
		};;
		$this->container['router'] = function($c) use ($container) {
			return $container->registerPublicRouter($c);
		};
		$this->container['list_table'] = $this->container->factory(function($c) use ($container) {
			return $container->registerListTable($c);
		});
		$this->container['ajax'] = $this->container->factory(function($c) use ($container) {
			return $container->registerAjax($c);
		});
		$this->container['post_type'] = $this->container->factory(function($c) use ($container) {
			return $container->registerPostType($c);
		});
		$this->container['request'] = $this->container->factory(function($c) use ($container) {
			return $container->registerRequest($c);
		});
	}

	/**
	 * Registers the Twig Loader
	 * @return Twig_Loader_Filesystem
	 */
	protected function registerTwigLoader($c) {
		return new \Twig_Loader_Filesystem($this->framework->getPath() . 'resources/views/');
	}

	/**
	 * Register the Twig Enviroment
	 * @return Twig_Enviroment
	 */
	protected function registerTwigEnviroment($c) {
		return new \Twig_Environment($c['twig_loader'], array(
			'cache'       => WP_CONTENT_DIR . '/.twig_cache/',
			'auto_reload' => true,
			'debug'       => defined('WP_DEBUG') ? WP_DEBUG : false
		));
	}

	/**
	 * Register the Admin Router
	 * @return Anunatak\Framework\Router\AdminRouter
	 */
	protected function registerAdminRouter($c) {
		return new \Anunatak\Framework\Http\Router\BackEnd($this->framework);
	}

	/**
	 * Register the Public Router
	 * @return Anunatak\Framework\Router\PublicRouter
	 */
	protected function registerPublicRouter($c) {
		return new \Anunatak\Framework\Http\Router\FrontEnd($this->framework);
	}

	/**
	 * Register the List Table
	 * @return Anunatak\Framework\ListTable
	 */
	protected function registerListTable($c) {
		if(!class_exists('WP_List_table')) {
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
		return new \Anunatak\Framework\Content\ListTable($this->framework);
	}

	/**
	 * Register the Ajax
	 * @return Anunatak\Framework\Ajax
	 */
	protected function registerAjax($c) {
		return new \Anunatak\Framework\Http\Ajax($this->framework);
	}

	/**
	 * Register the Post Type module
	 * @return Anunatak\Framework\PostType
	 */
	protected function registerPostType($c) {
		return new \Anunatak\Framework\Content\PostType($this->framework);
	}

	/**
	 * Register the Request
	 * @return Anunatak\Framework\PostType
	 */
	protected function registerRequest($c) {
		return \Symfony\Component\HttpFoundation\Request::createFromGlobals();
	}

	/**
	 * Fetch a module from the container
	 * @param  string $module Name of the module
	 * @return void
	 */
	public function make($module) {
		if( isset( $this->container[$module] ) ) {
			return $this->container[$module];
		}
		return null;
	}


}