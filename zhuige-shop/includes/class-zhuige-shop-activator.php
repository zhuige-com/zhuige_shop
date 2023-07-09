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

class ZhuiGe_Shop_Activator
{
    public static function activate()
    {
        global $wpdb;

        $charset_collate = '';
        if (!empty($wpdb->charset)) {
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }

        if (!empty($wpdb->collate)) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        //订单表
        $table_shop_user_order = $wpdb->prefix . 'zhuige_shop_user_order';
        $sql = "CREATE TABLE IF NOT EXISTS `$table_shop_user_order` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
            `trade_no` varchar(50) NOT NULL DEFAULT '' COMMENT '订单号',
            `wx_trans_id` varchar(50) NOT NULL DEFAULT '' COMMENT '微信流水号',
            `user_id` bigint(20) NOT NULL COMMENT '用户',
            `goods_list` text NOT NULL COMMENT '商品列表',
            `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
            `addressee` varchar(50) NOT NULL DEFAULT '' COMMENT '收件人',
            `mobile` varchar(20) NOT NULL COMMENT '手机',
            `address` varchar(100) NOT NULL DEFAULT '' COMMENT '地址',
            `remark` varchar(100) NOT NULL DEFAULT '' COMMENT '备注',
            `express_type` varchar(50) NOT NULL DEFAULT '' COMMENT '快递类型',
            `express_no` varchar(50) NOT NULL DEFAULT '' COMMENT '快递单号',
            `createtime` int(10) UNSIGNED DEFAULT NULL COMMENT '创建时间',
            `paytime` int(10) UNSIGNED DEFAULT NULL COMMENT '支付时间',
            `canceltime` int(10) UNSIGNED DEFAULT NULL COMMENT '取消时间',
            `confirmtime` int(10) UNSIGNED DEFAULT NULL COMMENT '确认时间',
            `deletetime` int(10) UNSIGNED DEFAULT NULL COMMENT '删除时间',
            PRIMARY KEY (`id`)
        ) $charset_collate;";
        dbDelta($sql);

        //待评论商品列表
        $table_shop_goods_comment = $wpdb->prefix . 'zhuige_shop_goods_comment';
        $sql = "CREATE TABLE IF NOT EXISTS `$table_shop_goods_comment` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
            `user_id` bigint(20) NOT NULL COMMENT '用户',
            `order_id` bigint(20) NOT NULL COMMENT '订单',
            `goods_id` bigint(20) NOT NULL COMMENT '商品',
            `createtime` int(10) UNSIGNED DEFAULT NULL COMMENT '创建时间',
            PRIMARY KEY (`id`)
        ) $charset_collate;";
        dbDelta($sql);
    }
}
