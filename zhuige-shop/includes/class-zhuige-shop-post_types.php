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

class ZhuiGe_Shop_Post_Types
{
    public function create_custom_post_type()
    {
        $goods_labels = array(
            'name'               => '追格商品',
            'singular_name'      => '追格商品', 'post type 单个 item 时的名称，因为英文有复数',
            'add_new'            => '新建商品', '添加新内容的链接名称',
            'add_new_item'       => '新建一个商品',
            'edit_item'          => '编辑商品',
            'new_item'           => '新建商品',
            'all_items'          => '所有商品',
            'view_item'          => '查看商品',
            'search_items'       => '搜索商品',
            'not_found'          => '没有找到有关商品',
            'not_found_in_trash' => '回收站里面没有相关商品',
            'parent_item_colon'  => '',
            'menu_name'          => '追格商品'
        );
        $goods_args = array(
            'labels'        => $goods_labels,
            'description'   => '我们网站的追格商品信息',
            'public'        => true,
            'menu_position' => 5,
            'supports'      => array('title', 'editor', 'thumbnail', 'excerpt', 'comments'),
            'has_archive'   => true
        );
        register_post_type('jq_goods', $goods_args);

        $goods_cat_labels = array(
            'name'              => '商品分类', 'taxonomy 名称',
            'singular_name'     => '商品分类', 'taxonomy 单数名称',
            'search_items'      => '搜索分类',
            'all_items'         => '所有分类',
            'parent_item'       => '该分类的上级分类',
            'parent_item_colon' => '该分类的上级分类：',
            'edit_item'         => '编辑分类',
            'update_item'       => '更新分类',
            'add_new_item'      => '添加新的分类',
            'new_item_name'     => '商品分类',
            'menu_name'         => '商品分类',
        );
        $goods_cat_args = array(
            'labels' => $goods_cat_labels,
            'hierarchical' => true,
        );
        register_taxonomy('jq_goods_cat', 'jq_goods', $goods_cat_args);
    }
}
