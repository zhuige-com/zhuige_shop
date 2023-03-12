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

function zhuige_shop_custom_dashboard()
{
	$content = '欢迎使用追格商城小程序小程序! <br/><br/> 微信客服：jianbing2011 (加开源群、问题咨询、项目定制、购买咨询) <br/><br/> <a href="https://www.zhuige.com/download.html" target="_blank">更多免费产品</a>';
	$res = wp_remote_get("https://www.zhuige.com/api/ad/wordpress?id=zhuige_xcx_shop", ['timeout' => 1]);
	if (!is_wp_error($res) && $res['response']['code'] == 200) {
		$data = json_decode($res['body'], TRUE);
		if ($data['code'] == 1) {
			$content = $data['data'];
		}
	}

	echo $content;
}

function zhuige_shop_add_dashboard_widgets()
{
	wp_add_dashboard_widget('zhuige_shop_dashboard_widget', '追格商城小程序', 'zhuige_shop_custom_dashboard');
}

add_action('wp_dashboard_setup', 'zhuige_shop_add_dashboard_widgets');
