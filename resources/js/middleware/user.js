import store from '~/store'

export default (to, from, next) => {
	if (store.getters['auth/user'].role.name !== 'User') {
		// next({ name: 'admin.dashboard' })
		next()
	} else {
		next()
	}
}
