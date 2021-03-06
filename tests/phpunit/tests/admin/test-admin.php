<?php

class Admin_Test extends PLL_UnitTestCase {

	static function wpSetUpBeforeClass() {
		parent::wpSetUpBeforeClass();

		self::create_language( 'en_US' );
		self::create_language( 'fr_FR' );
	}

	function test_admin_bar_menu() {
		// This test breaks if DOING_AJAX is defined due to is_admin_bar_showing() testing directly the constant (WP 4.9.1)
		global $wp_admin_bar;
		add_filter( 'show_admin_bar', '__return_true' ); // Make sure to show admin bar

		$this->go_to( home_url( '/wp-admin/edit.php' ) );
		self::$polylang = new PLL_Admin( self::$polylang->links_model );
		self::$polylang->init();

		_wp_admin_bar_init();
		do_action_ref_array( 'admin_bar_menu', [ &$wp_admin_bar ] );

		$languages = $wp_admin_bar->get_node( 'languages' );
		$this->assertEmpty( $languages->parent );
		$this->assertEquals( '/wp-admin/edit.php?lang=all', $languages->href );

		$en = $wp_admin_bar->get_node( 'en' );
		$this->assertEquals( 'languages', $en->parent );
		$this->assertEquals( '/wp-admin/edit.php?lang=en', $en->href );

		$fr = $wp_admin_bar->get_node( 'fr' );
		$this->assertEquals( 'languages', $fr->parent );
		$this->assertEquals( '/wp-admin/edit.php?lang=fr', $fr->href );
	}

	function _test_scripts( $scripts ) {
		self::$polylang        = new PLL_Admin( self::$polylang->links_model );
		self::$polylang->links = new PLL_Admin_Links( self::$polylang );

		$GLOBALS['wp_styles']  = new WP_Styles();
		$GLOBALS['wp_scripts'] = new WP_Scripts();
		wp_default_scripts( $GLOBALS['wp_scripts'] );

		do_action( 'admin_enqueue_scripts' );

		ob_start();
		do_action( 'admin_print_scripts' );
		$head = ob_get_clean();

		ob_start();
		do_action( 'admin_print_footer_scripts' );
		$footer = ob_get_clean();

		$test = strpos( $footer, 'pll_ajax_backend' );
		in_array( 'pll_ajax_backend', $scripts, true ) ? $this->assertNotFalse( $test ) : $this->assertFalse( $test );

		foreach ( [ 'media', 'term' ] as $key ) {
			$test = strpos( $footer, plugins_url( "/js/$key.min.js", POLYLANG_FILE ) );
			in_array( $key, $scripts, true ) ? $this->assertNotFalse( $test ) : $this->assertFalse( $test );
		}

		foreach ( [ 'post', 'user' ] as $key ) {
			$test = strpos( $head, plugins_url( "/js/$key.min.js", POLYLANG_FILE ) );
			in_array( $key, $scripts, true ) ? $this->assertNotFalse( $test ) : $this->assertFalse( $test );
		}

		$test = strpos( $footer, 'polylang_admin-css' );
		in_array( 'css', $scripts, true ) ? $this->assertNotFalse( $test ) : $this->assertFalse( $test );

		unset( $GLOBALS['hook_suffix'], $GLOBALS['current_screen'], $GLOBALS['wp_scripts'], $GLOBALS['wp_styles'] );
	}

	function test_scripts_in_post_list_table() {
		$GLOBALS['hook_suffix'] = 'edit.php';
		set_current_screen( 'edit' );

		$scripts = [ 'pll_ajax_backend', 'post', 'css' ];
		$this->_test_scripts( $scripts );
	}

	function test_scripts_in_edit_post() {
		$GLOBALS['hook_suffix'] = 'post.php';
		set_current_screen( 'post' );

		$scripts = [ 'pll_ajax_backend', 'post', 'css' ];
		$this->_test_scripts( $scripts );
	}

	function test_scripts_in_media_list_table() {
		$GLOBALS['hook_suffix'] = 'upload.php';
		set_current_screen( 'upload' );

		$scripts = [ 'pll_ajax_backend', 'media', 'css' ];
		$this->_test_scripts( $scripts );
	}

	function test_scripts_in_terms_list_table() {
		$GLOBALS['hook_suffix'] = 'edit-tags.php';
		set_current_screen( 'edit-tags' );

		$scripts = [ 'pll_ajax_backend', 'term', 'css' ];
		$this->_test_scripts( $scripts );
	}

	function test_scripts_in_edit_term() {
		$GLOBALS['hook_suffix'] = 'term.php';
		set_current_screen( 'term' );

		$scripts = [ 'pll_ajax_backend', 'term', 'css' ];
		$this->_test_scripts( $scripts );
	}

	function test_scripts_in_user_profile() {
		$GLOBALS['hook_suffix'] = 'profile.php';
		set_current_screen( 'profile' );

		$scripts = [ 'pll_ajax_backend', 'user', 'css' ];
		$this->_test_scripts( $scripts );
	}
}
