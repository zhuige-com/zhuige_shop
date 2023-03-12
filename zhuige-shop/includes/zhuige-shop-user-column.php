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


add_filter('manage_users_columns', 'zhuige_shop_manage_user_columns', 10, 2);
add_action('manage_users_custom_column', 'zhuige_shop_manage_user_custom_columnns', 10, 3);

function zhuige_shop_manage_user_columns($columns)
{
	unset($columns['name']);

	$new_columns = array();
	$new_columns['cb'] = $columns['cb'];
	$new_columns['username'] = $columns['username'];
	$new_columns['jqnickname'] = '昵称';
	$new_columns['jqmobile'] = '手机号';

	unset($columns['cb']);
	unset($columns['username']);

	return array_merge($new_columns, $columns);
}

function zhuige_shop_manage_user_custom_columnns($value, $column_name, $user_id)
{
	if ('jqnickname' == $column_name) {
		$value = get_user_meta($user_id, 'nickname', true);
	} else if ('jqmobile' == $column_name) {
		$value = get_user_meta($user_id, 'jiangqie_mobile', true);
	}

	return $value;
}

add_filter('get_avatar', 'zhuige_shop_get_avatar', 10, 2);
function zhuige_shop_get_avatar($avatar, $id_or_email, $size = 96, $default = '', $alt = '', $args = null)
{
	$jq_avatar = get_user_meta($id_or_email, 'zhuige_avatar', true);
	if ($jq_avatar) {
		return "<img src='$jq_avatar' class='avatar avatar-32 photo' height='32' width='32'>";
	} else {
		return $avatar;
	}
}
