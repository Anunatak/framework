<?php

namespace Anunatak\Framework\Http\Router;

use Anunatak\Framework\Framework;
/**
 * Admin Routing
 * Used to create admin pages via controllers
 */
class BackEnd {

	/**
	 * Holds the Framework
	 */
	protected $framework;

	/**
	 * Holds all the controllers
	 * @var array
	 */
	public $controllers = array();

	/**
	 * Set up everything
	 */
	public function __construct(Framework $framework) {
		$this->framework = $framework;
	}

	public function init() {
		add_action('init', array($this, 'load_routes'));
	}

	/**
	 * Load up all routes
	 * @return mixed
	 */
	public function load_routes() {
		if(!is_admin()) {
			return false;
		}
		do_action('anunaframework_admin_routes', $this);
	}

	/**
	 * Add a new admin controller
	 * @param string $controller Controller Name
	 */
	public function add($controller) {
		$namespace = $this->framework->getNamespace();
		$controller = $namespace . '\\' . $controller;
		$this->controllers[] = new $controller($this, $this->framework);
	}



}