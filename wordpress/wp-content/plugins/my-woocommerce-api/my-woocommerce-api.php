<?php
/*
Plugin Name: My WooCommerce API
Plugin URI: http://localhost/thailand/wordpress/my-woocommerce-api/
Description: Adds a custom API endpoint to retrieve sales data from WooCommerce.
Version: 1.0
Author: Mirza Yasir
Author URI: http://localhost/thailand/wordpress/
License: GPL2
*/

// Plugin code goes here

add_action('rest_api_init', function () {
    register_rest_route('api/v1', '/sales', array(
        'methods' => 'GET',
        'callback' => 'getApiResponse'
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('api/v1', '/reports', array(
        'methods' => 'GET',
        'callback' => 'getApiResponse'
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('api/v1', '/top_sellers', array(
        'methods' => 'GET',
        'callback' => 'getApiResponse'
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('api/v1', '/coupons_totals', array(
        'methods' => 'GET',
        'callback' => 'getApiResponse'
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('api/v1', '/customers_totals', array(
        'methods' => 'GET',
        'callback' => 'getApiResponse'
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('api/v1', '/orders_totals', array(
        'methods' => 'GET',
        'callback' => 'getApiResponse'
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('api/v1', '/products_totals', array(
        'methods' => 'GET',
        'callback' => 'getApiResponse'
    ));
});

function getApiResponse($request)
{
    $endpoint = $request->get_route();
    if(basename($endpoint) == "reports" || basename($endpoint) == 'sales'){
        if(basename($endpoint) == 'reports') {
            $api_url = 'http://localhost/thailand/wordpress/wp-json/wc/v3/reports';
        } else if(basename($endpoint) == 'sales') {
            $api_url = 'http://localhost/thailand/wordpress/wp-json/wc/v3/reports/sales';
        }
    } else {
        $temp = explode('_', basename($endpoint));
        $api_url = 'http://localhost/thailand/wordpress/wp-json/wc/v3/reports/'.implode("/", $temp);
    }
    
    $consumer_key = 'ck_5dce680c19cbcb38b159744b1433599d501c510c';
    $consumer_secret = 'cs_506a62c66034096d5a9f11e5a5a044ba45f0c61c';

    // Generate the OAuth 1.0 signature
    $oauth = array(
        'oauth_consumer_key' => $consumer_key,
        'oauth_nonce' => uniqid(),
        'oauth_signature_method' => 'HMAC-SHA1',
        'oauth_timestamp' => time(),
        'oauth_version' => '1.0',
    );
    $base_uri = 'GET&' . rawurlencode($api_url) . '&'. rawurlencode(http_build_query($oauth, '', '&', PHP_QUERY_RFC3986));
    $secret = rawurlencode($consumer_secret) . '&';
    $oauth['oauth_signature'] = base64_encode(hash_hmac('sha1', $base_uri, $secret, true));

    // Build the Authorization header
    $auth_header = 'OAuth ' . http_build_query($oauth, '', ',');
    $headers = array(
        'Authorization' => $auth_header,
    );

    // Make the API request
    $response = wp_remote_get($api_url, array(
        'headers' => $headers,
    ));

    // Check for errors
    if ( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        echo "Something went wrong: $error_message";
    } else {
        $data = json_decode( wp_remote_retrieve_body( $response ) );
        echo json_encode($data);
    }
}