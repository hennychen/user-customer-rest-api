<?php
/*
Plugin Name: user-customer-rest-api
Plugin URI: 插件的介绍或更新地址
Description: 扩展Rest api 
Version: 1.0
Author: hennychen
Author URI: http://github.com/hennychen
License: A "Slug" license name e.g. GPL2
*/

// Register REST API endpoints
class User_Custom_REST_API_Endpoints extends WP_REST_Controller {

	/**
	 * Register the routes for the objects of the controller.
	 */
	public static function register_endpoints() {
		// endpoints will be registered here
		register_rest_route( 'customapi/v1', '/createuserdata', array(
			'methods' => 'POST',
			'callback' => array( 'User_Custom_REST_API_Endpoints', 'create_userdata' ),

		) );
		register_rest_route( 'customapi/v1', '/getusercomments', array(
			'methods' => 'GET',
			'callback' => array( 'User_Custom_REST_API_Endpoints', 'getusercomments' ),

		) );
	}
	public static  function  getusercomments( $request_data ){
		$parameters = $request_data->get_params();
//		$args = array(
//			'author_email' => '',
//			'author__in' => '',
//			'author__not_in' => '',
//			'include_unapproved' => '',
//			'fields' => '',
//			'ID' => '',
//			'comment__in' => '',
//			'comment__not_in' => '',
//			'karma' => '',
//			'number' => '',
//			'offset' => '',
//			'orderby' => '',
//			'order' => 'DESC',
//			'parent' => '',
//			'post_author__in' => '',
//			'post_author__not_in' => '',
//			'post_ID' => '', // ignored (use post_id instead)
//			'post_id' => 0,
//			'post__in' => '',
//			'post__not_in' => '',
//			'post_author' => '',
//			'post_name' => '',
//			'post_parent' => '',
//			'post_status' => '',
//			'post_type' => '',
//			'status' => 'all',
//			'type' => '',
//			'type__in' => '',
//			'type__not_in' => '',
//			'user_id' => '',
//			'search' => '',
//			'count' => false,
//			'meta_key' => '',
//			'meta_value' => '',
//			'meta_query' => '',
//			'date_query' => null, // See WP_Date_Query
//		);
		$comments = get_comments( $parameters );
		foreach($comments as $comment) {
//			echo($comment->comment_author);

			$postdata = get_post($comment->comment_post_ID);
			$comment->comment_post = $postdata;
		}


		return new WP_REST_Response($comments,200);
	}
	/**
	 * Add a new user
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public static function create_userdata( $request_data ) {
		$parameters = $request_data->get_params();
		$user_name = $parameters['username'];
		$pwd = $parameters['pwd'];
		$user_email = $parameters['email'];
		
		if( !isset( $parameters['username'] ) || empty($parameters['username']) || !isset( $parameters['pwd'] ) || empty($parameters['pwd']) || !isset( $parameters['email'] ) || empty($parameters['email']) )
			return new WP_Error( 'cant-createuser', __( 'no_parameter_given', 'user-customer-rest-api'), array( 'status' => 500 ) );
		

		

		if( strlen( $user_name ) <= 5 )
			return new WP_Error('10001',__('keyword_not_long_enough','user-customer-rest-api'));

		$user_id = username_exists( $user_name );
		if ( !$user_id and email_exists($user_email) == false ) {
			// $random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
			$user_id = wp_create_user( $user_name, $pwd, $user_email );
			$data = array('userid' => $user_id,'username' => $user_name, 'pwd' => $pwd, 'email' => $user_email);
			return new WP_REST_Response($data,200);
		} else {
			return new WP_Error( 'cant-createuser', __( 'User already exists.  Password inherited.', 'user-customer-rest-api'), array( 'status' => 500 ) );
		
		}
		

	}
}
add_action( 'rest_api_init', array( 'User_Custom_REST_API_Endpoints', 'register_endpoints' ) );