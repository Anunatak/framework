<?php

namespace Anunatak\Framework;

/**
 * Admin Routing
 * Used to create admin pages via controllers
 */
class AdminRouter {

	/**
	 * Holds all the controllers
	 * @var array
	 */
	public $controllers = array();

	/**
	 * Set up everything
	 */
	public function __construct()
	{
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
		$this->controllers[] = Framework::make($controller, array(), false);
	}



}