import axios from 'axios'
import * as types from '../mutation-types'
import Form from 'vform'
import { getLeadEscalationNotificationSelection } from '~/helpers';
import moment, { now } from 'moment';

export const state = {
	organisation: null,
  organisationLoading: true,
  orgLocation: null,
	organisations: [],
	inactiveOrganisations: [],
	total: 0,
	inactiveTotal: 0,
	loading: true,
  loadingSentComment: false,
  orgPostcodes: [],
	orgPostcodesLoading: true,
  orgPostcodesTotal: 0,
  systemActivitiesLoading: true,
  systemActivitiesTotal: 0,
	systemActitivies: [],
	orgForm: new Form({
		first_name: "",
		last_name: "",
		name: "",
		org_code: "",
		contact_number: "",
		landline_contact: "",
		email: "",
		password: "",
		address_search: '',
		address: '',
		city: '',
		state: '',
		suburb: '',
		postcode: '',
		country: '',
		has_active_lead: true,
		is_suspended: false,
    low_priority: false,
    suspension_type: 'System',
		org_status: true,
		send_org_notification: false,
		manual_update: false,
    notificationsSelection: 'both',
    notifications: ['sms', 'email'],
    is_available: true,
    available_when: '',
    reason: '',
    pricing: "",
    priority: "",
    on_hold: false,
    priority: 'None',
    price: '',
		additional_details: "",
	}),
  orgFormAvailability: new Form({
    org_id: '',
    is_available: true,
    available_when: '',
    reason: '',
  }),
  orgFormAvailabilityRules:{
    available_when: [
      { required: true, message: 'Please select date', trigger: ['blur', 'change'] }
    ],
    reason: [
      { required: true, message: 'Please select reason', trigger: ['blur', 'change'] }
    ]
  },
	orgFormRules: {
		name: [
			{ required: true, message: 'Please input organisation name', trigger: 'blur' },
		],
		org_code: [
			{ required: true, message: 'Please input organisation code', trigger: 'blur' },
		],
		contact_number: [
			{ required: true, message: 'Please input contact number', trigger: 'blur' },
		],
		first_name: [
		  { required: true, message: 'Please input first name', trigger: 'blur' },
		],
		last_name: [
		  { required: true, message: 'Please input last name', trigger: 'blur' },
		],
		email: [
		  { required: true, message: 'Please input email address', trigger: 'blur' },
		  { type: 'email', trigger: ['blur', 'change'] }
    ],
		pricing: [
		  { required: true, message: 'Please input pricing', trigger: 'blur' },
    ],
		priority: [
		  { required: true, message: 'Please select priority', trigger: [ 'blur', 'change' ] },
    ],
	},
	orgCommentLoading: true,
	orgComments: [],
	orgCommentsTotal: 0,

	orgCommentForm:  new Form({
		org_comment_id: "",
		comment: "",
		organisation_id: ""
	}),
	orgCommentRules: {
		comment: [
			{ required: true, message: 'Please input a comment', trigger: 'blur' },
		],
	},
	addOrgCommentDialogVisible: false,
	countries: [],
	logs: [],
	log_count: 0,
	postcodes: [],
	filter_organizations: [],
  current_leads: [],
  reassigned_leads: [],

  loading_current_leads: true,
  currentLeadsTotal: 0,
  showOrganisationProfile: false,
  showOrganisationProfileId: 0,
  leadStats: []
}

export const getters = {
  orgPostcodes: state => state.orgPostcodes,
  orgPostcodesLoading: state => state.orgPostcodesLoading,
  orgPostcodesTotal: state => state.orgPostcodesTotal,
  systemActitivies: state => state.systemActitivies,
  systemActivitiesLoading: state => state.systemActivitiesLoading,
  systemActivitiesTotal: state => state.systemActivitiesTotal,
	organisation: state => state.organisation,
  organisationLoading: state => state.organisationLoading,
	orgLocation: state => state.orgLocation,
	organisations: state => state.organisations,
	inactiveOrganisations: state => state.inactiveOrganisations,
	loading: state => state.loading,
	total: state => state.total,
	inactiveTotal: state => state.inactiveTotal,
	orgForm: state => state.orgForm,
	orgFormRules: state => state.orgFormRules,
	orgCommentForm: state => state.orgCommentForm,
	orgCommentRules: state => state.orgCommentRules,
	orgComments: state => state.orgComments,
	addOrgCommentDialogVisible: state => state.addOrgCommentDialogVisible,
	orgCommentsTotal: state => state.orgCommentsTotal,
	orgCommentLoading: state => state.orgCommentLoading,
	countries: state => state.countries,
	logs: state => state.logs,
	log_count: state => state.log_count,
	postcodes: state => state.postcodes,
	filter_organizations: state => state.filter_organizations,
  current_leads: state => state.current_leads,
  loading_current_leads: state => state.loading_current_leads,
  orgFormAvailabilityRules: state => state.orgFormAvailabilityRules,
  orgFormAvailability: state => state.orgFormAvailability,
  reassigned_leads: state  => state.reassigned_leads,
  loadingSentComment: state => state.loadingSentComment,
  currentLeadsTotal: state => state.currentLeadsTotal,
  showOrganisationProfile: state => state.showOrganisationProfile,
  showOrganisationProfileId: state => state.showOrganisationProfileId,
  leadStats: state => state.leadStats,
}

