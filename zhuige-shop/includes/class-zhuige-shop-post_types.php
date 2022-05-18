<?php

/*
 * 追格商城小程序
 * Author: 追格
 * Help document: https://www.zhuige.com/product/sc.html
 * github: https://github.com/zhuige-com/zhuige_shop
 * gitee: https://gitee.com/zhuige_com/zhuige_shop
 * License：GPL-2.0
 * Copyright © 2022 www.zhuige.com All rights reserved.
 */

class ZhuiGe_Shop_Post_Types
{
    private function register_single_post_type($fields)
    {
        $labels = array(
            'name'                  => $fields['plural'],
            'singular_name'         => $fields['singular'],
            'menu_name'             => $fields['menu_name'],
            'new_item'              => sprintf('New %s', 'plugin-name', $fields['singular']),
            'add_new_item'          => sprintf('Add new %s', 'plugin-name', $fields['singular']),
            'edit_item'             => sprintf('Edit %s', 'plugin-name', $fields['singular']),
            'view_item'             => sprintf('View %s', 'plugin-name', $fields['singular']),
            'view_items'            => sprintf('View %s', 'plugin-name', $fields['plural']),
            'search_items'          => sprintf('Search %s', 'plugin-name', $fields['plural']),
            'not_found'             => sprintf('No %s found', 'plugin-name', strtolower($fields['plural'])),
            'not_found_in_trash'    => sprintf('No %s found in trash', 'plugin-name', strtolower($fields['plural'])),
            'all_items'             => sprintf('All %s', 'plugin-name', $fields['plural']),
            'archives'              => sprintf('%s Archives', 'plugin-name', $fields['singular']),
            'attributes'            => sprintf('%s Attributes', 'plugin-name', $fields['singular']),
            'insert_into_item'      => sprintf('Insert into %s', 'plugin-name', strtolower($fields['singular'])),
            'uploaded_to_this_item' => sprintf('Uploaded to this %s', 'plugin-name', strtolower($fields['singular'])),

            /* Labels for hierarchical post types only. */
            'parent_item'           => sprintf('Parent %s', 'plugin-name', $fields['singular']),
            'parent_item_colon'     => sprintf('Parent %s:', 'plugin-name', $fields['singular']),

            /* Custom archive label.  Must filter 'post_type_archive_title' to use. */
            'archive_title'        => $fields['plural'],
        );

        $args = array(
            'labels'             => $labels,
            'description'        => (isset($fields['description'])) ? $fields['description'] : '',
            'public'             => (isset($fields['public'])) ? $fields['public'] : true,
            'publicly_queryable' => (isset($fields['publicly_queryable'])) ? $fields['publicly_queryable'] : true,
            'exclude_from_search' => (isset($fields['exclude_from_search'])) ? $fields['exclude_from_search'] : false,
            'show_ui'            => (isset($fields['show_ui'])) ? $fields['show_ui'] : true,
            'show_in_menu'       => (isset($fields['show_in_menu'])) ? $fields['show_in_menu'] : true,
            'query_var'          => (isset($fields['query_var'])) ? $fields['query_var'] : true,
            'show_in_admin_bar'  => (isset($fields['show_in_admin_bar'])) ? $fields['show_in_admin_bar'] : true,
            'capability_type'    => (isset($fields['capability_type'])) ? $fields['capability_type'] : 'post',
            'has_archive'        => (isset($fields['has_archive'])) ? $fields['has_archive'] : true,
            'hierarchical'       => (isset($fields['hierarchical'])) ? $fields['hierarchical'] : true,
            'supports'           => (isset($fields['supports'])) ? $fields['supports'] : array(
                'title',
                'editor',
                'excerpt',
                'author',
                'thumbnail',
                'comments',
                'trackbacks',
                'custom-fields',
                'revisions',
                'page-attributes',
                'post-formats',
            ),
            'menu_position'      => (isset($fields['menu_position'])) ? $fields['menu_position'] : 21,
            'menu_icon'          => (isset($fields['menu_icon'])) ? $fields['menu_icon'] : 'dashicons-admin-generic',
            'show_in_nav_menus'  => (isset($fields['show_in_nav_menus'])) ? $fields['show_in_nav_menus'] : true,
            'show_in_rest'       => (isset($fields['show_in_rest'])) ? $fields['show_in_rest'] : true,
        );

        if (isset($fields['rewrite'])) {
            $args['rewrite'] = $fields['rewrite'];
        }

        if ($fields['custom_caps']) {
            $args['capabilities'] = array(
                // Meta capabilities
                'edit_post'                 => 'edit_' . strtolower($fields['singular']),
                'read_post'                 => 'read_' . strtolower($fields['singular']),
                'delete_post'               => 'delete_' . strtolower($fields['singular']),

                // Primitive capabilities used outside of map_meta_cap():
                'edit_posts'                => 'edit_' . strtolower($fields['plural']),
                'edit_others_posts'         => 'edit_others_' . strtolower($fields['plural']),
                'publish_posts'             => 'publish_' . strtolower($fields['plural']),
                'read_private_posts'        => 'read_private_' . strtolower($fields['plural']),

                // Primitive capabilities used within map_meta_cap():
                'delete_posts'              => 'delete_' . strtolower($fields['plural']),
                'delete_private_posts'      => 'delete_private_' . strtolower($fields['plural']),
                'delete_published_posts'    => 'delete_published_' . strtolower($fields['plural']),
                'delete_others_posts'       => 'delete_others_' . strtolower($fields['plural']),
                'edit_private_posts'        => 'edit_private_' . strtolower($fields['plural']),
                'edit_published_posts'      => 'edit_published_' . strtolower($fields['plural']),
                'create_posts'              => 'edit_' . strtolower($fields['plural'])

            );

            $args['map_meta_cap'] = true;

            $this->assign_capabilities($args['capabilities'], $fields['custom_caps_users']);
        }

        if (isset($fields['taxonomies']) && is_array($fields['taxonomies'])) {
            foreach ($fields['taxonomies'] as $taxonomy) {
                $this->register_single_post_type_taxnonomy($taxonomy);
            }
        }

        register_post_type($fields['slug'], $args);
    }

