<template>
	<view class="content">
		<view v-if="post" class="zhuige-single-page">
			<mp-html :content="post.content" />
		</view>
	</view>
</template>

<script>
	import Api from '@/utils/api';
	import Rest from '@/utils/rest';

	export default {
		data() {
			return {
				page_id: '',
				post: undefined
			}
		},

		onLoad(options) {
			this.page_id = options.page_id;
			Rest.post(Api.ZHUIGE_SHOP_POST_PAGE, {
				page_id: this.page_id
			}).then(res => {
				this.post = res.data;
				uni.setNavigationBarTitle({
					title: this.post.title
				})
			}, err => {
				console.log(err)
			});
		},

		onShareAppMessage() {
			return {
				title: this.post.title + '_' + getApp().globalData.appName,
				path: 'pages/page/page?page_id=' + this.page_id
			};
		},
		
		// #ifdef MP-WEIXIN
		onShareTimeline() {
			return {
				title: this.post.title + '_' + getApp().globalData.appName
			};
		},
		// #endif

	}
</script>

<style>
	@import "@/style/main.css";

	.zhuige-single-page {
		padding: 40rpx;
	}

	.zhuige-single-page view {
		word-break: break-word;
	}

	.zhuige-single-page image {
		max-height: 100%;
		max-width: 100%;
	}
</style>
