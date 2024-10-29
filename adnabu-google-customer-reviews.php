<?php
/**
 * Created by PhpStorm.
 * User: Mahaveer Chouhan
 * Date: 13/11/18
 * Time: 6:07 PM
 */

/*
Plugin Name: AdNabu Google Customer Reviews
Description: A woo-commerce google customer reviews plugin
Version: 1.0.0
Author: AdNabu
Author URI: http://adnabu.com
License: GPLv2 or later
Text Domain: adnabu-google-customer-reviews
*/
//stop direct aceess
defined( 'ABSPATH' ) or die( 'Hey, what are you doing here? You silly human!' );

//import base class and pixel class
if(!class_exists('AdNabuBasev2')){
    include_once 'includes/Base/AdNabuBasev2.php';
    if(!class_exists('AdNabuPixelBase')){
        include_once 'includes/Base/AdNabuPixelBase.php';
    }
}


include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
include_once 'includes/AdNabuGoogleCustomerReviews.php'; // import the main app class

register_deactivation_hook( __FILE__, array( 'AdNabuBasev2', 'deactivate' ) );
register_uninstall_hook(__FILE__, array('AdNabuGoogleCustomerReviews','uninstall_app'));
//check if woocommerce is active or not
if(!is_plugin_active('woocommerce/woocommerce.php' )){
    $instance = new AdNabuGoogleCustomerReviews();
    add_action( 'admin_notices', array( $instance, 'missing_woocommerce' ) );
}
else{
    $instance = new AdNabuGoogleCustomerReviews();
    add_action( 'admin_notices', array( $instance, 'activation_greeting' ) );//activation message
    add_action('admin_enqueue_scripts',array( $instance, 'enqueue_admin_assets' ));//add assets for app home
    $filter_name = "plugin_action_links_" . plugin_basename( __FILE__ );
    add_filter($filter_name, array($instance, 'settings_link' ));
    add_action('admin_menu',  array( $instance, 'add_menu_page' ), 0);
    add_action('admin_menu', array( $instance, 'add_app_page' ), 1);//adding menu entry
    register_activation_hook( __FILE__, array(  $instance, 'activate_app' ) );
    add_action( 'wp_footer', array(  $instance, 'add_google_reviews_snippet'), 100);//activation hook
}


