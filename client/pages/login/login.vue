<template>
	<view class="content">
		<view class="zhuige-login">
			<view class="zhuige-logo-set">
				<image :src="logo" mode="aspectFit"></image>
				<view>{{title}}</view>
			</view>
			<view class="zhuige-login-btn">

				<!-- #ifdef H5 -->
				<view class="jiangqie-login-tip">H5平台尚未适配</view>
				<!-- #endif -->

				<!-- #ifdef MP-WEIXIN -->
				<view v-if="code" @click="clickLogin()">授权登录</view>
				<!-- #endif -->

				<!-- #ifdef MP-QQ || MP-BAIDU -->
				<button v-if="code" open-type="getUserInfo" @getuserinfo="getuserinfo">授权登录</button>
				<!-- #endif -->

				<view @click="clickWalk()">随便逛逛</view>
			</view>
			<view class="zhuige-login-tips">
				登录即同意
				<text v-if="yhxy" @click="clickYhxy()">《用户协议》</text><template v-else>用户协议</template>
				及<text v-if="yszc" @click="clickYszc()">《隐私条款》</text><template v-else>隐私条款</template>
			</view>
		</view>
	</view>
</template>

<script>
	import Constant from '@/utils/constants';
	import Auth from '@/utils/auth';
	import Util from '@/utils/util';
	import Api from '@/utils/api';
	import Rest from '@/utils/rest';

	export default {
		data() {
			return {
				logo: '',
				title: '',

				code: undefined,

				yhxy: '',
				yszc: '',
			}
		},

		onLoad(options) {
			// #ifdef MP-WEIXIN || MP-QQ || MP-BAIDU
			uni.login({
				success: (res) => {
					this.code = res.code;
				}
			});
			// #endif

			Rest.post(Api.ZHUIGE_SHOP_SETTING_LOGIN, {}).then(res => {
				this.logo = res.data.logo;
				this.title = res.data.title;
				this.yhxy = res.data.yhxy;
				this.yszc = res.data.yszc;
			}, err => {
				console.log(err)
			});
		},

		methods: {
			clickLogin(e) {
				wx.getUserProfile({
					desc: '用于完善会员资料',
					success: res => {
						let userInfo = res.userInfo;
						this.login(userInfo.nickName, userInfo.avatarUrl);
					},
					fail: (err) => {
						console.log(err);
					}
				})
			},

			getuserinfo(res) {
				let userInfo = res.detail.userInfo;
				this.login(userInfo.nickName, userInfo.avatarUrl);
			},

			clickWalk() {
				Util.navigateBack();
			},

			clickYhxy() {
				Util.openLink(this.yhxy);
			},

			clickYszc() {
				Util.openLink(this.yszc);
			},

			login(nickname, avatar) {
				let params = {
					code: this.code,
					nickname: nickname,
					avatar: avatar
				};

				// #ifdef MP-WEIXIN
				params.channel = 'weixin';
				// #endif

				// #ifdef MP-QQ
				params.channel = 'qq';
				// #endif

				// #ifdef MP-BAIDU
				params.channel = 'baidu';
				// #endif

				Rest.post(Api.ZHUIGE_SHOP_USER_LOGIN, params).then(res => {
					Auth.setUser(res.data);
					Util.navigateBack();
				}, err => {
					console.log(err)
				});
			}
		}
	}
</script>

<style lang="scss" scoped>
	@import "@/style/main.css";

	.zhuige-login {
		position: fixed;
		height: 100%;
		width: 100%;
		text-align: center;
	}

	.zhuige-logo-set {
		padding: 200rpx 100rpx;
	}

	.zhuige-logo-set image {
		height: 200rpx;
		width: 200rpx;
	}

	.zhuige-logo-set view {
		height: 3rem;
		line-height: 3rem;
		font-size: 36rpx;
		font-weight: 500;
	}

	.zhuige-login-btn {
		position: absolute;
		width: 70%;
		margin: 0 15%;
		bottom: 180rpx;
	}

	.zhuige-login-btn view {
		height: 90rpx;
		line-height: 90rpx;
		font-size: 28rpx;
		color: #FFFFFF;
		background: #FF4400;
		border-radius: 90rpx;
		margin-bottom: 30rpx;
	}

	.zhuige-login-btn view:nth-child(2) {
		color: #333333;
		background: #F2F2F2;

	}

	.zhuige-login-tips {
		position: absolute;
		bottom: 80rpx;
		width: 80%;
		margin: 0 10%;
		color: #999999;
		font-size: 26rpx;
		font-weight: 200;
	}
</style>
