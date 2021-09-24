import * as types from '../mutation-types'
import axios from 'axios'
import Form from 'vform'
import { param } from 'jquery'
import { faSleigh } from '@fortawesome/free-solid-svg-icons'

// state
export const state = {
	customer: {},
	lead: null,
	active_escalation: null,
	lead_id: null,
	loading: false,
	histories: [],
	notifications: [],
	org_notifications: [],
	jobDetails: [],
	organizations: [],
	reasons: [
		{
			'level': 'Discontinued',
			'status': 'Discontinued',
			'reason': 'This Lead was DISCONTINUED by the Admin',
		},
    {
			'level': 'Discontinued',
			'status': 'Discontinued',
			'reason': 'This Lead was DISCONTINUED by the Organisation',
		},
		{
			'level': 'Abandoned',
			'status': 'Abandoned',
			'reason': 'This Lead was ABANDONED by the Organisation',
		},
		{
			'level': 'Declined',
			'status': 'Declined',
			'reason': 'This Lead was DECLINED by the Organisation',
		},
    {
			'level': 'Accept Or Decline',
			'status': 'Declined-Lapsed',
			'reason': 'This lead was not ACCEPTED on time (Lapsed)',
		},
	],
	reassignForm: new Form({
		reasonIndex: '',
		reason: '',
		old_organization_id: '',
		new_organization_id: '',
		escalation_level: '',
		escalation_status: '',
		send_email: true,
		send_sms: true
	}),
	metersForm: new Form({
		lead_id: '',
		gutter_edge_meters: '',
		valley_meters: '',
		installation_date: '',
	}),
	reassignFormRules: {
		reasonIndex: [
			{ required: true, message: 'Please select a reason', trigger: 'blur' },
		],
		new_organization_id: [
			{ required: true, message: 'Please select an organisation', trigger: 'blur' },
		],
	},
	sendNotificationForm: new Form({
		message: '',
		send_email: true,
		send_sms: true,
    send_to: ''
	}),
	sendNotificationFormRules: {
		message: [
			{ required: true, message: 'Please input a message', trigger: 'blur' },
		],
		notification_process: [
			{ required: true, message: 'Please tick at least one of these', trigger: 'blur' },
		]
	},

	sendEnquirerDetailsForm: new Form({
		inquirer: {},
		send_email: true,
		send_sms: true,
    send_to: ''
	}),
	sendEnquirerDetailsFormRules: {
		enquiry_process: [
			{ required: false, message: 'Please tick at least one of these' },
		]
	},
	reassignDialogVisible: false,
	sendNotificationDialogVisible: false,
	jobDetailsDialogVisible: false,
	total: 0,
	jobDetailsForm: new Form({
		id: '',
		lead_id: '',
		meters_gutter_edge: 0,
		meters_valley: 0,
		comments: '',
	}),
	jobDetailsFormRules: {
		meters_gutter_edge: [
			{ required: true, message: 'Please input metres of gutter edge', trigger: 'blur' },
			{ type: 'number', message: 'Metres of gutter edge must be a number'}
		],
		meters_valley: [
			{ required: true, message: 'Please input metres of valley', trigger: 'blur' },
			{ type: 'number', message: 'Metres of valley must be a number'}
		],
		comments: [
			{ required: true, message: 'Please input comments', trigger: 'blur' },
		],
	},
	saleForm: new Form({
		sale: 0,
	}),
	saleRules: {
		sale: [
			{ required: true, message: 'Please input sale amount', trigger: 'blur' },
		],
	},
	leadCommentLoading: true,
	leadComments: [],
	leadCommentsTotal: 0,

	leadCommentForm:  new Form({
		lead_comment_id: "",
		comment: "",
		lead_id: ""
	}),
	leadCommentRules: {
		comment: [
			{ required: true, message: 'Please input a comment', trigger: 'blur' },
		],
	},
	metersFormRules: {
		gutter_edge_meters: [
			{ required: true, message: 'Please enter a number or "0"', trigger: 'blur' },
		],
		valley_meters: [
			{ required: true, message: 'Please enter a number or "0"', trigger: 'blur' },
		],
    installation_date: [
      { required: true, message: 'Please pick an installation date.', trigger: 'blur' },
    ]
	},
	addLeadCommentDialogVisible: false,
	viewLeadCommentDialogVisible: false,

	// ASSIGN USER TO LEAD: STATE
	users: [],
	assignedUsers: [],

	assignUserToLeadForm: new Form({
		id: "",
		user_id: "",
		lead_escalation_id: "",
		allUsers: ""
	}),

	assignUserToLeadRules: {
		user_id: [
			{ required: true, message: "Please select a user", trigger: "blur" },
		],
	},

	assignUserToLeadDialogVisible: false,
	sendEnquirerDetailsDialogVisible: false,
  historyDrawer: false,
  nestedDrawer: false,
  historyDrawerId: null,
  historyDrawerNested: false,
  historyDrawerNestedId: null,
}

