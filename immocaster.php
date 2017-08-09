<?php

/*
Plugin Name: Immocaster
Plugin URI: http://www.immocaster.com/
Description: Das Plugin von Immocaster ermöglicht das einfache Einbinden von Immobiliendaten über die ImmobilienScout24-API. <strong>Bevor Sie loslegen können, müssen Sie unter "Einstellungen>Immocaster" die Grundeinstellungen für Immocaster vornhemen. Bitte achten Sie darauf, dass für die Einstellungen und die Pflege die Aktivierung von JavaScript empfohlen wird.</strong>
Version: 1.3.6
Author: WP-ImmoMakler
Author URI: https://www.wp-immomakler.de/
Min WP Version: 3.1
*/

/*
Credits and many thanks to the initial author
Norman Braun http://www.pixelairport.de/
*/

// Configuration
define('IMMOCASTER_PLUGIN_VERSION','1.3.6');
define('IMMOCASTER_PLUGIN_URL',plugin_dir_url(__FILE__));
define('IMMOCASTER_PLUGIN_PATH',plugin_dir_path(__FILE__));

// Set redirect locations (ImmobilienScout24)
function immocaster_allowed_redirect_hosts($allowed_host)
{
    $allowed_host[] = 'forward.immobilienscout24.de';
    return $allowed_host;
}
add_filter('allowed_redirect_hosts','immocaster_allowed_redirect_hosts');

// Redirect (for referrer links)
function immocaster_redirect()
{
	if(isset($_GET[IMMOCASTER_GET_PARAM_REDIRECT]))
	{
		wp_safe_redirect(base64_decode($_GET[IMMOCASTER_GET_PARAM_REDIRECT]));
		exit;
	}
}
add_action('init','immocaster_redirect');

// Start session
if(!isset($_SESSION))
{
	session_start();
}

// Messages and functions
require_once('messages.php');
require_once('functions.php');

// Languagefiles
define('IMMOCASTER_PO_TEXTDOMAIN','immocaster-translation');
load_plugin_textdomain(IMMOCASTER_PO_TEXTDOMAIN,false,dirname(plugin_basename(__FILE__)).'/languages');

// Permalinks, get and post
define('IMMOCASTER_PAGINATOR_PAGER',           'ic_list_pager');
define('IMMOCASTER_PERMALINK_PAGINATOR_PAGER', 'pager');
define('IMMOCASTER_POST_TYPE_NAME',            'ic_estate_view');
define('IMMOCASTER_GET_PARAM_EXPOSE',          'estate_id');
define('IMMOCASTER_PERMALINK_OBJECT',          'realestate');
define('IMMOCASTER_GET_PARAM_REDIRECT',        'ic_redirect');

// Posttype for dynamic pages
function immocaster_create_post_type()
{
	register_post_type(strtolower(IMMOCASTER_POST_TYPE_NAME),array('public'=>false,'has_archive'=>false));
}
add_action('init','immocaster_create_post_type');

// Register permalinks
add_filter('rewrite_rules_array','immocaster_rewrite_rules');
add_filter('query_vars','immocaster_rewrite_query_vars');
add_filter('wp_loaded','immocaster_flush_rules');

// Rewrite rules for objects (and to use seo-urls)
function immocaster_rewrite_rules($rules)
{
	global $post;
	$newrules = array();
	// Expose
	$newrules['(.+?)/'.IMMOCASTER_PERMALINK_OBJECT.'/([0-9]{1,16})/?$'] = 'index.php?'.IMMOCASTER_POST_TYPE_NAME.'=1&'.
	IMMOCASTER_GET_PARAM_EXPOSE.'=$matches[2]';
	// Paginator (Listing)
	$newrules['(.+?)/'.IMMOCASTER_PERMALINK_PAGINATOR_PAGER.'/([0-9]{1,16})/([0-9]{1,16})/?$'] =
		'index.php?page_id=$matches[2]&'.IMMOCASTER_PAGINATOR_PAGER.'=$matches[3]';
	return $newrules + $rules;
}

