import Config from "@/utils/config";

function makeURL(module, action) {
	return `https://${Config.JQ_DOMAIN}/wp-json/zhuige-shop/${module}/${action}`;
}

export default {

	// ---------- 文章 ----------

	/**
	 * 获取页面详情
	 */
	ZHUIGE_SHOP_POST_PAGE: makeURL('post', 'page'),


	// ---------- 商品 ----------

	/**
	 * 获取最新商品列表
	 */
	ZHUIGE_SHOP_GOODS_LAST: makeURL('goods', 'last'),

	/**
	 * 获取商品详情
	 */
	ZHUIGE_SHOP_GOODS_DETAIL: makeURL('goods', 'detail'),

	/**
	 * 获取分类配置
	 */
	ZHUIGE_SHOP_GOODS_CATEGORY: makeURL('goods', 'category'),

	/**
	 * 搜索商品
	 */
	ZHUIGE_SHOP_GOODS_SEARCH: makeURL('goods', 'search'),

	/**
	 * 购物车商品
	 */
	ZHUIGE_SHOP_GOODS_CART: makeURL('goods', 'cart'),

	/**
	 * 积分兑换记录
	 */
	ZHUIGE_SHOP_GOODS_RECORD: makeURL('goods', 'record'),

	// ---------- 评论 ----------

	/**
	 * 获取评论
	 */
	ZHUIGE_SHOP_COMMENT_INDEX: makeURL('comment', 'index'),

	/**
	 * 添加评论
	 */
	ZHUIGE_SHOP_COMMENT_ADD: makeURL('comment', 'add'),

	/**
	 * 删除评论
	 */
	ZHUIGE_SHOP_COMMENT_DELETE: makeURL('comment', 'delete'),

	// ---------- 配置 ----------

	/**
	 * 获取首页配置
	 */
	ZHUIGE_SHOP_SETTING_HOME: makeURL('setting', 'home'),

	/**
	 * 获取我的配置
	 */
	ZHUIGE_SHOP_SETTING_MINE: makeURL('setting', 'mine'),

	/**
	 * 获取登录配置
	 */
	ZHUIGE_SHOP_SETTING_LOGIN: makeURL('setting', 'login'),
	
	/**
	 * 获取注销配置
	 */
	ZHUIGE_SHOP_SETTING_LOGOUT: makeURL('setting', 'logout'),
	
	/**
	 * 获取搜索配置
	 */
	ZHUIGE_SHOP_SETTING_SEARCH: makeURL('setting', 'search'),

	// ---------- 用户 ----------

	/**
	 * 登录
	 */
	ZHUIGE_SHOP_USER_LOGIN: makeURL('user', 'login'),

	/**
	 * 设置手机号
	 */
	ZHUIGE_SHOP_SET_MOBILE: makeURL('user', 'set_mobile'),

	/**
	 * 注销-登录
	 */
	ZHUIGE_SHOP_USER_LOGOUT: makeURL('user', 'logout'),
	
	/**
	 * 注销-账号
	 */
	ZHUIGE_SHOP_USER_LOGOFF: makeURL('user', 'logoff'),

	/**
	 * 设置用户信息
	 */
	ZHUIGE_SHOP_USER_SET_INFO: makeURL('user', 'set_info'),

	// ---------- 订单 ----------

	/**
	 * 创建订单
	 */
	ZHUIGE_SHOP_ORDER_CREATE: makeURL('order', 'create'),

	/**
	 * 支付订单
	 */
	ZHUIGE_SHOP_ORDER_PAY: makeURL('order', 'pay'),

	/**
	 * 确认订单
	 */
	ZHUIGE_SHOP_ORDER_CONFIRM: makeURL('order', 'confirm'),

	/**
	 * 取消订单
	 */
	ZHUIGE_SHOP_ORDER_CANCEL: makeURL('order', 'cancel'),

	/**
	 * 删除订单
	 */
	ZHUIGE_SHOP_ORDER_DELETE: makeURL('order', 'delete'),

	/**
	 * 订单详情
	 */
	ZHUIGE_SHOP_ORDER_LIST: makeURL('order', 'list'),

	/**
	 * 订单详情
	 */
	ZHUIGE_SHOP_ORDER_DETAIL: makeURL('order', 'detail'),

	/**
	 * 订单统计
	 */
	ZHUIGE_SHOP_ORDER_COUNT: makeURL('order', 'count'),

	/**
	 * 上传图片
	 */
	ZHUIGE_SHOP_OTHER_UPLOAD: makeURL('other', 'upload'),

};