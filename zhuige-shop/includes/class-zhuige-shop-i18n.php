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

class ZhuiGe_Shop_i18n
{
	public function load_plugin_textdomain()
	{
		load_plugin_textdomain(
			'zhuige-shop',
			false,
			dirname(ZHUIGE_SHOP_BASE_NAME) . '/languages/'
		);
	}
}
