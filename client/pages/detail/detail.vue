<template>
	<view class="content">

		<!-- 大图轮播 -->
		<view v-if="goods && goods.slide && goods.slide.length>0" class="zhuige-detail-img">
			<swiper indicator-dots="true" autoplay="autoplay" circular="ture" indicator-color="rgba(255,255,255, 0.3)"
				indicator-active-color="rgba(255,255,255, 0.8)" interval="5000" duration="150" easing-function="linear">
				<swiper-item v-for="(item, index) in goods.slide" :key="index" @click="showSlides(index)">
					<image mode="aspectFill" :src="item.image.url"></image>
				</swiper-item>
			</swiper>
		</view>

		<view v-if="goods" class="zhuige-detail-title">
			<view class="goods-name">{{goods.title}}</view>
			<view class="goods-intro">
				<text v-if="goods.badge.length>0" class="mark">{{goods.badge}}</text>
				<text>{{goods.excerpt}}</text>
			</view>
			<view class="goods-option">
				<view class="price">
					<text>￥</text>
					<text>{{goods.price}}</text>
					<text>￥{{goods.orig_price}}</text>
				</view>
				<view class="numbers">
					<text>库存 {{goods.stock}}</text>
					<text>销量 {{goods.quantity}}</text>
				</view>
			</view>
		</view>

		<view v-if="goods" class="zhuige-goods-detail">
			<view class="goods-title">产品详情</view>
			<view class="goods-detail-view">
				<mp-html :content="goods.content" />
			</view>
		</view>
		
		<view v-if="goods && goods.recs" class="zhuige-goods-recs">
			<view class="recs-title">相关推荐</view>
			<view class="zhuige-goods-list">
				<view v-for="(item,index) in goods.recs" :key="index"
					@click="clickLink('/pages/detail/detail?goods_id=' + item.id)" class="zhuige-goods">
					<image :src="item.thumbnail" mode="aspectFill"></image>
					<view class="zhuige-goods-text">
						<view class="zhuige-goods-title">
							<text v-if="item.badge" class="mark">{{item.badge}}</text>
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
		</view>

		<view class="zhuige-comment-list">
			<view class="zhuige-comment-header">
				<text>用户评论</text>
				<uni-icons @click="clickLink('/pages/comment/comment?goods_id=' + goods_id)" type="chatbubble"
					size="24"></uni-icons>
			</view>

			<template v-if="comments && comments.length>0">
				<view v-for="(item,index) in comments" :key="index" class="zhuige-comment-item">
					<view>
						<image mode="aspectFill" class="avatar" :src="item.user.avatar"></image>
					</view>
					<view class="content-list">
						<view>{{item.user.name}}</view>
						<uni-rate :value="item.rate" :readonly="true" />
						<view class="content">{{item.content}}</view>
						<view class="time">{{item.time}}</view>
					</view>
				</view>
				<uni-load-more :status="loadMore"></uni-load-more>
			</template>
			<template v-else>
				<jiangqie-no-data v-if="loaded"></jiangqie-no-data>
			</template>
		</view>

		<view class="zhuige-goods-bar">
			<view @click="clickCart" class="zhuige-goods-cart-btn">
				<uni-icons type="cart" size="30" color="#ff4400"></uni-icons>
				<view>{{getCartCount}}</view>
			</view>
			<view class="zhuige-goods-btn">
				<view @click="cartGoodsAdd({goods_id: goods.id, count: 1})">加入购物车</view>
				<view @click="clickLink('/pages/order_confirm/order_confirm?goods_id=' + goods.id)">立即购买</view>
			</view>
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
		mapMutations
	} from 'vuex'
	import store from '@/store/index.js'

	import JiangqieNoData from "@/components/nodata/nodata";

	export default {
		components: {
			JiangqieNoData
		},
		
		data() {
			return {
				goods_id: 0,
				goods: undefined,

				comments: [],
				loadMore: 'more',
				loaded: false,
			}
		},

		computed: {
			...mapGetters([
				'getCartCount'
			])
		},

		onLoad(options) {
			if (!options.goods_id) {
				uni.reLaunch({
					url: '/pages/index/index'
				})
				return;
			}
			this.goods_id = options.goods_id;

			this.loadGoods();
			this.loadComments(true);
		},

		onPullDownRefresh() {
			this.loadGoods();
			this.loadComments(true);
		},

		onReachBottom() {
			if (this.loadMore == 'more') {
				this.loadComments(false);
			}
		},

		onShareAppMessage() {
			return {
				title: this.goods.title + '_' + getApp().globalData.appName,
				path: 'pages/detail/detail?goods_id=' + this.goods_id
			};
		},

		// #ifdef MP-WEIXIN
		onShareTimeline() {
			return {
				title: this.goods.title + '_' + getApp().globalData.appName
			};
		},
		// #endif

		methods: {
			...mapMutations(['cartGoodsAdd']),

			/**
			 * 点击打开链接
			 */
			clickLink(link) {
				Util.openLink(link);
			},

			/**
			 * 打开幻灯图片
			 */
			showSlides(index) {
				let urls = [];
				this.goods.slide.forEach(ele => {
					urls.push(ele.image.url)
				})

				uni.previewImage({
					urls: urls,
					current: index
				})
			},

			/**
			 * 打开购物车
			 */
			clickCart() {
				uni.switchTab({
					url: '/pages/cart/cart'
				})
			},

			/**
			 * 加载商品详情
			 */
			loadGoods() {
				Rest.post(Api.ZHUIGE_SHOP_GOODS_DETAIL, {
					post_id: this.goods_id
				}).then(res => {
					this.goods = res.data;

					uni.stopPullDownRefresh();
				}, err => {
					console.log(err)
				});
			},

			/**
			 * 加载评论
			 */
			loadComments(refresh) {
				let params = {
					post_id: this.goods_id
				};
				if (!refresh) {
					params.offset = this.comments.length;
				}
				Rest.post(Api.ZHUIGE_SHOP_COMMENT_INDEX, params).then(res => {
					this.comments = refresh ? res.data.comments : this.comments.concat(res.data.comments);
					this.loadMore = res.data.more;
					this.loaded = true;
				}, err => {
					console.log(err)
				});
			}
		}
	}
</script>

<style lang="scss" scoped>
	@import "@/style/main.css";

	.zhuige-goods-detail {
		padding-bottom: 30rpx;
	}

	.zhuige-comment-list {
		padding: 30rpx 30rpx 140rpx;
		border-top: 16rpx solid #EEEEEE;

		.zhuige-comment-header {
			display: flex;
			justify-content: space-between;
			font-size: 36rpx;
			font-weight: 600;
			padding-bottom: 20rpx;
		}

		.zhuige-comment-item {
			display: flex;
			padding: 10rpx 0;

			.avatar {
				width: 100rpx;
				height: 100rpx;
				border-radius: 50rpx;
			}

			.content-list {
				width: 100%;
				margin-left: 20rpx;
				border-bottom: 1rpx solid #EEEEEE;

				.content {
					font-size: 32rpx;
				}

				.time {
					color: #999999;
				}
			}
		}
	}
	
	.zhuige-goods-recs {
		padding: 30rpx;
		border-top: 16rpx solid #EEEEEE;
	}
	
	.recs-title {
		font-size: 36rpx;
		font-weight: 600;
		padding-bottom: 20rpx;
	}
	
	.zhuige-goods-list {
		padding: 0;
	}
</style>