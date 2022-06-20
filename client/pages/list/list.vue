<template>
	<view class="content">

		<!-- 商品列表 -->
		<template v-if="goods_list.length>0">
			<view class="zhuige-goods-list">

				<view v-for="(item,index) in goods_list" :key="index"
					@click="clickLink('/pages/detail/detail?goods_id=' + item.id)" class="zhuige-goods">
					<image :src="item.thumbnail" mode="aspectFill"></image>
					<view class="zhuige-goods-text">
						<view class="zhuige-goods-title">
							<text>{{item.title}}</text>
						</view>
						<view class="zhuige-goods-price">
							<view class="promotion">
								<text>￥</text>
								<text>{{item.price}}</text>
							</view>
							<view class="original">
								<text>￥{{item.orig_price}}</text>
							</view>
						</view>
					</view>
				</view>

			</view>
			<uni-load-more :status="loadMore"></uni-load-more>
		</template>
		<template v-else>
			<jiangqie-no-data v-if="loaded"></jiangqie-no-data>
		</template>

	</view>
</template>

<script>
	
	/*
	 * 追格商城小程序
	 * 作者: 追格
	 * 文档: https://www.zhuige.com/docs/sc.html
	 * gitee: https://gitee.com/zhuige_com/zhuige_shop
	 * github: https://github.com/zhuige-com/zhuige_shop
	 * Copyright © 2022 www.zhuige.com All rights reserved.
	 */
	
	import Util from '@/utils/util';
	import Alert from '@/utils/alert';
	import Api from '@/utils/api';
	import Rest from '@/utils/rest';
	import JiangqieNoData from "@/components/nodata/nodata";

	export default {
		data() {
			this.cat_id = undefined;
			
			return {
				title: '商品列表',

				goods_list: [],
				loadMore: 'more',
				loaded: false,
			}
		},

		components: {
			JiangqieNoData,
		},

		onLoad(options) {
			if (options.title) {
				this.title = options.title;
				uni.setNavigationBarTitle({
					title: this.title
				})
			}

			if (options.cat_id) {
				this.cat_id = options.cat_id;
			} else if (options.search) {
				this.search = options.search;
			}

			this.loadGoods();
		},

		onPullDownRefresh() {
			this.goods_list = [];
			this.loadGoods();
		},

		onShareAppMessage() {
			let path = 'pages/list/list?title=' + this.title;
			
			if (this.cat_id) {
				path = path + '&cat_id=' + this.cat_id;
			} else if (this.search) {
				path = path + '&search=' + this.search;
			}
			
			return {
				title: this.title + '_' + getApp().globalData.appName,
				path: path
			};
		},

		methods: {
			clickLink(link) {
				Util.openLink(link);
			},
			
			loadGoods() {
				if (this.loadMore == 'loading') {
					return;
				}
				this.loadMore = 'loading';

				let params = {
					offset: this.goods_list.length
				};

				let url = Api.ZHUIGE_SHOP_GOODS_LAST;
				if (this.cat_id) {
					params.cat_id = this.cat_id;
				} else if (this.search) {
					params.search = this.search;
					url = Api.ZHUIGE_SHOP_GOODS_SEARCH;
				}

				Rest.post(url, params).then(res => {
					this.goods_list = this.goods_list.concat(res.data.list);
					this.loadMore = res.data.more;
					this.loaded = true;
				});
			}
		}
	}
</script>

<style lang="scss" scoped>
	@import "@/style/main.css";

	page,
	.content {
		background: #FBFBFB;
	}
</style>
