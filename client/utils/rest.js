const Auth = require("./auth.js");

/**
 * request封装
 */
function request(url, data = {}, method = "GET") {
	return new Promise(function(resolve, reject) {
		uni.showLoading({
			title: '加载中'
		});

		data.token = Auth.getToken();
		
		if (method == "GET") {
			data.t = new Date().getTime();
			data.r = Math.floor(Math.random() * 10000);
		}
		
		// #ifdef MP-WEIXIN
		data.os = 'wx';
		// #endif
		
		// #ifdef MP-BAIDU
		data.os = 'bd';
		// #endif
		
		// #ifdef MP-QQ
		data.os = 'qq';
		// #endif
		
		uni.request({
			url: url,
			data: data,
			method: method,
			success(res) {
				if (res.statusCode != 200) {
					reject(res.errMsg);
					return;
				}

				if (res.data.code == -1) {
					uni.navigateTo({
					    url: '/pages/login/login',
					});
					return;
				}
				
				resolve(res.data);
			},
			fail(err) {
				reject(err);
			},
			complete() {
				uni.hideLoading();
			}
		});
	});
}

/**
 * get请求
 */
function get(url, data = {}) {
	return request(url, data, 'GET');
}

/**
 * post请求
 */
function post(url, data = {}) {
	return request(url, data, 'POST');
}

module.exports = {
	get,
	post,
};
