import * as types from '../mutation-types'
import axios from 'axios'
import Form from 'vform'

// state
export const state = {
    selectedLead: null,
    confirmationLeadDetails: null,
    confirmationMessage: '',
    escalationVisible: false,
    loading: false,
    showCallEnquirer: false,
    iconForm: new Form({
        contact_number: '',
        email: '',
        message: '',
        type: ''
    }),
    acceptForm: new Form({
        lead_id: '',
        reason: '',
        comments: '',
        escalation_level: 'Confirm Enquirer Contacted',
        escalation_status: 'Awaiting Response',
    }),
    declineForm: new Form({
        lead_id: '',
        reason: '',
        escalation_level: 'Accept Or Decline',
        escalation_status: 'Declined',

        indicate_reason: '',
    }),
    declineFormRules:{
      reason: [
        { required: true, message: 'Please select reason', trigger: ['change', 'blur'] },
      ],
      indicate_reason: [
        { required: true, message: 'Please provide a reason. Thank you.', trigger: ['change', 'blur'] },
      ],
    },
    iconFormRules: {
        message: [
            { required: true, message: 'Please enter message', trigger: [ 'change', 'blur' ] }
        ]
    },
    cecForm: new Form({
        lead_id: '',
        responseIndex: '',
        update_type: '',
        response: '', // under meta
        reason: '',
        comments: '',
        gutter_edge_meters: '',
        valley_meters: '',
        progress_period_date: '',
        installation_date: '',

        escalation_level: 'In Progress',
        escalation_status: 'Awaiting Response',

        tried_to_contact_date: '',

        // under meta
        other_reason: '',
        indicate_reason: '',
        what_system: '',
        selected_time: '14:00'
    }),

    cecFormRules:{
      responseIndex: [
        { required: true, message: 'Please select response.', trigger: ['change', 'blur'] },
      ],
      progress_period_date: [
        { required: true, message: 'This field required.', trigger: 'change' },
      ],
      reason: [
        { required: true, message: 'Please select reason.', trigger: 'change' },
      ],
      comments: [
        { required: false, message: 'Please add comments.', trigger: 'change' },
      ],
      other_reason: [
        { required: true, message: 'This field required.', trigger: 'change' },
      ],
      what_system: [
        { required: true, message: 'This field required.', trigger: 'change' },
      ],
      indicate_reason: [
        { required: true, message: 'This field required.', trigger: 'change' },
      ],
      gutter_edge_meters: [
        { required: true, message: 'This field required.', trigger: 'change' },
      ],
      valley_meters: [
        { required: true, message: 'This field required.', trigger: 'change' },
      ],
      installation_date: [
        { required: true, message: 'This field required.', trigger: 'change' },
      ],
      tried_to_contact_date: [
        { required: true, message: 'This field required.', trigger: 'change' },
      ],
      selected_time: [
        { required: true, message: 'This field required.', trigger: 'change' },
      ],
    },

    inProgressForm: new Form({
        lead_id: '',
        response: '', // under meta
        comments: '',
        update_type: '',
        gutter_edge_meters: '',
        valley_meters: '',
        progress_period_date: '',
        installation_date: '',

        escalation_level: '',
        escalation_status: '',

        // under meta
        other_reason: '',
        indicate_reason: '',
        what_system: '',
        selected_time: '14:00'
    }),
    inProgressFormRules:{
      progress_period_date: [
        { required: true, message: 'Please select date', trigger: ['blur', 'change'] },
      ],
      comments: [
        { required: false, message: 'Please add comments', trigger: 'blur' },
      ],
    },
    responses: [
        {
            'response': 'I have contacted the Enquirer',
            'reasons': [
                'This lead is currently Work in Progress',
                /* 'This lead will be installed soon', */
                'This lead has been WON and installed',
                'This lead has been LOST'
            ]
        },
        {
            'response': 'I have tried to contact the Enquirer',
            'reasons': [
                'Couldn\'t get through. Will keep trying.',
                'Waiting for their response. Will keep trying.',
                'DISCONTINUE this lead. Multiple attempts made. Could not connect with the Enquirer.'
            ]
        },
        {
            'response': 'I would like to DISCONTINUE this lead',
            'reasons': [
                'I\'m currently on holiday/business leave',
                'I\'m on sick leave',
                'Currently too much work on',
                'Too far to service',
                'Job is too difficult',
                'Other',
            ]
        }
    ],
    inprogressResponses: [
        'This lead is currently Work in Progress',
        'This lead has been WON and installed',
        'This lead has been LOST'
    ],
    lostLeadReasons: [
        'Enquirer went with someone with a different system (not a ski-slope system).',
        'Enquirer went with a ski-slope system but a different installer.',
        'Enquirer decided not to install ski-slope system because price too high.',
        'Enquirer might order in the future but the time frame is unknown.',
        'Other',
    ],
    workInprogressReasons: [
        'Yet to be quoted.',
        'Has been Quoted. Waiting for decision.',
        'Soon to be installed.',
        'Other',
    ],
    declineReasons: [
        'I\'m currently on holiday/business leave',
        'I\'m on sick leave',
        'Too far away to service',
        'Currently too much work on',
        'No longer wish to receive leads',
        'Other',
    ],
    escalationModals: [
        {
            title: 'aod',
            value: false
        },
        {
            title: 'cec',
            value: false
        },
        {
            title: 'inprogress',
            value: false
        },
        {
            title: 'finalize',
            value: false
        },
        {
            title: 'declined',
            value: false
        },
        {
            title: 'lost',
            value: false
        },
        {
            title: 'won',
            value: false
        },
        {
            title: 'confirmation',
            value: false
        },
        {
          title: 'declined-lapse',
          value: false
        },
        {
          title: 'cec-declined',
          value: false
        },
        {
          title: 'discontinued',
          value: false
        },
        {
          title: 'abandoned',
          value: false
        },
    ],
    cecFormRules: {
        responseIndex: [
            { required: true, message: 'Please select a response.', trigger: 'change' },
        ],
        reason: [
            { required: true, message: 'Please provide a reason. Thank you.', trigger: 'change' },
        ],
        tried_to_contact_date: [
            { required: true, message: 'This field required.', trigger: 'change' },
        ],
        progress_period_date: [
          { required: true, message: 'This field required.', trigger: 'change' },
        ],
        comments: [
            { required: false, message: 'Please add comments.', trigger: 'change' },
        ],
        selected_time: [
          { required: true, message: 'This field required.', trigger: 'change' },
        ],
    },
    inProgressFormRules: {
        response: [
			{ required: true, message: 'Please select a response.', trigger: 'change' },
        ],
        progress_period_date: [
            { required: true, message: 'Please select date', trigger: ['blur', 'change'] },
        ],
        comments: [
            { required: false, message: 'Please add comments', trigger: 'blur' },
        ],
        other_reason: [
            { required: false, message: 'Please provide a reason. Thank you.', trigger: 'change' },
        ],
        selected_time: [
          { required: true, message: 'This field required.', trigger: 'change' },
        ],
    },
    optionalRule: {
        gutter_edge_meters: [
			{ required: true, message: 'Please enter a number or "0".', trigger: 'blur' },
        ],
        valley_meters: [
			{ required: true, message: 'Please enter a number or "0".', trigger: 'blur' },
        ],
        installation_date: [
			{ required: true, message: 'Please pick an installation date.', trigger: 'blur' },
        ]
    },
    reasonOptionalRule: [
        {
            comments: [
                { required: false, message: 'Please provide a comment. Thank you.', trigger: 'change' },
            ],
        },
          {
            other_reason: [
                { required: true, message: 'Please provide a reason. Thank you.', trigger: 'change' },
            ],
          },
        {
            indicate_reason: [
                { required: true, message: 'Please provide a reason. Thank you.', trigger: 'change' },
            ],
        },
    ],
    reachedExtendedLimit: false,
    hasDeclined: false
}

