<?php

namespace Tangram\Includes;

class RestApi
{
	/**
	 * Plugin props set
	 * @var array	'plugin_name' => TANGRAM_PROJECTS_NAME,
	 *				'plugin_version' => '1.0.0',
	 *				'text_domain' => 'tangram_projects'
	 */
	public array $set;

	private string $error404;
	private string $error501;

	/**
	 * Adds hook handlers
	 * @param array $set
	 */
	public function __construct(array $set)
	{
		$this->set = $set;
		add_action('rest_api_init', [$this, 'routes']);
		add_action('save_post', [$this, 'update_post_project']);
		$this->error404 = "new \WP_Error('no_posts', __('No records found', " . $this->set['text_domain'] . "), ['status' => 404])";
		$this->error501 = "new \WP_Error('server_error', __('Internal server error', " . $this->set['text_domain'] . "), ['status' => 501])";
	}

	/**
	 * Describes routes
	 */
	public function routes()
	{
		$name_space = 'tangram/v1';
		$per_check = [$this, 'permissions_check'];
		register_rest_route($name_space, '/projects/', [
			'methods' => 'GET',
			'callback' => [$this, 'handler_all_posts'],
			'permission_callback' => $per_check,
		]);

		register_rest_route($name_space, '/project/(?P<id>\d+)', [
			'methods' => 'GET',
			'callback' => [$this, 'handler_post'],
			'permission_callback' => $per_check,
		]);

		register_rest_route($name_space, '/project/(?P<id>\d+)', [
			'methods' => 'DELETE',
			'callback' => [$this, 'handler_post_delete'],
			'permission_callback' => $per_check,
		]);

		register_rest_route($name_space, '/project/(?P<id>\d+)', [
			'methods' => 'POST',
			'callback' => [$this, 'handler_post_update'],
			'permission_callback' => $per_check,
		]);

		register_rest_route($name_space, '/project/create', [
			'methods' => 'POST',
			'callback' => [$this, 'handler_post_create'],
			'permission_callback' => $per_check,
		]);
	}

	/**
	 * Endpoint (route) to receive all posts
	 * @param $request
	 * @return int[]|\WP_Error|\WP_Post[]
	 */
	public function handler_all_posts(\WP_REST_Request $request)
	{
		$posts = get_posts([
			'post_type' => 'tangram_project',
			'nopaging' => true,
		]);

		if (empty($posts)) {
			return eval($this->error404);
		}

		return $posts;
	}

	/**
	 * Endpoint (route) to receive ane post by id
	 * @param \WP_REST_Request $request
	 * @return int[]|\WP_Error|\WP_Post[]
	 */
	public function handler_post(\WP_REST_Request $request)
	{
		$post = get_post((int)$request['id']);

		if (empty($post) || $post->post_type != 'tangram_project') {
			return eval($this->error404);
		}

		return $post;
	}

	/**
	 * Endpoint (route) to delete ane post by id
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function handler_post_delete(\WP_REST_Request $request)
	{
		$post = get_post((int)$request['id']);

		if (empty($post) || $post->post_type != 'tangram_project') {
			return eval($this->error404);
		}

		$message = ['ok' => 'post deleted'];
		$status = 200;

		remove_action('save_post', [$this, 'update_post_project']);

		if (!wp_trash_post($post->ID)) {
			return eval($this->error501);
		}

		add_action('save_post', [$this, 'update_post_project']);

		return new \WP_REST_Response($message, $status);
	}

	/**
	 * Endpoint (route) to update ane post by id
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function handler_post_update(\WP_REST_Request $request)
	{
		$data = $request->get_params();
		$post = get_post((int)$request['id']);
		$post_id = $post->ID;

		$message = ['ok' => 'post updated'];
		$status = 200;

		if (empty($post) || $post->post_type != 'tangram_project') {
			return eval($this->error404);
		}

		$post_data = [
			'ID' => $post->ID,
			'post_title' => sanitize_text_field($data['post_title']),
			'post_name' => sanitize_text_field($data['post_name']),
			'post_content' => $data['post_content'],
			'post_status' => 'publish',
			'post_author' => get_current_user_id(),
			'post_type' => 'tangram_project',
		];

		$this->update_post_project($post_data);

		$terms_data = [];
		$terms_data_sent = json_decode($data['project_categories']);

		foreach ($terms_data_sent as $tax_name => $tax_id) {
			if ($tax_id == term_exists($tax_name, 'project_categories')['term_id']) {
				$terms_data[] = (int)$tax_id;
			}
		}

		wp_set_post_terms($post_id, $terms_data, 'project_categories');

		return new \WP_REST_Response($message, $status);
	}

	/**
	 * Safely updates the post data
	 * @param $post_data
	 */
	public function update_post_project($post_data)
	{
		if(is_admin()) return;  // If the post is updated from the admin panel: exit
		if (!wp_is_post_revision($post_data['ID'])) {
			remove_action('save_post', [$this, 'update_post_project']);
			wp_update_post($post_data);
			add_action('save_post', [$this, 'update_post_project']);
		}
	}

	/**
	 * Endpoint (route) to create new post
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function handler_post_create(\WP_REST_Request $request)
	{
		$data = $request->get_params();

		$post_data = [
			'post_title' => sanitize_text_field($data['post_title']),
			'post_name' => sanitize_text_field($data['post_name']),
			'post_content' => $data['post_content'],
			'post_status' => 'publish',
			'post_author' => get_current_user_id(),
			'post_type' => 'tangram_project',
		];

		remove_action('save_post', [$this, 'update_post_project']);

		// Inserting a record into the database
		$post_id = wp_insert_post($post_data);

		add_action('save_post', [$this, 'update_post_project']);

		$message = ['ok' => 'post created', 'post_ID' => $post_id];
		$status = 200;

		if (empty($post_id)) {
			return eval($this->error501);
		}

		$terms_data = [];
		$terms_data_sent = json_decode($data['project_categories']);

		foreach ($terms_data_sent as $tax_name => $tax_id) {
			if ($tax_id == term_exists($tax_name, 'project_categories')['term_id']) {
				$terms_data[] = (int)$tax_id;
			}
		}

		wp_set_post_terms($post_id, $terms_data, 'project_categories');

		return new \WP_REST_Response($message, $status);
	}

	/**
	 * Check user permissions
	 * @param $request
	 * @return bool|\WP_Error
	 */
	public function permissions_check(\WP_REST_Request $request)
	{
		$possibility = 'read';
		switch ($request->get_method()) {

			case 'POST':
				$possibility = 'edit_posts';
				break;

			case 'GET':
				$possibility = 'read';
				break;

			case 'DELETE':
				$possibility = 'delete_posts';
				break;
		}

		if (!current_user_can($possibility))
			return new \WP_Error('permission_denied',
							__('You cannot access to this resource.'),
							['status' => is_user_logged_in() ? 403 : 401]);

		return true;
	}

}
