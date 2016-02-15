<?php

/*
Plugin Name: RESTful Localized Scripts
Version: 1.0
Description:
Author: Shawn Hooper
Author URI: https://profiles.wordpress.org/shooper
*/

add_action('rest_api_init', 'scriptsatrest_init', 1000);

function scriptsatrest_init() {
	$scriptsAtRest = new Scripts_At_REST();
	$scriptsAtRest->register_routes();
}

class Scripts_At_REST extends WP_REST_Controller {

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		$version = '1';
		$namespace = 'shawnhooper/v' . $version;
		$base = 'scripts';
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

		$items = $wp_scripts->registered; //do a query, call another class, etc
		$data = array();
		foreach( $items as $item ) {
			$itemdata = $this->prepare_item_for_response( $item, $request );
			if ($itemdata) {
				$data[$item->handle] = $itemdata;
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

		if (isset($params[0])) {
			$handle = $params[0];
			global $wp_scripts;

			foreach( $wp_scripts->registered as $script ) {
				if ($script->handle == $params[0]) {
					return new WP_REST_Response( $this->prepare_item_for_response( $script, $request ), 200 );
				}
			}
			return new WP_Error( 'code', __( 'No script with the requested handle can be found', 'restful-localized-scripts' ) );
		}

		$item = array();//do a query, call another class, etc
		$data = $this->prepare_item_for_response( $item, $request );

		//return a response or error based on some conditional
		if ( 1 == 1 ) {
			return new WP_REST_Response( $data, 200 );
		}else{
			return new WP_Error( 'code', __( 'No script with the requested handle can be found', 'restful-localized-scripts' ) );
		}
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