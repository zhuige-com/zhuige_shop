<script>
	/*
	 * 追格商城小程序 v1.5.1
	 * 作者: 追格
	 * 文档: https://www.zhuige.com/docs/sc.html
	 * gitee: https://gitee.com/zhuige_com/zhuige_shop
	 * github: https://github.com/zhuige-com/zhuige_shop
	 * Copyright © 2022-2023 www.zhuige.com All rights reserved.
	 */

	import Util from '@/utils/util';
	import store from '@/store/index.js'

	export default {
		globalData: {
			appName: '追格商城',
			appDesc: '',
		},

		onLaunch() {
			let cart = Util.loadCart();
			if (!cart) {
				cart = [];
			}
			store.commit('cartSet', cart);
			
			const updateManager = wx.getUpdateManager()
			updateManager.onCheckForUpdate(function(res) {
				// 请求完新版本信息的回调
				// console.log(res.hasUpdate)
			})
			updateManager.onUpdateReady(function() {
				wx.showModal({
					title: '更新提示',
					content: '新版本已经准备好，是否重启应用？',
					success: function(res) {
						if (res.confirm) {
							// 新的版本已经下载好，调用 applyUpdate 应用新版本并重启
							updateManager.applyUpdate()
						}
					}
				})
			})
			updateManager.onUpdateFailed(function() {
				// 新版本下载失败
			})
		},

		onShow() {

		},

		onHide() {
			console.log('App Hide')
		}
	}
</script>

<style>
	/*每个页面公共css */
</style>
