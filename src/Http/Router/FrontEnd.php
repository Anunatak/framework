<?php

namespace Anunatak\Framework\Http\Router;

use Anunatak\Framework\Fake\WP_Post;
use Anunatak\Framework\Framework;

/**
 * Routing
 * Creates a fake page for GET-requests, and appends content to it.
 * Processes POST-requests and halts further execution, meaning you have to redirect after your request.
 */
class FrontEnd {

	/**
	 * Holds the Framework
	 */
	protected $framework;

	/**
	 * Contains all defined routes
	 * @var array
	 */
	protected $routes = array();

	/**
	 * The current loaded route
	 * @var array
	 */
	protected $current_route = array();

	/**
	 * Symfony RouteCollection
	 * @var Router
	 */
	private $router;

	/**
	 * Twig Instance
	 * @var Router
	 */
	protected $twig;

	/**
	 * Load up WordPress-actions
	 */
	public function __construct(Framework $framework) {
		$this->framework = $framework;
	}

	public function init() {
		add_action('wp', array($this, 'load_routes'), 10);
		add_filter('the_content', array($this, 'load_content'), 20, 1);

		// instantiate altorouter
		$this->router = new \AltoRouter();

		$this->twig = $this->framework->make('twig');
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

	public function set_title($title) {
		$this->current_route['title'] = $title;

		return $title;
	}

	/**
	 * Loads and executes routes
	 * @return void
	 */
	public function load_routes() {
		global $wp_query;

		/**
		 * Adds new routes
		 * @param Anunatak\AnunaFramework\Router $this Instance of the Router class
		 */
		do_action( 'anunaframework_routes', $this );

		// get the matched route if any
		if($this->match_route()) {

			// load the title
			$this->load_title();

			// get current route
			$route = $this->current_route;
			if(($route['method'] === 'GET') && ($_SERVER['REQUEST_METHOD'] === 'GET')) {
				// set up the fake page
				$post = $this->setup_page($route);

				// modify the wp query
				$wp_query->queried_object = $post;
				$wp_query->post = $post;
				$wp_query->found_posts = 1;
				$wp_query->post_count = 1;
				$wp_query->max_num_pages = 1;
				$wp_query->is_single = false;
				$wp_query->is_page = true;
				$wp_query->is_404 = false;
				$wp_query->is_posts_page = 1;
				$wp_query->posts = array($post);
				$wp_query->is_post = false;
				$wp_query->page = true;
				status_header(200);
			} elseif(($route['method'] === 'POST') && ($_SERVER['REQUEST_METHOD'] === 'POST')) {
				// for POST requests halt all execution
				die($this->handle_post());
			}
		}
	}

	/**
	 * Add content to the page
	 * @param  string $content The content
	 * @return string
	 */
	public function load_content($content) {
		if($this->match_route()) {
			$params = $this->current_route['parameters'];
			$params['router'] = $this;
			$content = call_user_func_array($this->current_route['function'], $params);
		}
		return $content;
	}

	/**
	 * Add title to the page
	 * @param  string $title The title
	 * @return string
	 */
	public function load_title() {
		if($this->match_route()) {
			$params = $this->current_route['parameters'];
			$params['router'] = $this;
			ob_start();
			$content = call_user_func_array($this->current_route['function'], $params);
			$contents = ob_get_contents();
			$title = $this->current_route['title'];
		}
	}

	/**
	 * Handles a post route
	 * @return mixed
	 */
	private function handle_post() {
		return call_user_func($this->current_route['function'], $_POST);
	}

	/**
	 * Sets up a fake page
	 * @param  array $route The route to set up a page for
	 * @return Anunatak\AnunaFramework\Fakes\WP_Post
	 */
	private function setup_page($route) {
		$post = new WP_Post;
		$post->ID = -99;
		$post->post_title = $route['title'];
		$post->post_type ='page';
		$post->comment_status = 'closed';
		$post->ping_status = 'closed';
		return $post;
	}

	/**
	 * Fetches the current route
	 * @return string
	 */
	private function get_current_route() {
		global $wp;
		return $wp->request;
	}

	/**
	 * Returns the matched route
	 * @return mixed The matched route or false if none matched
	 */
	private function match_route() {
		$match = $this->router->match();
		if($match) {
			$route = $this->routes[$match['name']];
			$route['parameters'] = $match['params'];
			$this->current_route = $route;
			return $route;
		}
	}

	/**
	 * Adds a new route
	 * @param string $method The HTTP method (GET/POST)
	 * @param string $route The route
	 * @param mixed $title Either the title of the page or a closure (same as $function)
	 * @param Closure $function The code to run
	 * @return void
	 */
	public function add($method, $route, $title, $function = null) {
		if($function === null && ($title instanceof \Closure)) {
			$function = $title;
			$title = null;
		}
		$name = $this->sanitize_function($route);
		$this->routes[$name] = array(
			'method' => strtoupper($method),
			'route' => $route,
			'title' => $title,
			'function' => $function,
		);

		$this->router->map(strtoupper($method), $route, $function, $name);
	}

	/**
	 * Santize a string to be used as route name
	 * @param  string $string String to be sanitized
	 * @return string Sanitized string
	 */
	private function sanitize_function($string) {
		$pattern = '/[^a-zA-Z0-9]/';
		return preg_replace($pattern, '', (string) $string);
	}

	/**
	 * Adds a new GET route
	 * @param string $route The route
	 * @param mixed $title Either the title of the page or a closure (same as $function)
	 * @param Closure $function The code to run
	 * @return void
	 */
	public function get($route, $title, $function = null) {
		return $this->add('GET', $route, $title, $function);
	}

	/**
	 * Adds a new POST route
	 * @param string $route The route
	 * @param mixed $title Either the title of the page or a closure (same as $function)
	 * @param Closure $function The code to run
	 * @return void
	 */
	public function post($route, $title, $function = null) {
		return $this->add('POST', $route, $title, $function);
	}

}