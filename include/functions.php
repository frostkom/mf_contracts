<?php

function init_contract()
{
	$labels = array(
		'name' => _x(__("Contracts", 'lang_contract'), 'post type general name'),
		'singular_name' => _x(__("Contracts", 'lang_contract'), 'post type singular name'),
		'menu_name' => __("Contracts", 'lang_contract')
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'show_in_nav_menus' => false,
		'exclude_from_search' => true,
		'menu_position' => 100,
		'menu_icon' => 'dashicons-clipboard',
		'supports' => array('title'),
		'hierarchical' => true,
		'has_archive' => false,
	);

	register_post_type('mf_contract', $args);
}

function notices_contract()
{
	global $wpdb, $error_text;

	$meta_prefix = "mf_contract_";

	if(IS_ADMIN)
	{
		$result = $wpdb->get_results("SELECT ID, post_title FROM ".$wpdb->posts." INNER JOIN ".$wpdb->postmeta." ON ".$wpdb->posts.".ID = ".$wpdb->postmeta.".post_id WHERE post_type = 'mf_contract' AND post_status != 'trash' AND meta_key = '".$meta_prefix."expiration_date' AND meta_value < DATE_ADD(NOW(), INTERVAL 2 WEEK)");

		if($wpdb->num_rows > 0)
		{
			$unsent_links = "";

			foreach($result as $r)
			{
				$post_id = $r->ID;
				$post_title = $r->post_title;

				$unsent_links .= ($unsent_links != '' ? ", " : "")."<a href='".admin_url("edit.php?post_type=mf_contract")."'>".$post_title."</a>";
			}

			$error_text = __("There are soon to be expired contracts", 'lang_contract')." (".$unsent_links.")";

			echo get_notification();
		}
	}
}

function column_header_contract($cols)
{
	unset($cols['date']);

	$cols['start_date'] = __("End date", 'lang_contract');
	$cols['end_date'] = __("Start date", 'lang_contract');
	$cols['expiration_date'] = __("Expiration date", 'lang_contract');
	$cols['extends'] = __("Extends", 'lang_contract');

	return $cols;
}

function column_cell_contract($col, $id)
{
	$meta_prefix = "mf_contract_";

	$post_meta = get_post_meta($id, $meta_prefix.$col, true);

	switch($col)
	{
		case 'start_date':
		case 'end_date':
		case 'extends':
			echo $post_meta;
		break;

		case 'expiration_date':
			echo $post_meta;

			if($post_meta < date("Y-m-d", strtotime("+2 week")))
			{
				$renew = check_var('renew', 'int');

				if($renew > 0 && $renew == $id && wp_verify_nonce($_REQUEST['_wpnonce'], 'contract_renew_'.$renew))
				{
					$post_end_date = get_post_meta($id, $meta_prefix.'end_date', true);
					$post_expiration_date = $post_meta;
					$post_extends = get_post_meta($id, $meta_prefix.'extends', true);

					update_post_meta($id, $meta_prefix.'start_date', date("Y-m-d", strtotime($post_end_date." +1 day")));
					update_post_meta($id, $meta_prefix.'end_date', date("Y-m-d", strtotime($post_end_date." +".$post_extends." month")));
					update_post_meta($id, $meta_prefix.'expiration_date', date("Y-m-d", strtotime($post_expiration_date." +".$post_extends." month")));
				}

				else
				{
					echo "<div class='row-actions'>
						<a href='".wp_nonce_url(admin_url("edit.php?post_type=mf_contract&renew=".$id), 'contract_renew_'.$id)."'>".__("Renew", 'lang_contract')."</a>
					</div>";
				}
			}
		break;
	}
}

function meta_boxes_contract($meta_boxes)
{
	$meta_prefix = "mf_contract_";

	$meta_boxes[] = array(
		'id' => $meta_prefix.'settings',
		'title' => __("Settings", 'lang_contract'),
		'post_types' => array('mf_contract'),
		//'context' => 'side',
		'priority' => 'low',
		'fields' => array(
			array(
				'name' => __("Start date", 'lang_contract'),
				'id' => $meta_prefix.'start_date',
				'type' => 'date',
				'js_options' => array(
					//'autoSize' => true,
					'dateFormat' => __("yy-mm-dd", 'lang_contract'),
					'numberOfMonths'  => 1,
					//'showButtonPanel' => true,
				),
				'attributes' => array(),
			),
			array(
				'name' => __("End date", 'lang_contract'),
				'id' => $meta_prefix.'end_date',
				'type' => 'date',
				'js_options' => array(
					//'autoSize' => true,
					'dateFormat' => __("yy-mm-dd", 'lang_contract'),
					'numberOfMonths'  => 1,
					//'showButtonPanel' => true,
				),
				'attributes' => array(),
			),
			array(
				'name' => __("Expiration date", 'lang_contract'),
				'id' => $meta_prefix.'expiration_date',
				'type' => 'date',
				'js_options' => array(
					//'autoSize' => true,
					'dateFormat' => __("yy-mm-dd", 'lang_contract'),
					'numberOfMonths'  => 1,
					//'showButtonPanel' => true,
				),
				'attributes' => array(),
			),
			array(
				'name' => __("Extends", 'lang_contract')." (".__("Months", 'lang_contract').")",
				'id' => $meta_prefix.'extends',
				'type' => 'number',
				'std' => 12,
			),
		)
	);

	return $meta_boxes;
}