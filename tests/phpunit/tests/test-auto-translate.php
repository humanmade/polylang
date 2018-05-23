<?php

class Auto_Translate_Test extends PLL_UnitTestCase {

	static function wpSetUpBeforeClass() {
		parent::wpSetUpBeforeClass();

		self::create_language( 'en_US' );
		self::create_language( 'fr_FR' );
	}

	function setUp() {
		parent::setUp();

		self::$polylang->options['post_types'] = [
			'trcpt' => 'trcpt',
		];
		self::$polylang->options['taxonomies'] = [
			'trtax' => 'trtax',
		];

		register_post_type(
			'trcpt', [
				'public' => true,
				'has_archive' => true,
			]
		); // translated custom post type with archives
		register_taxonomy( 'trtax', 'trcpt' ); // translated custom tax

		self::$polylang->auto_translate = new PLL_Frontend_Auto_Translate( self::$polylang );
		self::$polylang->curlang        = self::$polylang->model->get_language( 'fr' );
		self::$polylang->filters        = new PLL_Frontend_Filters( self::$polylang );
	}

	function tearDown() {
		parent::tearDown();

		_unregister_post_type( 'trcpt' );
		_unregister_taxonomy( 'trtax' );
	}

	function test_category() {
		$fr = $this->factory->term->create(
			[
				'taxonomy' => 'category',
				'name' => 'essai',
			]
		);
		self::$polylang->model->term->set_language( $fr, 'fr' );

		$en = $this->factory->term->create(
			[
				'taxonomy' => 'category',
				'name' => 'test',
			]
		);
		self::$polylang->model->term->set_language( $en, 'en' );
		self::$polylang->model->term->save_translations( $en, compact( 'en', 'fr' ) );

		$post_fr = $this->factory->post->create();
		self::$polylang->model->post->set_language( $post_fr, 'fr' );
		wp_set_post_terms( $post_fr, [ $fr ], 'category' );

		$post_en = $this->factory->post->create();
		self::$polylang->model->post->set_language( $post_en, 'en' );
		wp_set_post_terms( $post_en, [ $en ], 'category' );

		$this->assertEquals( [ get_post( $post_fr ) ], get_posts( [ 'cat' => $en ] ) );
		$this->assertEquals( [ get_post( $post_fr ) ], get_posts( [ 'category_name' => 'test' ] ) );
		$this->assertEquals( [ get_post( $post_fr ) ], get_posts( [ 'category__in' => [ $en ] ] ) );
	}

	function test_tag() {
		$fr = $this->factory->term->create(
			[
				'taxonomy' => 'post_tag',
				'name' => 'essai',
			]
		);
		self::$polylang->model->term->set_language( $fr, 'fr' );

		$en = $this->factory->term->create(
			[
				'taxonomy' => 'post_tag',
				'name' => 'test',
			]
		);
		self::$polylang->model->term->set_language( $en, 'en' );
		self::$polylang->model->term->save_translations( $en, compact( 'en', 'fr' ) );

		$fr = $this->factory->term->create(
			[
				'taxonomy' => 'post_tag',
				'name' => 'essai2',
			]
		);
		self::$polylang->model->term->set_language( $fr, 'fr' );

		$en = $this->factory->term->create(
			[
				'taxonomy' => 'post_tag',
				'name' => 'test2',
			]
		);
		self::$polylang->model->term->set_language( $en, 'en' );
		self::$polylang->model->term->save_translations( $en, compact( 'en', 'fr' ) );

		$post_fr = $this->factory->post->create( [ 'tags_input' => [ 'essai', 'essai2' ] ] );
		self::$polylang->model->post->set_language( $post_fr, 'fr' );

		$post_en = $this->factory->post->create( [ 'tags_input' => [ 'test', 'test2' ] ] );
		self::$polylang->model->post->set_language( $post_en, 'en' );

		$this->assertEquals( [ get_post( $post_fr ) ], get_posts( [ 'tag_id' => $en ] ) );
		$this->assertEquals( [ get_post( $post_fr ) ], get_posts( [ 'tag' => 'test' ] ) );
		$this->assertEquals( [ get_post( $post_fr ) ], get_posts( [ 'tag' => 'test,test2' ] ) );
		$this->assertEquals( [ get_post( $post_fr ) ], get_posts( [ 'tag' => 'test+test2' ] ) );
		$this->assertEquals( [ get_post( $post_fr ) ], get_posts( [ 'tag_slug__in' => [ 'test' ] ] ) );
	}