// getters
export const getters = {
	lead: state => state.lead,
	lead_id: state => state.lead_id,
	histories: state => state.histories,
	notifications: state => state.notifications,
  org_notifications: state => state.org_notifications,
	reassignForm: state => state.reassignForm,
	reassignFormRules: state => state.reassignFormRules,
	sendNotificationForm: state => state.sendNotificationForm,
	sendNotificationFormRules: state => state.sendNotificationFormRules,
	sendEnquirerDetailsForm: state => state.sendEnquirerDetailsForm,
	sendEnquirerDetailsFormRules: state => state.sendEnquirerDetailsFormRules,
	reasons: state => state.reasons,
	reassignDialogVisible: state => state.reassignDialogVisible,
	sendNotificationDialogVisible: state => state.sendNotificationDialogVisible,
	jobDetailsDialogVisible: state => state.jobDetailsDialogVisible,
	loading: state => state.loading,
	total: state => state.total,
	jobDetails: state => state.jobDetails,
	saleForm: state => state.saleForm,
	saleRules: state => state.saleRules,
	jobDetailsForm: state => state.jobDetailsForm,
	jobDetailsFormRules: state => state.jobDetailsFormRules,
	active_escalation: state => state.active_escalation,
	leadCommentForm: state => state.leadCommentForm,
	leadCommentRules: state => state.leadCommentRules,
	leadComments: state => state.leadComments,
	addLeadCommentDialogVisible: state => state.addLeadCommentDialogVisible,
	viewLeadCommentDialogVisible: state => state.viewLeadCommentDialogVisible,
	leadCommentsTotal: state => state.leadCommentsTotal,
	leadCommentLoading: state => state.leadCommentLoading,
	customer: state => state.customer,
	organizations: state => state.organizations,
	metersForm: state => state.metersForm,
	metersFormRules: state => state.metersFormRules,

	// ASSIGN USER TO LEAD: GETTERS
	users: state => state.users,
	assignedUsers: state => state.assignedUsers,
	assignUserToLeadForm: state => state.assignUserToLeadForm,
	assignUserToLeadRules: state => state.assignUserToLeadRules,
	assignUserToLeadDialogVisible: state => state.assignUserToLeadDialogVisible,
	sendEnquirerDetailsDialogVisible: state => state.sendEnquirerDetailsDialogVisible,

  historyDrawer: state => state.historyDrawer,
  nestedDrawer: state => state.nestedDrawer,
  historyDrawerId: state => state.historyDrawerId,

  historyDrawerNested: state => state.historyDrawerNested,
  historyDrawerNestedId: state => state.historyDrawerNestedId,
}

