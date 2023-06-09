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

class ZhuiGe_Shop
{

	//分页 每页数量
	const POSTS_PER_PAGE = 10;

	protected $loader;

	protected $zhuige_shop;

	protected $version;

	public $admin;
	public $public;
	public $main;

	/**
	 * 获取配置
	 */
	public static function option_value($key, $default = '')
	{
		static $options = false;
		if (!$options) {
			$options = get_option('zhuige-shop');
		}

		if (isset($options[$key]) && !empty($options[$key])) {
			return $options[$key];
		}

		return $default;
	}

	/**
	 * 图片配置项url
	 */
	public static function option_image_url($image, $default = '')
	{
		if ($image && isset($image['url']) && $image['url']) {
			return $image['url'];
		} else {
			if ($default) {
				return plugins_url('public/images/' . $default, dirname(__FILE__));
			} else {
				return $default;
			}
		}
	}

	/**
	 * 用户头像
	 */
	public static function user_avatar($user_id)
	{
		$avatar = get_user_meta($user_id, 'zhuige_avatar', true);
		if (empty($avatar)) {
			$avatar = ZHUIGE_SHOP_BASE_URL . 'public/images/default_avatar.jpg';
		}
		return $avatar;
	}

	/**
	 * 追格商品属性
	 */
	public static function post_goods_property($post_id, $key, $default = '')
	{
		$options = get_post_meta($post_id, 'zhuige-jq_goods-opt', true);
		if (isset($options[$key]) && !empty($options[$key])) {
			return $options[$key];
		}

		return $default;
	}

	/**
	 * 微信 token
	 */
	public static function get_wx_token()
	{
		$access_token = get_option('zhuige-shop-wx-access-token');
		if ($access_token && isset($access_token['expires_in']) && $access_token['expires_in'] > time()) {
			return $access_token;
		}

		$wechat = ZhuiGe_Shop::option_value('basic_wechat');
		$app_id = '';
		$app_secret = '';
		if ($wechat) {
			$app_id = $wechat['appid'];
			$app_secret = $wechat['secret'];
		}

		if (empty($app_id) || empty($app_secret)) {
			return false;
		}

		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$app_id&secret=$app_secret";
		$body = wp_remote_get($url);
		if (!is_array($body) || is_wp_error($body) || $body['response']['code'] != '200') {
			return false;
		}
		$access_token = json_decode($body['body'], TRUE);

		$access_token['expires_in'] = $access_token['expires_in'] + time() - 200;
		update_option('zhuige-shop-wx-access-token', $access_token);

		return $access_token;
	}

	public function __construct()
	{
		$this->zhuige_shop = 'zhuige-shop';
		$this->version = ZHUIGE_SHOP_VERSION;

		$this->main = $this;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies()
	{
		require_once ZHUIGE_SHOP_BASE_DIR . 'includes/class-zhuige-shop-loader.php';
		require_once ZHUIGE_SHOP_BASE_DIR . 'includes/class-zhuige-shop-i18n.php';
		require_once ZHUIGE_SHOP_BASE_DIR . 'includes/class-zhuige-shop-post_types.php';
		require_once ZHUIGE_SHOP_BASE_DIR . 'admin/class-zhuige-shop-admin.php';
		require_once ZHUIGE_SHOP_BASE_DIR . 'public/class-zhuige-shop-public.php';

		/**
		 * rest api
		 */
		require_once ZHUIGE_SHOP_BASE_DIR . 'public/rest/class-zhuige-shop-base-controller.php';
		require_once ZHUIGE_SHOP_BASE_DIR . 'public/rest/class-zhuige-shop-setting-controller.php';
		require_once ZHUIGE_SHOP_BASE_DIR . 'public/rest/class-zhuige-shop-post-controller.php';
		require_once ZHUIGE_SHOP_BASE_DIR . 'public/rest/class-zhuige-shop-user-controller.php';
		require_once ZHUIGE_SHOP_BASE_DIR . 'public/rest/class-zhuige-shop-goods-controller.php';
		require_once ZHUIGE_SHOP_BASE_DIR . 'public/rest/class-zhuige-shop-order-controller.php';
		require_once ZHUIGE_SHOP_BASE_DIR . 'public/rest/class-zhuige-shop-other-controller.php';
		require_once ZHUIGE_SHOP_BASE_DIR . 'public/rest/class-zhuige-shop-comment-controller.php';

		/**
		 * AJAX
		 */
		require_once ZHUIGE_SHOP_BASE_DIR . 'includes/class-zhuige-shop-ajax.php';

		/**
		 * 后台管理
		 */
		require_once ZHUIGE_SHOP_BASE_DIR . 'admin/codestar-framework/codestar-framework.php';

		$this->loader = new ZhuiGe_Shop_Loader();
	}

	private function set_locale()
	{
		$plugin_i18n = new ZhuiGe_Shop_i18n();
		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	private function define_admin_hooks()
	{
		if (!is_admin()) {
			return;
		}

		$this->admin = new ZhuiGe_Shop_Admin($this->get_zhuige_shop(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $this->admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $this->admin, 'enqueue_scripts');

		$zhuige_shop_post_types = new ZhuiGe_Shop_Post_Types();
		$this->loader->add_action('init', $zhuige_shop_post_types, 'create_custom_post_type', 999);

		$this->loader->add_action('init', $this->admin, 'create_menu', 0);
		$this->loader->add_action('admin_init', $this->admin, 'admin_init');
		$this->loader->add_action('admin_menu', $this->admin, 'admin_menu', 20);
	}

	private function define_public_hooks()
	{
		$this->public = new ZhuiGe_Shop_Public($this->get_zhuige_shop(), $this->get_version());

		$this->loader->add_action('init', $this->public, 'plugin_init');

		$zhuige_shop_post_types = new ZhuiGe_Shop_Post_Types();
		$this->loader->add_action('init', $zhuige_shop_post_types, 'create_custom_post_type', 999);

		$this->loader->add_action('wp_enqueue_scripts', $this->public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $this->public, 'enqueue_scripts');

		$controller = [
			new ZhuiGe_Shop_Setting_Controller(),
			new ZhuiGe_Shop_Post_Controller(),
			new ZhuiGe_Shop_User_Controller(),
			new ZhuiGe_Shop_Goods_Controller(),
			new ZhuiGe_Shop_Order_Controller(),
			new ZhuiGe_Shop_Other_Controller(),
			new ZhuiGe_Shop_Comment_Controller(),
		];
		foreach ($controller as $control) {
			$this->loader->add_action('rest_api_init', $control, 'register_routes');
		}
	}

	public function run()
	{
		$this->loader->run();
	}

	public function get_zhuige_shop()
	{
		return $this->zhuige_shop;
	}

	public function get_loader()
	{
		return $this->loader;
	}

	public function get_version()
	{
		return $this->version;
	}
}
