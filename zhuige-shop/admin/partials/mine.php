<?php

/*
 * 追格商城小程序
 * Author: 追格
 * Help document: https://www.zhuige.com/product/sc.html
 * github: https://github.com/longwenjunjie/zhuige_shop
 * gitee: https://gitee.com/longwenjunj/zhuige_shop
 * License：GPL-2.0
 * Copyright © 2022 www.zhuige.com All rights reserved.
 */

//
// 我的
//
CSF::createSection($prefix, array(
    'id' => 'my',
    'title' => '我的设置',
    'icon'  => 'fas fa-user-cog',
    'fields' => array(
        array(
            'id'      => 'my_bg',
            'type'    => 'media',
            'title'   => '顶部背景图',
            'library' => 'image',
        ),

        array(
            'id'          => 'my_about',
            'type'        => 'select',
            'title'       => '关于我们',
            'chosen'      => true,
            // 'multiple'    => true,
            'sortable'    => true,
            'ajax'        => true,
            'options'     => 'pages',
            'placeholder' => '选择一个页面',
        ),
    )
));
