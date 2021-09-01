<?php

namespace Tangram\Includes;

class RestApi
{
	public $set;

	public function __construct($set)
	{
		$this->set = $set;
		add_action('rest_api_init', [$this, 'routes']);
	}

	public function routes()
	{
		$name_space = 'tangram/v1';
		register_rest_route($name_space, '/projects/', [
			'methods' => 'GET',
			'callback' => [$this, 'handler_all_posts'],
			'permission_callback' => [ $this, 'permissions_check' ],
		]);

		register_rest_route($name_space, '/project/(?P<id>\d+)', [
			'methods' => 'GET',
			'callback' => [$this, 'handler_post'],
			'permission_callback' => [ $this, 'permissions_check' ],
		]);

		register_rest_route($name_space, '/project/(?P<id>\d+)', [
			'methods' => 'DELETE',
			'callback' => [$this, 'handler_post_delete'],
			'permission_callback' => [ $this, 'permissions_check' ],
		]);

		register_rest_route($name_space, '/project/(?P<id>\d+)', [
			'methods' => 'POST',
			'callback' => [$this, 'handler_post_update'],
			'permission_callback' => [ $this, 'permissions_check' ],
		]);

		register_rest_route($name_space, '/project/create', [
			'methods' => 'POST',
			'callback' => [$this, 'handler_post_create'],
			'permission_callback' => [ $this, 'permissions_check' ],
		]);
	}

	/**
	 * Endpoint (route) to receive all posts
	 * @param $request
	 * @return int[]|\WP_Error|\WP_Post[]
	 */
	public function handler_all_posts( \WP_REST_Request $request)
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
	public function handler_post( \WP_REST_Request $request)
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
	public function handler_post_delete( \WP_REST_Request $request)
	{

		$post = get_post((int)$request['id']);

		if (empty($post) || $post->post_type != 'tangram_project') {
			return new \WP_Error('no_project_post', 'Записей не найдено', ['status' => 404]);
		}

		$message = ['ok' => 'post deleted'];
		$status = 200;

		if( ! wp_trash_post( $post->ID ) ) {
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
	public function handler_post_update( \WP_REST_Request $request)
	{



	}

	/**
	 * Endpoint (route) to create new post
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function handler_post_create( \WP_REST_Request $request )
	{

		$data = get_post((int)$request['id']);

		if (empty($post) || $post->post_type != 'tangram_project') {
			return new \WP_Error('no_project_post', 'Записей не найдено', ['status' => 404]);
		}

		$message = ['ok' => 'post deleted'];
		$status = 200;
		

		return new \WP_REST_Response($message, $status);

	}

	/**
	 * Check user premissions
	 * @param $request
	 * @return bool|\WP_Error
	 */
	public function permissions_check( $request ){
		$possibility = 'read';
		switch ( $request->get_method() ) {

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

		if ( ! current_user_can( $possibility ) )
			return new \WP_Error( 'rest_forbidden',
				esc_html__( 'You cannot access to this resource.' ),
				[ 'status' => is_user_logged_in() ? 403 : 401 ] );

		return true;
	}

}
