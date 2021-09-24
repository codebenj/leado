import axios from 'axios'
import * as types from '../mutation-types'

export const state = {
  leads: [],
  loading: true,
  total: 0
}

export const getters = {
  leads: state => state.leads,
  loading: state => state.loading,
  total: state => state.total
}

export const mutations = {
  [types.FETCH_LEADS_DASHBOARD] (state, {data, total}){
    state.leads = data
    state.loading = false
    state.total = total ? total : 0
  }
}

export const actions = {
  async fetchDashboard( {commit}, query){
    try{
      var parameters = {"pageNo" : query.page, "pageSize" : query.pageSize, "escalation_level" : query.filters[0].value, "postcode" : query.filters[1].value }
      state.loading = true
      const { data } = await axios.get('/api/v1/leads/getDashboard', { params: parameters })
      commit(types.FETCH_LEADS_DASHBOARD, { data: data.data.leads, total: data.data.total })
    }
    catch(e){console.log(e.message)}
  }
}