export const getters = {
	selectedLead: state => state.selectedLead,
    escalationVisible: state => state.escalationVisible,
    loading: state => state.loading,
    acceptForm: state => state.acceptForm,
    declineForm: state => state.declineForm,
    cecForm: state => state.cecForm,
    cecFormRules: state => state.cecFormRules,
    inProgressForm: state => state.inProgressForm,
    lostLeadReasons: state => state.lostLeadReasons,
    responses: state => state.responses,
    inprogressResponses: state => state.inprogressResponses,
    workInprogressReasons: state => state.workInprogressReasons,
    declineReasons: state => state.declineReasons,
    escalationModals: state => state.escalationModals,
    confirmationLeadDetails: state => state.confirmationLeadDetails,
    confirmationMessage: state => state.confirmationMessage,
    hasDeclined: state => state.hasDeclined,
    reachedExtendedLimit: state => state.reachedExtendedLimit,
    inProgressFormRules: state => state.inProgressFormRules,
    declineFormRules: state => state.declineFormRules,
    cecFormRules: state => state.cecFormRules,
    showCallEnquirer: state => state.showCallEnquirer,
    iconForm: state => state.iconForm,
    iconFormRules: state => state.iconFormRules,
}

// mutations
export const mutations = {
    [types.SHOW_ESCALATION] (state, { showEscalation, lead }) {
        if ( showEscalation ) {
            state.selectedLead = lead
            const forms = [ 'acceptForm', 'declineForm', 'cecForm', 'inProgressForm' ]
            for (let frm of forms) {
                state[frm].reset()
                state[frm].lead_id = lead.lead_id ? lead.lead_id : ''
            }
        }
    },

    [types.SHOW_MODAL] (state, { modal, extended, user = null, hasDeclined = false }) {
      if ( modal ) {
          state.escalationVisible = true
          state.hasDeclined = hasDeclined

          state.escalationModals = state.escalationModals.map((esModal) => {
              esModal.value = false
              if ( user && user.user_role.name == 'organisation' ) {
                  if ( modal == 'declined-lapse' && esModal.title == 'aod' ) {
                      esModal.value = true

                  } else {
                      if ( modal !== 'declined-lapse' ) {
                          esModal.value = esModal.title == modal
                      }
                  }
              } else {
                  esModal.value = esModal.title == modal;
              }

              return esModal
          })
      } else {
          state.escalationVisible = false
      }
      state.reachedExtendedLimit = extended
      state.confirmationLeadDetails = state.confirmationLeadDetails ? state.confirmationLeadDetails : 'Confirmation'

      // Modify confirmation message if declined
      if(hasDeclined) {
        state.confirmationMessage = 'Lead Declined'
      }
    },

    [types.UPDATED_ESCALATION] (state, { data, message, form }) {
        state.loading = false
        state.escalationVisible = false
        state.confirmationLeadDetails = data
        state.confirmationMessage = message ? message : ''
        state.reachedExtendedLimit = false
        state.showCallEnquirer = form == 'acceptForm'

        axios.post(`/api/v1/organisations/status/update/${data.organisation_id}`)
    },

    [types.CHANGE_RULES] (state, { formName, formRuleName }) {
        state[formName].comments = ''
        state[formName].indicate_reason = ''
        // state[formName].other_reason = ''
        // COMMENTED-OUT BECAUSE IT IS ALWAYS SETTING THE INPUT VALUE TO AN EMPTY STRING EVERY TRIGGER CHANGE

        if ( (state[formName].responseIndex !== '' && state[formName].reason == 'This lead has been WON and installed' ) ||
            state[formName].response == 'This lead has been WON and installed' )   {
            state[formRuleName] = { ...state[formRuleName] , ...state.optionalRule }
        } else if ( state[formName].reason == 'Other' || state[formName].reason == 'DISCONTINUE this lead. Multiple attempts made. Could not connect with the Enquirer.' ) {
            state[formRuleName] = { ...state[formRuleName] , ...state.reasonOptionalRule[0] }
        } else if ( state[formName].reason == 'This lead has been LOST' || state[formName].response == 'This lead has been LOST' ) {
            state[formRuleName] = { ...state[formRuleName] , ...state.reasonOptionalRule[1] }

            if ( state[formName].other_reason == 'Other' ) {
                state[formRuleName] = { ...state[formRuleName] , ...state.reasonOptionalRule[2] }
            }

        } else {
            if ( state[formName].responseIndex && state[formName].responseIndex !== '') {
                state[formRuleName] = {
                    responseIndex: [
                        { required: true, message: 'Please select a response.', trigger: 'change' },
                    ],

                    reason: [
                        { required: true, message: 'Please provide a reason. Thank you.', trigger: 'change' },
                    ],

                    tried_to_contact_date: [
                      { required: true, message: 'This field required.', trigger: 'change' },
                    ],

                    selected_time: [
                      { required: true, message: 'This field required.', trigger: 'change' },
                    ],

                    progress_period_date: [
                      { required: true, message: 'This field required.', trigger: 'change' },
                    ],

                    comments: [
                        { required: false, message: 'Please add comments.', trigger: 'change' },
                    ],
                }
            } else {
                if ( state[formName].other_reason == 'Other' ) {
                    state[formRuleName] = { ...state[formRuleName] ,
                        response: [
                            { required: true, message: 'Please select a response.', trigger: 'change' },
                        ],
                        indicate_reason: [
                            { required: true, message: 'This field required.', trigger: 'change' },
                        ],
                    }

                } else {
                    state[formRuleName] = { ...state[formRuleName] ,
                        response: [
                            { required: true, message: 'Please select a response.', trigger: 'change' },
                        ],
                    }
                }

            }
        }
    },

    [types.SET_ICON_FORM] (state, data) {
        state.iconForm.email = data.email
        state.iconForm.contact_number = data.contact_number
    },

    [types.SET_ICON_FORM_TYPE] (state, type, number) {
        state.iconForm.type = type
    },
}

