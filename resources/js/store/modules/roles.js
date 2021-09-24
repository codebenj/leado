import axios from 'axios'
import * as types from '../mutation-types'
import Form from 'vform'

// state
export const state = {
    roles: [],
    total: 0,
    loading: false,
    dialogVisible: false,
    rolesForm: new Form({
        name: "",
		permissions: [],
    }),
    rolesFormRules: {
        name: [
            { required: true, message: 'Please input role name', trigger: 'blur' },      
        ]
    },
}

// getters
export const getters = {
    roles: state => state.roles,
    rolesForm: state => state.rolesForm,
    rolesFormRules: state => state.rolesFormRules,
    permissions: state => state.permissions,
    loading: state => state.loading,
    total: state => state.total,
    dialogVisible: state => state.dialogVisible,
}

// mutations
export const mutations = {
    [types.FETCH_ROLES] (state, { roles, total }) {
        state.roles = roles.length ? roles : [] 
        state.total = total ? total : 0
        state.loading = false
    },

    // edit role
    [types.EDIT_ROLE] (state, { role }) {
		state.dialogVisible = true
        state.rolesForm.reset();
        
        if ( role ) {
            state.rolesForm.fill(role);
            state.rolesForm.id = role.id
            state.rolesForm.permissions = role.permissions.map(p => p.name);
        }
    },

    // save role
    [types.SAVE_ROLE] (state, { role }) {		
		const index = state.roles.findIndex(r => r.id === role.id);

        if ( index !== -1) {
            state.roles.splice(index, 1, role);
        } else {
            state.roles.push(role);
		}
		
		state.loading = false
	},
	
	[types.DELETE_ROLE] (state, id) {
		const index = state.roles.findIndex(r => r.id === id);

		if ( index !== -1) {
			state.roles.splice(index, 1);
		}
    },

    [types.DIALOG_ROLE_STATE] (state, close) {
		state.dialogVisible = close
    },
}

// actions
export const actions = {
    async fetchRoles({ commit }, queryInfo) {
		try {
            state.loading = true
            const params = {
                pageNo: queryInfo.page ? queryInfo.page : 1,
                pageSize: queryInfo.pageSize ? queryInfo.pageSize : 10,
                search: queryInfo.filters[0] ? queryInfo.filters[0].value : "",
            };
              
            const { data } = await axios.get(`/api/v1/roles`, { params });
		  	commit(types.FETCH_ROLES, data.data)
		} catch (error) {console.log(error.message)}
    },

    async editRole({ commit }, data) {
		try {
            if (data) {
                commit(types.EDIT_ROLE, { role: data })
                  
                return data;

            } else {
                commit(types.EDIT_ROLE, { role: null })
            }

            commit(types.DIALOG_ROLE_STATE, true)

		} catch (error) {console.log(error.message)}
    },

	async saveRole({ commit }) {
		try {
			state.loading = true
			const saveURL = state.rolesForm.id ? `/api/v1/roles/${state.rolesForm.id}` : `/api/v1/roles`
            const { data } = await (state.rolesForm.id ? state.rolesForm.put(saveURL) : state.rolesForm.post(saveURL))
			commit(types.SAVE_ROLE, { role: data.data })
      		return data;
		} catch (error) {
			return state.rolesForm.errors
		}
	},

	async deleteRole({ commit }, id) {
		try {
			const { data } = await axios.delete(`/api/v1/roles/${id}`)
            commit(types.DELETE_ROLE, id)
			return data;
		} catch (error) {
            const { response } = error 
            return response.data
        }
	},

    async setDialog({ commit }, close) {
        commit(types.DIALOG_ROLE_STATE, close)
    }

}
