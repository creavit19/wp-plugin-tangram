<?php

namespace Tangram\Includes;

class RestApi
{
	public $set;

	public function __construct($set)
	{
		$this->set = $set;

		// add_action( 'rest_api_init', [$this, 'custom_field']);

		add_action('rest_api_init', [$this, 'routes']);

		add_action( 'save_post', [$this, 'update_post_project'] );

	}

	public function routes()
	{
		$name_space = 'tangram/v1';
		register_rest_route($name_space, '/projects/', [
			'methods' => 'GET',
			'callback' => [$this, 'handler_all_posts'],
			'permission_callback' => [$this, 'permissions_check'],
		]);

		register_rest_route($name_space, '/project/(?P<id>\d+)', [
			'methods' => 'GET',
			'callback' => [$this, 'handler_post'],
			'permission_callback' => [$this, 'permissions_check'],
		]);

		register_rest_route($name_space, '/project/(?P<id>\d+)', [
			'methods' => 'DELETE',
			'callback' => [$this, 'handler_post_delete'],
			'permission_callback' => [$this, 'permissions_check'],
		]);

		register_rest_route($name_space, '/project/(?P<id>\d+)', [
			'methods' => 'POST',
			'callback' => [$this, 'handler_post_update'],
			'permission_callback' => [$this, 'permissions_check'],
		]);

		register_rest_route($name_space, '/project/create', [
			'methods' => 'POST',
			'callback' => [$this, 'handler_post_create'],
			'permission_callback' => [$this, 'permissions_check'],
		]);
	}

	/*
	public function add_custom_rest_fields() {
		register_rest_field(
			'tangram_project',
			'project_r_date',
			[
				'get_callback' => [$this, 'get_field_data'],
				'update_callback' => null,
				'schema' => null,
			]
		);
	}

	public function get_field_data( $object, $field_name, $request ) {

		return get_post_meta( $object[(int)$request['id']], $field_name, true );
	}

	public function custom_field() {
		register_rest_field('tangram_project', 'project_r_date', [
			error_log('rest_api_init action has fired')
		]);
	}
	*/

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
			return new \WP_Error('no_posts', 'Записей не найдено', ['status' => 404]);
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
			return new \WP_Error('no_project_post', 'Записей не найдено', ['status' => 404]);
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
			return new \WP_Error('no_project_post', 'Записей не найдено', ['status' => 404]);
		}

		$message = ['ok' => 'post deleted'];
		$status = 200;

		if (!wp_trash_post($post->ID)) {
			$message = ['error' => 'Failed to delete server error.'];
			$status = 501;
		}

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

		if(empty($post) || $post->post_type != 'tangram_project') {
			$message = ['error' => 'Post is empty'];
			$status = 404;
			return new \WP_REST_Response($message, $status);
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

		$this->update_post_project( $post_data );

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
	 * @param $post_data
	 */
	public function update_post_project( $post_data ){
		if ( ! wp_is_post_revision( $post_data['ID'] ) ){
			// удаляем этот хук, чтобы он не создавал бесконечного цикла
			remove_action('save_post', [$this, 'update_post_project']);

			// обновляем пост, когда снова вызовется хук save_post
			wp_update_post( $post_data );

			// снова вешаем хук
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
			$message = ['error' => 'Failed to add'];
			$status = 501;
			return new \WP_REST_Response($message, $status);
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
	 * Check user premissions
	 * @param $request
	 * @return bool|\WP_Error
	 */
	public function permissions_check($request)
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
			return new \WP_Error('rest_forbidden',
				esc_html__('You cannot access to this resource.'),
				['status' => is_user_logged_in() ? 403 : 401]);

		return true;
	}

}
