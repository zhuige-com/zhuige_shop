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

class ZhuiGe_Shop_Comment_Controller extends ZhuiGe_Shop_Base_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->module = 'comment';
		$this->routes = [
			'index' => 'comment_index',
			'add' => 'comment_add',
			'delete' => 'comment_delete',
		];
	}

	/**
	 * 文章的评论列表
	 */
	public function comment_index($request)
	{
		$post_id = (int)($this->param_value($request, 'post_id', 0));
		if (empty($post_id)) {
			return $this->make_error('缺少参数');
		}

		$offset = (int)($this->param_value($request, 'offset', 0));

		$my_user_id = get_current_user_id();

		$comments = $this->get_comments($post_id, $my_user_id, 0, $offset);

		return $this->make_success([
			'comments' => $comments,
			'more' => (count($comments) >= ZhuiGe_Shop::POSTS_PER_PAGE ? 'more' : 'nomore')
		]);
	}

	/**
	 * 发布评论
	 */
	public function comment_add($request)
	{
		$my_user_id = get_current_user_id();
		if (!$my_user_id) {
			return $this->make_error('还没有登陆', -1);
		}

		if (!ZhuiGe_Shop::option_value('switch_comment')) {
			return $this->make_error('评论功能未开启');
		}

		if (ZhuiGe_Shop::option_value('switch_comment_mobile')) {
			$mobile = get_user_meta($my_user_id, 'jiangqie_mobile', true);
			if (empty($mobile)) {
				return $this->make_error('还没有绑定手机号', -11);
			}
		}

		$post_id = (int)($this->param_value($request, 'post_id', 0));

		global $wpdb;
		$godds_log = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}zhuige_shop_goods_comment WHERE `user_id`=%d AND `goods_id`=%d",
				$my_user_id,
				$post_id
			),
			ARRAY_A
		);
		if (!$godds_log) {
			return $this->make_error('购买并确认收货后才能评论');
		}

		$parent_id = (int)($this->param_value($request, 'parent_id', 0));
		$content = $this->param_value($request, 'content', '');
		$rate = $this->param_value($request, 'rate', 1);
		if (empty($post_id) || empty($content)) {
			return $this->make_error('缺少参数');
		}

		if (!$this->msg_sec_check($content)) {
			return $this->make_error('请勿发布敏感信息');
		}

		$comment_approved = ZhuiGe_Shop::option_value('switch_comment_verify') ? 0 : 1;
		$comment_id = wp_insert_comment([
			'comment_post_ID' => $post_id,
			'comment_content' => $content,
			'comment_parent' => $parent_id,
			'comment_approved' => $comment_approved,
			'user_id' => $my_user_id,
		]);
		add_comment_meta($comment_id, 'zhuige_goods_rate', $rate);

		$wpdb->delete("{$wpdb->prefix}zhuige_shop_goods_comment", ['user_id' => $my_user_id, 'goods_id' => $post_id], ['%d', '%d']);

		return $this->make_success(['add_count' => $comment_approved]);
	}

	/**
	 * 删除评论
	 */
	public function comment_delete($request)
	{
		$my_user_id = get_current_user_id();
		if (!$my_user_id) {
			return $this->make_error('还没有登陆', -1);
		}

		$comment_id = (int)($this->param_value($request, 'comment_id', 0));
		if (empty($comment_id)) {
			return $this->make_error('缺少参数');
		}

		global $wpdb;
		$table_comments = $wpdb->prefix . 'comments';
		$wpdb->query($wpdb->prepare("DELETE FROM `$table_comments` WHERE `comment_ID`=%d OR `comment_parent`=%d", $comment_id, $comment_id));

		return $this->make_success();
	}

	/**
	 * 评论内容
	 */
	private function get_comments($post_id, $my_user_id, $parent, $offset = null)
	{
		global $wpdb;
		$per_page_count = ZhuiGe_Shop::POSTS_PER_PAGE;
		$table_comments = $wpdb->prefix . 'comments';
		$fields = 'comment_ID, comment_author, comment_date, comment_content, comment_approved, user_id';
		$where = $wpdb->prepare("comment_post_ID=%d AND comment_parent=%d", $post_id, $parent);
		if ($my_user_id) {
			$where = $where . $wpdb->prepare(" AND (comment_approved=1 OR user_id=%d)", $my_user_id);
		} else {
			$where = $where . " AND comment_approved=1";
		}

		$limit = '';
		if ($offset !== null) {
			$limit = $wpdb->prepare("LIMIT %d, %d", $offset, $per_page_count);
		}

		$result = $wpdb->get_results(
			"SELECT $fields FROM `$table_comments` WHERE $where ORDER BY comment_ID DESC $limit"
		);
		$comments = [];
		foreach ($result as $comment) {
			$name = get_user_meta($comment->user_id, 'nickname', true);
			if (!$name) {
				$name = $comment->comment_author;
			}

			$avatar = get_user_meta($comment->user_id, 'zhuige_avatar', true);

			$comments[] = [
				'id' => $comment->comment_ID,
				'user' => [
					'id' => $comment->user_id,
					'name' => $name,
					'avatar' => $avatar,
					'is_me' => ($comment->user_id === $my_user_id) ? 1 : 0,
				],
				'rate' => (int)(get_comment_meta($comment->comment_ID, 'zhuige_goods_rate', true)),
				'content' => $comment->comment_content,
				'approved' => $comment->comment_approved,
				'time' => $comment->comment_date,
			];
		}

		return $comments;
	}
}
