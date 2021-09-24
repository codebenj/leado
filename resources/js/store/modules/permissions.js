import axios from 'axios'
import * as types from '../mutation-types'
import Form from 'vform'

// state
export const state = {
    permissions: [],
    permissionInputVisible: '',
    loading: false,
    permissionForm: new Form({
        name: "",
    }),
}

// getters
export const getters = {
    permissions: state => state.permissions,
    permissionForm: state => state.permissionForm,
    permissionInputVisible: state => state.permissionInputVisible,
    loading: state => state.loading,
}

// mutations
export const mutations = {
    [types.FETCH_PERMISSIONS] (state, permissions) {
        state.permissions = permissions.length ? permissions : [] 
        state.loading = false
    },

    [types.ADD_PERMISSION] (state, { permission }) {
        if (permission.name !== '' && !state.permissions.includes(permission.name)) {
            state.permissions.push(permission.name)
        }
        state.permissionInputVisible = false;
        state.permissionForm.name = ''
    },

    [types.REMOVE_PERMISSION] (state, permission) {
        state.permissions.splice(state.permissions.indexOf(permission), 1);
    },

    [types.SHOW_PERMISSION_INPUT] (state) {
        state.permissionInputVisible = true;
    },

    [types.DIALOG_SETTINGS_STATE] (state, close) {
		state.dialogVisible = close
    },
}

// actions
export const actions = {
    async fetchPermissions({ commit }) {
		try {
            state.loading = true
            const { data } = await axios.get('/api/v1/permissions');
            commit(types.FETCH_PERMISSIONS, data.data)
            
		} catch (error) {console.log(error.message)}
    },

    async addPermission({ commit }) {
		try {
            
            if (state.permissionForm.name) {
                const { data } = await state.permissionForm.post('/api/v1/permissions');
                // add permission endpoint
                commit(types.ADD_PERMISSION, { permission: data.data })
            } else {
                state.permissionInputVisible = false;
                state.permissionForm.name = ''
            }

		} catch (error) {
            return error.response ? error.response.data : error
        }
    },

    async removePermission({ commit }, permission) {
		try {
            const { data } = await axios.delete(`/api/v1/permissions/${permission}`)
            
            commit(types.REMOVE_PERMISSION, permission)
            return data;


		} catch (error) {console.log(error.message)}
    },

    showPermission({ commit }) {
		try {
            commit(types.SHOW_PERMISSION_INPUT)
		} catch (error) {console.log(error.message)}
    },


    setDialog({ commit }, close) {
        commit(types.DIALOG_SETTINGS_STATE, close)
    }

}
