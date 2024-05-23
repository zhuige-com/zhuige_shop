<template>
	<view class="content"
		:style="background ? 'background: url(' + background + ') no-repeat top; background-size: 100% auto;' : ''">

		<uni-nav-bar :title="title" :color="nav_color" :background-color="nav_bgcolor" :border="false" :fixed="true" :statusBar="true" :placeholder="false">
			<!-- 顶部小搜索框 -->
			<view slot="left" @click="clickLink('/pages/search/search')">
				<view class="zhuige-nav-search">
					<uni-icons type="search" size="20" :color="nav_color"></uni-icons>
					<text :style="{color:nav_color}">关键词...</text>
				</view>
			</view>
		</uni-nav-bar>

		<view class="zhuige-main-top">
			<!-- 大图轮播 -->
			<view v-if="slides && slides.length>0" class="zhuige-swiper">
				<swiper indicator-dots="true" autoplay="autoplay" circular="ture"
					indicator-color="rgba(255,255,255, 0.3)" indicator-active-color="rgba(255,255,255, 0.8)"
					interval="5000" duration="150" easing-function="linear">
					<swiper-item v-for="(slide, index) in slides" :key="index" @click="clickLink(slide.link)">
						<view class="zhuige-swiper-title">{{slide.title}}</view>
						<image :src="slide.image" mode="aspectFill"></image>
					</swiper-item>
				</swiper>
			</view>

			<!-- 自定义图标 -->
			<view v-if="icon_navs && icon_navs.length>0" class="zhuige-icon-menu">
				<view v-for="(icon, index) in icon_navs" :key="index" @click="clickLink(icon.link)">
					<image :src="icon.image" mode="aspectFill"></image>
					<text>{{icon.title}}</text>
				</view>
			</view>
		</view>

		<!-- 滑动推荐 -->
		<view v-if="home_rec" class="zhuige-recom">
			<view class="zhuige-title">
				<view>{{home_rec.title}}</view>
				<text>滑动查看</text>
			</view>
			<view v-if="home_rec.posts && home_rec.posts.length>0" class="zhuige-scroll">
				<scroll-view scroll-x="true">
					<view v-for="(post,index) in home_rec.posts" :key="index"
						@click="clickLink('/pages/detail/detail?goods_id=' + post.id)" class="zhuige-scroll-block">
						<image :src="post.thumbnail" mode="aspectFill"></image>
						<view>{{post.title}}</view>
					</view>
				</scroll-view>
			</view>
		</view>

		<view class="zhuige-goods-group">
			<!-- 滑动导航 -->
			<view class="zhuige-goods-nav">
				<view class="zhuige-goods-scroll">
					<scroll-view>
						<view v-for="(item,index) in cats" :key="index" :class="cat_id==item.id?'active':''"
							@click="clickTab(item.id)">
							{{item.name}}
						</view>
					</scroll-view>
				</view>
				<view @click="clickCategory" class="zhuige-goods-more">
					<uni-icons type="bars" size="24"></uni-icons>
				</view>
			</view>

			<!-- 商品列表 -->
			<template v-if="goods_list.length>0">
				<view class="zhuige-goods-list">
					<view v-for="(item,index) in goods_list" :key="index"
						@click="clickLink('/pages/detail/detail?goods_id=' + item.id)" class="zhuige-goods">
						<image :src="item.thumbnail" mode="aspectFill"></image>
						<view class="zhuige-goods-text">
							<view class="zhuige-goods-title">
								<text v-if="item.badge" class="mark">{{item.badge}}</text>
								<text>{{item.title}}</text>
							</view>
							<view class="zhuige-goods-price">
								<view class="promotion">
									<text>￥</text>
									<text>{{item.price}}</text>
								</view>
								<view class="original">
									<text>￥{{item.orig_price}}</text>
								</view>
							</view>
						</view>
					</view>
				</view>
				<uni-load-more :status="loadMore"></uni-load-more>
			</template>
			<template v-else>
				<jiangqie-no-data v-if="loaded"></jiangqie-no-data>
			</template>
		</view>
		
		<view v-if="pop_ad" class="zhugie-pop-cover">
			<view @click="clickPopAd" class="zhuige-pop-box">
				<image mode="aspectFit" :src="pop_ad.image"></image>
				<view>
					<uni-icons @click="clickPopAdClose" type="close" size="32" color="#FFFFFF"></uni-icons>
				</view>
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

	import Constants from "@/utils/constants.js";
	import Util from '@/utils/util';
	import Alert from '@/utils/alert';
	import Api from '@/utils/api';
	import Rest from '@/utils/rest';
	import JiangqieNoData from "@/components/nodata/nodata";
	import {
		mapGetters,
	} from 'vuex'

	export default {
		components: {
			JiangqieNoData
		},
		
		data() {
			this.share_title = undefined;
			this.share_thumb = undefined;

			return {
				background: undefined,
				
				title: '',
				nav_color: 'rgb(255, 255, 255)',
				nav_bgcolor: 'rgba(255, 255, 255, 0)',

				slides: [],
				icon_navs: [],
				home_rec: undefined,

				cats: [],
				cat_id: undefined,

				goods_list: [],
				loadMore: 'more',
				loaded: false,
				
				// 弹窗广告
				pop_ad: undefined,
			}
		},

		computed: {
			...mapGetters([
				'getCartCount'
			])
		},

		onLoad(options) {
			this.loadSetting();
			this.loadGoods();
		},

		onShow() {
			Util.updateCartBadge(this.getCartCount);
		},
		
		onPageScroll(e) {
			if (e.scrollTop > 20) {
				let nav_opacity = (e.scrollTop - 20) / 255;
				if (nav_opacity <= 1) {
					let factor = 255 * (1 - nav_opacity);
					this.nav_color = `rgb(${factor}, ${factor}, ${factor})`;
					this.nav_bgcolor = `rgba(255, 255, 255, ${nav_opacity})`;
					this.title = '首页';
				} else if (this.nav_color != 'rgb(255, 255, 255)') {
					this.nav_color = 'rgb(0, 0, 0)';
					this.nav_bgcolor = 'rgba(255, 255, 255, 1)';
				}
				uni.setNavigationBarColor({
					frontColor: '#000000',
					backgroundColor: '#ffffff',
				})
			} else {
				this.nav_color = 'rgb(255, 255, 255)';
				this.nav_bgcolor = 'rgba(255, 255, 255, 0)';
				this.title = '';
				uni.setNavigationBarColor({
					frontColor: '#ffffff',
					backgroundColor: '#ffffff'
				})
			}
		},

		onReachBottom() {
			if (this.loadMore == 'more') {
				this.loadGoods();
			}
		},

		onPullDownRefresh() {
			this.refresh();
		},

		onShareAppMessage() {
			let params = {
				title: getApp().globalData.appDesc + '_' + getApp().globalData.appName,
				path: 'pages/index/index'
			};

			if (this.share_title && this.share_title.length > 0) {
				params.title = this.share_title;
			}

			if (this.share_thumb) {
				params.imageUrl = this.share_thumb;
			}

			return params;
		},

		// #ifdef MP-WEIXIN
		onShareTimeline() {
			return {
				title: getApp().globalData.appName
			};
		},
		// #endif

		methods: {
			/**
			 * 刷新
			 */
			refresh() {
				this.loadSetting();

				this.loaded = false;
				this.goods_list = [];
				this.loadGoods();
			},

			/**
			 * 点击打开链接
			 */
			clickLink(link) {
				Util.openLink(link);
			},

			/**
			 * 点击切换TAB
			 */
			clickTab(cat_id) {
				this.cat_id = cat_id;

				this.loaded = false;
				this.goods_list = [];
				this.loadGoods();
			},

			/**
			 * 打开分类
			 */
			clickCategory() {
				uni.switchTab({
					url: '/pages/category/category'
				})
			},

			/**
			 * 加载配置
			 */
			loadSetting() {
				Rest.post(Api.ZHUIGE_SHOP_SETTING_HOME).then(res => {
					getApp().globalData.appName = res.data.title;
					getApp().globalData.appDesc = res.data.desc;

					this.background = res.data.background;
					this.share_title = res.data.home_title;
					this.share_thumb = res.data.thumb;

					this.slides = res.data.slides;
					this.icon_navs = res.data.icon_navs;
					this.home_rec = res.data.home_rec;

					this.cats = res.data.cats;
					this.cat_id = this.cats[0].id;
					
					// 弹框
					this.pop_ad = Util.getPopAd(res.data.pop_ad, Constants.ZHUIGE_INDEX_MAXAD_LAST_TIME);

					uni.stopPullDownRefresh();
				}, err => {
					console.log(err)
				});
			},

			/**
			 * 加载商品列表
			 */
			loadGoods() {
				if (this.loadMore == 'loading') {
					return;
				}
				this.loadMore = 'loading';

				let params = {
					offset: this.goods_list.length
				};

				if (this.cat_id) {
					params.cat_id = this.cat_id;
				}

				Rest.post(Api.ZHUIGE_SHOP_GOODS_LAST, params).then(res => {
					this.goods_list = this.goods_list.concat(res.data.list);
					this.loadMore = res.data.more;
					this.loaded = true;
				});
			},
			
			/**
			 * 点击弹出窗口
			 */
			clickPopAd() {
				wx.setStorageSync(Constants.ZHUIGE_INDEX_MAXAD_LAST_TIME, new Date().getTime())
				Util.openLink(this.pop_ad.link);
				this.pop_ad = false;
			},
			
			/**
			 * 关闭弹出窗口
			 */
			clickPopAdClose() {
				this.pop_ad = false;
				wx.setStorageSync(Constants.ZHUIGE_INDEX_MAXAD_LAST_TIME, new Date().getTime())
			},
		}
	}
</script>

<style lang="scss">
	@import "@/style/main.css";
	
	/**
	 * 弹窗 start
	 */
	.zhugie-pop-cover {
		position: fixed;
		height: 100%;
		width: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		background: rgba(0, 0, 0, .6);
		z-index: 998;
		top: 0;
		left: 0;
	}
	
	.zhuige-pop-box {
		width: 600rpx;
		height: 600rpx;
		position: relative;
		text-align: center;
	}
	
	.zhuige-pop-box image {
		height: 100%;
		width: 100%;
	}
	
	.zhuige-pop-box view {
		position: absolute;
		bottom: -48rpx;
		height: 48rpx;
		width: 48rpx;
		z-index: 999;
		left: 50%;
		margin-left: -24rpx;
	}
	/**
	 * 弹窗 end
	 */
</style>