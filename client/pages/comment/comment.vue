<template>
	<view class="content-view">
		<!-- <view v-if="goods">{{goods.title}}</view> -->
		<uni-rate :size="36" v-model="rate" />
		<textarea class="content" v-model="content" maxlength="140" confirm-type="done" />
		<view class="footer">{{content.length}}/140</view>
		<button class="button" type="default" @click="clickComment">提交</button>
	</view>
</template>

<script>
	import Util from '@/utils/util';
	import Alert from '@/utils/alert';
	import Api from '@/utils/api';
	import Rest from '@/utils/rest';
	
	export default {
		data() {
			return {
				goods_id: 0,
				// goods: undefined,
				
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
			
			// this.loadGoods();
		},
		
		methods: {
			// loadGoods() {
			// 	Rest.post(Api.ZHUIGE_SHOP_GOODS_DETAIL, {
			// 		post_id: this.goods_id
			// 	}).then(res => {
			// 		this.goods = res.data;
			// 	}, err => {
			// 		console.log(err)
			// 	});
			// }
			
			clickComment() {
				Rest.post(Api.ZHUIGE_SHOP_COMMENT_ADD, {
					post_id: this.goods_id,
					content: this.content,
					rate: this.rate
				}).then(res => {
					// console.log(res)
					if (res.code != 0) {
						Alert.toast(res.msg);
					} else {
						Util.navigateBack()
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
		}
	}
</style>
