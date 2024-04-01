<template>
	<view class="content">

		<view @click="clickAvatar" class="zhuige-user-login"
			:style="background ? 'background: url(' + background + ') no-repeat center center; background-size: 100% 100%;':''">
			<template v-if="user">
				<image :src="user.avatar" mode="aspectFill"></image>
				<view>{{user.nickname}}</view>
			</template>
			<template v-else>
				<image src="/static/images/default/avatar.jpg" mode="aspectFill"></image>
				<view>立即登录</view>
			</template>
		</view>

		<view class="zhuige-icon-set">
			<view @click="clickOrder('create')" class="zhuige-icon">
				<uni-badge size="small" :text="create_count" absolute="rightTop" type="error">
					<image src="../../static/images/icon01.png" mode="aspectFill"></image>
				</uni-badge>
				<view>待付款</view>
			</view>
			<view @click="clickOrder('pay')" class="zhuige-icon">
				<uni-badge size="small" :text="pay_count" absolute="rightTop" type="error">
					<image src="../../static/images/icon02.png" mode="aspectFill"></image>
				</uni-badge>
				<view>待收货</view>
			</view>
			<view @click="clickOrder('confirm')" class="zhuige-icon">
				<image src="../../static/images/icon03.png" mode="aspectFill"></image>
				<view>售后/退换</view>
			</view>
			<view @click="clickOrder('all')" class="zhuige-icon">
				<image src="../../static/images/icon04.png" mode="aspectFill"></image>
				<view>全部订单</view>
			</view>
		</view>

		<view class="zhuige-user-menu">
			<button open-type="contact" class="zhuige-user-opt">
				<view class="zhuige-user-opt-title">
					<view>
						<uni-icons type="chat" size="24" color="#666666"></uni-icons>
					</view>
					<view>在线客服</view>
				</view>
				<view class="zhuige-user-more">
					<uni-icons type="forward" size="18" color="#AAAAAA"></uni-icons>
				</view>
			</button>
			<button open-type="feedback" class="zhuige-user-opt">
				<view class="zhuige-user-opt-title">
					<view>
						<uni-icons type="chatbubble" size="24" color="#666666"></uni-icons>
					</view>
					<view>意见反馈</view>
				</view>
				<view class="zhuige-user-more">
					<uni-icons type="forward" size="18" color="#AAAAAA"></uni-icons>
				</view>
			</button>
			<view @click="clickAbout" class="zhuige-user-opt">
				<view class="zhuige-user-opt-title">
					<view>
						<uni-icons type="info" size="24" color="#666666"></uni-icons>
					</view>
					<view>关于我们</view>
				</view>
				<view class="zhuige-user-more">
					<uni-icons type="forward" size="18" color="#AAAAAA"></uni-icons>
				</view>
			</view>
			<view @click="clickClear" class="zhuige-user-opt">
				<view class="zhuige-user-opt-title">
					<view>
						<uni-icons type="trash" size="24" color="#666666"></uni-icons>
					</view>
					<view>清除缓存</view>
				</view>
				<view class="zhuige-user-more">
					<uni-icons type="forward" size="18" color="#AAAAAA"></uni-icons>
				</view>
			</view>
			<view @click="clickScore" class="zhuige-user-opt">
				<view class="zhuige-user-opt-title">
					<view>
						<uni-icons type="star" size="24" color="#666666"></uni-icons>
					</view>
					<view>评价打分</view>
				</view>
				<view class="zhuige-user-more">
					<uni-icons type="forward" size="18" color="#AAAAAA"></uni-icons>
				</view>
			</view>
			
		</view>

		<view class="zhuige-brand">本小程序基于追格（zhuige.com）搭建</view>
		
		<view class="zhuige-record" @click="clickLink(beian_icp.link)" v-if="beian_icp">{{beian_icp.sn}}</view>
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

	import Auth from '@/utils/auth';
	import Util from '@/utils/util';
	import Alert from '@/utils/alert';
	import Api from '@/utils/api';
	import Rest from '@/utils/rest';
	import {
		mapGetters,
	} from 'vuex'

	export default {
		components: {
			
		},
		
		data() {
			return {
				user: undefined,

				background: undefined,

				page_about: undefined,

				create_count: 0,
				pay_count: 0,
				
				beian_icp: undefined,
			}
		},

		computed: {
			...mapGetters([
				'getCartCount'
			])
		},

		onLoad(options) {
			Rest.post(Api.ZHUIGE_SHOP_SETTING_MINE).then(res => {
				this.background = res.data.background;
				this.page_about = res.data.page_about;
				
				if (res.data.beian_icp) {
					this.beian_icp = res.data.beian_icp;
				}
			}, err => {
				console.log(err);
			});
		},

		onShow() {
			Util.updateCartBadge(this.getCartCount);
			this.user = Auth.getUser();
			if (this.user) {
				Rest.post(Api.ZHUIGE_SHOP_ORDER_COUNT).then(res => {
					this.create_count = parseInt(res.data.create_count);
					this.pay_count = parseInt(res.data.pay_count);
				});
			}
		},

		onShareAppMessage() {
			return {
				title: getApp().globalData.appDesc + '_' + getApp().globalData.appName,
				path: 'pages/index/index'
			};
		},

		methods: {
			/**
			 * 点击 打开链接
			 */
			clickLink(link) {
				Util.openLink(link);
			},

			/**
			 * 点击 头像
			 */
			clickAvatar() {
				if (this.user) {
					Util.openLink('/pages/verify/verify');
				} else {
					Util.openLink('/pages/login/login');
				}
			},

			/**
			 * 点击 订单管理
			 */
			clickOrder(tab) {
				Util.openLink('/pages/order_manage/order_manage?tab=' + tab);
			},

			/**
			 * 点击 关于
			 */
			clickAbout() {
				Util.openLink(this.page_about);
			},

			/**
			 * 点击 清楚缓存
			 */
			clickClear() {
				uni.showModal({
					title: '提示',
					content: '清除缓存 需要重新登录',
					success(res) {
						if (res.confirm) {
							if (Auth.getUser()) {
								Rest.post(Api.ZHUIGE_SHOP_USER_LOGOUT).then(res => {
									console.log(res);
								}, err => {
									console.log(err);
								});
							}

							uni.clearStorageSync();
							uni.showToast({
								title: '清除完毕'
							});

							uni.reLaunch({
								url: '/pages/index/index'
							});
						}
					}
				});
			},
			
			/**
			 * 点击 评价打分
			 */
			clickScore() {
				var plugin = requirePlugin("wxacommentplugin");
				plugin.openComment({
					success: (res) => {
						// console.log('plugin.openComment success', res)
						let lastTime = wx.getStorageSync('zhuige_comment_plugin_last_time');
						if (!lastTime) {
							lastTime = 0;
						}
						
						let now = new Date().getTime();
						let legal = ((now - lastTime) > 30 * 86400000);
						if (legal) {
							wx.setStorageSync('zhuige_comment_plugin_last_time', now)
						}
						
						uni.showToast({
							icon: 'none',
							title: (legal ? '感谢评价' : '您近期已评价过')
						});
					},
					fail: (res) => {
						// console.log('plugin.openComment fail', res)
						if (res.errCode != -3) {
							uni.showToast({
								icon: 'none',
								title: '评价功能暂不可用'
							});
						}
					}
				})
			}
		}
	}
</script>

<style lang="scss" scoped>
	@import "@/style/main.css";
</style>