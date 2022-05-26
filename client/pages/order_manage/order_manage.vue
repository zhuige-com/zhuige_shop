<template>
	<view class="content">
		<view class="zhuige-tab-nav">
			<view :class="cur_tab=='all'?'active':''" @click="clickTab('all')">
				<text>全部</text>
				<text>{{all_count}}</text>
			</view>
			<view :class="cur_tab=='create'?'active':''" @click="clickTab('create')">
				<text>待付款</text>
				<text>{{create_count}}</text>
			</view>
			<view :class="cur_tab=='pay'?'active':''" @click="clickTab('pay')">
				<text>待收货</text>
				<text>{{pay_count}}</text>
			</view>
			<view :class="cur_tab=='confirm'?'active':''" @click="clickTab('confirm')">
				<text>售后/退换</text>
				<text>{{confirm_count}}</text>
			</view>
			<view :class="cur_tab=='cancel'?'active':''" @click="clickTab('cancel')">
				<text>已取消</text>
				<text>{{cancel_count}}</text>
			</view>
		</view>

		<template v-if="orders.length>0">
			<view class="zhuige-tab-box">

				<view v-for="(order, index) in orders" :key="index"
					class="zhuige-order-block">
					<view class="zhuige-order-block-title">
						<view>订单编号：{{order.trade_no}}</view>
						<template v-if="order.paytime">
							<template v-if="order.confirmtime">
								<view class="state-over">已完成</view>
							</template>
							<template v-else>
								<view class="state-take">待收货</view>
							</template>
						</template>
						<template v-else>
							<template v-if="order.canceltime">
								<view class="state-cancel">已取消</view>
							</template>
							<template v-else>
								<view class="state-pay">待付款</view>
							</template>
						</template>
					</view>

					<view v-if="order.goods_list && order.goods_list.length>0"
						@click="clickLink('/pages/order_detail/order_detail?order_id=' + order.id)"
						class="zhuige-cart-list zhuige-order-list">
						<view v-for="(goods, inx) in order.goods_list" :key="inx" class="zhuige-cart-list-block">
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

					<view class="zhuige-order-block-footer">
						<view>{{order.createtime}}</view>
						<view class="zhuige-order-block-footer-btn">
							<template v-if="order.paytime">
								<template v-if="order.confirmtime">
									<button open-type="contact">退换/售后</button>
									<!-- <view class="active">再次购买</view> -->
								</template>
								<template v-else>
									<button open-type="contact">申请退款</button>
									<view @click="clickConfirm(order)" class="active">确认收货</view>
								</template>
							</template>
							<template v-else>
								<template v-if="order.canceltime">
									<view @click="clickDelete(order)">删除订单</view>
								</template>
								<template v-else>
									<view @click="clickCancel(order)">取消订单</view>
									<view @click="clickPay(order)" class="active">付款</view>
								</template>
							</template>
						</view>
					</view>

				</view>
				<uni-load-more :status="loadMore"></uni-load-more>
			</view>
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
			return {
				cur_tab: 'all',

				all_count: 0,
				create_count: 0,
				pay_count: 0,
				confirm_count: 0,
				cancel_count: 0,

				orders: [],
				loadMore: 'more',
				loaded: false,
			}
		},

		components: {
			JiangqieNoData,
		},

		onLoad(options) {
			if (options.tab) {
				this.cur_tab = options.tab;
			}
		},
		
		onShow() {
			this.refresh(true);
		},

		onReachBottom() {
			if (this.loadMore == 'more') {
				this.loadOrders();
			}
		},
		
		onPullDownRefresh() {
			this.refresh();
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

			clickTab(tab) {
				this.cur_tab = tab;

				this.orders = [];
				this.loadOrders();
			},
			
			clickConfirm(order) {
				uni.showModal({
					title: '提示',
					content: '请在收到货物后再确认',
					success: (res) => {
						if (!res.confirm) {
							return;
						}
						
						Rest.post(Api.ZHUIGE_SHOP_ORDER_CONFIRM, {
							order_id: order.id
						}).then(res => {
							if (res.code == 0) {
								order.confirmtime = res.data.confirmtime;
								this.loadCount();
							} else {
								Alert.toast(res.msg);
							}
						}, err => {
							console.log(err)
						});
					}
				});
			},
			
			clickCancel(order) {
				uni.showModal({
					title: '提示',
					content: '确定要取消订单吗？',
					success: (res) => {
						if (!res.confirm) {
							return;
						}
						
						Rest.post(Api.ZHUIGE_SHOP_ORDER_CANCEL, {
							order_id: order.id
						}).then(res => {
							if (res.code == 0) {
								order.canceltime = res.data.canceltime;
								this.loadCount();
							} else {
								Alert.toast(res.msg);
							}
						}, err => {
							console.log(err)
						});
					}
				});
			},
			
			clickDelete(order) {
				uni.showModal({
					title: '提示',
					content: '确定要删除订单吗？',
					success: (res) => {
						if (!res.confirm) {
							return;
						}
						
						Rest.post(Api.ZHUIGE_SHOP_ORDER_DELETE, {
							order_id: order.id
						}).then(res => {
							if (res.code == 0) {
								let orders = [];
								this.orders.forEach((item, index) => {
									if (item.id != order.id) {
										orders.push(item);
									}
								})
								this.orders = orders;
								this.loadCount();
							} else {
								Alert.toast(res.msg);
							}
						}, err => {
							console.log(err)
						});
					}
				});
			},
			
			clickPay(order) {
				Rest.post(Api.ZHUIGE_SHOP_ORDER_PAY, {
					order_id: order.id
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
								
								order.paytime = true;
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
			
			refresh() {
				this.loadCount();
				this.loadOrders(true);
			},

			loadOrders(refresh) {
				if (this.loadMore == 'loading') {
					return;
				}
				this.loadMore = 'loading';

				Rest.post(Api.ZHUIGE_SHOP_ORDER_LIST, {
					offset: refresh ? 0 : this.orders.length,
					filter: this.cur_tab
				}).then(res => {
					this.orders = refresh ? res.data.orders : this.orders.concat(res.data.orders);
					this.loadMore = res.data.more;
					this.loaded = true;
					
					if (refresh) {
						uni.stopPullDownRefresh()
					}
				});
			},
			
			loadCount() {
				Rest.post(Api.ZHUIGE_SHOP_ORDER_COUNT).then(res => {
					this.all_count = res.data.all_count;
					this.create_count = res.data.create_count;
					this.pay_count = res.data.pay_count;
					this.confirm_count = res.data.confirm_count;
					this.cancel_count = res.data.cancel_count;
				});
			}
		}
	}
</script>

<style lang="scss" scoped>
	@import "@/style/main.css";
</style>
