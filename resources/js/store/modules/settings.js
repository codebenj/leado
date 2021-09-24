import axios from 'axios'
import * as types from '../mutation-types'
import Form from 'vform'

// state
export const state = {
    setting: {},
    settingCompanyName: {},
    user: {},
    settings: [],
    logo: null,
    total: 0,
    loading: false,
    settingsForm: new Form({
        key: "",
		value: "",
        name: "",
        metadata: {
            level: "",
            status: "",
            type: "",
            admin_tooltip: "",
            org_tooltip: "",
        }
    }),
    escalations: {
        'Unassigned': [
            'Unassigned',
            'Special Opportunity',
            'Lost'
        ],
        'Accept Or Decline': [
            'Pending',
            'Declined',
            'Declined-Lapsed',
        ],
        'Confirm Enquirer Contacted': [
            'Awaiting Response',
            'Awaiting Response - Reminder Sent',
            'Awaiting Response - Admin Notified',
            'Discontinued',
        ],
        'In Progress': [
            'Awaiting Response',
            'Awaiting Response - Reminder Sent',
            'Awaiting Response - Admin Notified',
            'Discontinued',
            'General Enquiry',
            'Supply Only',
        ],
        'Won': [
            'Won'
        ],
        'Lost': [
            'Lost'
        ],
        'New': [
            'Supply Only',
            'General Enquiry'
        ],
        'Finalized': [
            'General Enquiry',
            'Supply Only'
        ],
    },
    dialogVisible: false,
    types: [
        "minutes",
        "hours",
        "days",
        "months"
    ],
    settingsFormRules: {
        key: [
            { required: true, message: 'Please input key', trigger: 'blur' },
        ],
        name: [
            { required: true, message: 'Please input name', trigger: 'blur' },
        ],
        metadata: {
            level: [
                { required: true, message: 'Please select escalation level', trigger: 'blur' },
            ],
            status: [
                { required: true, message: 'Please select escalation level', trigger: 'blur' },
            ],
        }
    },
    basicSettingsForm: new Form({
        receivers: '',
        day_of_week: '',
        time: '',
        am_pm: '',
        enquirer_message: 'Thank you for your Leaf Stopper Enquiry. If you do not receive a call from an installer within 72 hours, please call us on 1300 334 333 or email us at office@leafstopper.com.au',
        timezone_name: '',
        timezone: '',
        company_name: '',
    }),
    basicSettingsFormRules: {
        company_name: [
          { required: true, message: 'Please enter company name.', trigger: 'blur' },
        ],

        receivers: [
          { required: true, message: 'Please enter emails and comma separated.', trigger: 'blur' },
        ],

        enquirer_message: [
          { required: true, message: 'Please enter enquirer message template', trigger: 'blur' },
        ]
    },
    logo: '',
}

// getters
export const getters = {
    settings: state => state.settings,
    setting: state => state.setting,
    settingsForm: state => state.settingsForm,
    settingsFormRules: state => state.settingsFormRules,
    total: state => state.total,
    loading: state => state.loading,
    escalations: state => state.escalations,
    types: state => state.types,
    dialogVisible: state => state.dialogVisible,
    basicSettingsForm: state => state.basicSettingsForm,
    basicSettingsFormRules: state => state.basicSettingsFormRules,
    logo: state => state.logo,
    user: state => state.user,
    settingCompanyName: state => state.settingCompanyName
}

