<?php
/*
Plugin Name: MF Contract
Plugin URI: https://github.com/frostkom/mf_contracts
Description: 
Version: 1.0.15
Author: Martin Fors
Author URI: http://frostkom.se
Text Domain: lang_contract
Domain Path: /lang

GitHub Plugin URI: frostkom/mf_contracts
*/

if(is_admin())
{
	include_once("include/functions.php");

	load_plugin_textdomain('lang_contract', false, dirname(plugin_basename(__FILE__))."/lang/");

	register_activation_hook(__FILE__, 'activate_contract');

	add_action('init', 'init_contract');
	//add_action('admin_menu', 'menu_contract');
	add_action('admin_notices', 'notices_contract');
	add_filter('manage_mf_contract_posts_columns', 'column_header_contract', 5);
	add_action('manage_mf_contract_posts_custom_column', 'column_cell_contract', 5, 2);
	add_action('rwmb_meta_boxes', 'meta_boxes_contract');

	function activate_contract()
	{
		require_plugin("meta-box/meta-box.php", "Meta Box");
	}
}