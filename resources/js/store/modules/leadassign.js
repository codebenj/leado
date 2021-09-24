import * as types from '../mutation-types'
import axios from 'axios'
import Form from 'vform'

// state
export const state = {
    users: [],
    lead: null,
    lead_id: null,
    assignedUsers: [],
    assignUserToLeadForm: new Form({
		allUsers: []
	}),
}

// getters
export const getters = {
    users: state => state.users,
    lead: state => state.lead,
    lead_id: state => state.lead_id,
    assignedUsers: state => state.assignedUsers,
    assignUserToLeadForm: state => state.assignUserToLeadForm,
}

// mutations
export const mutations = {
    [types.FETCH_USERS] ( state, data ) {
        state.users = data
    },

    [types.GET_ASSIGNED_USERS] ( state, data ) {
        let ids = []
		if ( data ) {
			data.forEach( el => {
				ids.push( el.id )
			} )
		}
		state.assignUserToLeadForm.allUsers = ids
		state.assignedUsers = data;
    },

    [types.INSERT_ASSIGNED_USER] ( state, id ) {
        try {
			let users = state.assignUserToLeadForm.allUsers
			users.push( id )
			state.assignUserToLeadForm.allUsers = users

		} catch ( error ) {
			console.log( error.message )
		}
    },

    [types.DELETE_ASSIGNED_USER] ( state, id ) {
        try {
			let users = state.assignUserToLeadForm.allUsers
			let index = null

			for (let x = 0; x < users.length; x++) {
				let user = users[x]

				if ( user == id ) index = x
			}

			users.splice( index, 1 )
			state.assignUserToLeadForm.allUsers = users

		} catch (error) {
			console.log( error.message )
		}
    },

    [types.SET_LEAD_DATA] ( state, { id, lead } ) {
        state.lead_id = id
        state.lead = lead
    },
}

// actions
export const actions = {
    async fetchUsers({ commit }) {
		try {
			const { data } = await axios.get( `/api/v1/leads/fetchUsers` )
			commit( types.FETCH_USERS, data.data )

			return data.data
		} catch( error ) {
			console.log( error.message )
		}
	},

    async assignUserToLead({ commit }, obj) {
		try{
			state.assignUserToLeadForm.allUsers = obj.ids

			const { data } = await state.assignUserToLeadForm.post(`/api/v1/leads/assign_user_to_lead/${obj.lead_id}`);
			return data.data
		}
		catch(error){
			console.log(error.message)
		}
	},

	async fetchAssignedUsers({ commit }, id) {
		try {
			const { data } = await axios.get( `/api/v1/leads/fetch_assigned_users/${id}` )
			commit( types.GET_ASSIGNED_USERS, data.data )

			return data.data
		} catch( error ) {
			console.log( error.message )
		}
	},

	async removeAssignedUser({ commit }, obj) {
		try {
			state.assignUserToLeadForm.allUsers = obj.ids

            const { data } = await state.assignUserToLeadForm.post(`/api/v1/leads/assign_user_to_lead/${obj.lead_id}`)
			return data.data

		} catch (error) {
			console.log(error.message)
		}
	},

	async addAssignedUser({ commit }, id) {
		try {
			commit(types.INSERT_ASSIGNED_USER, id);
		} catch (error) {
			console.log(error.message)
		}
	},
}
