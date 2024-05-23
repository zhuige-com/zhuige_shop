import Constant from '@/utils/constants';


function getToken() {
	let user = uni.getStorageSync(Constant.ZHUIGE_USER_KEY);

	if (!user) {
		return false;
	}

	return user.token;
}

export default {
	getToken: getToken,

	setUser(user) {
		uni.setStorageSync(Constant.ZHUIGE_USER_KEY, user);
	},

	getUser() {
		return uni.getStorageSync(Constant.ZHUIGE_USER_KEY);
	}
};