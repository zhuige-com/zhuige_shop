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

class ZhuiGe_Shop_Goods_Controller extends ZhuiGe_Shop_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->module = 'goods';
		$this->routes = [
			'last' => 'get_last',
			'detail' => 'get_detail',

			'category' => 'get_category',

			'search' => 'get_search',

			'cart' => 'get_cart',
		];
	}

	/**
	 * 按【时间倒序】获取追格商品列表
	 */
	public function get_last($request)
	{
		$offset = (int)($this->param_value($request, 'offset', 0));
		$cat_id = (int)($this->param_value($request, 'cat_id', 0));

		$args = [
			'post_type' => ['jq_goods'],
			'posts_per_page' => ZhuiGe_Shop::POSTS_PER_PAGE,
			'offset' => $offset,
			'orderby' => 'date',
		];

		if ($cat_id) {
			$args['tax_query'] = [
				'relation' => 'AND',
				[
					'taxonomy' => 'jq_goods_cat',
					'field' => 'id',
					'terms' => [$cat_id],
					'operator' => 'IN'
				]
			];
		}

		$list = [];

		$query = new WP_Query();
		$result = $query->query($args);
		foreach ($result as $post) {
			$list[] = $this->_formatPost($post);
		}

		return $this->make_success([
			'list' => $list,
			'more' => (count($result) >= ZhuiGe_Shop::POSTS_PER_PAGE ? 'more' : 'nomore')
		]);
	}

	/**
	 * 获取商品详情
	 */
	public function get_detail($request)
	{
		$post_id = (int)($this->param_value($request, 'post_id'));
		if (!$post_id) {
			return $this->make_error('缺少参数');
		}

		$postObj = get_post($post_id);

		$post = [
			'id' => $postObj->ID,
			'title' => $postObj->post_title,
		];

		//文章摘要
		$post["excerpt"] = $this->_getExcerpt($postObj);

		$post['content'] = apply_filters('the_content', $postObj->post_content);

		$options = get_post_meta($postObj->ID, 'zhuige-jq_goods-opt', true);

		$post = array_merge($post, $options);

		//查询分类
		$cats = get_the_terms($post_id, 'jq_goods_cat');
		$cat_ids = [];
		foreach ($cats as $cat) {
			$cat_ids[] = $cat->term_id;
		}

		// 推荐商品
		$args = [
			'posts_per_page' => 4,
			'offset' => 0,
			'orderby' => 'date',
			'post_type' => ['jq_goods'],
			'tax_query' => array(
				array(
					'taxonomy' => 'jq_goods_cat',
					'field' => 'id',
					'terms' => $cat_ids,
					'operator' => 'IN'
				),
			),
			'post__not_in' => [$post_id]
		];

		$query = new WP_Query();
		$result = $query->query($args);
		$recs = [];
		foreach ($result as $item) {
			$recs[] = $this->_formatPost($item);
		}

		if (count($recs) > 0) {
			$post['recs'] = $recs;
		}

		return $this->make_success($post);
	}

	/**
	 * 分类页配置
	 */
	public function get_category($request)
	{
		$category_cat = ZhuiGe_Shop::option_value('category_cat');
		$term_args = [
			'taxonomy' => 'jq_goods_cat',
			'hide_empty' => false,
			'parent'   => 0,
		];
		if (is_array($category_cat) && count($category_cat) > 0) {
			$term_args['include'] = $category_cat;
			$term_args['orderby'] = 'include';
		}
		$terms = get_terms($term_args);

		$cats = [];
		foreach ($terms as $term) {
			$cats[] = [
				'id' => $term->term_id,
				'name' => $term->name,
				'subs' => []
			];
		}

		$terms = get_terms([
			'taxonomy' => 'jq_goods_cat',
			'hide_empty' => false,
		]);
		foreach ($terms as $term) {
			foreach ($cats as &$cat) {
				if ($term->parent == $cat['id']) {

					$sub_count = (int)(ZhuiGe_Shop::option_value('category_sub_count', 4));

					$args = [
						'post_type' => ['jq_goods'],
						'posts_per_page' => $sub_count,
						'offset' => 0,
						'orderby' => 'date',
						'tax_query' => [
							'relation' => 'AND',
							[
								'taxonomy' => 'jq_goods_cat',
								'field' => 'id',
								'terms' => [$term->term_id],
								'operator' => 'IN'
							]
						]
					];

					$list = [];

					$query = new WP_Query();
					$result = $query->query($args);
					foreach ($result as $post) {
						$list[] = $this->_formatPost($post);
					}

					$cat['subs'][] = [
						'id' => $term->term_id,
						'name' => $term->name,
						'list' => $list
					];
				}
			}
		}

		return $this->make_success($cats);
	}

	/**
	 * 搜索商品
	 */
	public function get_search($request)
	{
		$offset = (int)($this->param_value($request, 'offset', 0));
		$search = $this->param_value($request, 'search', '');

		if (empty($search)) {
			return $this->make_error('缺少参数');
		}

		$os = $this->param_value($request, 'os', 'wx');
		if (!$this->msg_sec_check($search, $os)) {
			return $this->make_error('请勿搜索敏感信息');
		}

		$args = [
			'posts_per_page' => ZHuige_Shop::POSTS_PER_PAGE,
			'offset' => $offset,
			'orderby' => 'date',
			's' => $search,
			'post_type' => ['jq_goods']
		];

		$query = new WP_Query();
		$result = $query->query($args);
		$resources = [];
		foreach ($result as $post) {
			$resources[] = $this->_formatPost($post);
		}

		return $this->make_success([
			'list' => $resources,
			'more' => (count($result) >= ZHuige_Shop::POSTS_PER_PAGE ? 'more' : 'nomore')
		]);
	}

	/**
	 * 购物车产品信息
	 */
	public function get_cart($request)
	{
		$goods_ids = $this->param_value($request, 'goods_ids');
		if (empty($goods_ids)) {
			return $this->make_success(['list' => []]);
		}

		$args = [
			'post_type' => ['jq_goods'],
			'post__in' => explode(',', $goods_ids),
			'posts_per_page' => -1,
			'offset' => 0,
			'orderby' => 'date',
		];

		$list = [];

		$query = new WP_Query();
		$result = $query->query($args);
		foreach ($result as $post) {
			$list[] = $this->_formatPost($post);
		}

		return $this->make_success([
			'list' => $list
		]);
	}

	/**
	 * 获取摘要
	 */
	private function _getExcerpt($post)
	{
		if ($post->post_excerpt) {
			return html_entity_decode(wp_trim_words($post->post_excerpt, 50, '...'));
		} else {
			$content = apply_filters('the_content', $post->post_content);
			return html_entity_decode(wp_trim_words($content, 50, '...'));
		}
	}

	/**
	 * 格式化文章
	 */
	private function _formatPost($post)
	{
		$data = [
			'id' => $post->ID,
			'title' => $post->post_title,
		];

		//缩略图
		$data['thumbnail'] = $this->get_one_post_thumbnail($post, true);

		$options = get_post_meta($post->ID, 'zhuige-jq_goods-opt', true);

		return array_merge($data, $options);
	}
}
