import Vue from 'vue'
import Vuex from 'vuex'

import Util from '@/utils/util';

Vue.use(Vuex);

const store = new Vuex.Store({
	state: {
		cart: []
	},

	getters: {
		/**
		 * 购物车商品数量
		 */
		getCartCount(state) {
			let count = 0;
			state.cart.forEach((item, index) => {
				count += parseInt(item.count);
			})
			
			return count;
		},
		
		/**
		 * 是否全选 - 购买
		 */
		getCheckBuyAll(state) {
			for (let i=0; i<state.cart.length; i++) {
				if (state.cart[i].check_buy == 0) {
					return false;
				}
			}
			
			return true;
		},
		
		/**
		 * 是否全选 - 管理
		 */
		getCheckDelAll(state) {
			for (let i=0; i<state.cart.length; i++) {
				if (state.cart[i].check_del == 0) {
					return false;
				}
			}
			
			return true;
		},
		
		/**
		 * 选中的数量 - 购买
		 */
		getCheckBuyCount(state) {
			let count = 0;
			state.cart.forEach((item, index) => {
				if (item.check_buy == 1) {
					count += item.count;
				}
			})
			
			return count;
		},
		
		/**
		 * 选中的数量 - 管理
		 */
		getCheckDelCount(state) {
			let count = 0;
			state.cart.forEach((item, index) => {
				if (item.check_del == 1) {
					count += item.count;
				}
			})
			
			return count;
		},
		
		/**
		 * 所有的商品ID
		 */
		getAllGoodsIds(state) {
			let ids = [];
			state.cart.forEach((item, index) => {
				ids.push(item.id);
			})
			
			return ids;
		},
		
		/**
		 * 选中的商品 - 购买
		 */
		getCheckBuyGoodsIds(state) {
			let ids = [];
			state.cart.forEach((item, index) => {
				if (item.check_buy == 1) {
					ids.push(item.id);
				}
			})
			
			return ids;
		},
		
		/**
		 * 选中的商品 - 管理
		 */
		// getCheckDelGoods(state) {
		// 	let ids = [];
		// 	state.cart.forEach((item, index) => {
		// 		if (item.check_del == 1) {
		// 			ids.push(item.id);
		// 		}
		// 	})
			
		// 	return ids;
		// },
		
		/**
		 * 选中的商品 - 金额
		 */
		getCheckBuyAmount(state) {
			let amount = 0;
			state.cart.forEach((item, index) => {
				if (item.check_buy == 1) {
					if (item.price && item.count) {
						amount += parseFloat((item.price * item.count).toFixed(2))
					}
				}
			})
			
			return amount;
		},
	},

	mutations: {
		/**
		 * 设置购物车
		 */
		cartSet(state, cart) {
			state.cart = cart;
		},
		
		/**
		 * 增加商品数量
		 */
		cartGoodsAdd(state, payload) {
			let find = false;
			state.cart.forEach((item, index) => {
				if (item.id == payload.goods_id) {
					find = true;
					item.count += payload.count;
					return;
				}
			})

			if (!find) {
				state.cart.push({
					id: payload.goods_id,
					count: payload.count,
					check_buy: 1,
					check_del: 0,
				})
			}
			
			Util.saveCart(state.cart);
		},

		/**
		 * 减少商品数量
		 */
		cartGoodsMinus(state, payload) {
			state.cart.forEach((item, index) => {
				if (item.id == payload.goods_id) {
					item.count -= payload.count;
					if (item.count <= 0) {
						state.cart.splice(index, 1)
					}
				}
			})
			
			Util.saveCart(state.cart);
		},
		
		/**
		 * 设置商品的数量
		 */
		cartSetGoodsCount(state, payload) {
			state.cart.forEach((item, index) => {
				if (item.id == payload.goods_id) {
					item.count = payload.count;
				}
			})
			
			Util.saveCart(state.cart);
		},

		/**
		 * 删除商品
		 */
		cartGoodsDelete(state, payload) {
			let ncart = [];
			state.cart.forEach((item, index) => {
				if (item.id != payload.goods_id) {
					ncart.push(item)
				}
			})
			state.cart = ncart;
			
			Util.saveCart(state.cart);
		},
		
		/**
		 * 删除已提交订单的商品
		 */
		cartGoodsBuyCheck(state) {
			let ncart = [];
			state.cart.forEach((item, index) => {
				if (item.check_buy == 0) {
					ncart.push(item)
				}
			})
			state.cart = ncart;
			
			Util.saveCart(state.cart);
		},
		
		/**
		 * 删除选中的商品
		 */
		cartGoodsDelCheck(state) {
			let ncart = [];
			state.cart.forEach((item, index) => {
				if (item.check_del == 0) {
					ncart.push(item)
				}
			})
			state.cart = ncart;
			
			Util.saveCart(state.cart);
		},

		// /**
		//  * 清空购物车
		//  */
		// cartClear(state) {
		// 	state.cart = [];
		//  Util.saveCart(state.cart);
		// },
		
		/**
		 * 全选 - 购买
		 */
		cartCheckAllBuy(state, payload) {
			state.cart.forEach((item, index) => {
				item.check_buy = payload.check;
			})
			
			Util.saveCart(state.cart);
		},
		
		/**
		 * 全选 - 管理
		 */
		cartCheckAllDel(state, payload) {
			state.cart.forEach((item, index) => {
				item.check_del = payload.check;
			})
			
			Util.saveCart(state.cart);
		},
		
		/**
		 * 设置商品
		 */
		cartSetGoodsList(state, payload) {
			let cart = payload.list;
			for (let i=0; i<cart.length; i++) {
				for (let j=0; j<state.cart.length; j++) {
					if (state.cart[j].id == cart[i].id) {
						cart[i].count = state.cart[j].count;
						cart[i].check_buy = state.cart[j].check_buy;
						cart[i].check_del = state.cart[j].check_del;
					}
				}
			}
			state.cart = cart;
			
			Util.saveCart(state.cart);
		},
		
		/**
		 * 设置商品选中状态 - 购买
		 */
		cartSetGoodsCheckBuy(state, payload) {
			for (let j=0; j<state.cart.length; j++) {
				if (state.cart[j].id == payload.goods_id) {
					state.cart[j].check_buy = (state.cart[j].check_buy == 1 ? 0 : 1);
				}
			}
			
			Util.saveCart(state.cart);
		},
		
		/**
		 * 设置商品选中状态 - 管理
		 */
		cartSetGoodsCheckDel(state, payload) {
			for (let j=0; j<state.cart.length; j++) {
				if (state.cart[j].id == payload.goods_id) {
					state.cart[j].check_del = (state.cart[j].check_del == 1 ? 0 : 1);
				}
			}
			
			Util.saveCart(state.cart);
		},
	},

	actions: {

	},

})

export default store
