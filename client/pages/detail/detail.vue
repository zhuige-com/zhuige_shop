<template>
	<view class="content">

		<!-- 大图轮播 -->
		<view v-if="goods && goods.slide && goods.slide.length>0" class="zhuige-detail-img">
			<swiper indicator-dots="true" autoplay="autoplay" circular="ture"
					indicator-color="rgba(255,255,255, 0.3)" indicator-active-color="rgba(255,255,255, 0.8)"
					interval="5000" duration="150" easing-function="linear">
				<swiper-item v-for="(item, index) in goods.slide" :key="index">
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
	import Util from '@/utils/util';
	import Alert from '@/utils/alert';
	import Api from '@/utils/api';
	import Rest from '@/utils/rest';

	import {
		mapGetters,
		mapMutations
	} from 'vuex'
	import store from '@/store/index.js'

	export default {
		data() {
			return {
				goods_id: 0,
				goods: undefined,
			}
		},

		computed: {
			...mapGetters([
				'getCartCount'
			])
		},

		onLoad(options) {
			this.goods_id = options.goods_id;

			this.loadGoods();
		},

		onPullDownRefresh() {
			this.loadGoods();
		},

		onShareAppMessage() {
			return {
				title: this.goods.title  + '_' +  getApp().globalData.appName,
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
			
			clickLink(link) {
				Util.openLink(link);
			},
			
			// clickAddCart(goods_id, count) {
			// 	store.commit('cartGoodsAdd', {goods_id: goods_id, count: count});
			// },
			
			clickCart() {
				uni.switchTab({
					url : '/pages/cart/cart'
				})
			},

			loadGoods() {
				Rest.post(Api.ZHUIGE_SHOP_GOODS_DETAIL, {
					post_id: this.goods_id
				}).then(res => {
					this.goods = res.data;

					uni.stopPullDownRefresh();
				}, err => {
					console.log(err)
				});
			}
		}
	}
</script>

<style>
	@import "@/style/main.css";
</style>
