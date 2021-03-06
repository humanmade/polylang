<?php

class Settings_CPT_Test extends PLL_UnitTestCase {

	function setUp() {
		parent::setUp();

		// De-activate cache for translated post types and taxonomies
		self::$polylang->model->cache = $this->getMockBuilder( 'PLL_Cache' )->getMock();
		self::$polylang->model->cache->method( 'get' )->willReturn( false );

		self::$polylang->options['post_types'] = [];
		self::$polylang->options['taxonomies'] = [];
	}

	function tearDown() {
		parent::tearDown();

		_unregister_post_type( 'cpt' );
		_unregister_taxonomy( 'tax' );
	}

	function filter_translated_post_type_in_settings( $post_types, $is_settings ) {
		$post_types[] = 'cpt';
		return $post_types;
	}

	function filter_untranslated_post_type_in_settings( $post_types, $is_settings ) {
		if ( $is_settings ) {
			$post_types[] = 'cpt';
		}
		return $post_types;
	}

	function filter_translated_post_type_not_in_settings( $post_types, $is_settings ) {
		if ( $is_settings ) {
			$k = array_search( 'cpt', $post_types );
			unset( $post_types[ $k ] );
		} else {
			$post_types[] = 'cpt';
		}
		return $post_types;
	}

	function filter_translated_taxonomy_in_settings( $taxonomies, $is_settings ) {
		$taxonomies[] = 'tax';
		return $taxonomies;
	}

	function filter_untranslated_taxonomy_in_settings( $taxonomies, $is_settings ) {
		if ( $is_settings ) {
			$taxonomies[] = 'tax';
		}
		return $taxonomies;
	}

	function filter_translated_taxonomy_not_in_settings( $taxonomies, $is_settings ) {
		if ( $is_settings ) {
			$k = array_search( 'tax', $taxonomies );
			unset( $taxonomies[ $k ] );
		} else {
			$taxonomies[] = 'tax';
		}
		return $taxonomies;
	}

	function test_no_cpt_no_tax() {
		$module = new PLL_Settings_CPT( self::$polylang );
		$this->assertEmpty( $module->get_form() );
	}

	function test_untranslated_public_post_type() {
		register_post_type(
			'cpt', [
				'public' => true,
				'label' => 'CPT',
			]
		);
		$module = new PLL_Settings_CPT( self::$polylang );

		$doc = new DomDocument();
		$doc->loadHTML( $module->get_form() );
		$xpath = new DOMXpath( $doc );

		$input = $xpath->query( '//input[@name="post_types[cpt]"]' );
		$this->assertEmpty( $input->item( 0 )->getAttribute( 'checked' ) );
		$this->assertEmpty( $input->item( 0 )->getAttribute( 'disabled' ) );
	}

	function test_translated_public_post_type() {
		self::$polylang->options['post_types'] = [ 'cpt' ];
		register_post_type(
			'cpt', [
				'public' => true,
				'label' => 'CPT',
			]
		);
		$module = new PLL_Settings_CPT( self::$polylang );

		$doc = new DomDocument();
		$doc->loadHTML( $module->get_form() );
		$xpath = new DOMXpath( $doc );

		$input = $xpath->query( '//input[@name="post_types[cpt]"]' );
		$this->assertEquals( 'checked', $input->item( 0 )->getAttribute( 'checked' ) );
		$this->assertEmpty( $input->item( 0 )->getAttribute( 'disabled' ) );
	}

	function test_programmatically_translated_public_post_type() {
		add_filter( 'pll_get_post_types', [ $this, 'filter_translated_post_type_in_settings' ], 10, 2 );
		register_post_type(
			'cpt', [
				'public' => true,
				'label' => 'CPT',
			]
		);
		$module = new PLL_Settings_CPT( self::$polylang );

		$doc = new DomDocument();
		$doc->loadHTML( $module->get_form() );
		$xpath = new DOMXpath( $doc );

		$input = $xpath->query( '//input[@name="post_types[cpt]"]' );
		$this->assertEquals( 'checked', $input->item( 0 )->getAttribute( 'checked' ) );
		$this->assertEquals( 'disabled', $input->item( 0 )->getAttribute( 'disabled' ) );
	}

	function test_untranslated_private_post_type() {
		register_post_type(
			'cpt', [
				'public' => false,
				'label' => 'CPT',
			]
		);
		$module = new PLL_Settings_CPT( self::$polylang );
		$this->assertEmpty( $module->get_form() );
	}

	function test_translated_private_post_type() {
		self::$polylang->options['post_types'] = [ 'cpt' ];
		register_post_type(
			'cpt', [
				'public' => false,
				'label' => 'CPT',
			]
		);
		$module = new PLL_Settings_CPT( self::$polylang );
		$this->assertEmpty( $module->get_form() );
	}

	function test_programmatically_translated_private_post_type() {
		add_filter( 'pll_get_post_types', [ $this, 'filter_translated_post_type_not_in_settings' ], 10, 2 );
		register_post_type(
			'cpt', [
				'public' => false,
				'label' => 'CPT',
			]
		);
		$module = new PLL_Settings_CPT( self::$polylang );
		$this->assertEmpty( $module->get_form() );
	}

	function test_untranslated_private_post_type_in_settings() {
		add_filter( 'pll_get_post_types', [ $this, 'filter_untranslated_post_type_in_settings' ], 10, 2 );
		register_post_type(
			'cpt', [
				'public' => false,
				'label' => 'CPT',
			]
		);
		$module = new PLL_Settings_CPT( self::$polylang );

		$doc = new DomDocument();
		$doc->loadHTML( $module->get_form() );
		$xpath = new DOMXpath( $doc );

		$input = $xpath->query( '//input[@name="post_types[cpt]"]' );
		$this->assertEmpty( $input->item( 0 )->getAttribute( 'checked' ) );
		$this->assertEmpty( $input->item( 0 )->getAttribute( 'disabled' ) );
	}

