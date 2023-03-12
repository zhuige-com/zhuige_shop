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

class ZhuiGe_Shop_Setting_Controller extends ZhuiGe_Shop_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->module = 'setting';
		$this->routes = [
			'home' => 'get_home',
			'mine' => 'get_mine',
			'login' => 'get_login',
		];
	}

	/**
	 * 获取配置 首页
	 */
	public function get_home($request)
	{
		$data = [];

		//小程序名称
		$data['title'] = ZhuiGe_Shop::option_value('basic_title', '追格商城小程序');

		//描述
		$data['desc'] = ZhuiGe_Shop::option_value('basic_desc', '');

		// //关键字
		// $data['keywords'] = ZhuiGe_Shop::option_value('basic_keywords', '');

		//背景图
		$home_bg = ZhuiGe_Shop::option_value('home_bg');
		$data['background'] = ZhuiGe_Shop::option_image_url($home_bg, 'default_bg.jpg');

		//首页分享头图
		$home_thumb = ZhuiGe_Shop::option_value('home_thumb');
		if ($home_thumb && $home_thumb['url']) {
			$data['thumb'] = $home_thumb['url'];
		}

		// 幻灯片
		$slides_org = ZhuiGe_Shop::option_value('home_slide');
		$slides = [];
		if (is_array($slides_org)) {
			foreach ($slides_org as $item) {
				if ($item['switch'] && $item['image'] && $item['image']['url']) {
					$slides[] = [
						'image' => $item['image']['url'],
						'link' => $item['link'],
					];
				}
			}
		}
		$data['slides'] = $slides;

		//图标导航
		$icon_nav_org = ZhuiGe_Shop::option_value('home_nav');
		$icon_navs = [];
		if (is_array($icon_nav_org)) {
			foreach ($icon_nav_org as $item) {
				if ($item['switch'] && $item['image'] && $item['image']['url']) {
					$icon_navs[] = [
						'image' => $item['image']['url'],
						'link' => $item['link'],
						'title' => $item['title'],
					];
				}
			}
		}
		$data['icon_navs'] = $icon_navs;

		//好货推荐
		$home_rec = ZhuiGe_Shop::option_value('home_rec');
		if ($home_rec && $home_rec['switch']) {
			if (empty($home_rec['title'])) {
				$home_rec['title'] = '好货甄选';
			}

			$posts = [];
			if (!empty($home_rec['goods_ids'])) {
				$args = [
					'post_type' => 'jq_goods',
					'post__in' => $home_rec['goods_ids'],
					'orderby' => 'post__in',
					'posts_per_page' => -1,
					'ignore_sticky_posts' => 1,
				];

				$query = new WP_Query();
				$result = $query->query($args);
				foreach ($result as $item) {
					$posts[] = [
						'id' => $item->ID,
						'title' => $item->post_title,
						'thumbnail' => $this->get_one_post_thumbnail($item->ID)
					];
				}
			}
			unset($rec_head['goods_ids']);
			$home_rec['posts'] = $posts;

			$data['home_rec'] = $home_rec;
		}

		//分类导航tab
		$terms = get_terms([
			'taxonomy' => 'jq_goods_cat',
			'hide_empty' => false,
			'parent'   => 0
		]);
		$cats = [['id' => 0, 'name' => '最新']];
		foreach ($terms as $term) {
			$cats[] = [
				'id' => $term->term_id,
				'name' => $term->name
			];
		}
		$data['cats'] = $cats;

		return $this->make_success($data);
	}

	/**
	 * 获取配置 我的
	 */
	public function get_mine($request)
	{
		$data = [];

		$my_bg = ZhuiGe_Shop::option_value('my_bg');
		$data['background'] = ZhuiGe_Shop::option_image_url($my_bg, 'default_bg.jpg');

		// $copyright = ZhuiGe_Shop::option_value('copyright');
		// if ($copyright['switch']) {
		// 	$copyright['logo'] = ZhuiGe_Shop::option_image_url($copyright['logo'], 'logo_f.png');
		// 	$data['copyright'] = $copyright;
		// }

		$my_about = ZhuiGe_Shop::option_value('my_about');
		if ($my_about) {
			$data['page_about'] = '/pages/about/about?page_id=' . $my_about;
		}

		return $this->make_success($data);
	}

	/**
	 * 获取配置 登录
	 */
	public function get_login($request)
	{
		$data = [];

		// $login_bg = ZhuiGe_Shop::option_value('login_bg');
		// $data['background'] = ZhuiGe_Shop::option_image_url($login_bg, 'default_bg.jpg');

		$login_logo = ZhuiGe_Shop::option_value('login_logo');
		$data['logo'] = ZhuiGe_Shop::option_image_url($login_logo, 'logo_f.png');

		$data['title'] = ZhuiGe_Shop::option_value('login_title');

		$login_yhxy = ZhuiGe_Shop::option_value('login_yhxy');
		if ($login_yhxy) {
			$data['yhxy'] = '/pages/about/about?page_id=' . $login_yhxy;
		}

		$login_yszc = ZhuiGe_Shop::option_value('login_yszc');
		if ($login_yszc) {
			$data['yszc'] = '/pages/about/about?page_id=' . $login_yszc;
		}

		$data['mobile'] = ZhuiGe_Shop::option_value('login_require_mobile');

		return $this->make_success($data);
	}
}
