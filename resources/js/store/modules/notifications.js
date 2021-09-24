import axios from 'axios'
import * as types from '../mutation-types'
import Form from 'vform'

export const state = {
    notifications: [],
    unReadNotification: [],
    nextPage: '',
    total: 0,
    showOutgoingNotifications: false,
	  loading: true,
    custom_notification_form: new Form({
      'notify': '',
      'title': '',
      'body': '',
      'notification_type': []
    }),
    custom_notification_form_rules:{
      notify: [
        { required: true, message: 'Please select to notify', trigger: 'blur' },
      ],
      title: [
        { required: true, message: 'Please enter an title', trigger: 'blur' },
      ],
      body: [
        { required: true, message: 'Please enter body message', trigger: 'blur' },
      ],
      notification_type: [
        { required: true, message: 'Please notification type', trigger: 'blur' },
      ],
    }
}

export const getters = {
	notifications: state => state.notifications,
  loading: state => state.loading,
  unReadNotification: state => state.unReadNotification,
  showOutgoingNotifications: state => state.showOutgoingNotifications,
  custom_notification_form: state => state.custom_notification_form,
  custom_notification_form_rules: state => state.custom_notification_form_rules
}

export const mutations = {
    // fetch activity logs FETCH_UNREAD_NOTIFICATIONS
    [types.FETCH_NOTIFICATIONS](state, { notifications }) {

      console.log(notifications)
        state.notifications = notifications.data.filter( x => {
          if(x.notifications) return x
        }).reverse();
        state.total = notifications.total;
        state.nextPage = notifications.next_page_url;
        state.loading = false;
        // OLD CODE
        state.notifications = state.notifications.sort(
            (a, b) => (a.notifications[0] && b.notifications[0]) ? a.notifications[0].id < b.notifications[0].id : false
        );
    },

    [types.FETCH_LOAD_MORE_NOTIFICATION](state, { notifications }) {
      state.notifications = state.notifications.concat(notifications.data.reverse());
      state.total = notifications.total;
      state.nextPage = notifications.next_page_url;
      state.loading = false;

    //   state.notifications = state.notifications.sort(
    //     (a, b) => (a.notifications[0] && b.notifications[0]) ? a.notifications[0].id < b.notifications[0].id : false
    // );
    },

    [types.FETCH_UNREAD_NOTIFICATIONS](state, { data }) {
        state.unReadNotification = data
        state.loading = false;
    },

    [types.READ_NOTIFICATION](state, { notification }) {
        const index = state.notifications.findIndex(n => n.id === notification.id);
        const unreadIndex = state.unReadNotification.findIndex(n => n.id === notification.id);

        if ( index !== -1) {
            state.notifications.splice(index, 1, notification);
        }

        if ( unreadIndex !== -1) {
            state.unReadNotification.splice(unreadIndex, 1);
        }

    },

    [types.READ_ALL_NOTIFICATION](state, { notification }) {
      state.unReadNotification = []
      state.loading = false;
      // state.notifications = []
  },

    [types.SHOW_OOUTGOING_MODAL] (state, { outgoingModalState }) {
      state.showOutgoingNotifications = outgoingModalState;
    },
}

export const actions = {

  async sentNotifications( {commit}){
    try{
      state.loading = true
      const { data } = await state.custom_notification_form.post(`/api/v1/custom/notifications/send`)
      state.loading = false
      return data
    }catch(e){ console.log(e.message) }
  },

  setOutgoingModalState({ commit }, { outgoingModalState = false }) {
		try {
			commit(types.SHOW_OOUTGOING_MODAL, { outgoingModalState })
		} catch (error) {console.log(error.message)}
  },

    async fetchUnreadNotification({commit}){
        try{
            state.loading = true;
            let param = { is_read: 0 }
            const {data} = await axios.get(`/api/v1/notifications/notification`, { params: param })
            commit(types.FETCH_UNREAD_NOTIFICATIONS, {data: data.data})
        }
        catch(e){ console.log(e.message) }
    },

    async fetchNotifications({ commit }, payload) {
		try {
            state.loading = true;
            let param = { "key" :  payload.key }
            const { data } = await axios.get('/api/v1/notifications', {params: param})
            commit(types.FETCH_NOTIFICATIONS, { notifications: data })
		} catch (error) {
            console.log(error.message)
        }
    },

    async fetchMoreNotifications({ commit }, payload) {
		try {
            if (state.nextPage) {
                let param = { "key" :  payload.key ?? '' }
                const { data } = await axios.get(state.nextPage, {params: param})
                commit(types.FETCH_LOAD_MORE_NOTIFICATION, { notifications: data })
            }
		} catch (error) {
            console.log(error.message)
        }
    },

    async readNotification({ commit }, id) {
		try {
            const { data } = await axios.get(`/api/v1/notifications/read/${id}`)
            commit(types.READ_NOTIFICATION, { notification: data.data })
		} catch (error) {
            console.log(error.message)
        }
	},

  async readAllNotification({ commit }) {
		try {
            state.loading = true;
            const { data } = await axios.get(`/api/v1/notifications/read-all`)
            commit(types.READ_ALL_NOTIFICATION, { notification: data.data })
		} catch (error) {
            console.log(error.message)
        }
	},
}
