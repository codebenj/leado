import store from '~/store'

export default (to, from, next) => {
	if (store.getters['auth/user'].role.name !== 'organisation') {
		next();
		// next({ name: 'admin.dashboard' })
	} else {
		next();
	}
}
