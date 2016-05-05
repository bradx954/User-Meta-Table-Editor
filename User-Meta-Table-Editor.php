<?php
/*
Plugin Name: User Table Meta Editor
Description: Adds a configurable table for editing user meta.
Version:     1.0
Author:      Bradley Robert Baago
Author URI:  https://bradtech.ca
*/

//Prevent direct script access.
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

//Load option menus.
add_action( 'admin_menu', 'User_Table_Meta_Editor_menu' );
add_action( 'admin_menu', 'User_Table_Meta_Editor_table_page' );

//Add ajax callback functions.
add_action( 'wp_ajax_User_Table_Meta_Editor_table_edit', 'User_Table_Meta_Editor_table_edit' );
add_action( 'wp_ajax_User_Table_Meta_Editor_table_save_csv', 'User_Table_Meta_Editor_table_save_csv' );

//Load javascript libraries.
add_action( 'admin_enqueue_scripts', 'User_Table_Meta_Editor_scripts' );

// Add settings link on plugin page
function User_Table_Meta_Editor_settings_link($links) {
  $settings_link = '<a href="options-general.php?page=User_Table_Meta_Editor_menu_page">Settings</a>';
  array_unshift($links, $settings_link);
  return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'User_Table_Meta_Editor_settings_link' );

//Load functions library.
require_once('functions.php');
