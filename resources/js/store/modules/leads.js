import axios from 'axios'
import * as types from '../mutation-types'
import Form from 'vform'
import { correctSource } from "~/helpers"

export const state = {
	leads: [],
  leads_needs_attention: [],
  leads_my_leads: [],
	paused_dates: [],
	is_paused: '',
	has_critical: '',
  noticeModal: false,
	org_data: {},
	total: 0,
	loading: false,
	settings: [],
	leadForm: new Form({
		'lead_id': '',

		'update_type': '',

		'customer_type': '',
		'escalation_level': '',
		'escalation_status': '',

		'first_name': '',
		'last_name': '',
		'address_search': '',
		'address': '',
		'streetNo': '',
		'streetName': '',
		'city': '',
		'suburb': '',
		'state': '',
		'postcode': '',
		'country_id': '',
		'country': 'Australia',
		'email': '',
		'contact_number': '',
		'comments': '',
		'inquiry_type': 'General Enquiry',
    'enquirer_send_sms': true,
    'enquirer_send_email': true,

		'house_type': '',
		'commercial': '',
		'commercial_other': '',
		'house_type': '',

		'roof_preference': '',
		'use_for': '',

		'gutter_edge_meters': '',
		'valley_meters': '',
		'source': '',
		'source_comments': '',
		'additional_information': '',
		'landline_number': '',

		'received_via': '',
		'staff_comments': '',
		'enquirer_message': 'Thank you for your Leaf Stopper Enquiry. If you do not receive a call from an installer within 72 hours, please call us on 1300 334 333 or email us at office@leafstopper.com.au',
		'notify_enquirer': true,

		'house_type_other': '',
		'roof_preference_other': '',
		'use_for_other': '',

		'organisation_id': '',
    'send_sms': true,
    'send_email': true,

		'created_at': '',
	}),
	orgLocatorForm: new Form({
		'org_postcode': '',
		'locator_kilometer_from_postcode': '',
		'locator_ytd_sales_greater_than': '',
		'locator_ly_sales_greater_than': '',
		'locator_priority': '',
		'locator_stock_kits': '',
    'locator_state': '',
	}),
	leadFilters: {
		level: '',
		organisations: '',
		postcode: ''
	},
	leadFormRules: {
		customer_type: [
			{ required: true, message: 'Please select lead type', trigger: 'blur' },
		],

		escalation_level: [
			{ required: true, message: 'Please select escalation level', trigger: 'blur' },
		],

		escalation_status: [
			{ required: true, message: 'Please select escalation status', trigger: 'blur' },
		],

		first_name: [
			{ required: true, message: 'Please input first name', trigger: 'blur' },
		],

		last_name: [
			{ required: true, message: 'Please input last name', trigger: 'blur' },
		],

		email: [
			{ required: false, message: 'Please input email address', trigger: 'blur' },
			{ type: 'email', message: 'Please provide vaild email address format', trigger: ['blur', 'change'] }
		],

		// address_search: [
		// 	{ required: true, message: 'Please input complete address (should have state, postcode, and country)', trigger: 'blur' },
		// ],

		suburb: [
			{ required: true, message: 'Please input suburb', trigger: 'blur' },
		],

		postcode: [
			{ required: true, message: 'Please input postcode', trigger: 'blur' },
		],

		state: [
			{ required: true, message: 'Please input state', trigger: 'blur' },
		],

		contact_number: [
			{ required: true, message: 'Please input contact number', trigger: 'blur' },
		],

		received_via: [
			{ required: true, message: 'Please select enquiry type', trigger: 'blur' },
    ],

    comments: [
			{ required: true, message: 'Comments is required', trigger: 'blur' },
    ],

	},
	stepFields: [
		{
			step_no: 0,
			fieldsRequired: [
        'customer_type', 'escalation_level', 'escalation_status', 'first_name', 'last_name', 'contact_number', 'suburb', 'state', 'postcode', 'comments', 'received_via'
			]
		},
		{
			step_no: 1,
			fieldsRequired: []
		},
		{
			step_no: 2,
			fieldsRequired: [
				//'received_via'
			]
    }

	],
	organisations: [],
	leadTypes: {
		'Supply & Install': {
			'Unassigned': [
				'Special Opportunity',
        'Lost'
			],
			'New Lead': [
				'Waiting',
				'Critical',
				'Declined',
			],
			'Contact': [
				'Waiting',
				'Prompt',
				'Critical',
				'Discontinued',
			],
			'In Progress': [
				'Waiting',
				'Prompt',
				'Critical',
				'Discontinued',
			],
			'Won': [],
			'Lost': [],
			'Lost - Inconclusive': [
			],
		},
		'Supply Only': {
			'Supply Only': [
				'New',
        'In Progress',
        'Finalized'
			],
		},
		'General Enquiry':{
			'General Enquiry': [
				'New',
        'In Progress',
        'Finalized'
			],
		}
	},
	receivedViaTypes: [
		'Email', 'Phone', 'Web Form', 'Walk-In'
	],
	statesList: [
		{'value': 'ACT', 'label': 'ACT'},
		{'value': 'NSW', 'label': 'NSW'},
		{'value': 'NT', 'label': 'NT'},
		{'value': 'QLD', 'label': 'QLD'},
		{'value': 'SA', 'label': 'SA'},
		{'value': 'TAS', 'label': 'TAS'},
		{'value': 'VIC', 'label': 'VIC'},
		{'value': 'WA', 'label': 'WA'},
	],
	isFilterSaved: true
}

