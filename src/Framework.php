<?php

namespace Anunatak\Framework;

/**
 * The main framework class
 */
final class Framework {

	/**
	 * Holds the options
	 * @var array
	 */
	private static $options = array();

	/**
	 * Initialize the framework
	 * @param  array  $options Options
	 * @return void
	 */
	public static function initialize(array $options)
	{
		self::$options = array_merge(array(
			'text_domain' => 'anunaframework',
			'namespace'   => __NAMESPACE__,
			'file'        => __FILE__,
			'dir'         => __DIR__,
			'version'     => '1.0.0',
			'slug'        => 'anunaframework'
		), $options);
	}

	/**
	 * Get the textdomain
	 * @return string
	 */
	public static function getTextDomain()
	{
		return static::$options['text_domain'];
	}

	/**
	 * Get the slug
	 * @return string
	 */
	public static function getSlug()
	{
		return static::$options['slug'];
	}

	/**
	 * Get the version
	 * @return string
	 */
	public static function getVersion()
	{
		return static::$options['version'];
	}

	/**
	 * Get the plugin URL
	 * @return string
	 */
	public static function getUrl()
	{
		$url = plugins_url( '/' );
		$path = basename(self::getPath());

		return trailingslashit($url . $path);
	}

	/**
	 * Get the plugin path
	 * @return string
	 */
	public static function getPath()
	{
		$file = static::$options['file'];
		$dir = static::$options['dir'];
		$path = trailingslashit( str_replace( basename( dirname( $file ) ), '', $dir ) );
		return $path;
	}

	/**
	 * Get the plugin namespace
	 * @return string
	 */
	public static function getNamespace()
	{
		return static::$options['namespace'];
	}

	/**
	 * Instantiates framework and plugin modules
	 *
	 * Kind of like an app container
	 *
	 * @param  string  $module   The module to load
	 * @param  array   $args     Constructor arguments
	 * @param  boolean $isGlobal If the module is part of the framework
	 * @return object            An instance of the module
	 */
	public static function make($module, $args = array(), $isGlobal = true) {
		$module = ucfirst($module); // make sure the module is upper case first

		// for the list table module we'll need to load one WP dependency
		if($module === 'ListTable') {
			if( !class_exists( 'WP_List_Table' ) )
				require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}

		if($isGlobal) {
			$namespace = __NAMESPACE__;
		}
		else {
			$namespace = self::getNamespace();
		}

		$class = $namespace . '\\' . $module;
	 	if (!empty($args)) {
			$rc = new \ReflectionClass($name);
			$instance = $rc->newInstanceArgs($args);
		}
		else {
			$instance = new $class();
		}
		return $instance;
	}

}