	function test_custom_tax() {
		$term_fr = $fr = $this->factory->term->create(
			[
				'taxonomy' => 'trtax',
				'name' => 'essai',
			]
		);
		self::$polylang->model->term->set_language( $fr, 'fr' );

		$term_en = $en = $this->factory->term->create(
			[
				'taxonomy' => 'trtax',
				'name' => 'test',
			]
		);
		self::$polylang->model->term->set_language( $en, 'en' );
		self::$polylang->model->term->save_translations( $en, compact( 'en', 'fr' ) );

		$fr = $this->factory->term->create(
			[
				'taxonomy' => 'trtax',
				'name' => 'essai2',
			]
		);
		self::$polylang->model->term->set_language( $fr, 'fr' );

		$en = $this->factory->term->create(
			[
				'taxonomy' => 'trtax',
				'name' => 'test2',
			]
		);
		self::$polylang->model->term->set_language( $en, 'en' );
		self::$polylang->model->term->save_translations( $en, compact( 'en', 'fr' ) );

		$fr = $this->factory->term->create(
			[
				'taxonomy' => 'trtax',
				'name' => 'essai3',
			]
		);
		self::$polylang->model->term->set_language( $fr, 'fr' );

		$en = $this->factory->term->create(
			[
				'taxonomy' => 'trtax',
				'name' => 'test3',
			]
		);
		self::$polylang->model->term->set_language( $en, 'en' );
		self::$polylang->model->term->save_translations( $en, compact( 'en', 'fr' ) );

		$post_fr = $this->factory->post->create( [ 'post_type' => 'trcpt' ] );
		wp_set_post_terms( $post_fr, [ 'essai', 'essai2' ], 'trtax' ); // don't use 'tax_input' above as we don't pass current_user_can test in wp_insert_post
		self::$polylang->model->post->set_language( $post_fr, 'fr' );

		$post_en = $this->factory->post->create( [ 'post_type' => 'trcpt' ] );
		wp_set_post_terms( $post_en, [ 'test', 'test2' ], 'trtax' ); // don't use 'tax_input' above as we don't pass current_user_can test in wp_insert_post
		self::$polylang->model->post->set_language( $post_en, 'en' );

		// old way
		$this->assertEquals(
			[ get_post( $post_fr ) ], get_posts(
				[
					'post_type' => 'trcpt',
					'trtax' => 'test',
				]
			)
		);
		$this->assertEquals(
			[ get_post( $post_fr ) ], get_posts(
				[
					'post_type' => 'trcpt',
					'trtax' => 'test,test2',
				]
			)
		);
		$this->assertEquals(
			[ get_post( $post_fr ) ], get_posts(
				[
					'post_type' => 'trcpt',
					'trtax' => 'test+test2',
				]
			)
		);

		// tax query
		$args = [
			'post_type' => 'trcpt',
			'tax_query' => [
				[
					'taxonomy' => 'trtax',
					'terms'    => 'test',
					'field'    => 'slug',
				],
			],
		];

		$this->assertEquals( [ get_post( $post_fr ) ], get_posts( $args ) );

		// Nested tax query
		$args  = [
			'post_type' => 'trcpt',
			'tax_query' => [
				'relation' => 'OR',
				[
					'taxonomy' => 'trtax',
					'field'    => 'term_id',
					'terms'    => [ $en ],
				],
				[
					'relation' => 'AND',
					[
						'taxonomy' => 'trtax',
						'field'    => 'term_id',
						'terms'    => [ $term_en ],
					],
					[
						'taxonomy' => 'trtax',
						'field'    => 'slug',
						'terms'    => [ 'test2' ],
					],
				],
			],
		];
		$query = new WP_Query( $args );

		$this->assertEquals( $fr, $query->tax_query->queries[0]['terms'][0] );
		$this->assertEquals( $term_fr, $query->tax_query->queries[1][0]['terms'][0] );
		$this->assertEquals( 'essai2', $query->tax_query->queries[1][1]['terms'][0] );

		// #223
		$args = [
			'post_type' => 'trcpt',
			'lang'      => '',
			'tax_query' => [
				[
					'taxonomy' => 'trtax',
					'terms'    => [ $term_en, $term_fr ],
					'field'    => 'term_id',
				],
			],
		];

		$query = new WP_Query( $args );

		$this->assertEqualSets( [ $post_en, $post_fr ], wp_list_pluck( $query->posts, 'ID' ) );
	}

