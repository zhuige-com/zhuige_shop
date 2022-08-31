<template>
	<view class="content-view">
		<uni-rate :size="36" v-model="rate" />
		<textarea class="content" v-model="content" maxlength="140" confirm-type="done" />
		<view class="footer">{{content.length}}/140</view>
		<button class="button" type="default" @click="clickComment">提交</button>
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
			this.goods_id = 0;
			
			return {
				rate: 5,
				content: '',
			};
		},
		
		onLoad(options) {
			if (!options.goods_id) {
				uni.reLaunch({
					url: '/pages/index/index'
				})
				return;
			}
			
			this.goods_id = options.goods_id;
		},
		
		methods: {
			clickComment() {
				Rest.post(Api.ZHUIGE_SHOP_COMMENT_ADD, {
					post_id: this.goods_id,
					content: this.content,
					rate: this.rate
				}).then(res => {
					if (res.code == -11) {
						Util.openLink('/pages/login/login?type=mobile');
					} else if (res.code != 0) {
						Alert.toast(res.msg);
					} else {
						Util.navigateBack();
					}
				}, err => {
					console.log(err)
				});
			}
		}
	}
</script>

<style lang="scss" scoped>
	.content-view {
		padding: 30rpx;

		.content {
			box-sizing: border-box;
			width: 100%;
			margin: 20rpx 0;
			padding: 10rpx;
			border: 1rpx solid #AAAAAA;
			border-radius: 10rpx;
		}

		.footer {
			text-align: end;
		}
		
		.button {
			margin-top: 20rpx;
			border: none;
			background: #ff4400;
			color: #FFFFFF;
		}
	}
</style>
