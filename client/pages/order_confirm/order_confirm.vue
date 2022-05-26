<template>
	<view class="content">

		<view @click="clickAddress" class="zhuige-address-set">
			<template v-if="address">
				<view class="address-icon">
					<uni-icons type="location-filled" size="24"></uni-icons>
				</view>
				<view class="address-info">
					<view>
						<text>{{address.addressee}}</text>
						<text>{{address.mobile}}</text>
					</view>
					<view>{{address.address}}</view>
				</view>
				<view class="address-more">
					<uni-icons type="forward" size="24"></uni-icons>
				</view>
			</template>
			<view class="address-set-tips" v-else>
				请选择地址
			</view>
		</view>

		<view class="zhuige-cart-list zhuige-confirm-list">

			<view v-for="(item,index) in goods_list" :key="index" class="zhuige-cart-list-block">
				<view class="list-opt">
					<image :src="item.thumbnail" mode="aspectFill"></image>
				</view>
				<view class="list-info">
					<view class="list-title">{{item.title}}</view>
					<view class="list-setup">
						<view class="goods-confirm">
							<view class="list-setup-num">
								<text>￥</text>
								<text>{{item.price}}</text>
							</view>
							<view>
								<uni-icons type="closeempty" size="12" color="#999999"></uni-icons>
								<text>{{item.count}}</text>
							</view>
						</view>

					</view>
				</view>
			</view>

		</view>

		<view class="zhuige-order-remark">
			<view class="zhuige-title">备注信息</view>
			<textarea placeholder="请输入备注…" v-model="remark"></textarea>
		</view>

		<view class="zhuige-order-pay-type">
			<view class="zhuige-title">支付方式</view>
			<view class="pay-wechat">
				<uni-icons type="weixin" size="32" color="#4CBF00"></uni-icons>
				<text>微信支付</text>
			</view>
		</view>

		<view class="zhuige-cart-count">
			<view class="cart-check">
				<text class="count-num">共{{goodsCount}}件商品</text>
			</view>
			<view class="cart-confirm">
				<view class="cart-total">
					<text>￥</text>
					<text>{{goodsAmount}}</text>
				</view>
				<view @click="clickOrderSubmit" class="cart-btn">立即付款</view>
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
	 * Copyright © 2022 www.zhuige.com All rights reserved.
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

	export default {
		data() {
			return {
				address: false,

				goods_list: [],

				remark: '',
			}
		},

		computed: {
			...mapGetters([
				'getCheckBuyGoodsIds',
				'getCheckBuyCount',
				'getCheckBuyAmount',
			]),

			goodsCount() {
				let count = 0;
				this.goods_list.forEach((item, index) => {
					count += item.count;
				})
				return count;
			},

			goodsAmount() {
				let amount = 0;
				this.goods_list.forEach((item, index) => {
					if (item.price && item.count) {
						amount += parseFloat((item.price * item.count).toFixed(2))
					}
				})
				return amount;
			},
		},

		onLoad(options) {
			this.address = uni.getStorageSync('zhuige_shop_address');

			let goods_ids = '';
			if (options.goods_id) {
				goods_ids = options.goods_id;
			} else {
				goods_ids = this.getCheckBuyGoodsIds.join(',');
			}

			if (goods_ids.length == 0) {
				uni.reLaunch('/pages/index/index')
				return;
			}

			Rest.post(Api.ZHUIGE_SHOP_GOODS_CART, {
				goods_ids: goods_ids
			}).then(res => {
				let goods_list = res.data.list;
				if (options.goods_id) {
					goods_list[0].count = 1;
				} else {
					for (let i = 0; i < goods_list.length; i++) {
						for (let j = 0; j < store.state.cart.length; j++) {
							if (store.state.cart[j].id == goods_list[i].id) {
								goods_list[i].count = store.state.cart[j].count;
							}
						}
					}
				}
				this.goods_list = goods_list;
			}, err => {
				console.log(err)
			});
		},

		onShareAppMessage() {
			return {
				title: getApp().globalData.appDesc + '_' + getApp().globalData.appName,
				path: 'pages/index/index'
			};
		},

		methods: {
			clickLink(link) {
				Util.openLink(link);
			},

			clickAddress() {
				uni.chooseAddress({
					success: res => {
						this.address = {
							addressee: res.userName,
							mobile: res.telNumber,
							address: res.provinceName + res.cityName + res.countyName + res.detailInfo
						}
						uni.setStorageSync('zhuige_shop_address', this.address);
					}
				})
			},

			clickOrderSubmit() {
				if (!this.address) {
					Alert.toast('请选择地址');
					return;
				}

				if (!this.goods_list || this.goods_list.length == 0) {
					Alert.toast('请选择商品');
					return;
				}

				let goods_list = [];
				this.goods_list.forEach((item, index) => {
					goods_list.push({
						id: item.id,
						count: item.count
					});
				})

				Rest.post(Api.ZHUIGE_SHOP_ORDER_CREATE, {
					addressee: this.address.addressee,
					mobile: this.address.mobile,
					address: this.address.address,
					goods_list: JSON.stringify(goods_list),
					remark: this.remark
				}).then(res => {
					if (res.code == 0) {
						store.commit('cartGoodsBuyCheck');

						// #ifdef MP-WEIXIN
						let pay_params = res.data.pay_params;

						// 发起微信支付
						wx.requestPayment({
							timeStamp: pay_params.timeStamp,
							nonceStr: pay_params.nonceStr,
							package: pay_params.package,
							signType: 'MD5',
							paySign: pay_params.paySign,
							success: res3 => {
								Alert.toast('支付成功');

								uni.redirectTo({
									url: '/pages/order_detail/order_detail?order_id=' + res
										.data.order_id
								});
							},
							fail: res4 => {
								console.log(res4);
								Alert.toast(res4);
							},
						});
						// #endif

						// #ifndef MP-WEIXIN
						Alert.toast('平台暂不支持');
						uni.redirectTo({
							url: '/pages/order_detail/order_detail?order_id=' + res.data.order_id
						});
						// #endif
					} else {
						Alert.toast(res.msg);
					}
				}, err => {
					console.log(err)
				});
			}
		}
	}
</script>

<style lang="scss" scoped>
	@import "@/style/main.css";
</style>