export const getters = {
	leads: state => state.leads,
	paused_dates: state => state.paused_dates,
	is_paused: state => state.is_paused,
	has_critical: state => state.has_critical,
	org_data: state => state.org_data,
	total: state => state.total,
	loading: state => state.loading,
	leadForm: state => state.leadForm,
	leadFormRules: state => state.leadFormRules,
	leadTypes: state => state.leadTypes,
	organisations: state => state.organisations,
	receivedViaTypes: state => state.receivedViaTypes,
	orgLocatorForm: state => state.orgLocatorForm,
	stepFields: state => state.stepFields,
	statesList: state => state.statesList,
	isFilterSaved: state => state.isFilterSaved,
  settings: state => state.settings,
  noticeModal: state => state.noticeModal,
  leads_needs_attention: state => state.leads_needs_attention,
  leads_my_leads: state => state.leads_my_leads
}

export const mutations = {
	[types.FETCH_LEADS] (state, { leads, total, paused_dates, is_paused, has_critical, org_data }) {
		state.leads = leads.length ? leads : []
		state.paused_dates = paused_dates.length ? paused_dates : []
		state.is_paused = is_paused
		state.has_critical = has_critical
    state.noticeModal = has_critical
		state.org_data = org_data
		state.total = total ? total : 0
		state.loading = false;
	},

  [types.FETCH_LEADS_NEEDS_ATTENTION] (state, { leads, total, paused_dates, is_paused, has_critical, org_data }) {
		state.leads = leads.length ? leads : []
		state.paused_dates = paused_dates.length ? paused_dates : []
		state.is_paused = is_paused
		state.has_critical = has_critical
    state.noticeModal = has_critical
		state.org_data = org_data
		state.total = total ? total : 0
		state.loading = false;
	},

  [types.FETCH_LEADS_MY_LEADS] (state, { leads, total, paused_dates, is_paused, has_critical, org_data }) {
		state.leads = leads.length ? leads : []
		state.paused_dates = paused_dates.length ? paused_dates : []
		state.is_paused = is_paused
		state.has_critical = has_critical
    state.noticeModal = has_critical
		state.org_data = org_data
		state.total = total ? total : 0
		state.loading = false;
	},

	[types.FETCH_ORGANISATIONS] (state, data) {
		state.organisations = data.length ? data.sort((a, b) => a.name > b.name) : []
	},

    [types.EDIT_LEAD] (state, { leadEscalation, id }) {
		state.leadForm.reset();

		if ( leadEscalation ) {
			const enquirer_message = state.settings.find(s => s.key === 'admin-enquire-message')

			state.leadForm.fill(leadEscalation);
			state.leadForm.id = id // lead escalation id
			state.leadForm.lead_id = leadEscalation.lead_id // lead id

			state.leadForm.customer_type = leadEscalation.lead.customer_type
			state.leadForm.first_name = leadEscalation.lead.customer.first_name
			state.leadForm.last_name = leadEscalation.lead.customer.last_name
			state.leadForm.address = leadEscalation.lead.customer.address.address
			state.leadForm.city = leadEscalation.lead.customer.address.city
			state.leadForm.suburb = leadEscalation.lead.customer.address.suburb
			state.leadForm.state = leadEscalation.lead.customer.address.state
			state.leadForm.postcode = leadEscalation.lead.customer.address.postcode
			state.leadForm.country = leadEscalation.lead.customer.address.country.name
			state.leadForm.email = leadEscalation.lead.customer.email
			state.leadForm.contact_number = leadEscalation.lead.customer.contact_number ?? ''
			state.leadForm.comments = leadEscalation.lead.comments ?? ''
			state.leadForm.inquiry_type = leadEscalation.lead.metadata && leadEscalation.lead.metadata['inquiry_type'] || 'general-enquiry'

			state.leadForm.house_type = leadEscalation.lead.house_type
			state.leadForm.commercial = leadEscalation.lead.commercial
			state.leadForm.commercial_other = leadEscalation.lead.commercial_other
			state.leadForm.roof_preference = leadEscalation.lead.roof_preference
			state.leadForm.use_for = leadEscalation.lead.use_for
			state.leadForm.gutter_edge_meters = leadEscalation.lead.gutter_edge_meters
			state.leadForm.valley_meters = leadEscalation.lead.valley_meters
			state.leadForm.source = correctSource(leadEscalation.lead.source)
			state.leadForm.source_comments = leadEscalation.lead.source_comments
			state.leadForm.additional_information = leadEscalation.lead.metadata && leadEscalation.lead.metadata['additional_information'] || ''
			state.leadForm.landline_number = leadEscalation.lead.metadata && leadEscalation.lead.metadata['landline_number'] || ''

			state.leadForm.received_via = leadEscalation.lead.received_via
			state.leadForm.staff_comments = leadEscalation.lead.staff_comments
			state.leadForm.enquirer_message = ( leadEscalation.lead.enquirer_message ) ? leadEscalation.lead.enquirer_message : ( enquirer_message.value ) ? enquirer_message.value : 'Thank you for your Leaf Stopper Enquiry. If you do not receive a call from an installer within 72 hours, please call us on 1300 334 333 or email us at office@leafstopper.com.au'
			state.leadForm.house_type = leadEscalation.lead.house_type
			state.leadForm.notify_enquirer = leadEscalation.lead.notify_enquirer == 1

			state.leadForm.address_search = leadEscalation.lead.metadata && leadEscalation.lead.metadata['address_search'] || ''
			state.leadForm.house_type_other = leadEscalation.lead.metadata && leadEscalation.lead.metadata['house_type_other'] || ''
			state.leadForm.roof_preference_other = leadEscalation.lead.metadata && leadEscalation.lead.metadata['roof_preference_other'] || ''
			state.leadForm.use_for_other = leadEscalation.lead.metadata && leadEscalation.lead.metadata['use_for_other'] || ''
      state.leadForm.organisation_id = leadEscalation.organisation_id
      state.leadForm.created_at = leadEscalation.lead.created_at
      state.leadForm.enquirer_send_email = true
      state.leadForm.enquirer_send_sms = true
      state.leadForm.send_email = true
      state.leadForm.send_sms = true
		}

    },

    [types.SAVE_LEAD] (state, { lead }) {
		const index = state.leads.findIndex(l => l.lead_id === lead.id);

        if ( index !== -1) {
            state.leads.splice(index, 1, lead);
        } else {
            //update leadForm during creating
            state.leadForm.lead_id = lead.lead_escalation.lead_id
            state.leads.push(lead);
		}

		state.loading = false
	},

	[types.UPDATE_LEAD] (state, { lead, originalLeadId }) {
		const index = state.leads.findIndex(l => l.lead_id === originalLeadId);

        if ( index !== -1) {
            state.leads.splice(index, 1, lead);
        } else {
            state.leads.push(lead);
		}

		state.loading = false
	},

  [types.CLOSE_NOTICE_MODAL] (state){
    state.noticeModal = false
  },

	[types.SET_ORG_ID] (state, { newOrgId }) {
		state.leadForm.organisation_id = newOrgId
	},

	[types.DELETE_LEAD] (state, id) {
		const index = state.leads.findIndex(lead => lead.lead_id === id);

		if ( index !== -1) {
			state.leads.splice(index, 1);
		}
    },

	[types.FETCH_SETTINGS] (state, { settings }) {
        state.settings = settings.length ? settings : []
        if ( settings ) {
            const enquirer_message = settings.find(s => s.key === 'admin-enquire-message')

            state.leadForm.enquirer_message = enquirer_message && enquirer_message.value ? enquirer_message.value : 'Thank you for your Leaf Stopper Enquiry. If you do not receive a call from an installer within 72 hours, please call us on 1300 334 333 or email us at office@leafstopper.com.au'
        }
    },
}

