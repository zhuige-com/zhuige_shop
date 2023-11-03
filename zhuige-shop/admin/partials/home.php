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

// 首页设置
CSF::createSection($prefix, array(
    'id'    => 'home',
    'title' => '首页设置',
    'icon'  => 'fas fa-home',
    'fields' => array(

        array(
            'id'      => 'home_bg',
            'type'    => 'media',
            'title'   => '顶部背景图',
            'library' => 'image',
        ),

        array(
            'id'          => 'home_title',
            'type'        => 'text',
            'title'       => '分享标题',
            'placeholder' => '分享标题'
        ),

        array(
            'id'      => 'home_thumb',
            'type'    => 'media',
            'title'   => '分享缩略图',
            'library' => 'image',
        ),

        array(
            'id'     => 'home_slide',
            'type'   => 'group',
            'title'  => '幻灯片',
            'fields' => array(
                array(
                    'id'       => 'title',
                    'type'     => 'text',
                    'title'    => '标题',
                ),
                array(
                    'id'      => 'image',
                    'type'    => 'media',
                    'title'   => '图片',
                    'library' => 'image',
                ),
                array(
                    'id'       => 'link',
                    'type'     => 'text',
                    'title'    => '链接',
                    'default'  => 'https://www.zhuige.com',
                ),
                array(
                    'id'    => 'switch',
                    'type'  => 'switcher',
                    'title' => '开启/停用',
                    'default' => '1'
                ),
            ),
        ),

        array(
            'id'     => 'home_nav',
            'type'   => 'group',
            'title'  => '导航项',
            'fields' => array(
                array(
                    'id'          => 'title',
                    'type'        => 'text',
                    'title'       => '标题',
                    'placeholder' => '标题'
                ),
                array(
                    'id'      => 'image',
                    'type'    => 'media',
                    'title'   => '图片',
                    'library' => 'image',
                ),
                array(
                    'id'       => 'link',
                    'type'     => 'text',
                    'title'    => '链接',
                    'default'  => 'https://www.zhuige.com',
                ),
                array(
                    'id'    => 'switch',
                    'type'  => 'switcher',
                    'title' => '开启/停用',
                    'default' => '1'
                ),
            ),
        ),

        array(
            'id'     => 'home_rec',
            'type'   => 'fieldset',
            'title'  => '推荐商品',
            'fields' => array(
                array(
                    'id'          => 'title',
                    'type'        => 'text',
                    'title'       => '标题',
                    'placeholder' => '标题'
                ),

                array(
                    'id'          => 'goods_ids',
                    'type'        => 'select',
                    'title'       => '选择商品',
                    'chosen'      => true,
                    'multiple'    => true,
                    'sortable'    => true,
                    'ajax'        => true,
                    'options'     => 'posts',
                    'query_args'  => array(
                        'post_type' => 'jq_goods',
                    ),
                    'placeholder' => '请选择商品',
                ),

                array(
                    'id'    => 'switch',
                    'type'  => 'switcher',
                    'title' => '开启/停用',
                    'subtitle' => '是否显示活动区域',
                    'default' => '1'
                ),
            ),
        ),

        array(
            'id'          => 'goods_cat',
            'type'        => 'select',
            'title'       => '导航设置',
            'placeholder' => '选择分类',
            'chosen'      => true,
            'multiple'    => true,
            'sortable'    => true,
            'options'     => 'categories',
            'query_args'  => array(
                'taxonomy'  => 'jq_goods_cat'
            ),
        ),

        array(
            'id'     => 'home_ad_pop',
            'type'   => 'fieldset',
            'title'  => '弹窗广告',
            'fields' => array(
                array(
                    'id'      => 'image',
                    'type'    => 'media',
                    'title'   => '图片',
                    'library' => 'image',
                ),
                array(
                    'id'       => 'link',
                    'type'     => 'text',
                    'title'    => '链接',
                    'default'  => 'https://www.zhuige.com',
                ),
                array(
                    'id'    => 'switch',
                    'type'  => 'switcher',
                    'title' => '开启/停用',
                    'default' => '1'
                ),
                array(
                    'id'    => 'interval',
                    'type'  => 'number',
                    'title' => '间隔时间',
                    'subtitle' => '单位（小时）',
                ),
            ),
        ),
    )
));
