<?php

/*
 * 追格商城小程序
 * Author: 追格
 * Help document: https://www.zhuige.com/product/sc.html
 * github: https://github.com/zhuige-com/zhuige_shop
 * gitee: https://gitee.com/zhuige_com/zhuige_shop
 * License：GPL-2.0
 * Copyright © 2022-2023 www.zhuige.com All rights reserved.
 */

class ZhuiGe_Shop_Public
{

	private $zhuige_shop;

	private $version;

	public function __construct($zhuige_shop, $version)
	{
		$this->zhuige_shop = $zhuige_shop;
		$this->version = $version;
	}

	public function plugin_init()
	{
		$token = '';
		if (isset($_GET['token'])) {
			$token = sanitize_text_field(wp_unslash($_GET['token']));
		} else if (isset($_POST['token'])) {
			$token = sanitize_text_field(wp_unslash($_POST['token']));
		} else {
			$json = json_decode(file_get_contents('php://input'), TRUE);
			if ($json && isset($json['token'])) {
				$token = $json['token'];
			}
		}

		if (empty($token) || $token == 'false') {
			return;
		}

		global $wpdb;
		$table_usermeta = $wpdb->prefix . 'usermeta';
		$user_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT user_id FROM `$table_usermeta` WHERE meta_key='zhuige_token' AND meta_value=%s",
				$token
			)
		);

		if ($user_id) {
			wp_set_current_user($user_id);
		}
	}

	public function enqueue_styles()
	{
		wp_enqueue_style($this->zhuige_shop, ZHUIGE_SHOP_BASE_URL . 'public/css/zhuige-shop-public.css', array(), $this->version, 'all');
	}

	public function enqueue_scripts()
	{
		wp_enqueue_script($this->zhuige_shop, ZHUIGE_SHOP_BASE_URL . 'public/js/zhuige-shop-public.js', array('jquery'), $this->version, false);
	}
}
