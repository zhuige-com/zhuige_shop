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

class ZhuiGe_Shop_Admin
{
	private $zhuige_shop;

	private $version;

	public function __construct($zhuige_shop, $version)
	{
		$this->zhuige_shop = $zhuige_shop;
		$this->version = $version;
	}

	public function enqueue_styles()
	{
		wp_enqueue_style($this->zhuige_shop, ZHUIGE_SHOP_BASE_URL . 'admin/css/zhuige-shop-admin.css', array(), $this->version, 'all');
	}

	public function enqueue_scripts()
	{
		wp_enqueue_script($this->zhuige_shop, ZHUIGE_SHOP_BASE_URL . 'admin/js/zhuige-shop-admin.js', array('jquery'), $this->version, false);
	}

	public function create_menu()
	{
		$prefix = 'zhuige-shop';

		CSF::createOptions($prefix, array(
			'framework_title' => '追格商城Free <small>by <a href="https://www.zhuige.com" target="_blank" title="追格商城小程序">www.zhuige.com</a></small>',
			'menu_title' => '追格商城Free',
			'menu_slug'  => 'zhuige-shop',
			'menu_position' => 2,
			'show_bar_menu' => false,
            'show_sub_menu' => false,
			'footer_credit' => 'Thank you for creating with <a href="https://www.zhuige.com/" target="_blank">追格</a>',
			'menu_icon' => 'dashicons-layout',
		));

		$base_dir = plugin_dir_path(__FILE__);
		$base_url = plugin_dir_url(__FILE__);
		require_once $base_dir . 'partials/overview.php';
		require_once $base_dir . 'partials/global.php';
		require_once $base_dir . 'partials/home.php';
		require_once $base_dir . 'partials/category.php';
		require_once $base_dir . 'partials/search.php';
		require_once $base_dir . 'partials/mine.php';
		require_once $base_dir . 'partials/login.php';
		require_once $base_dir . 'partials/goods.php';

		//
        // 备份
        //
        CSF::createSection($prefix, array(
            'title'       => '备份',
            'icon'        => 'fas fa-shield-alt',
            'fields'      => array(
                array(
                    'type' => 'backup',
                ),
            )
        ));

		//过滤ID - 修复多选情况下 ID丢失造成的bug
		function zhuige_shop_sanitize_ids($ids, $type='') {
			if (!is_array($ids)) {
				return $ids;
			}

			$ids_n = [];
			foreach ($ids as $id) {
				if (($type=='cat' && get_category($id))) {
					$ids_n[] = $id;
				} else if ($type=='post' || $type=='page') {
					$post = get_post($id);
					if ($post && $post->post_status == 'publish') {
						$ids_n[] = $id;
					}
				}
			}
			return $ids_n;
		}

		function zhuige_shop_save_before( &$data, $option ) {
			$data['home_rec']['goods_ids'] = zhuige_shop_sanitize_ids($data['home_rec']['goods_ids'], 'post');
			return $data;
		}
		add_filter( 'csf_zhuige-shop_save', 'zhuige_shop_save_before', 10, 2 );

		//追格商品属性
        $prefix_jq_goods_opts = 'zhuige-jq_goods-opt';

        CSF::createMetabox($prefix_jq_goods_opts, array(
            'title'        => '追格商城设置',
            'post_type'    => 'jq_goods',
        ));

        CSF::createSection($prefix_jq_goods_opts, array(
            'fields' => array(

                array(
                    'id'     => 'slide',
                    'type'   => 'group',
                    'title'  => '幻灯片',
                    'fields' => array(
                        array(
                            'id'      => 'image',
                            'type'    => 'media',
                            'title'   => '图片',
                            'library' => 'image',
                        ),
                    ),
                ),

				array(
					'id'          => 'badge',
					'type'        => 'text',
					'title'       => '角标',
					'placeholder' => '角标'
				),

                array(
                    'id'      => 'orig_price',
                    'type'    => 'number',
                    'title'   => '原价格',
                    'unit'    => '元',
                    'default' => '1',
                ),

				array(
                    'id'      => 'price',
                    'type'    => 'number',
                    'title'   => '促销价格',
                    'unit'    => '元',
                    'default' => '1',
                ),

				array(
                    'id'      => 'stock',
                    'type'    => 'number',
                    'title'   => '库存',
                    'unit'    => '套',
                    'default' => '100',
                ),

                array(
                    'id'      => 'quantity',
                    'type'    => 'number',
                    'title'   => '销量',
                    'unit'    => '套',
                    'default' => '0',
                ),

            )
        ));
	}

	public function admin_init()
	{
		$this->handle_external_redirects();
	}

	public function admin_menu()
	{
		add_submenu_page('zhuige-shop', '', '追格产品', 'manage_options', 'ZhuiGe_Shop_setup', array(&$this, 'handle_external_redirects'));
		add_submenu_page('zhuige-shop', '', '新版下载', 'manage_options', 'ZhuiGe_Shop_upgrade', array(&$this, 'handle_external_redirects'));
	}

	public function handle_external_redirects()
	{
		$page = isset($_GET['page']) ? $_GET['page'] : '';
		
		if ('ZhuiGe_Shop_setup' === $page) {
			wp_redirect('https://www.zhuige.com/product.html');
			die;
		}

		if ('ZhuiGe_Shop_upgrade' === $page) {
			wp_redirect('https://www.zhuige.com/product/zhuige-wpmall.html');
			die;
		}
	}
}
