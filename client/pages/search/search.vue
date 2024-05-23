<template>
	<view class="content">
		<view class="zhuige-wide-search">
			<view class="bar">
				<uni-icons type="search" size="24" color="#333333"></uni-icons>
				<input type="text" v-model="keyword" confirm-type="search" @confirm="confirmSearch"
					placeholder="请输入关键词..." />
			</view>
			<text v-if="keyword.length>0" @click="clickSearch">搜索</text>
			<text v-else @click="clickCancel">取消</text>
		</view>

		<view v-if="historys.length>0" class="zhuige-search-list">
			<view class="zhuige-title">搜索历史</view>
			<view class="zhuige-search-tags">
				<text v-for="(item, index) in historys" :key="index" @click="clickHistory(item)">{{item}}</text>
				<text @click="clickClear" class="del">清空历史</text>
			</view>
		</view>
		
		<!-- 热门 -->
		<view v-if="hots && hots.length>0" class="zhuige-search-hot">
			<view class="zhuige-block">
				<view class="zhuige-search-hot-title">热门搜索</view>
				<view v-for="(item, index) in hots" :key="index" @click="search(item)">{{item}}</view>
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
	 * Copyright © 2022-2023 www.zhuige.com All rights reserved.
	 */

	import Constant from '@/utils/constants';
	import Util from '@/utils/util';
	import Alert from '@/utils/alert';
	import Api from '@/utils/api';
	import Rest from '@/utils/rest';

	export default {
		components: {
			
		},
		
		data() {
			return {
				keyword: '',

				historys: [],
				
				hots: [],
			}
		},
		
		onLoad(options) {
			uni.getStorage({
				key: Constant.ZHUIGE_SEARCH_KEY,
				success: (res) => {
					this.historys = res.data;
				}
			});
			
			this.loadSetting();
		},

		onShareAppMessage() {
			return {
				title: getApp().globalData.appDesc + '_' + getApp().globalData.appName,
				path: 'pages/index/index'
			};
		},

		methods: {
			/**
			 * 点击 搜索
			 */
			clickSearch() {
				this.search(this.keyword)
			},

			/**
			 *确认 搜索
			 */
			confirmSearch() {
				this.search(this.keyword)
			},

			/**
			 * 点击 搜索历史
			 */
			clickHistory(keyword) {
				this.search(keyword)
			},

			/**
			 * 点击 清除历史
			 */
			clickClear() {
				let that = this;
				uni.showModal({
					title: '提示',
					content: '确定要清空搜索历史吗？',
					success: (res) => {
						if (res.confirm) {
							uni.setStorage({
								key: Constant.ZHUIGE_SEARCH_KEY,
								data: [],
								success: () => {
									this.historys = [];
								}
							});
						}
					}
				});
			},

			/**
			 * 点击 取消
			 */
			clickCancel() {
				Util.navigateBack()
			},

			/**
			 * 搜索
			 */
			search(keyword) {
				uni.getStorage({
					key: Constant.ZHUIGE_SEARCH_KEY,
					success: (res) => {
						let keys = [keyword];
						for (let i = 0; i < res.data.length && keys.length < Constant
							.ZHUIGE_SEARCH_MAX_COUNT; i++) {
							if (keyword == res.data[i]) {
								continue;
							}

							keys.push(res.data[i]);
						}
						this.historys = keys;

						uni.setStorage({
							data: keys,
							key: Constant.ZHUIGE_SEARCH_KEY
						});
					},

					fail: (e) => {
						let keys = [keyword];
						this.historys = keys;

						uni.setStorage({
							data: keys,
							key: Constant.ZHUIGE_SEARCH_KEY
						});
					}

				});

				Util.openLink('/pages/list/list?search=' + keyword + '&title=搜索【' + keyword + '】');
			},
			
			/**
			 * 加载配置
			 */
			loadSetting() {
				Rest.post(Api.ZHUIGE_SHOP_SETTING_SEARCH, {}).then(res => {
					this.hots = res.data.hots;
				}, err => {
					console.log(err)
				});
			}
		}
	}
</script>

<style lang="scss" scoped>
	@import "@/style/main.css";

	.content {
		padding: 30rpx;
	}
	
	.zhuige-search-hot {
		margin-top: 10rpx;
		text-align: center;
	}
	
	.zhuige-search-hot .zhuige-block view {
		padding: 20rpx;
		font-size: 30rpx;
		font-weight: 400;
		color: #555555;
	}
	
	.zhuige-search-hot .zhuige-block view.zhuige-search-hot-title {
		font-size: 36rpx;
		font-weight: 600;
		color: #010101;
	}
</style>