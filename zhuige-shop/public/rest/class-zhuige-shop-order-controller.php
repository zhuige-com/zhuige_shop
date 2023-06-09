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

require_once(ZHUIGE_SHOP_BASE_DIR . 'includes/wxpay/class.WxPay.Api.php');
require_once(ZHUIGE_SHOP_BASE_DIR . 'includes/wxpay/class.WxPay.JsApiPay.php');
require_once(ZHUIGE_SHOP_BASE_DIR . 'includes/wxpay/class.WxPay.Notify.php');
require_once(ZHUIGE_SHOP_BASE_DIR . 'includes/wxpay/class.WxPay.Data.php');

class ZhuiGe_Shop_Order_Controller extends ZhuiGe_Shop_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->module = 'order';
		$this->routes = [
			'create' => 'create',
			'confirm' => 'confirm',
			'cancel' => 'cancel',
			'delete' => 'delete',
			'pay' => 'pay',

			'list' => 'order_list',
			'detail' => 'detail',

			'count' => 'count',

			'wx_notify' => 'wx_notify',
		];
	}

	/**
	 * 创建订单
	 */
	public function create($request)
	{
		$my_user_id = get_current_user_id();
		if (!$my_user_id) {
			return $this->make_error('还没有登陆', -1);
		}

		$addressee = $this->param_value($request, 'addressee');
		if (empty($addressee)) {
			return $this->make_error('收件人不可为空');
		}

		$mobile = $this->param_value($request, 'mobile');
		if (empty($mobile)) {
			return $this->make_error('手机号不可为空');
		}

		$address = $this->param_value($request, 'address');
		if (empty($address)) {
			return $this->make_error('地址不可为空');
		}

		$remark = $this->param_value($request, 'remark');

		if (!$this->msg_sec_check($addressee . $mobile . $address . $remark)) {
			return $this->make_error('请勿输入敏感信息');
		}

		$goods_list = $this->param_value($request, 'goods_list');
		if (!$goods_list) {
			return $this->make_error('缺少参数');
		}
		$goods_list = json_decode($goods_list, true);

		$amount = 0;
		$order_name = '';
		foreach ($goods_list as &$goods) {
			$options = get_post_meta($goods['id'], 'zhuige-jq_goods-opt', true);
			if ((int)$goods['count'] > (int)$options['stock']) {
				return $this->make_error('库存不足');
			}

			$post = get_post($goods['id']);
			$goods['name'] = $post->post_title;
			$goods['thumb'] = $this->get_one_post_thumbnail($post, true);
			$goods['price'] = $options['price'];

			$amount += (float)$goods['price'] * (int)$goods['count'];

			if (empty($order_name)) {
				$order_name = $post->post_title;
			}
		}

		if (count($goods_list) > 1) {
			$order_name .= '等商品';
		}

		$trade_no = 'ZG_' . $my_user_id . '_' . time();
		$data = [
			'trade_no' => $trade_no,
			'user_id' => $my_user_id,
			'goods_list' => serialize($goods_list),
			'amount' => $amount,
			'addressee' => $addressee,
			'mobile' => $mobile,
			'address' => $address,
			'remark' => $remark,
			'createtime' => time()
		];

		global $wpdb;

		$wpdb->insert("{$wpdb->prefix}zhuige_shop_user_order", $data);

		$order_id = $wpdb->insert_id;

		foreach ($goods_list as &$goods) {
			$options = get_post_meta($goods['id'], 'zhuige-jq_goods-opt', true);
			$options['stock'] = (int)($options['stock']) - (int)$goods['count'];
			$options['quantity'] = (int)($options['quantity']) + (int)$goods['count'];
			update_post_meta($goods['id'], 'zhuige-jq_goods-opt', $options);
		}

		$pay_params = $this->_get_wx_pay_params($trade_no, $order_name, $amount);
		if (!$pay_params) {
			return $this->make_error('请稍后再支付');
		}

		return $this->make_success(['order_id' => $order_id, 'pay_params' => $pay_params]);
	}

	/**
	 * 确认订单
	 */
	public function confirm($request)
	{
		$my_user_id = get_current_user_id();
		if (!$my_user_id) {
			return $this->make_error('还没有登陆', -1);
		}

		$order_id = (int)($this->param_value($request, 'order_id'));
		if (empty($order_id)) {
			return $this->make_error('缺少参数');
		}

		global $wpdb;
		$order = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}zhuige_shop_user_order WHERE `id`=%d AND `user_id`=%d",
				$order_id,
				$my_user_id
			),
			ARRAY_A
		);
		if (!$order) {
			return $this->make_error('无权查看此订单');
		}

		if (!$order['paytime'] || $order['canceltime'] || $order['deletetime']) {
			return $this->make_error('此订单状态异常');
		}

		$confirmtime = time();
		$wpdb->update("{$wpdb->prefix}zhuige_shop_user_order", ['confirmtime' => $confirmtime], ['id' => $order_id, 'user_id' => $my_user_id], ['%d'], ['%d', '%d']);

		//在待评论列表中记录
		$goods_list = unserialize($order['goods_list']);
		foreach ($goods_list as $goods) {
			$wpdb->insert("{$wpdb->prefix}zhuige_shop_goods_comment", [
				'user_id' => $my_user_id,
				'order_id' => $order['id'],
				'goods_id' => $goods['id'],
				'createtime' => time()
			]);
		}

		return $this->make_success(['confirmtime' => $confirmtime]);
	}

	/**
	 * 取消订单
	 */
	public function cancel($request)
	{
		$my_user_id = get_current_user_id();
		if (!$my_user_id) {
			return $this->make_error('还没有登陆', -1);
		}

		$order_id = (int)($this->param_value($request, 'order_id'));
		if (empty($order_id)) {
			return $this->make_error('缺少参数');
		}

		global $wpdb;
		$order = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}zhuige_shop_user_order WHERE `id`=%d AND `user_id`=%d",
				$order_id,
				$my_user_id
			),
			ARRAY_A
		);
		if (!$order) {
			return $this->make_error('无权查看此订单');
		}

		if ($order['paytime'] || $order['confirmtime'] || $order['deletetime']) {
			return $this->make_error('此订单状态异常');
		}

		$canceltime = time();
		$wpdb->update("{$wpdb->prefix}zhuige_shop_user_order", ['canceltime' => $canceltime], ['id' => $order_id, 'user_id' => $my_user_id], ['%d'], ['%d', '%d']);

		return $this->make_success(['canceltime' => $canceltime]);
	}

	/**
	 * 删除订单
	 */
	public function delete($request)
	{
		$my_user_id = get_current_user_id();
		if (!$my_user_id) {
			return $this->make_error('还没有登陆', -1);
		}

		$order_id = (int)($this->param_value($request, 'order_id'));
		if (empty($order_id)) {
			return $this->make_error('缺少参数');
		}

		global $wpdb;
		$order = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}zhuige_shop_user_order WHERE `id`=%d AND `user_id`=%d",
				$order_id,
				$my_user_id
			),
			ARRAY_A
		);
		if (!$order) {
			return $this->make_error('无权查看此订单');
		}

		if (!$order['canceltime']) {
			return $this->make_error('此订单状态异常');
		}

		$deletetime = time();
		$wpdb->update("{$wpdb->prefix}zhuige_shop_user_order", ['deletetime' => $deletetime], ['id' => $order_id, 'user_id' => $my_user_id], ['%d'], ['%d', '%d']);

		return $this->make_success(['deletetime' => $deletetime]);
	}

	/**
	 * 支付订单
	 */
	public function pay($request)
	{
		$my_user_id = get_current_user_id();
		if (!$my_user_id) {
			return $this->make_error('还没有登陆', -1);
		}

		$order_id = (int)($this->param_value($request, 'order_id'));
		if (empty($order_id)) {
			return $this->make_error('缺少参数');
		}

		global $wpdb;
		$order = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}zhuige_shop_user_order WHERE `id`=%d AND `user_id`=%d",
				$order_id,
				$my_user_id
			),
			ARRAY_A
		);
		if (!$order) {
			return $this->make_error('无权查看');
		}

		if ($order['paytime'] || $order['confirmtime'] || $order['canceltime'] || $order['deletetime']) {
			return $this->make_error('订单异常');
		}

		$goods_list = unserialize($order['goods_list']);

		$order_name = '';
		foreach ($goods_list as &$goods) {
			if (empty($order_name)) {
				$post = get_post($goods['id']);
				$order_name = $post->post_title;
			} else {
				$order_name .= '等商品';
				break;
			}
		}

		$pay_params = $this->_get_wx_pay_params($order['trade_no'], $order_name, $order['amount']);
		if (!$pay_params) {
			return $this->make_error('请稍后再支付');
		}

		return $this->make_success(['pay_params' => $pay_params]);
	}

	/**
	 * 订单列表
	 */
	public function order_list($request)
	{
		$my_user_id = get_current_user_id();
		if (!$my_user_id) {
			return $this->make_error('还没有登陆', -1);
		}

		$offset = (int)($this->param_value($request, 'offset', 0));
		$filter = $this->param_value($request, 'filter', '');
		$where = '';
		if ($filter == 'create') {
			$where = ' AND paytime is null AND confirmtime is null AND canceltime is null ';
		} else if ($filter == 'pay') {
			$where = ' AND paytime is not null AND confirmtime is null ';
		} else if ($filter == 'confirm') {
			$where = ' AND confirmtime is not null ';
		} else if ($filter == 'cancel') {
			$where = ' AND canceltime is not null ';
		}

		global $wpdb;
		$orders = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM `{$wpdb->prefix}zhuige_shop_user_order` WHERE `user_id`=%d AND deletetime is null $where ORDER BY `id` DESC LIMIT %d, %d",
				$my_user_id,
				$offset,
				ZhuiGe_Shop::POSTS_PER_PAGE
			),
			ARRAY_A
		);
		foreach ($orders as &$order) {
			$order['goods_list'] = unserialize($order['goods_list']);
			$order['createtime'] = wp_date('Y.m.d H:i:s', $order['createtime']);
		}

		return $this->make_success(['more' => count($orders) == ZhuiGe_Shop::POSTS_PER_PAGE ? 'more' : 'nomore', 'orders' => $orders]);
	}

	/**
	 * 订单统计
	 */
	public function count($request)
	{
		$my_user_id = get_current_user_id();
		if (!$my_user_id) {
			return $this->make_error('还没有登陆', -1);
		}

		$data = [];

		global $wpdb;
		$table_order = "`{$wpdb->prefix}zhuige_shop_user_order` ";
		$data['all_count'] = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM $table_order WHERE `user_id`=%d AND deletetime is null",
				$my_user_id
			)
		);
		$data['create_count'] = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM $table_order WHERE `user_id`=%d AND deletetime is null AND paytime is null AND confirmtime is null AND canceltime is null",
				$my_user_id
			)
		);
		$data['pay_count'] = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM $table_order WHERE `user_id`=%d AND deletetime is null AND paytime is not null AND confirmtime is null",
				$my_user_id
			)
		);
		$data['confirm_count'] = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM $table_order WHERE `user_id`=%d AND deletetime is null AND confirmtime is not null",
				$my_user_id
			)
		);
		$data['cancel_count'] = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM $table_order WHERE `user_id`=%d AND deletetime is null AND canceltime is not null",
				$my_user_id
			)
		);

		return $this->make_success($data);
	}

	/**
	 * 订单详情
	 */
	public function detail($request)
	{
		$my_user_id = get_current_user_id();
		if (!$my_user_id) {
			return $this->make_error('还没有登陆', -1);
		}

		$order_id = (int)($this->param_value($request, 'order_id'));
		if (empty($order_id)) {
			return $this->make_error('缺少参数');
		}

		global $wpdb;
		$order = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}zhuige_shop_user_order WHERE `id`=%d AND `user_id`=%d",
				$order_id,
				$my_user_id
			),
			ARRAY_A
		);
		if (!$order) {
			return $this->make_error('无权查看');
		}

		if ($order['deletetime']) {
			return $this->make_error('订单不存在');
		}

		$order['goods_list'] = unserialize($order['goods_list']);
		$order['createtime'] = wp_date('Y.m.d H:i:s', $order['createtime']);
		if ($order['paytime']) {
			$order['paytime'] = wp_date('Y.m.d H:i:s', $order['paytime']);
		}

		return $this->make_success(['order' => $order]);
	}

	/**
	 * 微信支付参数
	 */
	private function _get_wx_pay_params($trade_no, $order_name, $amount)
	{
		$my_user_id = get_current_user_id();
		if (!$my_user_id) {
			return false;
		}

		$wechat = ZhuiGe_Shop::option_value('basic_wechat');
		$wx_app_id = '';
		if ($wechat) {
			$wx_app_id = $wechat['appid'];
		}

		$wx_pay = ZhuiGe_Shop::option_value('basic_wx_pay');
		$wx_pay_mchid = '';
		$wx_pay_key = '';
		if ($wx_pay) {
			$wx_pay_mchid = $wx_pay['mchid'];
			$wx_pay_key = $wx_pay['key'];
		}

		if (empty($wx_app_id) || empty($wx_pay_mchid) || empty($wx_pay_key)) {
			return false;
		}

		$user = get_userdata($my_user_id);

		$input = new ZhuiGe_WxPayUnifiedOrder();
		$body = $order_name;
		$input->SetBody($body);
		$input->SetOut_trade_no($trade_no);
		$input->SetTotal_fee(floatval($amount) * 100);
		$input->SetTime_start(date_i18n('YmdHis'));
		$input->SetTime_expire(date('YmdHis', strtotime(date_i18n('YmdHis')) + 600));
		$input->SetNotify_url(get_rest_url(null, $this->namespace . '/' . $this->module . '/wx_notify'));
		$input->SetTrade_type('JSAPI');
		$input->SetOpenid($user->user_login);
		$order = ZhuiGe_WxpayApi::unifiedOrder($input);

		$tools = new ZhuiGe_JsApiPay();
		$js_api_params = $tools->GetJsApiParameters($order);
		$js_api_params['success'] = !isset($js_api_params['return_code']);

		return $js_api_params;
	}

	// 支付VIP通知
	public function wx_notify($request)
	{
		$notify = new ZhuiGe_Shop_PayNotifyCallBack();
		$notify->Handle(false);
	}
}

