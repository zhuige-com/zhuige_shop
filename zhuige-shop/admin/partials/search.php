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

// 分类设置
CSF::createSection($prefix, array(
    'id'    => 'search',
    'title' => '搜索设置',
    'icon'  => 'fas fa-search',
    'fields' => array(

        array(
            'id'    => 'hot_search',
            'type'  => 'text',
            'title' => '热门搜索',
            'subtitle' => '英文逗号分隔',
        ),

    )
));
