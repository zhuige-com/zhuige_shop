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

class ZhuiGe_Shop_Base_Controller extends WP_REST_Controller
{
	public $routes = [];

	public function __construct()
	{
		$this->namespace = 'zhuige-shop';
	}

	public function register_routes()
	{
		foreach ($this->routes as $key => $value) {
			register_rest_route($this->namespace, '/' . $this->module . '/' . $key, [
				[
					'methods' => WP_REST_Server::CREATABLE,
					'callback' => [$this, $value]
				]
			]);
		}
	}

	//组合返回值
	public function make_response($code, $msg, $data = null)
	{
		$response = [
			'code' => $code,
			'msg' => $msg,
		];

		if ($data !== null) {
			$response['data'] = $data;
		}

		return $response;
	}

	//组合返回值 成功
	public function make_success($data = null)
	{
		return $this->make_response(0, '操作成功！', $data);
	}

	//组合返回值 失败
	public function make_error($msg = '', $code = 1)
	{
		return $this->make_response($code, $msg);
	}

	/**
	 * 获取参数的方便方法
	 */
	public function param_value($request, $param_name, $default_value = false)
	{
		if (isset($request[$param_name])) {
			return sanitize_text_field(wp_unslash($request[$param_name]));
		}

		return $default_value;
	}

	/**
	 * 获取一个缩略图
	 */
	public function get_one_post_thumbnail($post_id, $default = true)
	{
		if (has_post_thumbnail($post_id)) {
			$thumbnail_id = get_post_thumbnail_id($post_id);
			if ($thumbnail_id) {
				$attachment = wp_get_attachment_image_src($thumbnail_id, 'full');
				if ($attachment) {
					return $attachment[0];
				}
			}
		}

		$post = get_post($post_id);
		$post_content = $post->post_content;
		preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', do_shortcode($post_content), $matches);
		if ($matches && isset($matches[1]) && isset($matches[1][0])) {
			return $matches[1][0];
		}

		if ($default) {
			return plugins_url('images/default_thumb.png', dirname(__FILE__));
		}

		return '';
	}

	/**
	 * 检查敏感内容
	 */
	public function msg_sec_check($content)
	{
		if (!isset($_REQUEST['os']) || (isset($_REQUEST['os']) && $_REQUEST['os'] != 'wx')) {
			return true;
		}

		$wx_session = ZhuiGe_Shop::get_wx_token();
		$access_token = $wx_session['access_token'];
		if (empty($access_token)) {
			return false;
		}

		$api = 'https://api.weixin.qq.com/wxa/msg_sec_check?access_token=' . $access_token;

		$args = array(
			'method'  => 'POST',
			'body' 	  => json_encode(['content' => $content], JSON_UNESCAPED_UNICODE),
			'headers' => array(
				'Content-Type' => 'application/json'
			),
			'cookies' => array()
		);

		$res = wp_remote_post($api, $args);
		if (is_wp_error($res)) {
			return true;
		}

		if ($res['response']['code'] == 200) {
			$body = json_decode($res['body'], TRUE);
			if ($body['errcode'] == 0) {
				return true;
			}
		}

		return false;
	}
}
