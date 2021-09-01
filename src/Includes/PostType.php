<?php

namespace Tangram\Includes;

/**
 * Register custom post type
 */
class PostType
{

	public $set;

	/**
	 * Create and register new post type 'tangram_project'
	 * Also, register taxonomy `project_categories`
	 * @param array $set
	 */
	public  function __construct(array $set)
	{
		$this->set = $set;
		add_action('init', [$this, 'register_taxonomy']);
		add_action('init', [$this, 'register_post_type']);
		add_action('after_setup_theme', [$this, 'add_image_support']);
		add_filter( 'use_block_editor_for_post_type', [$this, 'disable_gutenberg'], 10, 2 );
		add_action( 'activation_' . $this->set['plugin_name'], [$this, 'register_taxonomy']); // For register taxonomy when this plugin activating
	}

	public function register_taxonomy(){
		register_taxonomy( 'project_categories', [ 'tangram_project' ], [
			'label'                 => __('Project categories', $this->set['text_domain']),
			'labels'                => [
				'name'              => __('Project categories', $this->set['text_domain']),
				'singular_name'     => __('Project category', $this->set['text_domain']),
				'search_items'      => __('Search categories', $this->set['text_domain']),
				'all_items'         => __('All Project categories', $this->set['text_domain']),
				'view_item '        => __('View Project category', $this->set['text_domain']),
				'parent_item'       => __('Parent Project category', $this->set['text_domain']),
				'parent_item_colon' => __('Parent Project category', $this->set['text_domain']),
				'edit_item'         => __('Edit Project category', $this->set['text_domain']),
				'update_item'       => __('Update Project category', $this->set['text_domain']),
				'add_new_item'      => __('Add New Project category', $this->set['text_domain']),
				'new_item_name'     => __('New Project category Name', $this->set['text_domain']),
				'menu_name'         => __('Project category', $this->set['text_domain']),
			],
			'description'           => '',
			'public'                => true,
			// 'publicly_queryable'    => null,
			// 'show_in_nav_menus'     => true,
			// 'show_ui'               => true,
			// 'show_in_menu'          => true,
			// 'show_tagcloud'         => true,
			// 'show_in_quick_edit'    => null,
			'hierarchical'          => false,

			'rewrite'               => true,
			//'query_var'             => $taxonomy, // name of the request parameter
			'capabilities'          => array(),
			'meta_box_cb'           => 'post_categories_meta_box', // metabox html. callback: 'post_categories_meta_box' or 'post_tags_meta_box'. false - the metabox is disabled.
			'show_admin_column'     => false, // auto-creation of the tax column in the table of the associated record type. (since version 3.5)
			'show_in_rest'          => null, // add to REST API
			'rest_base'             => null, // $taxonomy
			// '_builtin'              => false,
			//'update_count_callback' => '_update_post_term_count',
		] );
	}

	/**
	 * Registering custom post type `tangram_project`
	 *
	 */
	public function register_post_type(){
		register_post_type( 'tangram_project', [
			'label'  => null,
			'labels' => [
				'name'               => __('Projects', $this->set['text_domain']),
				'singular_name'      => __('Project', $this->set['text_domain']),
				'add_new'            => __('Add Project', $this->set['text_domain']),
				'add_new_item'       => __('Adding Project', $this->set['text_domain']),
				'edit_item'          => __('Editing  Project', $this->set['text_domain']),
				'new_item'           => __('New Project', $this->set['text_domain']),
				'view_item'          => __('View Project', $this->set['text_domain']),
				'search_items'       => __('Search Project', $this->set['text_domain']),
				'not_found'          => __('Not found', $this->set['text_domain']),
				'not_found_in_trash' => __('Not found in the cart', $this->set['text_domain']),
				'parent_item_colon'  => '',
				'menu_name'          => __('Projects', $this->set['text_domain']),
			],
			'description'         => '',
			'public'              => true,
			// 'publicly_queryable'  => null,
			// 'exclude_from_search' => null,
			// 'show_ui'             => null,
			// 'show_in_nav_menus'   => null,
			'show_in_menu'        => null,
			// 'show_in_admin_bar'   => null,
			'show_in_rest'        => true,
			'rest_base'           => null,
			'menu_position'       => null,
			'menu_icon'           => null,
			//'capability_type'   => 'post',
			//'capabilities'      => 'post', // an array of additional permissions for this post type
			//'map_meta_cap'      => null, // Set to true to enable the default special rights handler
			'hierarchical'        => false,
			'supports'            => [ 'title', 'editor', 'thumbnail'], // 'title','editor','author','thumbnail','excerpt','trackbacks','custom-fields','comments','revisions','page-attributes','post-formats'
			'taxonomies'          => ['project_categories'],
			'has_archive'         => true,
			'rewrite'             => true,
			'query_var'           => true,
		] );
	}

	/**
	 * Adds image support
	 */
	public function add_image_support(){
		add_theme_support( 'post-thumbnails', array( 'tangram_project' ) );
	}

	/**
	 * Disable Gutenberg
	 */
	public function disable_gutenberg($is_enabled, $post_type) {
		if ($post_type === 'tangram_project') return false;
		return $is_enabled;
	}

}
