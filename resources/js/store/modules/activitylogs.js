import axios from 'axios'
import * as types from '../mutation-types'
import Form from 'vform'

export const state = {
  activitylogs: [],
  emailData: [],
  oldNextPage: '',
  nextPage: '',
  total: 0,
	loading: true,
}

export const getters = {
  activitylogs: state => state.activitylogs,
  emailData: state => state.emailData,
	loading: state => state.loading,
}

export const mutations = {
  // fetch activity logs
  [types.FETCH_ACTIVITY_LOGS](state, { activitylogs }) {
    state.activitylogs = activitylogs.data;
    state.total = activitylogs.total;
    state.nextPage = activitylogs.next_page_url;
    state.loading = false;

    // state.activitylogs = state.activitylogs.sort(
    //   (a, b) => (a.activities[0] && b.activities[0]) ? a.activities[0].id < b.activities[0].id : false
    // );
  },

  [types.FETCH_MORE_ACTIVITY_LOGS](state, { activitylogs }) {
    if (state.nextPage && state.nextPage != activitylogs.next_page_url) {
      state.activitylogs = state.activitylogs.concat(activitylogs.data);
      state.total = activitylogs.total;
      state.nextPage = activitylogs.next_page_url;
      state.loading = false;

      // state.activitylogs = state.activitylogs.sort(
      //   (a, b) => (a.activities[0] && b.activities[0]) ? a.activities[0].id < b.activities[0].id : false
      // );
    }
  },

  [types.FETCH_ACTIVITY_EMAIL_DATA](state, { emailData }){
    state.emailData = emailData
    state.loading = false;
  }
}

export const actions = {
  async fetchActivityEmailData( {commit}, activity_id){
    try{
      const { data } = await axios.get(`/api/v1/activities/view/email/${activity_id}`)
      commit(types.FETCH_ACTIVITY_EMAIL_DATA, { emailData:data })
    }catch(error){}
  },

  async fetchActivityLogs({ commit }, payload) {
    try {
      let param = { 'key': payload.key }

      const { data } = await axios.get('/api/v1/activities', {params: param})
      commit(types.FETCH_ACTIVITY_LOGS, { activitylogs: data })
    } catch (error) {}
  },

  async fetchMoreActivityLogs({ commit }, payload) {
    try {
      if (state.nextPage) {
        let param = { 'key': payload.key ?? '' }
        const { data } = await axios.get(state.nextPage, {params: param})
        commit(types.FETCH_MORE_ACTIVITY_LOGS, { activitylogs: data })
      }
    } catch (error) {}
	},
}
