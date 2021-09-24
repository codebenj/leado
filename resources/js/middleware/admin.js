import store from '~/store'

export default (to, from, next) => {
	if (store.getters['auth/user'].role.name !== 'administrator') {
		// next({ name: 'organisation.dashboard' })
	} else {
		next()
	}
	next()

}
