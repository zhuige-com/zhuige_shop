import Constant from '@/utils/constants';


function getToken() {
	let user = uni.getStorageSync(Constant.ZHUIGE_USER_KEY);

	if (!user) {
		return false;
	}

	return user.token;
}

module.exports = {
	getToken: getToken,

	setUser: function(user) {
		uni.setStorageSync(Constant.ZHUIGE_USER_KEY, user);
	},

	getUser: function() {
		return uni.getStorageSync(Constant.ZHUIGE_USER_KEY);
	}
};
