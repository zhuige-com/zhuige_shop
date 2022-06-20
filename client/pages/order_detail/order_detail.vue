<template>
	<view class="content">
		<template v-if="order">
			<view class="zhuige-order-state">
				<view v-if="order.confirmtime">已完成</view>
				<view v-else-if="order.canceltime">已取消</view>
				<view v-else-if="order.paytime">待收货</view>
				<view v-else>待付款</view>
			</view>
			<view class="zhuige-address-set">
				<view class="address-icon">
					<uni-icons type="location-filled" size="24"></uni-icons>
				</view>
				<view class="address-info">
					<view>
						<text>{{order.addressee}}</text>
						<text>{{order.mobile}}</text>
					</view>
					<view>{{order.address}}</view>
				</view>
			</view>

			<view v-if="order.goods_list && order.goods_list.length>0" class="zhuige-cart-list zhuige-confirm-list">

				<view v-for="(goods,index) in order.goods_list" :key="index" class="zhuige-cart-list-block">
					<view class="list-opt">
						<image :src="goods.thumb" mode="aspectFill"></image>
					</view>
					<view class="list-info">
						<view class="list-title">{{goods.name}}</view>
						<view class="list-setup">
							<view class="goods-confirm">
								<view class="list-setup-num">
									<text>￥</text>
									<text>{{goods.price}}</text>
								</view>
								<view>
									<uni-icons type="closeempty" size="12" color="#999999"></uni-icons>
									<text>{{goods.count}}</text>
								</view>
							</view>
						</view>
					</view>
				</view>

			</view>

			<view class="zhuige-order-remark">
				<view class="zhuige-title">备注信息</view>
				<view class="zhuige-order-remark-info">{{order.remark?order.remark:'无'}}</view>
			</view>

			<view class="zhuige-order-data">
				<view>
					<view>订单编号：</view>
					<text class="order-num">{{order.trade_no}}</text>
				</view>
				<view>
					<view>下单时间：</view>
					<text>{{order.createtime}}</text>
				</view>
				<view>
					<view>支付方式：</view>
					<text>微信支付</text>
				</view>
				<view>
					<view>支付时间：</view>
					<text>{{order.paytime ? order.paytime : '未支付'}}</text>
				</view>
				<view class="order-prix">
					<view>实付金额：</view>
					<text>￥</text>
					<text>{{order.amount}}</text>
				</view>
			</view>

			<view v-if="order.express_type && order.express_no" class="zhuige-order-data">
				<view>
					<view>配送方式：</view>
					<text>{{order.express_type}}</text>
				</view>
				<view>
					<view>快递单号：</view>
					<text>{{order.express_no}}</text>
				</view>
			</view>
			
			<view class="zhuige-order-button">
				<template v-if="order.paytime">
					<template v-if="order.confirmtime">
						<button open-type="contact">退换/售后</button>
						<!-- <view class="active">再次购买</view> -->
					</template>
					<template v-else>
						<button open-type="contact">申请退款</button>
						<view @click="clickConfirm" class="confirm-btn">确认收货</view>
					</template>
				</template>
				<template v-else>
					<template v-if="order.canceltime">
						<view @click="clickDelete">删除订单</view>
					</template>
					<template v-else>
						<view @click="clickCancel">取消订单</view>
						<view @click="clickPay" class="confirm-btn">付款</view>
					</template>
				</template>
			</view>
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

	export default {
		data() {
			this.order_id = undefined;
			
			return {
				order: undefined,
			}
		},

		onLoad(options) {
			if (!options.order_id) {
				uni.reLaunch('/pages/index/index')
				return;
			}
			this.order_id = options.order_id;

			this.loadData();
		},
		
		onPullDownRefresh() {
			this.loadData();
		},

		onShareAppMessage() {
			return {
				title: getApp().globalData.appDesc + '_' + getApp().globalData.appName,
				path: 'pages/index/index'
			};
		},

		methods: {
			clickConfirm() {
				uni.showModal({
					title: '提示',
					content: '请在收到货物后再确认',
					success: (res) => {
						if (!res.confirm) {
							return;
						}
						
						Rest.post(Api.ZHUIGE_SHOP_ORDER_CONFIRM, {
							order_id: this.order_id
						}).then(res => {
							if (res.code == 0) {
								this.order.confirmtime = res.data.confirmtime;
							} else {
								Alert.toast(res.msg);
							}
						}, err => {
							console.log(err)
						});
					}
				});
			},
			
			clickCancel() {
				uni.showModal({
					title: '提示',
					content: '确定要取消订单吗？',
					success: (res) => {
						if (!res.confirm) {
							return;
						}
						
						Rest.post(Api.ZHUIGE_SHOP_ORDER_CANCEL, {
							order_id: this.order_id
						}).then(res => {
							if (res.code == 0) {
								this.order.canceltime = res.data.canceltime;
							} else {
								Alert.toast(res.msg);
							}
						}, err => {
							console.log(err)
						});
					}
				});
			},
			
			clickDelete() {
				uni.showModal({
					title: '提示',
					content: '确定要删除订单吗？',
					success: (res) => {
						if (!res.confirm) {
							return;
						}
						
						Rest.post(Api.ZHUIGE_SHOP_ORDER_DELETE, {
							order_id: this.order_id
						}).then(res => {
							if (res.code == 0) {
								Util.navigateBack()
							} else {
								Alert.toast(res.msg);
							}
						}, err => {
							console.log(err)
						});
					}
				});
			},
			
			clickPay() {
				Rest.post(Api.ZHUIGE_SHOP_ORDER_PAY, {
					order_id: this.order_id
				}).then(res => {
					if (res.code == 0) {
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
								
								setTimeout(() => {
									this.loadData();
								}, 1000);
							},
							fail: res4 => {
								Alert.toast('取消支付');
							},
						});
						// #endif
				
						// #ifndef MP-WEIXIN
						Alert.toast('平台暂不支持');
						// #endif
					} else {
						Alert.toast(res.msg);
					}
				}, err => {
					console.log(err)
				});
			},
			
			loadData() {
				Rest.post(Api.ZHUIGE_SHOP_ORDER_DETAIL, {
					order_id: this.order_id
				}).then(res => {
					if (res.code == 0) {
						this.order = res.data.order;
					}
					uni.stopPullDownRefresh()
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