	function test_post() {
		$en = $this->factory->post->create( [ 'post_title' => 'test' ] );
		self::$polylang->model->post->set_language( $en, 'en' );

		$fr = $this->factory->post->create( [ 'post_title' => 'essai' ] );
		self::$polylang->model->post->set_language( $fr, 'fr' );

		self::$polylang->model->post->save_translations( $en, compact( 'en', 'fr' ) );

		$this->assertEquals( [ get_post( $fr ) ], get_posts( [ 'p' => $en ] ) );
		$this->assertEquals( [ get_post( $fr ) ], get_posts( [ 'name' => 'test' ] ) );
		$this->assertEquals(
			[ get_post( $fr ) ], get_posts(
				[
					'name' => 'test',
					'post_type' => 'post',
				]
			)
		);
		$this->assertEquals(
			[ get_post( $fr ) ], get_posts(
				[
					'name' => 'test',
					'post_type' => 'any',
				]
			)
		);
		$this->assertEquals(
			[ get_post( $fr ) ], get_posts(
				[
					'name' => 'test',
					'post_type' => [ 'post', 'page' ],
				]
			)
		);
		$this->assertEquals( [ get_post( $fr ) ], get_posts( [ 'post__in' => [ $en ] ] ) );
	}

	function test_page() {
		$parent_en = $en = $this->factory->post->create(
			[
				'post_title' => 'test_parent',
				'post_type' => 'page',
			]
		);
		self::$polylang->model->post->set_language( $en, 'en' );

		$parent_fr = $fr = $this->factory->post->create(
			[
				'post_title' => 'essai_parent',
				'post_type' => 'page',
			]
		);
		self::$polylang->model->post->set_language( $fr, 'fr' );

		self::$polylang->model->post->save_translations( $en, compact( 'en', 'fr' ) );

		$en = $this->factory->post->create(
			[
				'post_title' => 'test',
				'post_type' => 'page',
				'post_parent' => $parent_en,
			]
		);
		self::$polylang->model->post->set_language( $en, 'en' );

		$fr = $this->factory->post->create(
			[
				'post_title' => 'essai',
				'post_type' => 'page',
				'post_parent' => $parent_fr,
			]
		);
		self::$polylang->model->post->set_language( $fr, 'fr' );

		self::$polylang->model->post->save_translations( $en, compact( 'en', 'fr' ) );

		$query = new WP_Query( [ 'page_id' => $en ] );
		$this->assertEquals( [ get_post( $fr ) ], $query->posts );
		$query = new WP_Query( [ 'pagename' => 'test_parent' ] ); // Top page
		$this->assertEquals( [ get_post( $parent_fr ) ], $query->posts );
		$query = new WP_Query( [ 'pagename' => 'test_parent/test' ] ); // Child page
		$this->assertEquals( [ get_post( $fr ) ], $query->posts );
		$query = new WP_Query(
			[
				'post_parent' => $parent_en,
				'post_type' => 'page',
			]
		);
		$this->assertEquals( [ get_post( $fr ) ], $query->posts );
		$query = new WP_Query(
			[
				'post_parent__in' => [ $parent_en ],
				'post_type' => 'page',
			]
		);
		$this->assertEquals( [ get_post( $fr ) ], $query->posts );
	}

	function test_get_terms() {
		$fr = $this->factory->term->create(
			[
				'taxonomy' => 'category',
				'name' => 'essai',
			]
		);
		self::$polylang->model->term->set_language( $fr, 'fr' );

		$en = $this->factory->term->create(
			[
				'taxonomy' => 'category',
				'name' => 'test',
			]
		);
		self::$polylang->model->term->set_language( $en, 'en' );
		self::$polylang->model->term->save_translations( $en, compact( 'en', 'fr' ) );

		$expected = get_term( $fr, 'category' );
		$terms    = get_terms(
			'category', [
				'hide_empty' => 0,
				'include' => [ $en ],
			]
		);
		$this->assertEquals( [ $expected->term_id ], wp_list_pluck( $terms, 'term_id' ) );

		if ( version_compare( $GLOBALS['wp_version'], '4.5', '>=' ) ) {
			// The taxonomy parameter is now optional
			$terms = get_terms(
				[
					'hide_empty' => 0,
					'include' => [ $en ],
				]
			);
			$this->assertEquals( [ $expected->term_id ], wp_list_pluck( $terms, 'term_id' ) );
		}

		$expected = get_term( $en, 'category' );
		$terms    = get_terms(
			'category', [
				'hide_empty' => 0,
				'include' => [ $en ],
				'lang' => '',
			]
		);
		$this->assertEquals( [ $expected->term_id ], wp_list_pluck( $terms, 'term_id' ) );
	}
}
