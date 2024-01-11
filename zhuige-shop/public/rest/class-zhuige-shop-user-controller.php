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

class ZhuiGe_Shop_User_Controller extends ZhuiGe_Shop_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->module = 'user';
		$this->routes = [
			'login' => 'user_login',
			'logout' => 'user_logout',
			
			'logoff' => 'user_logoff',

			'set_mobile' => 'set_mobile',

			'set_info' => 'set_info',
		];
	}

	/**
	 *用户登录
	 */
	public function user_login($request)
	{
		$code = $this->param_value($request, 'code', '');
		$nickname = $this->param_value($request, 'nickname', '');
		$channel = $this->param_value($request, 'channel', '');
		if (empty($code) || empty($nickname) || empty($channel)) {
			return $this->make_error('缺少参数');
		}

		$session = false;
		if ('weixin' == $channel) {
			$session = $this->wx_code2openid($code);
		}

		if (!is_array($session)) {
			return $this->make_error($session);
		}

		$user = get_user_by('login', $session['openid']);
		$first = 0;
		if ($user) {
			$user_id = $user->ID;
			$nickname = get_user_meta($user->ID, 'nickname', true);
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

			$first = 1;
		}

		update_user_meta($user_id, 'jq_channel', $channel);

		if ('weixin' == $channel) {
			update_user_meta($user_id, 'jq_wx_session_key', $session['session_key']);
		}

		if (isset($session['unionid']) && !empty($session['unionid'])) {
			update_user_meta($user_id, 'jq_unionid', $session['unionid']);
		}

		$zhuige_token = $this->_generate_token();
		update_user_meta($user_id, 'zhuige_token', $zhuige_token);

		$user = [
			'user_id' => $user_id,
			'nickname' => $nickname,
			'avatar' => ZhuiGe_Shop::user_avatar($user_id),
			'token' => $zhuige_token,
		];

		if ($first) {
			$user['first'] = $first;
		}

		return $this->make_success($user);
	}

	/**
	 *用户注销
	 */
	public function user_logout($request)
	{
		$my_user_id = get_current_user_id();
		if (!$my_user_id) {
			return $this->make_error('还没有登陆', -1);
		}

		update_user_meta($my_user_id, 'zhuige_token', '');

		return $this->make_success();
	}

	/**
	 * 注销账户
	 */
	public function user_logoff($request)
	{
		$user_id = get_current_user_id();
		if (!$user_id) {
			return $this->make_error('还没有登陆', -1);
		}

		$res = wp_delete_user($user_id);
		if (!$res) {
			return $this->make_error('请稍后再试~');
		}

		global $wpdb;

		$wpdb->delete($wpdb->prefix . 'comments', ['user_id' => $user_id]);

		$wpdb->delete($wpdb->prefix . 'zhuige_shop_user_order', ['user_id' => $user_id]);

		$wpdb->delete($wpdb->prefix . 'zhuige_shop_goods_comment', ['user_id' => $user_id]);

		return $this->make_success();

	}

	/**
	 * 设置手机号
	 */
	public function set_mobile($request)
	{
		$user_id = get_current_user_id();
		if (!$user_id) {
			return $this->make_error('还没有登陆', -1);
		}

		$code = $this->param_value($request, 'code', '');
		$encrypted_data = $this->param_value($request, 'encrypted_data', '');
		$iv = $this->param_value($request, 'iv', '');
		if (empty($code) || empty($encrypted_data) || empty($iv)) {
			return $this->make_error('缺少参数');
		}

		$os = $this->param_value($request, 'os', '');

		$mobile = '';
		if ($os == 'wx') {
			$wechat = ZhuiGe_Shop::option_value('basic_wechat');
			$app_id = '';
			$app_secret = '';
			if ($wechat) {
				$app_id = $wechat['appid'];
				$app_secret = $wechat['secret'];
			}

			if (!$app_id || !$app_secret) {
				return $this->make_error('未配置微信小程序信息');
			}

			$session = $this->wx_code2openid($code);
			if (!is_array($session)) {
				return $this->make_error($session);
			}

			$res = $this->weixin_decryptData($app_id, $session['session_key'], $encrypted_data, $iv, $data);
			if ($res != 0) {
				return $this->make_error('系统异常');
			}
			$dataMobile = json_decode($data, true);
			$mobile = $dataMobile['phoneNumber'];
		} else {
			return $this->make_error('暂不支持此平台');
		}
		update_user_meta($user_id, 'jiangqie_mobile', $mobile);

		return $this->make_success($mobile);
	}

	/**
	 * 设置用户信息
	 */
	public function set_info($request)
	{
		$user_id = get_current_user_id();
		if (!$user_id) {
			return $this->make_error('还没有登陆', -1);
		}

		$avatar = $this->param_value($request, 'avatar', '');
		$nickname = $this->param_value($request, 'nickname', '');
		if (!$this->msg_sec_check($nickname)) {
			return $this->make_error('请勿发布敏感信息');
		}

		if (empty($nickname)) {
			return $this->make_error('昵称不可为空');
		}
		wp_update_user([
			'ID' => $user_id,
			'nickname' => $nickname,
			'user_nicename' => $nickname,
			'display_name' => $nickname,
		]);

		if (!empty($avatar)) {
			update_user_meta($user_id, 'zhuige_avatar', $avatar);
		}

		return $this->make_success();
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
			return '请在后台设置appid和secret';
		}

		$params = [
			'appid' => $app_id,
			'secret' => $app_secret,
			'js_code' => $code,
			'grant_type' => 'authorization_code'
		];

		$result = wp_remote_get(add_query_arg($params, 'https://api.weixin.qq.com/sns/jscode2session'));
		if (!is_array($result) || is_wp_error($result) || $result['response']['code'] != '200') {
			return '网络请求异常';
		}

		// file_put_contents('wx_login', json_encode($result));

		$body = stripslashes($result['body']);
		$session = json_decode($body, true);

		if (!isset($session['openid']) || empty($session['openid'])) {
			return json_encode($session);
		}

		return $session;
	}

	/**
	 * 检验数据的真实性，并且获取解密后的明文.
	 * @param $encryptedData string 加密的用户数据
	 * @param $iv string 与用户数据一同返回的初始向量
	 * @param $data string 解密后的原文
	 *
	 * @return int 成功 0，失败返回对应的错误码
	 */
	private function weixin_decryptData($appid, $session, $encryptedData, $iv, &$data)
	{
		$ErrorCode = array(
			'OK'                => 0,
			'IllegalAesKey'     => -41001,
			'IllegalIv'         => -41002,
			'IllegalBuffer'     => -41003,
			'DecodeBase64Error' => -41004
		);

		if (strlen($session) != 24) {
			return array('code' => $ErrorCode['IllegalAesKey'], 'message' => 'session_key 长度不合法', 'session_key' => $session);
		}
		$aesKey = base64_decode($session);
		if (strlen($iv) != 24) {
			return array('code' => $ErrorCode['IllegalIv'], 'message' => 'iv 长度不合法', 'iv' => $iv);
		}
		$aesIV = base64_decode($iv);
		$aesCipher = base64_decode($encryptedData);
		$result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
		$data_decode = json_decode($result);
		if ($data_decode  == NULL) {
			return array('code' => $ErrorCode['IllegalBuffer'], 'message' => '解密失败，非法缓存');
		}
		if ($data_decode->watermark->appid != $appid) {
			return array('code' => $ErrorCode['IllegalBuffer'], 'message' => '解密失败，AppID 不正确');
		}
		$data = $result;
		return $ErrorCode['OK'];
	}

	private function _generate_token()
	{
		return md5(uniqid());
	}
}
