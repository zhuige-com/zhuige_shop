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
// 登录页
//
CSF::createSection($prefix, array(
    'id' => 'my',
    'title' => '登录',
    'icon'  => 'fas fa-user-lock',
    'fields' => array(

        array(
            'id'      => 'login_logo',
            'type'    => 'media',
            'title'   => '登录页LOGO',
            'library' => 'image',
        ),

        array(
            'id'    => 'login_title',
            'type'  => 'text',
            'title' => '标题',
        ),

        array(
            'id'          => 'login_yhxy',
            'type'        => 'select',
            'title'       => '用户协议',
            'chosen'      => true,
            // 'multiple'    => true,
            'sortable'    => true,
            'ajax'        => true,
            'options'     => 'pages',
            'placeholder' => '选择一个页面',
        ),

        array(
            'id'          => 'login_yszc',
            'type'        => 'select',
            'title'       => '隐私政策',
            'chosen'      => true,
            // 'multiple'    => true,
            'sortable'    => true,
            'ajax'        => true,
            'options'     => 'pages',
            'placeholder' => '选择一个页面',
        ),

    )
));