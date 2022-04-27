<template>
	<view>
		<web-view :src="src"></web-view>
	</view>
</template>

<script>
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