    private function register_single_post_type_taxnonomy($tax_fields)
    {
        $labels = array(
            'name'                       => $tax_fields['plural'],
            'singular_name'              => $tax_fields['single'],
            'menu_name'                  => $tax_fields['plural'],
            'all_items'                  => sprintf('All %s', 'plugin-name', $tax_fields['plural']),
            'edit_item'                  => sprintf('Edit %s', 'plugin-name', $tax_fields['single']),
            'view_item'                  => sprintf('View %s', 'plugin-name', $tax_fields['single']),
            'update_item'                => sprintf('Update %s', 'plugin-name', $tax_fields['single']),
            'add_new_item'               => sprintf('Add New %s', 'plugin-name', $tax_fields['single']),
            'new_item_name'              => sprintf('New %s Name', 'plugin-name', $tax_fields['single']),
            'parent_item'                => sprintf('Parent %s', 'plugin-name', $tax_fields['single']),
            'parent_item_colon'          => sprintf('Parent %s:', 'plugin-name', $tax_fields['single']),
            'search_items'               => sprintf('Search %s', 'plugin-name', $tax_fields['plural']),
            'popular_items'              => sprintf('Popular %s', 'plugin-name', $tax_fields['plural']),
            'separate_items_with_commas' => sprintf('Separate %s with commas', 'plugin-name', $tax_fields['plural']),
            'add_or_remove_items'        => sprintf('Add or remove %s', 'plugin-name', $tax_fields['plural']),
            'choose_from_most_used'      => sprintf('Choose from the most used %s', 'plugin-name', $tax_fields['plural']),
            'not_found'                  => sprintf('No %s found', 'plugin-name', $tax_fields['plural']),
        );

        $args = array(
            'label'                 => $tax_fields['plural'],
            'labels'                => $labels,
            'hierarchical'          => (isset($tax_fields['hierarchical']))          ? $tax_fields['hierarchical']          : true,
            'public'                => (isset($tax_fields['public']))                ? $tax_fields['public']                : true,
            'show_ui'               => (isset($tax_fields['show_ui']))               ? $tax_fields['show_ui']               : true,
            'show_in_nav_menus'     => (isset($tax_fields['show_in_nav_menus']))     ? $tax_fields['show_in_nav_menus']     : true,
            'show_tagcloud'         => (isset($tax_fields['show_tagcloud']))         ? $tax_fields['show_tagcloud']         : true,
            'meta_box_cb'           => (isset($tax_fields['meta_box_cb']))           ? $tax_fields['meta_box_cb']           : null,
            'show_admin_column'     => (isset($tax_fields['show_admin_column']))     ? $tax_fields['show_admin_column']     : true,
            'show_in_quick_edit'    => (isset($tax_fields['show_in_quick_edit']))    ? $tax_fields['show_in_quick_edit']    : true,
            'update_count_callback' => (isset($tax_fields['update_count_callback'])) ? $tax_fields['update_count_callback'] : '',
            'show_in_rest'          => (isset($tax_fields['show_in_rest']))          ? $tax_fields['show_in_rest']          : true,
            'rest_base'             => $tax_fields['taxonomy'],
            'rest_controller_class' => (isset($tax_fields['rest_controller_class'])) ? $tax_fields['rest_controller_class'] : 'WP_REST_Terms_Controller',
            'query_var'             => $tax_fields['taxonomy'],
            'rewrite'               => (isset($tax_fields['rewrite']))               ? $tax_fields['rewrite']               : true,
            'sort'                  => (isset($tax_fields['sort']))                  ? $tax_fields['sort']                  : '',
        );

        $args = apply_filters($tax_fields['taxonomy'] . '_args', $args);

        register_taxonomy($tax_fields['taxonomy'], $tax_fields['post_types'], $args);
    }

    public function assign_capabilities($caps_map, $users)
    {
        foreach ($users as $user) {
            $user_role = get_role($user);
            foreach ($caps_map as $cap_map_key => $capability) {
                $user_role->add_cap($capability);
            }
        }
    }

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