// extra

const getExtensionNo = (status) => {
    const number = parseInt(status.replace(/[^0-9]/g,''))

    return number ? (number + 1) : 1;
}

const updateLevelAndStatus = (formName) => {
  try{
    state[formName].response = state[formName].responseIndex !== '' ? state.responses[state[formName].responseIndex].response : state[formName].response
  }
  catch(err){}
    if ( state[formName].reason == 'This lead has been LOST'
        || state[formName].response == 'This lead has been LOST' ) {
        state[formName].escalation_level = 'Lost'
        state[formName].escalation_status = 'Lost'
    } else if ( state[formName].reason == 'This lead has been WON and installed'
        || state[formName].response == 'This lead has been WON and installed' ) {
        state[formName].escalation_level = 'Won'
        state[formName].escalation_status = 'Won'
    } else if ( state[formName].response === 'I would like to DISCONTINUE this lead' ) {
        state[formName].escalation_level = 'Confirm Enquirer Contacted'
        state[formName].escalation_status = 'Discontinued'

    } else if ( state[formName].response === 'I have tried to contact the Enquirer' ) {
      if(state[formName].reason == 'DISCONTINUE this lead. Multiple attempts made. Could not connect with the Enquirer.') {
        state[formName].escalation_level = 'Discontinued'
        state[formName].escalation_status = 'Discontinued'
      } else {
        state[formName].escalation_level = 'Confirm Enquirer Contacted'
        state[formName].escalation_status = 'Awaiting Response'
        state[formName].modified_escalation_status = 'Awaiting Response - Tried Contacting'
      }
    } else if ( formName == 'inProgressForm' && (
        state[formName].response == 'This lead is currently Work in Progress' ||
        state[formName].response == 'This lead will be installed soon'
    ) )  {
        state[formName].escalation_level = 'In Progress'
        state[formName].escalation_status = `Extended ${getExtensionNo(state.selectedLead.escalation_status)}`
    }
}

