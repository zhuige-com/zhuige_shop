<template>
	<view class="content">
		<view class="zhuige-category">

			<view class="zhuige-category-nav">
				<view v-for="(cat,index) in cats" :key="index" :class="cur_cat_id==cat.id ? 'active' : ''"
					@click="clickTopCat(cat.id)">
					{{cat.name}}
				</view>
			</view>

			<template v-for="(cat,index) in cats">
				<view v-if="cur_cat_id==cat.id" :key="index" class="zhuige-category-list">

					<view v-for="(subcat,inx) in cat.subs" :key="inx">
						<view @click="clickLink('/pages/list/list?cat_id=' + subcat.id + '&title=' + subcat.name)"
							class="zhuige-category-title">
							{{subcat.name}}
						</view>

						<view class="zhuige-category-group">
							<view v-for="(goods,ginx) in subcat.list" :key="ginx" class="zhuige-category-block"
								@click="clickLink('/pages/detail/detail?goods_id=' + goods.id)">
								<image :src="goods.thumbnail" mode="aspectFill"></image>
								<view class="zhuige-category-goods">
									<view class="goods-name">{{goods.title}}</view>
									<view class="goods-option">
										<view class="price">
											<text>￥</text>
											<text>{{goods.price}}</text>
											<text>￥{{goods.orig_price}}</text>
										</view>
										<view @click.stop="clickGoodsAdd(goods.id, 1)" class="goods-add">
											<uni-icons type="plus-filled" size="28" color="#FF4400"></uni-icons>
										</view>
									</view>
								</view>
							</view>
						</view>
					</view>

				</view>
			</template>

		</view>
	</view>
</template>

<script>
	/*
	 * 追格商城小程序
	 * 作者: 追格
	 * 文档: https://www.zhuige.com/docs/sc.html
	 * gitee: https://gitee.com/zhuige_com/zhuige_shop
	 * github: https://github.com/zhuige-com/zhuige_shop
	 * Copyright © 2022-2023 www.zhuige.com All rights reserved.
	 */

	import Util from '@/utils/util';
	import Alert from '@/utils/alert';
	import Api from '@/utils/api';
	import Rest from '@/utils/rest';
	import {
		mapGetters,
	} from 'vuex'
	import store from '@/store/index.js'

	export default {
		components: {
			
		},
		
		data() {
			return {
				cats: [],

				cur_cat_id: undefined
			}
		},

		computed: {
			...mapGetters([
				'getCartCount'
			])
		},

		onLoad(options) {
			Rest.post(Api.ZHUIGE_SHOP_GOODS_CATEGORY, {}).then(res => {
				this.cats = res.data;
				if (this.cats.length > 0) {
					this.cur_cat_id = this.cats[0].id;
				}
			}, err => {
				console.log(err)
			});
		},

		onShow() {
			Util.updateCartBadge(this.getCartCount);
		},

		onShareAppMessage() {
			return {
				title: '商品分类_' + getApp().globalData.appName,
				path: 'pages/category/category'
			};
		},

		methods: {
			/**
			 * 点击打开链接
			 */
			clickLink(link) {
				Util.openLink(link);
			},

			/**
			 * 点击分类
			 */
			clickTopCat(cat_id) {
				this.cur_cat_id = cat_id;
			},

			/**
			 * 添加商品数量
			 */
			clickGoodsAdd(goods_id, count) {
				store.commit('cartGoodsAdd', {
					goods_id: goods_id,
					count: count
				});

				Util.updateCartBadge(this.getCartCount);
			},
		}
	}
</script>

<style lang="scss" scoped>
	@import "@/style/main.css";
</style>