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
		</view>

		<view class="zhuige-brand">追格 zhuige.com 提供技术支持</view>

	</view>
</template>

<script>
	import Auth from '@/utils/auth';
	import Util from '@/utils/util';
	import Alert from '@/utils/alert';
	import Api from '@/utils/api';
	import Rest from '@/utils/rest';
	import {
		mapGetters,
	} from 'vuex'

	export default {
		data() {
			return {
				user: undefined,

				background: undefined,

				page_about: undefined,

				create_count: 0,
				pay_count: 0,
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
			clickLink(link) {
				Util.openLink(link);
			},

			clickAvatar() {
				if (this.user) {
					return;
				}

				Util.openLink('/pages/login/login');
			},

			clickOrder(tab) {
				Util.openLink('/pages/order_manage/order_manage?tab=' + tab);
			},

			clickAbout() {
				Util.openLink(this.page_about);
			},

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
			}
		}
	}
</script>

<style lang="scss" scoped>
	@import "@/style/main.css";
</style>
