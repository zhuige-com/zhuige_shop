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

class ZhuiGe_Shop_Post_Controller extends ZhuiGe_Shop_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->module = 'post';
		$this->routes = [
			'page' => 'get_page',
		];
	}

	/**
	 * 获取页面详情
	 */
	public function get_page($request)
	{
		$page_id = (int)($this->param_value($request, 'page_id'));
		$page_id = (int)$page_id;
		if (!$page_id) {
			return $this->make_error('缺少参数');
		}

		global $wpdb;
		$table_post = $wpdb->prefix . 'posts';
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT post_title, post_content FROM `$table_post` WHERE ID=%d",
				$page_id
			)
		);
		if (!$result) {
			return $this->make_error('未找到文章');
		}
		$page['title'] = $result->post_title;
		$page['content'] = apply_filters('the_content', $result->post_content);

		return $this->make_success($page);
	}
}
