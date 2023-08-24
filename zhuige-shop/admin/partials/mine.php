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

        array(
            'id'     => 'beian_icp',
            'type'   => 'fieldset',
            'title'  => 'ICP备案',
            'fields' => array(
                array(
                    'id'    => 'sn',
                    'type'  => 'text',
                    'title' => '备案号',
                ),
                array(
                    'id'    => 'link',
                    'type'  => 'text',
                    'title' => '链接',
                ),
                array(
                    'id'    => 'switch',
                    'type'  => 'switcher',
                    'title' => '是否显示',
                    'default' => ''
                ),
            ),
        ),
        
    )
));