// Rewrite query_vars
function immocaster_rewrite_query_vars($vars)
{
	array_push($vars,IMMOCASTER_PAGINATOR_PAGER);
	array_push($vars,IMMOCASTER_POST_TYPE_NAME);
	array_push($vars,IMMOCASTER_GET_PARAM_EXPOSE);
    return $vars;
}

// Flush rules
function immocaster_flush_rules()
{
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}

// Load language
require_once('language.php');

// Load immocaster
require_once(dirname(__FILE__).'/lib/immocaster.php');

// Load ajax & jquery
function immocaster_javascript_init()
{
	// jquery, jquery ui (+autoload)
	wp_enqueue_script('jquery-ui-autocomplete');
	// galleria
	wp_deregister_script('galleria');
	wp_register_script('galleria',plugins_url('lib/galleria/galleria-1.2.6.wp-version.min.js',__FILE__),false,'1.2.6',false);
	wp_enqueue_script('galleria');
	// immocaster ajax
	wp_deregister_script('immocaster_ajax');
	wp_register_script('immocaster_ajax',plugins_url('js/ajax.js',__FILE__),false,'1.0',true);
	wp_enqueue_script('immocaster_ajax');
	// ajaxcall and exit
	if(isset($_GET['immocaster_ajax']))
	{
			require_once('ajax/'.$_GET['immocaster_ajax'].'.php');
			exit;
	}
}
add_action('init','immocaster_javascript_init');

// Load css for backend
function immocaster_backend_stylesheets()
{
	wp_register_style('immocaster-jquery-ui-theme',plugins_url('lib/jquery-ui/css/smoothness/jquery-ui-1.10.0.custom.min.css',__FILE__));
	wp_enqueue_style('immocaster-jquery-ui-theme');
	wp_register_style('immocaster-backend-css',plugins_url('css/admin.css',__FILE__));
	wp_enqueue_style('immocaster-backend-css');
}
add_action('admin_head','immocaster_backend_stylesheets');

// Load css for frontend
function immocaster_frontend_stylesheets()
{
	if(is_file(WP_PLUGIN_DIR.'/immocaster/custom/css/style.css'))
	{
		wp_register_style('immocaster-main-style',plugins_url('custom/css/style.css',__FILE__));
	}else{
		wp_register_style('immocaster-main-style',plugins_url('default/css/style.css',__FILE__));
	}
	wp_enqueue_style('immocaster-main-style');
}
add_action( 'wp_enqueue_scripts', 'immocaster_frontend_stylesheets' );

// Load required files
require_once(dirname(__FILE__).'/pages/main.php');
require_once(dirname(__FILE__).'/pages/immobilienscout24.php');
$oImmocasterSDK = ImmocasterSDK::getInstance('is24precheck');
if($oImmocasterSDK->checkConnection())
{
	require_once(dirname(__FILE__).'/widget_teaser.php');
	require_once(dirname(__FILE__).'/content_resultlist.php');
	require_once(dirname(__FILE__).'/content_object.php');
}

// Create immocaster menu for admin
function immocaster_add_menu()
{
	add_menu_page('Immocaster',__('Immocaster',IMMOCASTER_PO_TEXTDOMAIN),'manage_options','immocaster','immocaster_pages_main',plugins_url('/images/immocaster_menu_icon.png',__FILE__));
	add_submenu_page('immocaster','ImmobilienScout24',__('ImmobilienScout24',IMMOCASTER_PO_TEXTDOMAIN),'manage_options','immocaster-immobilienscout24','immocaster_pages_immobilienscout24');
}
add_action('admin_menu', 'immocaster_add_menu');

// Clean uninstall
function uninstall_immocaster()
{
	global $wpdb;
	$wpdb->query("DROP TABLE IF EXISTS `Immocaster_Storage`;");
	delete_option('is24_rest_key');
	delete_option('is24_rest_secret');
	delete_option('immocaster_supportlink');
	delete_option('is24_account_username');
	delete_option('immocaster_plugin_key');
	delete_metadata('post', null,'_immocaster_meta',null,true);
	delete_option('widget_immocaster_teaser');

}
register_uninstall_hook(__FILE__,'uninstall_immocaster');