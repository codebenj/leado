import axios from 'axios'
import * as types from '../mutation-types'

export const state = {
  stats: [],
  loading: true,
  total: 0,
  organisation_name: [],
  dates: [],
  columns: []
}

export const getters = {
  stats: state => state.stats,
  loading: state => state.loading,
  total: state => state.total,
  organisation_name: state => state.organisation_name,
  dates: state => state.dates,
  columns: state => state.columns
}

export const actions = {
  async fetchStats( {commit}, lead_id){
    try{
      const {data} = await axios.get(`/api/v1/admin/reports/lead/${lead_id}/stat`)
      commit(types.FETCH_LEAD_STATS, { data: data.data })
    }
    catch(e){console.log(e.message)}
  },

  async fetchOrganizationStats( {commit}, org_id){
    try{
      const {data} = await axios.get(`/api/v1/admin/reports/organization/${org_id}/stat`)
      commit(types.FETCH_ORGANIZATION_STATS, { data: data.data })

      return { data: data.data }
    }
    catch(e){console.log(e.message)}
  },
}

export const mutations = {
  [types.FETCH_LEAD_STATS] (state, {data}){
    state.stats = data.stats
    state.organisation_name = data.organisation_name
    state.dates = data.dates
    state.columns = data.columns
  },

  [types.FETCH_ORGANIZATION_STATS] (state, {data}){
    state.stats = data.stats
    state.organisation_name = data.organisation_name
    state.dates = data.dates
    state.columns = data.columns
  }
}
