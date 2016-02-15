# RESTful Localized Scripts #
**Contributors:** shooper  
**Donate link:** https://shawnhooper.ca/  
**Tags:** javascript, i18n, api  
**Requires at least:** 4.4  
**Tested up to:** 4.4.2  
**Stable tag:** trunk  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

WP REST API enhancement to return JSON arrays containing localized strings registered with WordPress' wp_localize_script() function

## Description ##

WP REST API enhancement to return JSON arrays containing localized strings registered with WordPress' wp_localize_script() function

## Usage ##

1. Install & Activate Plugin
1. Specify which localized scripts are allowed to be returned via the API using the *allowed_restful_localized_scripts* filter
1. Go to endpoint `/wp-json/shawnhooper/v1/localized/` to see all scripts
1. Go to endpoint `/wp-json/shawnhooper/v1/localized/<script_handle>` to see a specific script.

## Specifying Which Scripts Are Allowed ##

By default, no scripts are allowed to be returned by the API.

To specify specific scripts that are allowed to be returned via the API:

<pre>add_filter('allowed_restful_localized_scripts', function($allowed) {
	array_push($allowed, 'my_script_name');
	array_push($allowed, 'my_second_script_name');
	return $allowed;
});</pre>

To allow all scripts to be returned via the API:

`add_filter('allowed_restful_localized_scripts', '__return_true');`

## Changelog ##

### 1.0 ###
* First release.