export const mutations = {
  [types.GET_REASSIGNED_LEADS] (state, {data}){
    state.reassigned_leads = data
    state.loading_current_leads = false
  },

  [types.GET_CURRENT_LEADS] (state, {data, total}){
      state.currentLeadsTotal = total
      state.current_leads = data
      state.loading_current_leads = false
  },

	[types.FETCH_ORGANISATION] ( state, { data, location, systemActitivies } ) {
		state.organisation = data
    state.orgLocation = location
    state.systemActitivies = systemActitivies
    state.organisationLoading = false
    state.systemActivitiesLoading = false

    state.orgFormAvailability.org_id = data.id
    state.orgFormAvailability.is_available = (data.is_available == 1) ? true : false
    state.orgFormAvailability.available_when = data.available_when
    state.orgFormAvailability.reason = data.reason
	},

	[types.FETCH_ORGANISATIONS] (state, { org, org_status }) {
		if (  org_status == 'active' ) {
			state.organisations = org.organisations.length ? org.organisations : []
		}

		if ( org_status == 'inactive' ) {
			state.inactiveOrganisations = org.organisations.length ? org.organisations : []
		}

		state.total = org.total ? org.total : 0
		state.loading = false;
	},

	[types.FETCH_COUNTRIES] (state, { countries }) {
		state.countries = countries
	},

	[types.ADD_ORG_COMMENT] (state, comment) {
        state.loading = false
        state.loadingSentComment = false
		state.addOrgCommentDialogVisible = false
		state.orgCommentForm.reset();

		const index = state.orgComments.findIndex(c => c.id === comment.id);

        if ( index !== -1) {
            state.orgComments.splice(index, 1, comment);
        } else {
            state.orgComments.unshift(comment);
		}
	},

	[types.EDIT_ORG_COMMENT] (state, comment) {
		state.orgCommentForm.fill(comment)
		state.orgCommentForm.org_comment_id = comment.id
	},

	[types.DELETE_ORG_COMMENT] (state, id) {
		const index = state.orgComments.findIndex(comment => comment.id === id);

		if ( index !== -1) {
			state.orgComments.splice(index, 1);
		}
  	},

	[types.DIALOG_STATE] (state, { form, close }) {
        if (form == 'add_organisation_comment')
			state.addOrgCommentDialogVisible = close
			state.orgCommentForm.reset()
		},

	// edit users
    [types.EDIT_ORGANISATION] (state, { organisation, id }) {
		state.orgForm.reset();

		if ( organisation ) {
			state.orgForm.fill(organisation);
			state.orgForm.id = organisation.id
			state.orgForm.first_name = organisation.user.first_name
			state.orgForm.last_name = organisation.user.last_name
			state.orgForm.email = organisation.user.email
			state.orgForm.address = organisation.address.address
			state.orgForm.city = organisation.address.city
			state.orgForm.state = organisation.address.state
			state.orgForm.suburb = organisation.address.suburb
			state.orgForm.postcode = organisation.address.postcode
			state.orgForm.country = organisation.address.country.name
			state.orgForm.is_suspended = organisation.is_suspended == '1'
      state.orgForm.suspension_type = organisation.metadata['suspension_type'] ?? 'System'
      state.orgForm.low_priority = organisation.metadata['low_priority'] ?? ''
			state.orgForm.org_status = organisation.org_status == '1'
			state.orgForm.notifications = organisation.notifications ?? ['sms', 'notification']
      state.orgForm.notificationsSelection = getLeadEscalationNotificationSelection(state.orgForm.notifications)
			state.orgForm.address_search = organisation.metadata['address_search'];
			state.orgForm.manual_update = organisation.metadata['manual_update'] ?? false;
			state.orgForm.pricing = organisation.metadata['pricing'] ?? '';
			state.orgForm.priority = organisation.metadata['priority'] ?? '';
			state.orgForm.has_active_lead = organisation.has_active_lead
      state.orgForm.is_available = organisation.is_available
      state.orgForm.available_when = organisation.available_when
      state.orgForm.reason = organisation.reason
      state.orgForm.on_hold = (organisation.on_hold == 1) ? false : true
		}

		state.orgFormRules.password = id ? [] : [
			{ required: true, message: 'Please input password', trigger: 'blur' },
		]
    },

	// edit users
  [types.SAVE_ORGANISATION] (state, { organisation }) {
		const index = state.organisations.findIndex(org => org.id === organisation.id);

    if ( index !== -1) {
      state.organisations.splice(index, 1, organisation);
    } else {
      state.organisations.push(organisation);
    }

    state.orgFormAvailability.org_id = organisation.id
    state.orgFormAvailability.is_available = (organisation.is_available == 1) ? true : false
    state.orgFormAvailability.available_when = organisation.available_when
    state.orgFormAvailability.reason = organisation.reason

    state.organisation = organisation

    state.loading = false
	},

  [types.UPDATE_AVAILABILITY] (state, { organisation }){
    state.orgFormAvailability.org_id = organisation.id
    state.orgFormAvailability.is_available = (organisation.is_available == 1) ? true : false
    state.orgFormAvailability.available_when = organisation.available_when
    state.orgFormAvailability.reason = organisation.reason
  },

	[types.DELETE_ORGANISATION] (state, id) {
		const index = state.organisations.findIndex(org => org.id === id);

		if ( index !== -1) {
			state.organisations.splice(index, 1);
		}
  },

  [types.FETCH_POSTCODE_IMPORT] (state, {data, log_count}){
    state.loading = false
    state.logs = data ? data : []
    state.log_count = log_count ? log_count : 0
  },

  [types.FETCH_ORG_COMMENTS] (state, { comments, total }) {
	state.orgComments = comments.length ? comments : []
	state.orgCommentsTotal = total ? total : 0
	state.orgCommentLoading = false;
},


  [types.FETCH_ORGANISATION_POSTCODES] (state, {data}){
    state.postcodes = data
  },

  [types.FETCH_FILTER_ORGANISATION] (state, {data}){
    state.filter_organizations = data
  },

  [types.FETCH_ORG_POSTCODES](state, { data, parameters }) {
    state.orgPostcodesLoading = false
    state.orgPostcodesTotal = data.length
    state.orgPostcodes = data
  },

  [types.FILTER_SYSTEM_ACTIVITIES]( state, parameters ) {
    state.systemActivitiesLoading = true
    let systemActivities = state.systemActitivies
    state.systemActitivies = []
    let systemActivities_pagination = systemActivities.slice((parameters.pageNo - 1) * parameters.pageSize, parameters.pageNo * parameters.pageSize)

    state.systemActivitiesLoading = false
    state.systemActivitiesTotal = systemActivities.length
    state.systemActivities = systemActivities
  },

  [types.ORGANISATION_OVERVIEW] (state, {show, org_id}){
    state.showOrganisationProfile = show
    state.showOrganisationProfileId = org_id
  },

  [types.GET_ORGANISATION_LEAD_STATS] (state, {data}){
    state.leadStats = data
  },
}