// mutations
export const mutations = {
	[types.SET_DYNAMIC_VALIDATION_RULE] ( state, params ){
		if(state.lead.customer.email != null && state.lead.customer.contact_number == null){
			state.sendNotificationFormRules.notification_process[0].required = false;
		}
		else if(state.lead.customer.email == null && state.lead.customer.contact_number != null){
			state.sendNotificationFormRules.notification_process[0].required = false;
		}
		else if(state.lead.customer.email != null && state.lead.customer.contact_number != null){
			state.sendNotificationFormRules.notification_process[0].required = false;
		}
	},

	[types.SET_DYNAMIC_VALIDATION_RULE_2] ( state, type ){
		if(state.sendNotificationForm.send_email == false && state.sendNotificationForm.send_sms == false){
			state.sendNotificationFormRules.notification_process[0].required = true;
		}
		else if(state.sendNotificationForm.send_email == true && state.sendNotificationForm.send_sms == false){
			state.sendNotificationFormRules.notification_process[0].required = false;
		}
		else if(state.sendNotificationForm.send_email == false && state.sendNotificationForm.send_sms == true){
			state.sendNotificationFormRules.notification_process[0].required = false;
		}
		else{
			state.sendNotificationFormRules.notification_process[0].required = false;
		}
	},


	[types.SET_ENQUIRY_DETAILS_DYNAMIC_VALIDATION_RULE] ( state, params ) {
		if(params.organisation_email != null && params.organisation_number == null){
			state.sendEnquirerDetailsFormRules.enquiry_process[0].required = false;
		}
		else if(params.organisation_email == null && params.organisation_number != null){
			state.sendEnquirerDetailsFormRules.enquiry_process[0].required = false;
		}
		else if(params.organisation_email != null && params.organisation_number != null){
			state.sendEnquirerDetailsFormRules.enquiry_process[0].required = false;
		}
	},

	[types.SET_ENQUIRY_DETAILS_DYNAMIC_VALIDATION_RULE_2] ( state, type ) {
		if(state.sendEnquirerDetailsForm.send_email == false && state.sendEnquirerDetailsForm.send_sms == false){
			state.sendEnquirerDetailsFormRules.enquiry_process[0].required = true;
		}
		else if(state.sendEnquirerDetailsForm.send_email == true && state.sendEnquirerDetailsForm.send_sms == false){
			state.sendEnquirerDetailsFormRules.enquiry_process[0].required = false;
		}
		else if(state.sendEnquirerDetailsForm.send_email == false && state.sendEnquirerDetailsForm.send_sms == true){
			state.sendEnquirerDetailsFormRules.enquiry_process[0].required = false;
		}
		else{
			state.sendEnquirerDetailsFormRules.enquiry_process[0].required = false;
		}
	},

  [types.GET_ORGANIZATION_POSTCODE_FILTERED] (state, {data}){
	state.organizations = data
  },

    [types.GET_LEAD_HISTORY] (state, { id, lead, active_escalation }) {
        state.staff_comment = lead.staff_comments;
        state.lead = lead
        state.active_escalation = active_escalation
        state.lead_id = id
        state.histories = lead.lead_escalations
        state.notifications = lead.notifications
        state.org_notifications = lead.org_notifications
        state.reassignForm.old_organization_id = state.histories[0].organisation_id
        state.saleForm.sale = lead.sale
        state.jobDetailsForm.lead_id = id
        state.loading = false;
        state.customer = lead.customer
        state.metersForm.reset();
        // find the won here

        const latestWonEscalation = lead.lead_escalations.find((l) => {
            return l.escalation_status == 'Won' && l.escalation_level == 'Won'
        })

        state.metersForm.id = latestWonEscalation ? latestWonEscalation.id : ''
        state.metersForm.gutter_edge_meters = latestWonEscalation ? latestWonEscalation.gutter_edge_meters : ''
        state.metersForm.valley_meters = latestWonEscalation ? latestWonEscalation.valley_meters : ''
        state.metersForm.installation_date = latestWonEscalation ? latestWonEscalation.installed_date : ''
    },

    [types.GET_ENQUIRER_NOTIFICATION] (state, { notifications }){
      state.notifications.unshift(notifications[0])
    },

    [types.GET_LEAD_ORG_NOTIFICATION] (state, { notifications }){
      console.log(this.notifications)
      state.org_notifications.unshift(notifications[0])
    },

    [types.SET_REASSIGN_FIELDS] (state, { organisation_id }) {
        state.reassignForm.escalation_level = state.reasons[state.reassignForm.reasonIndex].level
        state.reassignForm.escalation_status = state.reasons[state.reassignForm.reasonIndex].status
        state.reassignForm.reason = state.reasons[state.reassignForm.reasonIndex].reason
        console.log(state.reassignForm.reason);
        state.reassignForm.old_organization_id = organisation_id ? organisation_id : 0
        state.loading = true
    },

    [types.REASSIGN_LEAD] (state) {
        state.reassignDialogVisible = false;
        state.loading = false;
        state.reassignForm.reset();
    },

    [types.FETCH_JOB_DETAILS] (state, { jobDetails, total }) {
        state.jobDetails = jobDetails
        state.total = total ? total : 0
		state.loading = false;
	},

	[types.EDIT_JOB_DETAILS] (state, { jobDetail, id, leadId }) {
		state.jobDetailsDialogVisible = true
		state.jobDetailsForm.reset();
		state.jobDetailsForm.lead_id = leadId

		if (jobDetail) {
			state.jobDetailsForm.fill(jobDetail)
		}
	},

	[types.DELETE_JOB_DETAILS] (state, id) {
		const index = state.jobDetails.findIndex(jd => jd.id === id);

		if ( index !== -1) {
				state.jobDetails.splice(index, 1);
				state.total--
		}
	},

	[types.SAVE_JOB_DETAILS] (state, {jobDetail}) {
	  try{
		const index = state.jobDetails.findIndex(jd => jd.id === jobDetail.id);

		if ( index !== -1) {
			state.jobDetails.splice(index, 1, jobDetail)
		} else {
			state.jobDetails.push(jobDetail)
		}
	  }
	  catch(error){}

		state.loading = false
		state.jobDetailsDialogVisible = false
	},

	[types.SAVE_SALE] (state, { lead }) {
		state.lead.sale = lead.sale
	},

	[types.SEND_ENQUIRER_MESSAGE] (state) {
		state.loading = false
		state.sendNotificationDialogVisible = false
		state.sendEnquirerDetailsDialogVisible = false
	},

	[types.DIALOG_STATE] (state, { form, close }) {
		if (form == 'reassign'){
      state.reassignDialogVisible = close
    }
		else if (form == 'send_notification'){
      state.sendNotificationDialogVisible = close
      state.sendNotificationForm.message = ''
    }
		else if (form == 'job_details'){
      state.jobDetailsDialogVisible = close
    }
		else if (form == 'add_lead_comment'){
      state.addLeadCommentDialogVisible = close
    }
		else if (form == 'view_lead_comment'){
      state.viewLeadCommentDialogVisible = close
    }
		else if (form == 'assign_user_to_lead'){
      state.assignUserToLeadDialogVisible = close
    }
		else if (form == 'send_enquirer_details'){
      state.sendEnquirerDetailsDialogVisible = close
    }
	},

	[types.ADD_LEAD_COMMENT] (state, comment) {
		state.loading = false
		state.addLeadCommentDialogVisible = false
		state.leadCommentForm.reset();

		const index = state.leadComments.findIndex(c => c.id === comment.id);

		if ( index !== -1) {
			state.leadComments.splice(index, 1, comment);
		} else {
			state.leadComments.unshift(comment);
		}
	},

	// ASSIGN USER TO LEAD: MUTATION
	[types.FETCH_USERS] (state, data) {
		state.loading = false;
		state.users = data;
	},

	[types.ASSIGN_USER_TO_LEAD] (state, data) {
		state.assignedUsers = data;
	},

	[types.FETCH_ASSIGNED_USERS] (state, data) {
		let ids = []
		if ( data ) {
			data.forEach( el => {
				ids.push( el.id )
			} )
		}
		state.assignUserToLeadForm.allUsers = ids
		state.assignedUsers = data;
	},

	[types.EDIT_LEAD_COMMENT] (state, comment) {
		state.leadCommentForm.fill(comment)
		state.leadCommentForm.lead_comment_id = comment.id
	},

	[types.DELETE_LEAD_COMMENT] (state, id) {
		const index = state.leadComments.findIndex(comment => comment.id === id);

		if ( index !== -1) {
			state.leadComments.splice(index, 1);
		}
  },

	[types.FETCH_LEAD_COMMENTS] (state, { comments, total }) {
		state.leadComments = comments.length ? comments : []
		state.leadCommentsTotal = total ? total : 0
		state.leadCommentLoading = false;
	},

    [types.SAVE_ESCALATION_METERS] (state, { lead_escalation }) {
      try{
        const index = state.histories.findIndex(h => h.id === lead_escalation.id);
        if ( index !== -1) {
          state.histories.splice(index, 1, lead_escalation)
        }
      }
      catch(error){}
      state.loading = false
    },

	[types.REMOVE_ASSIGNED_USER] (state, id) {
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

	[types.ADD_ASSIGNED_USER] (state, id) {
		try {
			let users = state.assignUserToLeadForm.allUsers
			users.push( id )

			state.assignUserToLeadForm.allUsers = users
		} catch (error) {
			console.log( error.message )
		}
	},

  [types.LEAD_OVERVIEW] (state, {show, lead_id, nested}){
    state.historyDrawer = show
    state.historyDrawerId = lead_id
    state.nestedDrawer = nested
  },

  [types.LEAD_OVERVIEW_NESTED] (state, {show, lead_id, nested}){
    state.historyDrawerNested = show
    state.historyDrawerNestedId = lead_id
  },


}

// actions
export const actions = {
  async openLeadOverviewNested( {commit}, lead_id){
    commit(types.LEAD_OVERVIEW_NESTED, {show: true, lead_id: lead_id, nested: true })
  },

  async closeLeadOverviewNested( {commit}){
    commit(types.LEAD_OVERVIEW_NESTED, {show: false, lead_id: 0, nested: false })
  },

  async openLeadOverview( {commit}, lead_id){
    commit(types.LEAD_OVERVIEW, {show: true, lead_id: lead_id, nested: true })
  },

  async closeLeadOverview( {commit}){
    commit(types.LEAD_OVERVIEW, {show: false, lead_id: 0, nested: false })
  },

	async getOrganizationPostCodes( {commit}, lead_id){
	  try{
		const { data } = await axios.get(`/api/v1/organizations/postcode/get/by/lead_id/${lead_id}`)
		commit(types.GET_ORGANIZATION_POSTCODE_FILTERED, {data: data.data })
	  }
	  catch(e){console.log(e.message)}
	},

	async getLeadHistory ({ commit }, id) {
		try {
			const { data } = await axios.get(`/api/v1/leads/${id}/history`)

			commit(types.GET_LEAD_HISTORY, {
				id,
				lead: data.data,
				active_escalation: data.active_escalation
			})

			return data;
		} catch (error) {console.log(error.message)}
	},

	async reassignLead({ commit }, payload) {
		try {
			commit(types.SET_REASSIGN_FIELDS, {organisation_id: payload.organisation_id} )
			const leadId = payload.lead_id ? payload.lead_id : state.lead.id;
			const { data } = await state.reassignForm.post(`/api/v1/leads/${leadId}/reassign`)
			commit(types.REASSIGN_LEAD)
			return data;
		} catch (error) {
			state.loading = false
			console.log(error.message)
		}
	},

	async fetchJobDetails({ commit }, { queryInfo, leadId }) {
		try {
	  state.loading = true

	  const params = {
		lead_id: leadId,
		pageNo: queryInfo.page ? queryInfo.page : 1,
		pageSize: queryInfo.pageSize ? queryInfo.pageSize : 10,
	  };

	  const { data } = await axios.get(`/api/v1/lead/jobs`, { params });
			commit(types.FETCH_JOB_DETAILS, { jobDetails: data.data.jobDetails, total: 0 })
		} catch (error) {console.log(error.message)}
	},

	editJobDetails ({ commit }, { data, form, close, leadId }) {

		try {
			if (data) {
				commit(types.EDIT_JOB_DETAILS, { jobDetail: data, id: data.id, leadId })

				return data;

			} else {
				commit(types.EDIT_JOB_DETAILS, { jobDetail: null, leadId })
			}

			commit(types.DIALOG_STATE, { form, close, data })

		} catch (error) {console.log(error.message)}
	},

	async saveJobDetails ({ commit }) {
		try {
			state.loading = true

			const saveURL = state.jobDetailsForm.id ? `/api/v1/lead/jobs/${state.jobDetailsForm.id}` : `/api/v1/lead/jobs`
			const { data } = await (state.jobDetailsForm.id ? state.jobDetailsForm.put(saveURL) : state.jobDetailsForm.post(saveURL))

			commit(types.SAVE_JOB_DETAILS, { jobDetail: data.data })
			return data;
		} catch (error) {console.log(error.message)}
	},

	async deleteJobDetails({ commit }, id) {
		try {
			const { data } = await axios.delete(`/api/v1/lead/jobs/${id}`)

			commit(types.DELETE_JOB_DETAILS, id)
			return data;

		} catch (error) {console.log(error.message)}
	},

	async saveSale ({ commit }) {
		try {
			const { data } = await state.saleForm.post(`/api/v1/leads/${state.lead.id}/save/sale`)
			commit(types.SAVE_SALE, { lead: data.data })
			return data;
		} catch (error) {console.log(error.message)}
	},

	async sendEnquirerMessage ({ commit }) {
		try {
			state.loading = true
			const { data } = await state.sendNotificationForm.post(`/api/v1/leads/${state.lead.id}/send_message`)
			commit(types.SEND_ENQUIRER_MESSAGE)
			commit(types.GET_ENQUIRER_NOTIFICATION, { notifications: data.data })

			return data;
		} catch (error) {console.log(error.message)}
	},

	async sendOrgMessage ({ commit }) {
		try {
			state.loading = true
			const { data } = await state.sendNotificationForm.post(`/api/v1/leads/${state.lead.id}/send_message`)
			commit(types.SEND_ENQUIRER_MESSAGE)
			commit(types.GET_LEAD_ORG_NOTIFICATION, { notifications: data.data })

			return data;
		} catch (error) {console.log(error.message)}
	},


	async sendOrgEnquirerDetails ({ commit }) {
		try {
			state.loading = true
			const { data } = await state.sendEnquirerDetailsForm.post(`/api/v1/leads/${state.lead.id}/send_enquirer_details`)
			commit(types.SEND_ENQUIRER_MESSAGE)
			commit(types.GET_LEAD_ORG_NOTIFICATION, { notifications: data.data })

			return data;
		} catch (error) {console.log(error.message)}
	},

	async setDialog({ commit }, { close, form }) {
		commit(types.DIALOG_STATE, { form, close })
	},

	async fetchLeadComments( {commit}, { leadId, queryInfo }){
		try{
		  var parameters = { "pageNo" : queryInfo.page, "pageSize" : queryInfo.pageSize }
		  const { data } = await axios.get(`/api/v1/leads/comments/${leadId}`, { params: parameters })
		  commit(types.FETCH_LEAD_COMMENTS, data.data)
		}
		catch(error) {console.log(error.message)}
	  },

  async resetLeadComment() {
		state.leadCommentForm.reset();
  },
	async addLeadComment ({commit}) {
		try {
			state.loading = true
			const { data } = await state.leadCommentForm.post(`/api/v1/leads/add_lead_comment/${state.lead.id}`)
			commit(types.ADD_LEAD_COMMENT, data.data)

			return data;
		} catch (error) {console.log(error.message)}
	},


	async editLeadComment ({commit}, { comment, form, close }) {
		try {
			commit(types.DIALOG_STATE, { form, close })
			commit(types.EDIT_LEAD_COMMENT, comment)

		} catch (error) {console.log(error.message)}
	},

	async deleteLeadComments({ commit }, id) {
		try {
			const { data } = await axios.post(`/api/v1/leads/delete_lead_comment/${id}`)

			commit(types.DELETE_LEAD_COMMENT, id)
			return data;
		} catch (error) {console.log(error.message)}
	},

	async saveMeters({ commit }) {
		try {
			const { data } = await state.metersForm.post(`/api/v1/leads/actual-estimations`)

			commit(types.SAVE_ESCALATION_METERS, { lead_escalation: data.data })
			return data;
		} catch (error) {console.log(error.message)}
	},

	// ASSIGN USER TO LEAD: ACTION
	async fetchUsers({ commit }) {
		try{
			state.loading = true;

			const { data } = await axios.get(`/api/v1/leads/fetchUsers`);
			commit(types.FETCH_USERS, data.data);

			return data;
		}
		catch(error){
			console.log(error.message)
		}
	},

	async assignUserToLead({ commit }) {
		try{
			// state.loading = true;
			const { data } = await state.assignUserToLeadForm.post(`/api/v1/leads/assign_user_to_lead/${state.lead.id}`);
			commit(types.ASSIGN_USER_TO_LEAD, data.data);

			return data;
		}
		catch(error){
			console.log(error.message)
		}
	},

	async fetchAssignedUsers({ commit }, id) {
		try{
			// state.loading = true;
			const { data } = await axios.get(`/api/v1/leads/fetch_assigned_users/${id}`);
			commit(types.FETCH_ASSIGNED_USERS, data.data);

			return data;
		}
		catch(error){
			console.log(error.message)
		}
	},

	async removeAssignedUser({ commit }, id) {
		try {
			commit(types.REMOVE_ASSIGNED_USER, id);
		} catch (error) {
			console.log(error.message)
		}
	},

	async addAssignedUser({ commit }, id) {
		try {
			commit(types.ADD_ASSIGNED_USER, id);
		} catch (error) {
			console.log(error.message)
		}
	},

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

	validateEnquirerDetails({ commit }, params){
		try {
			commit(types.SET_ENQUIRY_DETAILS_DYNAMIC_VALIDATION_RULE, params);
		} catch (error) {
			console.log(error.message)
		}
	},

	validateEnquirerDetails2({ commit }, type) {
		try {
			commit(types.SET_ENQUIRY_DETAILS_DYNAMIC_VALIDATION_RULE_2, type);
		} catch (error) {
			console.log(error.message)
		}
	}
}