export const actions = {

  async closeNoticeModal ({ commit }){
    commit(types.CLOSE_NOTICE_MODAL)
  },

  async validateName({ commit }) {
		try {
			const { data } = await state.leadForm.post(`/api/v1/leads/name-validate`)
      return data;
		} catch (error) {console.log(error.message)}
	},


	async fetchLeads({ commit }, queryInfo) {
		try {
      state.loading = true

			const params = {
				pageNo: queryInfo.page ? queryInfo.page : 1,
				pageSize: queryInfo.pageSize ? queryInfo.pageSize : 20,
				escalation_level: (queryInfo.filters != null) ? ((queryInfo.filters[0].value != null) ? queryInfo.filters[0].value : "") : "",
				organisation_id: (queryInfo.filters != null) ? ((queryInfo.filters[1].value != null) ? queryInfo.filters[1].value : "") : "",
				postcode: (queryInfo.filters != null) ? ((queryInfo.filters[2].value != null) ? queryInfo.filters[2].value : "") : "",
				search: (queryInfo.filters != null) ? ((queryInfo.filters[3].value != null) ? queryInfo.filters[3].value : "") : "",
				lead_escalation_status: (queryInfo.filters != null) ? ((queryInfo.filters[4].value != null) ? queryInfo.filters[4].value : "") : "",
				lead_type: (queryInfo.filters != null) ? ((queryInfo.filters[5].value != null) ? queryInfo.filters[5].value : "") : "",
        sorted_by: (queryInfo.filters != null) ? ((queryInfo.filters[6].value != null) ? queryInfo.filters[6].value : "") : "",
        lead_group: (queryInfo.filters != null) ? ((queryInfo.filters[7].value != null) ? queryInfo.filters[7].value : 0) : 0,
			};

			const { data } = await axios.get(`/api/v1/leads`, { params });
			commit(types.FETCH_LEADS, data.data)
		} catch (error) {console.log(error.message)}
	},

  async fetchLeadsNeedsAttention({ commit }, queryInfo) {
		try {
      state.loading = true

			const params = {
				pageNo: queryInfo.page ? queryInfo.page : 1,
				pageSize: queryInfo.pageSize ? queryInfo.pageSize : 20,
				escalation_level: (queryInfo.filters != null) ? ((queryInfo.filters[0].value != null) ? queryInfo.filters[0].value : "") : "",
				organisation_id: (queryInfo.filters != null) ? ((queryInfo.filters[1].value != null) ? queryInfo.filters[1].value : "") : "",
				postcode: (queryInfo.filters != null) ? ((queryInfo.filters[2].value != null) ? queryInfo.filters[2].value : "") : "",
				search: (queryInfo.filters != null) ? ((queryInfo.filters[3].value != null) ? queryInfo.filters[3].value : "") : "",
				lead_escalation_status: (queryInfo.filters != null) ? ((queryInfo.filters[4].value != null) ? queryInfo.filters[4].value : "") : "",
				lead_type: (queryInfo.filters != null) ? ((queryInfo.filters[5].value != null) ? queryInfo.filters[5].value : "") : "",
        sorted_by: (queryInfo.filters != null) ? ((queryInfo.filters[6].value != null) ? queryInfo.filters[6].value : "") : "",
        lead_group: 1
			};

			const { data } = await axios.get(`/api/v1/leads`, { params });
			commit(types.FETCH_LEADS_NEEDS_ATTENTION, data.data)
		} catch (error) {console.log(error.message)}
	},

  async fetchLeadsMyLeads({ commit }, queryInfo) {
		try {
      state.loading = true

			const params = {
				pageNo: queryInfo.page ? queryInfo.page : 1,
				pageSize: queryInfo.pageSize ? queryInfo.pageSize : 20,
				escalation_level: (queryInfo.filters != null) ? ((queryInfo.filters[0].value != null) ? queryInfo.filters[0].value : "") : "",
				organisation_id: (queryInfo.filters != null) ? ((queryInfo.filters[1].value != null) ? queryInfo.filters[1].value : "") : "",
				postcode: (queryInfo.filters != null) ? ((queryInfo.filters[2].value != null) ? queryInfo.filters[2].value : "") : "",
				search: (queryInfo.filters != null) ? ((queryInfo.filters[3].value != null) ? queryInfo.filters[3].value : "") : "",
				lead_escalation_status: (queryInfo.filters != null) ? ((queryInfo.filters[4].value != null) ? queryInfo.filters[4].value : "") : "",
				lead_type: (queryInfo.filters != null) ? ((queryInfo.filters[5].value != null) ? queryInfo.filters[5].value : "") : "",
        sorted_by: (queryInfo.filters != null) ? ((queryInfo.filters[6].value != null) ? queryInfo.filters[6].value : "") : "",
        lead_group: 2
			};

			const { data } = await axios.get(`/api/v1/leads`, { params });
			commit(types.FETCH_LEADS_MY_LEADS, data.data)
		} catch (error) {console.log(error.message)}
	},

	async fetchOrgAll({ commit }) {
		try {
			const { data } = await axios.get(`/api/v1/organizations/all`);
			commit(types.FETCH_ORGANISATIONS, data.data)
		} catch (error) {console.log(error.message)}
	},

	async addLead({ commit }) {
		try {
			commit(types.EDIT_LEAD, {})
		} catch (error) {console.log(error.message)}
	},

	async editLead({ commit }, id) {
		try {
			const { data } = await axios.get(`/api/v1/leads/${id}`)
			commit(types.EDIT_LEAD, { leadEscalation: data.data, id })
      		return data;
		} catch (error) {console.log(error.message)}
	},

	async saveLead({ commit }) {
		try {
			state.loading = true
			const saveURL = state.leadForm.id ? `/api/v1/leads/${state.leadForm.lead_id}` : `/api/v1/leads`
			const { data } = await (state.leadForm.id ? state.leadForm.put(saveURL) : state.leadForm.post(saveURL))
			commit(types.SAVE_LEAD, { lead: data.data })
      		return data;
		} catch (error) {
			state.loading = false
			return state.leadForm.errors
		}
	},

	async updateLeads({ commit }, { lead, originalLeadId }) {
		try {
			commit(types.UPDATE_LEAD, { lead, originalLeadId })
		} catch (error) {console.log(error.message)}
	},

	async deleteLead({ commit }, id) {
		try {
			const { data } = await axios.delete(`/api/v1/leads/${id}`)

			commit(types.DELETE_LEAD, id)
			return data;

		} catch (error) {console.log(error.message)}
	},

	async setOrgId({ commit }, newOrgId) {
		try {
			commit(types.SET_ORG_ID, { newOrgId })
		} catch (error) {console.log(error.message)}
	},

	async fetchSettings({ commit }, queryInfo) {
		try {
            const params = {
                pageNo: queryInfo.page ? queryInfo.page : 1,
                pageSize: queryInfo.pageSize ? queryInfo.pageSize : 10,
                search: queryInfo.filters[0] ? queryInfo.filters[0].value : "",
            };

            const { data } = await axios.get(`/api/v1/admin/setting`, { params });
            commit(types.FETCH_SETTINGS, {
                settings: data.data.settings,
            })
		} catch (error) {console.log(error.message)}
    },

	async updateStateLead({ commit }, event) {
		try {
			state.leads[event.index].lead.user_ids = event.ids
		} catch ( error ) {
			console.log( error.message )
		}
	}
}