// mutations
export const mutations = {
    [types.FETCH_SETTINGS] (state, { settings, total, user }) {
        state.settings = settings.length ? settings : []
        state.total = total ? total : 0
        state.loading = false
        state.user = user

        //console.log(state.user)

        if ( total ) {
            let options = {
              hour: '2-digit',
              minute: '2-digit',
              hour12: true,
              timeZone: 'UTC',
            };

            const logo = settings.find(s => s.key === 'main-logo')
            const receivers = settings.find(s => s.key === 'admin-email-notification-receivers')
            const manual_notifications = settings.find(s => s.key === 'manual-notifications-organisation')
            const enquirer_message = settings.find(s => s.key === 'admin-enquire-message')
            const company_name = settings.find(s => s.key === 'company-name')

            let options_user_timezone = {
              hour: '2-digit',
              minute: '2-digit',
              hour12: false,
              timeZone: state.user.metadata.timezone ?? 'Australia/Sydney',
            };

            //create date as backup if no timezone found in the settings. we just needs the hour and minute
            let time = new Date('2021-04-30T08:00:00+08:00')

            try{
              time = new Date(Date.parse(manual_notifications.metadata.timezone))
              time = new Intl.DateTimeFormat('en-US', options_user_timezone).format(time)
            }catch(e){
            }

            state.logo = logo ? logo.value : ''
            state.basicSettingsForm.receivers = receivers ? receivers.value : ''
            state.basicSettingsForm.day_of_week = manual_notifications.metadata.day ?? 1
            state.basicSettingsForm.time = time ?? '08:00'
            //state.basicSettingsForm.time = time.slice(0, -3) ?? '08:00'
            state.basicSettingsForm.am_pm = manual_notifications.metadata.am_pm ?? 'AM'
            state.basicSettingsForm.enquirer_message = enquirer_message.value ? enquirer_message.value : 'Thank you for your Leaf Stopper Enquiry. If you do not receive a call from an installer within 72 hours, please call us on 1300 334 333 or email us at office@leafstopper.com.au'
            state.basicSettingsForm.timezone_name = manual_notifications.metadata.timezone_name
            state.basicSettingsForm.timezone = manual_notifications.metadata.timezone

            state.basicSettingsForm.company_name = company_name.value ?? 'Traleado'
        }
    },

    [types.EDIT_SETTINGS] (state, { setting, id }) {
        state.dialogVisible = true
        state.settingsForm.reset();

        if ( setting ) {
            state.settingsForm.fill(setting);
            state.settingsForm.id = id
            state.settingsForm.metadata = JSON.parse(setting.metadata)
        }
    },

    [types.SAVE_SETTINGS] (state, { setting }) {
		const index = state.settings.findIndex(s => s.id === setting.id);

        if ( index !== -1) {
            state.settings.splice(index, 1, setting);
        } else {
            state.settings.push(setting);
        }

        state.loading = false
	},

	[types.DELETE_SETTINGS] (state, id) {
		const index = state.settings.findIndex(s => s.id === id);

		if ( index !== -1) {
			state.settings.splice(index, 1);
		}
  },

  [types.DIALOG_SETTINGS_STATE] (state, close) {
		state.dialogVisible = close
  },

  [types.GET_SETTING] (state, data){
    // console.log(data)
    state.setting = data.data
  },

  [types.SETTING_COMPANY_NAME] (state, data){
    state.settingCompanyName = data.data
  }
}

// actions
export const actions = {
    async fetchSettings({ commit }, queryInfo) {
		try {
            state.loading = true

            const params = {
                pageNo: queryInfo.page ? queryInfo.page : 1,
                pageSize: queryInfo.pageSize ? queryInfo.pageSize : 10,
                search: queryInfo.filters[0] ? queryInfo.filters[0].value : "",
            };

            const { data } = await axios.get(`/api/v1/admin/setting`, { params });
            //debugger
            commit(types.FETCH_SETTINGS, {
                settings: data.data.settings,
                total: data.data.total,
                user: data.data.user
            })
		} catch (error) {console.log(error.message)}
    },

    async editSettings({ commit }, data) {
		try {
            if (data) {
                commit(types.EDIT_SETTINGS, { setting: data, id: data.id })

                return data;

            } else {
                commit(types.EDIT_SETTINGS, { setting: null })
            }

            commit(types.DIALOG_SETTINGS_STATE, true)

		} catch (error) {console.log(error.message)}
    },

    async saveSettings({ commit }) {
		try {
            state.loading = true
            const saveURL = state.settingsForm.id ? `/api/v1/admin/setting/${state.settingsForm.id}` : `/api/v1/admin/setting`
            const { data } = await (state.settingsForm.id ? state.settingsForm.put(saveURL) : state.settingsForm.post(saveURL))

            commit(types.SAVE_SETTINGS, { setting: data.data })
      		return data;
		} catch (error) {
            state.loading = false
            return error;
        }
    },

    async deleteSettings({ commit }, id) {
		try {
            const { data } = await axios.delete(`/api/v1/admin/setting/${id}`)
			commit(types.DELETE_SETTINGS, id)
			return data;
		} catch (error) {console.log(error.message)}
    },

    async setDialog({ commit }, close) {
        commit(types.DIALOG_SETTINGS_STATE, close)
    },

    async saveBasicSettings({ commit }) {
        try {
            state.loading = true
            const { data } = await state.basicSettingsForm.post('/api/v1/admin/setting/admin_email_receivers')
            commit(types.SAVE_SETTINGS, { setting: data.data })
      		return data;
		} catch (error) {
            state.loading = false
            return error;
        }
    },

    async getGetSetting( {commit}, key){
      try{
        const { data } = await axios.get(`/api/v1/admin/setting/get/${key}`)
        commit(types.GET_SETTING, { data: data.data })
      }
      catch(e){

      }
    },

    async getGetSettingCompanyName( {commit} ){
      try{
        const { data } = await axios.get(`/api/v1/admin/setting/get/company-name`)
        commit(types.SETTING_COMPANY_NAME, { data: data.data })
      }
      catch(e){

      }
    }
}
