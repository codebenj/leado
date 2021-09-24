import * as types from '../mutation-types'
import axios from 'axios'
import Form from 'vform'

// states
export const state = {
  organisation: null,
  loading: false,
  sendOrgNotificationForm: new Form({
		message: '',
		send_email: true,
		send_sms: true,
    send_to: '',
    email: '',
    number: '',
	}),
	sendOrgNotificationFormRules: {
		message: [
			{ required: true, message: 'Please input a message', trigger: 'blur' },
		],
		notification_process: [
			{ required: true, message: 'Please tick atleast one of these', trigger: 'blur' },
		]
	},
	sendOrgNotificationDialogVisible: false,
  orgNotificationHistory: [],
  orgNotificationHistoryTotal: 0,
  orgNotificationHistoryLoading: true,
}

// getters
export const getters = {
  organisation: state => state.organisation,
  loading: state => state.loading,
  sendOrgNotificationForm: state => state.sendOrgNotificationForm,
  sendOrgNotificationFormRules: state => state.sendOrgNotificationFormRules,
  sendOrgNotificationDialogVisible: state => state.sendOrgNotificationDialogVisible,
  orgNotificationHistory: state => state.orgNotificationHistory,
  orgNotificationHistoryTotal: state => state.orgNotificationHistoryTotal,
  orgNotificationHistoryLoading: state => state.orgNotificationHistoryLoading,
}

// mutations
export const mutations = {
  [types.FETCH_ORG_NOTIFICATION_HISTORY] ( state, { data, total } ) {
    state.orgNotificationHistory = data
    state.orgNotificationHistoryTotal = total
    state.orgNotificationHistoryLoading = false
  },

  [types.SET_DYNAMIC_VALIDATION_RULE] ( state, { params } ){
    console.log( params )
    state.sendOrgNotificationForm.email = params.customer_email
    state.sendOrgNotificationForm.number = params.customer_number

		if(params.customer_email != null && params.customer_number == null){
			state.sendOrgNotificationFormRules.notification_process[0].required = false;
		}
		else if(params.customer_email == null && params.customer_number != null){
			state.sendOrgNotificationFormRules.notification_process[0].required = false;
		}
		else if(params.customer_email != null && params.customer_number != null){
			state.sendOrgNotificationFormRules.notification_process[0].required = false;
		}
	},

	[types.SEND_ORG_MESSAGE] (state) {
		state.loading = false
	},

	[types.SET_DYNAMIC_VALIDATION_RULE_2] ( state, type ){
		if(state.sendOrgNotificationForm.send_email == false && state.sendOrgNotificationForm.send_sms == false){
			state.sendOrgNotificationFormRules.notification_process[0].required = true;
		}
		else if(state.sendOrgNotificationForm.send_email == true && state.sendOrgNotificationForm.send_sms == false){
			state.sendOrgNotificationFormRules.notification_process[0].required = false;
		}
		else if(state.sendOrgNotificationForm.send_email == false && state.sendOrgNotificationForm.send_sms == true){
			state.sendOrgNotificationFormRules.notification_process[0].required = false;
		}
		else{
			state.sendOrgNotificationFormRules.notification_process[0].required = false;
		}
	},
}

// actions
export const actions = {
	validateNotif({ commit }, params){
		try {
			commit(types.SET_DYNAMIC_VALIDATION_RULE, params);
		} catch (error) {
			console.log(error.message)
		}
	},

	validateNotif2({ commit }, type){
		try {
			commit(types.SET_DYNAMIC_VALIDATION_RULE_2, type);
		} catch (error) {
			console.log(error.message)
		}
	},

	async setDialog({ commit }, { close, form }) {
		commit(types.DIALOG_STATE, { form, close })
	},

  setOrg( data ) {
    state.organisation = data
  },

	async sendOrgMessageProfile ({ commit }, id) {
		try {
			const { data } = await state.sendOrgNotificationForm.post(`/api/v1/organizations/${id}/send_message`)
			return data
		} catch (error) {console.log(error.message)}
	},

  async fetchOrgNotificationHistory( { commit }, { id, queryInfo } ) {
    try {
      const params = {
				pageNo: queryInfo.page ? queryInfo.page : 1,
				pageSize: queryInfo.pageSize ? queryInfo.pageSize : 20,
			};

      const { data } = await axios.get( `/api/v1/organizations/${id}/fetch_messages`, { params: params } )
      commit( types.FETCH_ORG_NOTIFICATION_HISTORY, { data: data.data.notifications, total: data.data.total } )

    } catch (error) {
      console.log( error.message )
    }
  }
}