class ZhuiGe_Shop_PayNotifyCallBack extends ZhuiGe_WxPayNotify
{

	// 重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		// file_put_contents('wx_pay_callback.txt', json_encode($data) . PHP_EOL, FILE_APPEND | LOCK_EX);

		if (!array_key_exists('transaction_id', $data)) {
			$msg = '输入参数不正确';
			return false;
		}

		$wx_pay = ZhuiGe_Shop::option_value('basic_wx_pay');
		$wx_pay_key = '';
		if ($wx_pay) {
			$wx_pay_key = $wx_pay['key'];
		}

		if (!$this->_checkNotifySign($data, $wx_pay_key)) {
			return false;
		}

		$trade_no = $data['out_trade_no'];
		$wx_trans_id = $data['transaction_id'];

		global $wpdb;
		$wpdb->update("{$wpdb->prefix}zhuige_shop_user_order", ['wx_trans_id' => $wx_trans_id, 'paytime' => time()], ['trade_no' => $trade_no]);

		return true;
	}

	// 检查付款通知签名
	private function _checkNotifySign($data, $key)
	{
		ksort($data);
		$buff = '';
		foreach ($data as $k => $v) {
			if ($k == 'sign' ||  is_array($v)) {
				continue;
			}

			$buff .= $k . '=' . $v . '&';
		}

		$string_sign = $buff . 'key=' . $key;
		$sign = strtoupper(md5($string_sign));
		if ($sign == $data['sign']) {
			return true;
		}
	}
}
