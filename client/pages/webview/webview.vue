<template>
	<view>
		<web-view :src="src"></web-view>
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
	
	export default {
		data() {
			return {
				src: 'https://www.zhuige.com/'
			};
		},

		onLoad(options) {
			if (options.src) {
				this.src = decodeURIComponent(options.src);
			}
		},

		onShareAppMessage() {
			return {
				title: getApp().globalData.appName,
				path: 'pages/webview/webview?src=' + encodeURIComponent(this.src)
			};
		},

		// #ifdef MP-WEIXIN
		onShareTimeline() {
			return {
				title: getApp().globalData.appName
			};
		},

		onAddToFavorites(res) {
			return {
				title: getApp().globalData.appName
			};
		},
		// #endif
	}
</script>

<style lang="scss" scoped>

</style>
