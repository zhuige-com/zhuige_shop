<script>
	/*
	 * 追格商城小程序 v1.1.0
	 * Author: 追格
	 * Help document: https://www.zhuige.com
	 * Copyright © 2022 www.zhuige.com All rights reserved.
	 */
	
	import Util from '@/utils/util';
	import store from '@/store/index.js'
	
	export default {
		globalData: {
			appName: '追格商城',
			appDesc: '',
		},

		onLaunch: function() {
			console.log('App Launch')
			
			let cart = Util.loadCart();
			if (!cart) {
				cart = [];
			}
			store.commit('cartSet', cart);
		},
		
		onShow: function() {
			// console.log('App Show')
		},
		
		onHide: function() {
			console.log('App Hide')
		}
	}
</script>

<style>
	/*每个页面公共css */
</style>
