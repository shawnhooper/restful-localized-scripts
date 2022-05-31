<?php

/*
Plugin Name: RESTful Localized Scripts
Version: 1.0
Description: WP REST API enhancement to return JSON arrays containing localized strings registered with WordPress' wp_localize_script() function
Author: Shawn Hooper
Author URI: https://profiles.wordpress.org/shooper
*/

add_action('rest_api_init', 'scriptsatrest_init', 1000);

function scriptsatrest_init() {
	$restfulLocalizedScripts = new Restful_Localized_Scripts();
	$restfulLocalizedScripts->register_routes();

}

class Restful_Localized_Scripts extends WP_REST_Controller {

	public $allowed_scripts = array();

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		$version = '1';
		$namespace = 'shawnhooper/v' . $version;
		$base = 'localized';
		register_rest_route( $namespace, '/' . $base, array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
				'permission_callback' => null,
				'args'            => array(

				),
			)
		) );

		register_rest_route( $namespace, '/' . $base  . '/([a-z0-9]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_item' ),
				'permission_callback' => null,
				'args'            => array(

				),
			)
		) );

	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		global $wp_scripts;

		if ($wp_scripts === null) {
			$wp_scripts = wp_scripts();
		}

		$items = $wp_scripts->registered; //do a query, call another class, etc

		$data = array();
		$allowed = apply_filters('allowed_restful_localized_scripts', $this->allowed_scripts);
		foreach( $items as $item ) {
			if ( true === $allowed || in_array($item->handle, $allowed)  ) {
				$itemdata = $this->prepare_item_for_response( $item, $request );
				if ($itemdata) {
					$data[$item->handle] = $itemdata;
				}
			}
		}

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		//get parameters from request
		$params = $request->get_params();
		$allowed = apply_filters('allowed_restful_localized_scripts', $this->allowed_scripts);

		if (isset($params[0])) {
			global $wp_scripts;
			if ($wp_scripts === null) {
				$wp_scripts = wp_scripts();
			}

			foreach( $wp_scripts->registered as $script ) {
				if ($script->handle == $params[0]) {

					if ( true != $allowed && !in_array($params[0], $allowed) ) {
						return new WP_Error( 'code', __( 'Script not authorized to be returned via REST API endpoint. Add script handle with allowed_restful_localized_scripts filter.', 'restful-localized-scripts', 'wpcli-clean-multisitedb' ), $params[0] );
					}

					return new WP_REST_Response( $this->prepare_item_for_response( $script, $request ), 200 );
				}
			}
		}

		return new WP_Error( 'code', __( 'No script with the requested handle can be found', 'restful-localized-scripts', 'wpcli-clean-multisitedb' ) );
	}



	/**
	 * Prepare the item for the REST response
	 *
	 * @param mixed $item WordPress representation of the item.
	 * @param WP_REST_Request $request Request object.
	 * @return mixed
	 */
	public function prepare_item_for_response( $item, $request ) {
		if (isset($item->extra['data'])) {
			$data = $item->extra['data'];

			$dataArray = substr($data, strpos($data, '=')+2);

			if (substr($dataArray, -1) == ';') {
				$dataArray = substr($dataArray, 0, strlen($dataArray)-1);
			}

			$object = json_decode($dataArray);

			return $object;
		}

		return false;
	}
}

