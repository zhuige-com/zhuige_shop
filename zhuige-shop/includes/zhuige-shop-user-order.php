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

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WP_List_Table')) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

require dirname(__FILE__) . '/class-zhuige-shop-user-order-list.php';

add_action('admin_menu', 'zhuige_shop_add_user_order_menu');
function zhuige_shop_add_user_order_menu()
{
	add_menu_page(
		'追格商城订单', 			// Page title.
		'追格商城订单',        		// Menu title.
		'activate_plugins',			// Capability.
		'zhuige_shop_user_order',			// Menu slug.
		'zhuige_shop_render_user_order',		// Callback function.
		'dashicons-chart-bar',
		3
	);
}

function zhuige_shop_render_user_order()
{
	$action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '';

	if ('edit' == $action) {
		$id = isset($_GET['id']) ? sanitize_text_field(wp_unslash($_GET['id'])) : '';

		$order = [];
		global $wpdb;
		$table_shop_user_order = $wpdb->prefix . 'zhuige_shop_user_order';
		$order = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $table_shop_user_order WHERE id=%d",
				$id
			),
			ARRAY_A
		);

		if (isset($_POST['submit'])) {
			$express_type = isset($_POST['express_type']) ? sanitize_text_field(wp_unslash($_POST['express_type'])) : '';
			$express_no = isset($_POST['express_no']) ? sanitize_text_field(wp_unslash($_POST['express_no'])) : '';
			$error = '';
			$success = '';

			$udata = [
				'express_type' => $express_type,
				'express_no' => $express_no,
			];
			$wdata = [
				'id' => $id,
			];
			$uformat = [
				'%s',
				'%s',
			];
			$wformat = [
				'%d'
			];
			$wpdb->update($table_shop_user_order, $udata, $wdata, $uformat, $wformat);

			$success = '修改成功';
		}
?>
		<div class="wrap">
			<h1>设置快递信息</h1>
			<?php if (isset($error) && $error) { ?><div class="notice notice-error">
					<ul>
						<li><?php echo $error; ?></li>
					</ul>
				</div><?php } ?>
			<?php if (isset($success) && $success) { ?>
				<div class="notice notice-success">
					<ul>
						<li><?php echo $success; ?></li>
					</ul>
				</div>
				<script>
					setTimeout(() => {
						window.location.href = '<?php echo add_query_arg(['page' => 'zhuige_shop_user_order'], 'admin.php'); ?>';
					}, 1000);
				</script>
			<?php } ?>
			<form method="post" enctype="multipart/form-data">
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="category">订单号</label>
						</th>
						<td>
							<?php
							echo $order['trade_no'];
							?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="category">商品</label>
						</th>
						<td>
							<?php
							$goods_list = unserialize($order['goods_list']);

							$content = "<table>";
							foreach ($goods_list as $goods) {
								$content .= "<tr>";
								$content .= "<td><img src='" . $goods['thumb'] . "' style='width:48px;height:48px;'/></td>";
								$content .= "<td>" . $goods['name'] . "-";
								$content .= "" . $goods['price'] . "元 X " . $goods['count'] . "</td>";
								$content .= "</tr>";
							}
							$content .= "</table>";

							echo $content;
							?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="category">收件人</label>
						</th>
						<td>
							<?php
							echo $order['addressee'] . '<br/>';
							echo $order['mobile'] . '<br/>';
							echo $order['address'] . '<br/>';
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="express_type">快递类型</label></th>
						<td><input type="text" class="regular-text" name="express_type" id="express_type" value="<?php echo $order['express_type']; ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label for="express_no">快递单号</label></th>
						<td><input type="text" class="regular-text" name="express_no" id="express_no" value="<?php echo $order['express_no']; ?>"></td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button button-primary" value="修改">
				</p>
			</form>
		</div>
	<?php
	} else {
		$user_order_list = new ZhuiGe_Shop_User_Order_List();
		$search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
		$user_order_list->prepare_items($search);
	?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php echo esc_html(get_admin_page_title()); ?></h1>

			<?php
			if (strlen($search)) {
				echo '<span class="subtitle">';
				printf(
					__('Search results for: %s'),
					'<strong>' . esc_html($search) . '</strong>'
				);
				echo '</span>';
			}
			?>
			<hr class="wp-header-end">

			<?php $user_order_list->views(); ?>

			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']) ?>" />
				<?php $user_order_list->search_box('搜索', 'search_id'); ?>
			</form>

			<form id="movies-filter" method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']) ?>" />
				<?php $user_order_list->display() ?>
			</form>
		</div>
<?php
	}
}
