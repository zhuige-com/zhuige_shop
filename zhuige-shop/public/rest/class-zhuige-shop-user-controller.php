<?php

/*
 * 追格商城小程序
 * Author: 追格
 * Help document: https://www.zhuige.com/product/sc.html
 * github: https://github.com/longwenjunjie/zhuige_shop
 * gitee: https://gitee.com/longwenjunj/zhuige_shop
 * License：GPL-2.0
 * Copyright © 2022 www.zhuige.com All rights reserved.
 */

class ZhuiGe_Shop_User_Controller extends ZhuiGe_Shop_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->module = 'user';
		$this->routes = [
			'login' => 'user_login',
		];
	}

	/**
	 *用户登录
	 */
	public function user_login($request)
	{
		$code = $this->param_value($request, 'code', '');
		$nickname = $this->param_value($request, 'nickname', '');
		$avatar = $this->param_value($request, 'avatar', '');
		$channel = $this->param_value($request, 'channel', '');
		if (empty($code) || empty($nickname) || empty($avatar) || empty($channel)) {
			return $this->make_error('缺少参数');
		}

		$session = false;
		if ('weixin' == $channel) {
			$session = $this->wx_code2openid($code);
		}

		if (!$session) {
			return $this->make_error('授权失败');
		}

		$user = get_user_by('login', $session['openid']);
		if ($user) {
			$user_id = $user->ID;
		} else {
			$email_domain = '@zhuige.com';
			$user_id = wp_insert_user([
				'user_login' => $session['openid'],
				'nickname' => $nickname,
				'user_nicename' => $nickname,
				'display_name' => $nickname,
				'user_email' => $session['openid'] . $email_domain,
				'role' => 'subscriber',
				'user_pass' => wp_generate_password(16, false),
			]);
			
			if (is_wp_error($user_id)) {
				return $this->make_error('创建用户失败');
			}
		}
		
		update_user_meta($user_id, 'jq_channel', $channel);

		if ('weixin' == $channel) {
			update_user_meta($user_id, 'jq_wx_session_key', $session['session_key']);
		}

		if (isset($session['unionid']) && !empty($session['unionid'])) {
			update_user_meta($user_id, 'jq_unionid', $session['unionid']);
		}

		update_user_meta($user_id, 'zhuige_avatar', $avatar);

		$zhuige_token = $this->_generate_token();
		update_user_meta($user_id, 'zhuige_token', $zhuige_token);

		$user = array(
			'user_id' => $user_id,
			'nickname' => $nickname,
			'avatar' => $avatar,
			'token' => $zhuige_token,
		);

		return $this->make_success($user);
	}

	/**
	 * 微信登录
	 */
	private function wx_code2openid($code)
	{
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

		$params = [
			'appid' => $app_id,
			'secret' => $app_secret,
			'js_code' => $code,
			'grant_type' => 'authorization_code'
		];

		$result = wp_remote_get(add_query_arg($params, 'https://api.weixin.qq.com/sns/jscode2session'));
		if (!is_array($result) || is_wp_error($result) || $result['response']['code'] != '200') {
			return false;
		}

		// file_put_contents('wx_login', json_encode($result));

		$body = stripslashes($result['body']);
		$session = json_decode($body, true);

		return $session;
	}

	private function _generate_token()
	{
		return md5(uniqid());
	}
}
