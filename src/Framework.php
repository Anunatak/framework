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
	private $options = array();

	/**
	 * App Container
	 * @var Pimple\Container
	 */
	private $container;

	/**
	 * Initialize the framework
	 * @param  array  $options Options
	 * @return void
	 */
	public function __construct(array $options)
	{
		$this->options = array_merge(array(
			'text_domain' => 'anunaframework',
			'namespace'   => __NAMESPACE__,
			'file'        => __FILE__,
			'dir'         => __DIR__,
			'version'     => '1.0.0',
			'slug'        => 'anunaframework'
		), $options);

		$this->container = new Container( $this );
	}

	/**
	 * Get the app container
	 * @return Pimple\Container
	 */
	public function container() {
		return $this->container;
	}

	/**
	 * Get the textdomain
	 * @return string
	 */
	public function getTextDomain()
	{
		return $this->options['text_domain'];
	}

	/**
	 * Get the slug
	 * @return string
	 */
	public function getSlug()
	{
		return $this->options['slug'];
	}

	/**
	 * Get the version
	 * @return string
	 */
	public function getVersion()
	{
		return $this->options['version'];
	}

	/**
	 * Get the plugin URL
	 * @return string
	 */
	public function getUrl()
	{
		$url  = plugins_url( '/' );
		$path = basename($this->getPath());
		return trailingslashit($url . $path);
	}

	/**
	 * Get the plugin path
	 * @return string
	 */
	public function getPath()
	{
		$file = $this->options['file'];
		$dir  = $this->options['dir'];
		$path = trailingslashit( str_replace( basename( dirname( $file ) ), '', $dir ) );
		return $path;
	}

	/**
	 * Get the plugin namespace
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->options['namespace'];
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
	public function make($module) {
		$instance = $this->container->make($module);
		return $instance;
	}

}