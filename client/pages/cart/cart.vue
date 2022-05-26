<template>
	<view class="content">

		<template v-if="getCartCount>0">
			<view class="zhuige-cart-opt">
				<view class="num">
					购物数量
					<text>{{getCartCount}}</text>
				</view>
				<view @click="clickManage">{{manage?'取消':'管理'}}</view>
			</view>

			<view class="zhuige-cart-list">

				<view v-for="(item,index) in cart" :key="index" class="zhuige-cart-list-block">
					<view class="list-opt">
						<uni-icons
							:type="manage ? (item.check_del==1 ? 'checkbox-filled' : 'circle') : (item.check_buy==1 ? 'checkbox-filled' : 'circle')"
							size="24" color="#ff4400" @click.stop="clickItemCheck(item.id)"></uni-icons>
						<image @click="clickItem(item.id)" :src="item.thumbnail" mode="aspectFill"></image>
					</view>
					<view class="list-info">
						<view @click="clickItem(item.id)" class="list-title">{{item.title}}</view>
						<view class="list-setup">
							<view class="list-setup-num">
								<text>￥</text>
								<text>{{item.price}}</text>
							</view>
							<view class="list-setup-opt">
								<text @click="clickGoodsMinus(item.id, item.count, 1)">-</text>
								<input type="number" :value="item.count" @input="inputGoodsCount" :data-goods_id="item.id"/>
								<text @click="clickGoodsAdd(item.id, 1)">+</text>
							</view>
						</view>
					</view>
				</view>

			</view>

			<view class="zhuige-cart-count">
				<view class="cart-check">
					<uni-icons @click="clickCheckAll"
						:type="manage ? (getCheckDelAll ? 'checkbox-filled' : 'circle') : (getCheckBuyAll ? 'checkbox-filled' : 'circle')"
						size="24" color="#ff4400"></uni-icons>
					<text @click="clickCheckAll"
						class="count-num">全选（{{manage ? getCheckDelCount : getCheckBuyCount}}件）</text>
				</view>
				<view class="cart-confirm">
					<view class="cart-total">
						<text>￥</text>
						<text>{{getCheckBuyAmount}}</text>
					</view>
					<view @click="clickNext" class="cart-btn" :class="{'del':manage}">
						{{manage ? '立即删除' : '立即购买'}}
					</view>
				</view>
			</view>
		</template>

		<view v-else class="zhuige-none-tips" @click="clickCartEmpty">
			<image src="../../static/images/none_tip.png" mode="aspectFit"></image>
			<view>暂无商品，去添加点什么吧~</view>
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
	 * Copyright © 2022 www.zhuige.com All rights reserved.
	 */
	
	import Util from '@/utils/util';
	import Alert from '@/utils/alert';
	import Api from '@/utils/api';
	import Rest from '@/utils/rest';

	import {
		mapState,
		mapGetters,
		mapMutations
	} from 'vuex'
	import store from '@/store/index.js'

	export default {
		data() {
			return {
				manage: false,
			}
		},

		computed: {
			...mapState([
				'cart'
			]),
			...mapGetters([
				'getCartCount',
				'getCheckBuyAll',
				'getCheckDelAll',
				'getCheckBuyCount',
				'getCheckDelCount',
				'getAllGoodsIds',
				'getCheckBuyAmount'
			])
		},

		onLoad(options) {

		},

		onShow() {
			Util.updateCartBadge(this.getCartCount);

			Rest.post(Api.ZHUIGE_SHOP_GOODS_CART, {
				goods_ids: this.getAllGoodsIds.join(',')
			}).then(res => {
				store.commit('cartSetGoodsList', {
					list: res.data.list
				});
			}, err => {
				console.log(err)
			});
		},
		
		onShareAppMessage() {
			return {
				title: getApp().globalData.appDesc + '_' + getApp().globalData.appName,
				path: 'pages/index/index'
			};
		},

		methods: {
			/**
			 * 打开链接
			 */
			clickLink(link) {
				Util.openLink(link);
			},

			/**
			 * 管理 切换
			 */
			clickManage() {
				this.manage = !this.manage;
			},

			/**
			 * 打开商品链接
			 */
			clickItem(goods_id) {
				Util.openLink('/pages/detail/detail?goods_id=' + goods_id);
			},

			/**
			 * 项目 选中 点击
			 */
			clickItemCheck(goods_id) {
				if (this.manage) {
					// item.check_del = (item.check_del == 1 ? 0 : 1);
					store.commit('cartSetGoodsCheckDel', {goods_id: goods_id})
				} else {
					// item.check_buy = (item.check_buy == 1 ? 0 : 1);
					store.commit('cartSetGoodsCheckBuy', {goods_id: goods_id})
				}
			},

			/**
			 * 添加商品数量
			 */
			clickGoodsAdd(goods_id, count) {
				store.commit('cartGoodsAdd', {
					goods_id: goods_id,
					count: count
				});

				Util.updateCartBadge(this.getCartCount);
			},

			/**
			 * 减少商品数量
			 */
			clickGoodsMinus(goods_id, goods_count, count) {
				if (goods_count <= count) {
					uni.showModal({
						title: '提示',
						content: '要删除这个商品吗？',
						success: (res) => {
							if (res.confirm) {
								store.commit('cartGoodsDelete', {
									goods_id: goods_id
								});

								Util.updateCartBadge(this.getCartCount);
							}
						}
					});
					return;
				}

				store.commit('cartGoodsMinus', {
					goods_id: goods_id,
					count: count
				});

				Util.updateCartBadge(this.getCartCount);
			},
			
			/**
			 * 设置商品数量
			 */
			inputGoodsCount(e) {
				let count = parseInt(e.detail.value);
				if (!count || count < 1) {
					count = 1;
				}
				
				store.commit('cartSetGoodsCount', {
					goods_id: e.currentTarget.dataset.goods_id,
					count: count
				});
				
				Util.updateCartBadge(this.getCartCount);
			},

			/**
			 * 全选
			 */
			clickCheckAll() {
				if (this.manage) {
					store.commit('cartCheckAllDel', {
						check: (this.getCheckDelAll ? 0 : 1)
					});
				} else {
					store.commit('cartCheckAllBuy', {
						check: (this.getCheckBuyAll ? 0 : 1)
					});
				}
			},

			clickNext() {
				if (this.manage) {
					if (this.getCheckDelCount == 0) {
						Alert.toast('请选择商品')
						return;
					}
					store.commit('cartGoodsDelCheck');
				} else {
					if (this.getCheckBuyCount == 0) {
						Alert.toast('请选择商品')
						return;
					}
					Util.openLink('/pages/order_confirm/order_confirm')
				}
			},

			/**
			 * 购物车为空 点击
			 */
			clickCartEmpty() {
				uni.switchTab({
					url: '/pages/index/index'
				})
			}
		}
	}
</script>

<style lang="scss" scoped>
	@import "@/style/main.css";
</style>