export const actions = {
  async getLeadStats( {commit}, org_id){
    try{
      console.log('getLeadStats')
      const { data } = await axios.get(`/api/v1/organizations/leadStats/${org_id}`)
      commit(types.GET_ORGANISATION_LEAD_STATS, {data: data.data})
    }catch(e){ console.log(e.message) }
  },

  async openOrganisationOverview( {commit}, org_id){
    commit(types.ORGANISATION_OVERVIEW, {show: true, org_id: org_id })
  },

  async closeOrganisationOverview( {commit}){
    commit(types.ORGANISATION_OVERVIEW, {show: false, org_id: 0 })
  },

  async fetchReassignedLeads( {commit}, id ){
    try{
      state.loading_current_leads = true
      const { data } = await axios.get(`/api/v1/organisation/${id}/leads/reassigned`)
      commit(types.GET_REASSIGNED_LEADS, {data: data.data})
    }catch(e){ console.log(e.message) }
  },

  async updateStatus( {commit} ){
    try{
      const { data } = await axios.get(`/api/v1/organisations/status/update`)
    }
    catch(e){ console.log(e.message) }
  },

  async fetchCurrentLeads( {commit}, { id, queryInfo }){
    try{
      state.loading_current_leads = true

      const params = {
				pageNo: queryInfo.page ? queryInfo.page : 1,
				pageSize: queryInfo.pageSize ? queryInfo.pageSize : 100,
			};

      const { data } = await axios.get(`/api/v1/organizations/assigned/leads/${id}`, { params })
      commit(types.GET_CURRENT_LEADS, {data: data.data.leads, total: data.data.total })
    }
    catch(e){ console.log(e.message) }
  },

  async fetchFilterOrganizations( {commit}, ids ){
    try{

      let params = { ids: ids }
      const { data } = await axios.get(`/api/v1/organizations/ids`, { params })
      commit(types.FETCH_FILTER_ORGANISATION, { data: data.data })
    }
    catch(e){ console.log(e.message) }
  },

	async fetchOrganisationPostcodes( {commit}){
		try{
			const { data } = await axios.get(`/api/v1/organizations/postcodes`)
			commit(types.FETCH_ORGANISATION_POSTCODES, { data: data.data })
		}
		catch(e){ console.log(error.message) }
	},

	async fetchOrganisations({ commit }, queryInfo) {
		try {
      state.loading = true
			const params = {
				pageNo: queryInfo.page ? queryInfo.page : 1,
				pageSize: queryInfo.pageSize ? queryInfo.pageSize : 100,
				search: queryInfo.filters[0] ? queryInfo.filters[0].value : "",
				org_postcode: queryInfo.filters[1] ? queryInfo.filters[1].value : "",
				org_state: queryInfo.filters[2] ? queryInfo.filters[2].value : "",
				org_suspended: queryInfo.filters[3] ? queryInfo.filters[3].value : "",
				org_status: 'active',
				org_type: queryInfo.filters[4] ? queryInfo.filters[4].value : "",
			};

			const { data } = await axios.get(`/api/v1/organizations`, { params });

			commit(types.FETCH_ORGANISATIONS, { 'org': data.data, 'org_status': 'active' })

		} catch (error) {console.log(error.message)}
	},

	async fetchInactiveOrganisations({ commit }, queryInfo) {
		try {
			const params = {
				pageNo: queryInfo.page ? queryInfo.page : 1,
				pageSize: queryInfo.pageSize ? queryInfo.pageSize : 100,
				search: queryInfo.filters[0] ? queryInfo.filters[0].value : "",
				org_postcode: queryInfo.filters[1] ? queryInfo.filters[1].value : "",
				org_state: queryInfo.filters[2] ? queryInfo.filters[2].value : "",
				org_suspended: queryInfo.filters[3] ? queryInfo.filters[3].value : "",
				org_status: 'inactive',
				org_type: queryInfo.filters[4] ? queryInfo.filters[4].value : "",
			};

			const { data } = await axios.get(`/api/v1/organizations`, { params });

			commit(types.FETCH_ORGANISATIONS, { 'org': data.data, 'org_status': 'inactive' })

		} catch (error) {console.log(error.message)}
	},

	async fetchStates({ commit }) {
		try {
			const { data } = await axios.get('/api/v1/organizations/countries')
		  	commit(types.FETCH_COUNTRIES, { countries: data.data })
		} catch (error) {console.log(error.message)}
	},

	async addOrganisation({ commit }) {
		try {
			commit(types.EDIT_ORGANISATION, {})
		} catch (error) {console.log(error.message)}
	},

	async editOrganisation({ commit }, id) {
		try {
			const { data } = await axios.get(`/api/v1/organizations/${id}`)
		  	commit(types.EDIT_ORGANISATION, { organisation: data.data, id })
      		return data;
		} catch (error) {console.log(error.message)}
	},

	async saveOrganisation({ commit }) {
		try {
			state.loading = true
			const saveURL = state.orgForm.id ? `/api/v1/organizations/${state.orgForm.id}` : `/api/v1/organizations`
			const { data } = await (state.orgForm.id ? state.orgForm.put(saveURL) : state.orgForm.post(saveURL))
			commit(types.SAVE_ORGANISATION, { organisation: data.data })

      return data;
		} catch (error) {
			return state.orgForm.errors
		}
	},

  async saveOrganisationAvailabilityStatus( {commit} ){
    try{
      const {data} = await state.orgFormAvailability.post(`/api/v1/organisations/availability/status`)
      commit(types.UPDATE_AVAILABILITY, { organisation: data.data })
      return data
    }catch(e){
      return state.orgFormAvailability.errors
    }
  },

	async deleteOrganisation({ commit }, id) {
		try {
			const { data } = await axios.delete(`/api/v1/organizations/${id}`)

			commit(types.DELETE_ORGANISATION, id)
			return data;

		} catch (error) {console.log(error.message)}
  },

  async deleteOrganisations({ commit }, ids) {
		try {
			const { data } = await axios.post(`/api/v1/organizations/delete`, { ids: ids })

			return data;

		} catch (error) {console.log(error.message)}
  },

  async fetchImportPostCodeLogs( {commit}, query){
    try{
      var parameters = { "pageNo" : query.page, "pageSize" : query.pageSize }

      const {data} = await axios.get(`/api/v1/organisation/postcode/logs`, { params: parameters })
      commit(types.FETCH_POSTCODE_IMPORT, {data: data.data.data, log_count: data.data.count })
    }
    catch(error) {console.log(error.message)}
  },

  async fetchOrganizationsLogs( {commit}, query){
    try{
      var params = { "pageNo": query.page, "pageSize" : query.pageSize }

      const {data} = await axios.get(`/api/v1/organisations/logs`, {params: params})
      commit(types.FETCH_POSTCODE_IMPORT, {data: data.data.data, log_count: data.data.total })
    }
    catch(e){console.log(e.message)}
  },

  async deleteLogs( {commit}, ids){
    try{
      var parameters = { "ids" : ids }
      const {data} = await axios.post(`/api/v1/organisation/postcode/deleteLogs`, { "ids" : ids })

      return data
    }
    catch(e){console.log(e.message)}
  },

  async export( {commit}, ids){
    try{
      const { data } = await axios.post(`/api/v1/organizations/export`, { ids: ids }, {responseType: 'blob'})
      return data
    }
    catch(e){
      console.log(e.message)
    }
  },

  async fetchOrgComments( {commit}, { orgId, queryInfo }){
    try{
	  var parameters = { "pageNo" : queryInfo.page, "pageSize" : queryInfo.pageSize }
    var orgid = queryInfo.orgId ?? orgId

	  const { data } = await axios.get(`/api/v1/organisations/comments/${orgid}`, { params: parameters })
      commit(types.FETCH_ORG_COMMENTS, data.data)
    }
    catch(error) {console.log(error.message)}
  },

  async setDialog({ commit }, { close, form }) {
	commit(types.DIALOG_STATE, { form, close })
	},

	async addOrgComment ({commit}) {
		try {
			state.loadingSentComment = true
			const { data } = await state.orgCommentForm.post(`/api/v1/organisations/add_org_comment/${state.orgForm.id}`)
			commit(types.ADD_ORG_COMMENT, data.data)

			return data;
		} catch (error) {console.log(error.message)}
	},


	async editOrgComment ({commit}, { comment, form, close }) {
		try {
			commit(types.DIALOG_STATE, { form, close })
			commit(types.EDIT_ORG_COMMENT, comment)

		} catch (error) {console.log(error.message)}
	},

	async deleteOrgComments({ commit }, id) {
		try {
			const { data } = await axios.post(`/api/v1/organizations/delete_org_comment/${id}`)

			commit(types.DELETE_ORG_COMMENT, id)
			return data;
		} catch (error) {console.log(error.message)}
	},

	async getOrganisation( { commit }, { id, load } ) {
		try {
      state.organisationLoading = load
			const { data } = await axios.get(`/api/v1/organizations/${id}`)
			commit(types.FETCH_ORGANISATION, { data: data.data, location: data.location, systemActitivies: data.systemActivity } )

			return data
		} catch (error) { console.log( error.message ) }
	},

	getOrgSystemActivities({ commit }) {
		try {
			commit( types.FETCH_ORG_SYSTEM_ACTIVITY )
		} catch ( error ) {
			console.log( error.message )
		}
	},

	async fetchOrgPostcodes( { commit }, org_id ) {
		try {
			state.orgPostcodesLoading = true
			//let parameters = { "pageNo" : queryInfo.page, "pageSize" : queryInfo.pageSize }
			const { data } = await axios.get( `/api/v1/organizations/postcodes/get/${org_id}` )

			commit( types.FETCH_ORG_POSTCODES, { data: data.data } )

			return data
		} catch ( error ) {
			console.log( error.message )
		}
	},

  async sendLeadUpdate( { commit }, org_id){
    try{
      const { data } = await axios.post(`/api/v1/organizations/lead/update/${org_id}`)
      return data
    }
    catch(error){ console.log( error.message ) }
  },

  filterSystemActivities({ commit }, queryInfo ) {
		try {
			let parameters = { "pageNo" : queryInfo.page, "pageSize" : queryInfo.pageSize }
			commit( types.FILTER_SYSTEM_ACTIVITIES, parameters )
		} catch ( error ) {
			console.log( error.message )
		}
	}
}