// actions
export const actions = {
    showEscalationModal({ commit }, { showEscalation, modal, lead, user = null }) {
		try {
			commit(types.SHOW_ESCALATION, { showEscalation, lead })
			commit(types.SHOW_MODAL, { modal, user })
		} catch (error) {console.log(error.message)}
    },

    showConfirmationModal({ commit }, {extended = false, hasDeclined = false}) {
		try {
			commit(types.SHOW_MODAL, {
                modal: 'confirmation',
                extended,
                hasDeclined: hasDeclined
            })
		} catch (error) {console.log(error.message)}
    },

    async updateLeadEscalation({ commit }, form) {
        try {
            state.loading = true;

            // overwrite reason if other reason has a value
            // update escalation level and status depending on the selected response and reason
            if ( ['cecForm', 'inProgressForm'].includes(form) ) {
                updateLevelAndStatus(form)
            }

            const { data } = await state[form].post(`/api/v1/leads/update_response`)
            commit(types.UPDATED_ESCALATION, { data: data.data, message: data.message, form })

            return {
                data,
                originalLeadId: state[form].lead_id
            };

        } catch (error) {
            state.loading = false
            console.log(error.message)
        }
    },

    async changeRules({ commit }, { formName, formRuleName}) {
        commit(types.CHANGE_RULES, { formName, formRuleName})
    },

    async setIconForm({ commit }, data) {
        commit( types.SET_ICON_FORM, data )
    },

    async setIconFormType({ commit }, type, number = null) {
        commit( types.SET_ICON_FORM_TYPE, type, number )
    },

    async sendIconAction({ commit }) {
        state.loading = true
        const { data } = await state.iconForm.post( '/api/v1/leads/orgs-icon-action' )

        state.iconForm.reset()
        state.loading = false
        return data
    },

    async iconFormReset({ commit }) {
        state.iconForm.reset()
    }
}
