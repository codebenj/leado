import axios from 'axios'
import Cookies from 'js-cookie'
import * as types from '../mutation-types'
import Form from 'vform'

// state
export const state = {
  user: null,
  userOrganisationSuspended: false,
  token: Cookies.get('token'),
  loginForm: new Form({
      email: '',
      password: '',
      timezone: '',
  }),
  loginRules: {
    email: [
    	{ required: true, message: 'Please input email address', trigger: 'blur' },
    ],
    password: [
    	{ required: true, message: 'Please input password', trigger: 'blur' },
    ],
  },
  loading: false,
  navigation: [],
}

// getters
export const getters = {
  user: state => state.user,
  roles: state => state.roles,
  permissions: state => state.permissions,
  loginForm: state => state.loginForm,
  loginRules: state => state.loginRules,
  loginErrors: state => state.loginErrors,
  loading: state => state.loading,
  token: state => state.token,
  check: state => state.user !== null,
  navigation: state => state.navigation,
  userOrganisationSuspended: state => state.userOrganisationSuspended
}

// mutations
export const mutations = {
  [types.SAVE_TOKEN] (state, { token, remember }) {
    state.token = token
    Cookies.set('token', token, { expires: remember ? 365 : 30 })
  },

  [types.LOGIN_USER_FAILED] (state, { errors }) {
    state.loginErrors = errors;
    state.loading = false;
  },


  [types.FETCH_USER_SUCCESS] (state, { user }) {
    state.user = user
    //try to get if the user is organisation else not organisation, so its has not suspended
    try{ state.userOrganisationSuspended = (user.organisation_user.organisation.is_suspended == '1') ? true : false }
    catch(e){ state.userOrganisationSuspended = false }
  },

  [types.CLOSE_SUSPENDED_MODAL] (state){
    state.userOrganisationSuspended = false
  },

  [types.FETCH_USER_ROLES] (state, { roles }) {
    state.roles = roles
  },

  [types.FETCH_USER_PERMISSIONS] (state, { permissions }) {
    state.permissions = permissions
    state.loading = false;
  },

  [types.FETCH_USER_FAILURE] (state) {
    state.token = null
    Cookies.remove('token')
  },

  [types.LOGOUT] (state) {
    state.user = null
    state.token = null

    Cookies.remove('token')
  },

  [types.UPDATE_USER] (state, { user }) {
    state.user = user
  },

  [types.FETCH_USER_MENUS] ( state, { navigation }) {
	  state.navigation = navigation;
  }
}

// actions
export const actions = {
  async closeOrganisationSuspendedModal({ commit }){
    commit(types.CLOSE_SUSPENDED_MODAL)
  },

  async loginUser ({ commit, dispatch }, payload) {

    try {
      state.loginErrors = [];
      state.loading = true;
      const { data } = await state.loginForm.post('/api/login')
      return data
    } catch (e) {
      state.loading = false;
      // return e;
    }
  },

  saveToken ({ commit, dispatch }, payload) {
    commit(types.SAVE_TOKEN, payload)
  },

  async fetchUser ({ commit }) {
    try {
      const { data } = await axios.get('/api/user')
      commit(types.FETCH_USER_SUCCESS, { user: data })
    } catch (e) {
      commit(types.FETCH_USER_FAILURE)
    }
  },

  async fetchUserRoles ({ commit }) {
    try {
      const { data } = await axios.get('/api/user/roles')
      commit(types.FETCH_USER_ROLES, { roles: data })

      return data
    } catch (e) {
      return e
    }
  },

  async fetchUserPermissions ({ commit }) {
    try {
      const { data }  = await axios.get('/api/user/permissions');

      commit(types.FETCH_USER_PERMISSIONS, { permissions: data })

      return data
    } catch (e) {
      return e
    }
  },

  updateUser ({ commit }, payload) {
    commit(types.UPDATE_USER, payload)
  },

  async logout ({ commit }) {
    try {
      await axios.post('/api/logout')
    } catch (e) { }

    commit(types.LOGOUT)
  },

  async fetchOauthUrl (ctx, { provider }) {
    const { data } = await axios.post(`/api/oauth/${provider}`)

    return data.url
  },

  async fetchUserMenus({ commit }) {
	  try {
		  const { data } = await axios.get('/api/user/menu');
		  commit(types.FETCH_USER_MENUS, { navigation: data.data })
	  } catch (e) { console.log(e) }
  }
}
