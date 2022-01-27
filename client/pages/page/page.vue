<template>
	<view class="content">
		<view v-if="post" class="content-wrapper">
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
			};
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

<style lang="scss">
	@import "@/style/style.scss";

	.content-wrapper {
		padding: 30rpx 40rpx;
	}

	.content-wrapper rich-text {
		line-height: 1.8rem;
		font-weight: $Q-rich-text-weight;
		min-height: 1.8rem;
		font-size: $Q-text-size;
	}

	._img {
		border-radius: $Q-radio;
	}
</style>