	function test_translated_private_post_type_in_settings() {
		self::$polylang->options['post_types'] = [ 'cpt' ];
		add_filter( 'pll_get_post_types', [ $this, 'filter_untranslated_post_type_in_settings' ], 10, 2 );
		register_post_type(
			'cpt', [
				'public' => false,
				'label' => 'CPT',
			]
		);
		$module = new PLL_Settings_CPT( self::$polylang );

		$doc = new DomDocument();
		$doc->loadHTML( $module->get_form() );
		$xpath = new DOMXpath( $doc );

		$input = $xpath->query( '//input[@name="post_types[cpt]"]' );
		$this->assertEquals( 'checked', $input->item( 0 )->getAttribute( 'checked' ) );
		$this->assertEmpty( $input->item( 0 )->getAttribute( 'disabled' ) );
	}

	function test_untranslated_public_taxonomy() {
		register_taxonomy( 'tax', [ 'post' ], [ 'public' => true ] );
		$module = new PLL_Settings_CPT( self::$polylang );

		$doc = new DomDocument();
		$doc->loadHTML( $module->get_form() );
		$xpath = new DOMXpath( $doc );

		$input = $xpath->query( '//input[@name="taxonomies[tax]"]' );
		$this->assertEmpty( $input->item( 0 )->getAttribute( 'checked' ) );
		$this->assertEmpty( $input->item( 0 )->getAttribute( 'disabled' ) );
	}

	function test_translated_public_taxonomy() {
		self::$polylang->options['taxonomies'] = [ 'tax' ];
		register_taxonomy( 'tax', [ 'post' ], [ 'public' => true ] );
		$module = new PLL_Settings_CPT( self::$polylang );

		$doc = new DomDocument();
		$doc->loadHTML( $module->get_form() );
		$xpath = new DOMXpath( $doc );

		$input = $xpath->query( '//input[@name="taxonomies[tax]"]' );
		$this->assertEquals( 'checked', $input->item( 0 )->getAttribute( 'checked' ) );
		$this->assertEmpty( $input->item( 0 )->getAttribute( 'disabled' ) );
	}

	function test_programmatically_translated_public_taxonomy() {
		add_filter( 'pll_get_taxonomies', [ $this, 'filter_translated_taxonomy_in_settings' ], 10, 2 );
		register_taxonomy( 'tax', [ 'post' ], [ 'public' => true ] );
		$module = new PLL_Settings_CPT( self::$polylang );

		$doc = new DomDocument();
		$doc->loadHTML( $module->get_form() );
		$xpath = new DOMXpath( $doc );

		$input = $xpath->query( '//input[@name="taxonomies[tax]"]' );
		$this->assertEquals( 'checked', $input->item( 0 )->getAttribute( 'checked' ) );
		$this->assertEquals( 'disabled', $input->item( 0 )->getAttribute( 'disabled' ) );
	}

	function test_untranslated_private_taxonomy() {
		register_taxonomy( 'tax', [ 'post' ], [ 'public' => false ] );
		$module = new PLL_Settings_CPT( self::$polylang );
		$this->assertEmpty( $module->get_form() );
	}

	function test_translated_private_taxonomy() {
		self::$polylang->options['taxonomies'] = [ 'tax' ];
		register_taxonomy( 'tax', [ 'post' ], [ 'public' => false ] );
		$module = new PLL_Settings_CPT( self::$polylang );

		$this->assertEmpty( $module->get_form() );
	}

	function test_programmatically_translated_private_taxonomy() {
		add_filter( 'pll_get_taxonomies', [ $this, 'filter_translated_taxonomy_not_in_settings' ], 10, 2 );
		register_taxonomy( 'tax', [ 'post' ], [ 'public' => false ] );
		$module = new PLL_Settings_CPT( self::$polylang );
		$this->assertEmpty( $module->get_form() );
	}

	function test_untranslated_private_taxonomy_in_settings() {
		add_filter( 'pll_get_taxonomies', [ $this, 'filter_untranslated_taxonomy_in_settings' ], 10, 2 );
		register_taxonomy( 'tax', [ 'post' ], [ 'public' => false ] );
		$module = new PLL_Settings_CPT( self::$polylang );

		$doc = new DomDocument();
		$doc->loadHTML( $module->get_form() );
		$xpath = new DOMXpath( $doc );

		$input = $xpath->query( '//input[@name="taxonomies[tax]"]' );
		$this->assertEmpty( $input->item( 0 )->getAttribute( 'checked' ) );
		$this->assertEmpty( $input->item( 0 )->getAttribute( 'disabled' ) );
	}

	function test_translated_private_taxonomy_in_settings() {
		self::$polylang->options['taxonomies'] = [ 'tax' ];
		add_filter( 'pll_get_taxonomies', [ $this, 'filter_untranslated_taxonomy_in_settings' ], 10, 2 );
		register_taxonomy( 'tax', [ 'post' ], [ 'public' => false ] );
		$module = new PLL_Settings_CPT( self::$polylang );

		$doc = new DomDocument();
		$doc->loadHTML( $module->get_form() );
		$xpath = new DOMXpath( $doc );

		$input = $xpath->query( '//input[@name="taxonomies[tax]"]' );
		$this->assertEquals( 'checked', $input->item( 0 )->getAttribute( 'checked' ) );
		$this->assertEmpty( $input->item( 0 )->getAttribute( 'disabled' ) );
	}

}
