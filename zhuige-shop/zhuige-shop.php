<?php

/**
 * Plugin Name:		追格商城小程序
 * Plugin URI:		https://www.zhuige.com/product/sc.html
 * Description:		WordPress + uni-app 购物商城小程序
 * Version:			1.5.1
 * Author:			追格
 * Author URI:		https://www.zhuige.com/
 * License:			GPLv2 or later
 * License URI:		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain:		zhuige-shop
 */

if (!defined('WPINC')) {
	die;
}

define('ZHUIGE_SHOP_VERSION', '1.5.1');
define('ZHUIGE_SHOP_BASE_DIR', plugin_dir_path(__FILE__));
define('ZHUIGE_SHOP_BASE_NAME', plugin_basename(__FILE__));
define('ZHUIGE_SHOP_BASE_URL', plugin_dir_url(__FILE__));

function activate_zhuige_shop()
{
	require_once ZHUIGE_SHOP_BASE_DIR . 'includes/class-zhuige-shop-activator.php';
	ZhuiGe_Shop_Activator::activate();
}

function deactivate_zhuige_shop()
{
	require_once ZHUIGE_SHOP_BASE_DIR . 'includes/class-zhuige-shop-deactivator.php';
	ZhuiGe_Shop_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_zhuige_shop');
register_deactivation_hook(__FILE__, 'deactivate_zhuige_shop');

function zhuige_shop_action_links($actions)
{
	$actions[] = '<a href="admin.php?page=zhuige-shop">设置</a>';
	$actions[] = '<a href="https://www.zhuige.com/page/docs.html" target="_blank">技术支持</a>';
    return $actions;
}
add_filter('plugin_action_links_' . ZHUIGE_SHOP_BASE_NAME, 'zhuige_shop_action_links');

require ZHUIGE_SHOP_BASE_DIR . 'includes/class-zhuige-shop.php';
require ZHUIGE_SHOP_BASE_DIR . 'includes/zhuige-market.php';
require ZHUIGE_SHOP_BASE_DIR . 'includes/zhuige-shop-function.php';
require ZHUIGE_SHOP_BASE_DIR . 'includes/zhuige-shop-dashboard.php';
require ZHUIGE_SHOP_BASE_DIR . 'includes/zhuige-shop-user-column.php';
require ZHUIGE_SHOP_BASE_DIR . 'includes/zhuige-shop-user-order.php';

function run_zhuige_shop()
{
	$plugin = new ZhuiGe_Shop();
	$plugin->run();
}
run_zhuige_shop();
