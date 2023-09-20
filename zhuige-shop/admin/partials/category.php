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
    'id'    => 'category',
    'title' => '分类设置',
    'icon'  => 'fas fa-coins',
    'fields' => array(

        array(
            'id'          => 'category_cat',
            'type'        => 'select',
            'title'       => '导航设置',
            'placeholder' => '选择分类',
            'chosen'      => true,
            'multiple'    => true,
            'sortable'    => true,
            'options'     => 'categories',
            'query_args'  => array(
                'taxonomy'  => 'jq_goods_cat',
                'parent' => 0
            ),
        ),

        array(
            'id'      => 'category_sub_count',
            'type'    => 'number',
            'title'   => '二级分类商品个数',
            'default' => 4,
        ),

    )
));
