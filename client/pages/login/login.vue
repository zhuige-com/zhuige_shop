<template>
	<view class="content">
		<view class="zhuige-login">
			<view class="zhuige-logo-set">
				<image :src="logo" mode="aspectFit"></image>
				<view v-if="login">{{title}}</view>
				<view>绑定手机号后才能评论</view>
			</view>
			<view class="zhuige-login-btn">

				<template v-if="type=='login'">
					<!-- #ifdef H5 -->
					<view class="jiangqie-login-tip">H5平台尚未适配</view>
					<!-- #endif -->

					<!-- #ifdef MP-WEIXIN -->
					<view v-if="code" class="button" @click="clickLogin()">授权登录</view>
					<!-- #endif -->

					<!-- #ifdef MP-QQ || MP-BAIDU -->
					<button v-if="code" class="button" open-type="getUserInfo" @getuserinfo="getuserinfo">授权登录</button>
					<!-- #endif -->
				</template>
				
				<template v-if="type=='mobile'">
					<!-- #ifdef MP-WEIXIN -->
					<button v-if="code" class="button" open-type="getPhoneNumber" @getphonenumber="getPhoneNumber">绑定手机号</button>
					<!-- #endif -->
					
					<!-- #ifndef MP-WEIXIN -->
					该平台下的手机绑定功能暂未实现
					<!-- #endif -->
				</template>

				<view class="button" @click="clickWalk()">随便逛逛</view>
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
	
	/*
	 * 追格商城小程序
	 * 作者: 追格
	 * 文档: https://www.zhuige.com/docs/sc.html
	 * gitee: https://gitee.com/zhuige_com/zhuige_shop
	 * github: https://github.com/zhuige-com/zhuige_shop
	 * Copyright © 2022 www.zhuige.com All rights reserved.
	 */
	
	import Constant from '@/utils/constants';
	import Auth from '@/utils/auth';
	import Util from '@/utils/util';
	import Alert from '@/utils/alert';
	import Api from '@/utils/api';
	import Rest from '@/utils/rest';

	export default {
		data() {
			return {
				type: 'login',
				
				logo: '',
				title: '',

				code: undefined,

				yhxy: '',
				yszc: '',
			}
		},

		onLoad(options) {
			if (options.type) {
				this.type = options.type;
			}
			
			// #ifdef MP-WEIXIN || MP-QQ || MP-BAIDU
			uni.login({
				success: (res) => {
					this.code = res.code;
				},
				fail: (err) => {
					console.log(err)
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
			},
			
			getPhoneNumber(e) {
				Rest.post(Api.ZHUIGE_SHOP_SET_MOBILE, {
					encrypted_data: e.detail.encryptedData,
					iv: e.detail.iv,
					code: this.code,
				}).then(res => {
					Alert.toast(res.msg)
					Util.navigateBack();
				})
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
		border-radius: 50%;
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

	.zhuige-login-btn .button {
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
