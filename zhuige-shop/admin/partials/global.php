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

// 基础设置
CSF::createSection($prefix, array(
    'id'    => 'basic',
    'title' => '基础设置',
    'icon'  => 'fas fa-cubes',
    'fields' => array(

        array(
            'id'          => 'basic_title',
            'type'        => 'text',
            'title'       => '标题',
            'placeholder' => '标题'
        ),

        array(
            'id'          => 'basic_desc',
            'type'        => 'text',
            'title'       => '描述',
            'placeholder' => '描述'
        ),

        array(
            'id'     => 'basic_wechat',
            'type'   => 'fieldset',
            'title'  => '微信小程序',
            'fields' => array(
                array(
                    'id'    => 'appid',
                    'type'  => 'text',
                    'title' => 'App ID',
                ),
                array(
                    'id'    => 'secret',
                    'type'  => 'text',
                    'title' => 'App Secret',
                ),
            ),
        ),

        array(
            'id'     => 'basic_wx_pay',
            'type'   => 'fieldset',
            'title'  => '微信支付',
            'fields' => array(
                array(
                    'id'    => 'mchid',
                    'type'  => 'text',
                    'title' => '商户号',
                    'placeholder' => '商户号'
                ),
                array(
                    'id'    => 'key',
                    'type'  => 'text',
                    'title'       => '支付密钥',
                    'placeholder' => '支付密钥'
                ),
            ),
        ),
    )
));
