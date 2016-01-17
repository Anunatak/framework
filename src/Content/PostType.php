<?php

namespace Anunatak\Framework\Content;

use Anunatak\Framework\Framework;

class PostType {

	/**
	 * Holds the Framework
	 */
	protected $framework;

	/**
	 * Constructor
	 */
	public function __construct(Framework $framework) {
		$this->framework = $framework;
	}

	/**
     * Create a custom post type.
     *
     * @param mixed $post_type_names The name(s) of the post type, accepts (post type name, slug, plural, singular).
     * @param array $options User submitted options.
     */
	public function create( $post_type_names, $options = array() ) {
		$cpt = new \CPT( $post_type_names, $options = array() );
		$cpt->set_textdomain( $this->framework->getTextDomain() );
		return $cpt;
	}


}