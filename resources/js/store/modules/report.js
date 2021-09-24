import axios from 'axios'
import * as types from '../mutation-types'
import { getSpanArr } from "~/helpers"

export const state = {
  advertisingMediumBreakdown: [],
  organisationStatusBreakdown: [],
  leadsWonBreakdown: [],
  advertisingMediumBreakdownArr: [],
  organisationStatusBreakdownArr: [],
  leadsWonBreakdownArr: [],
  loading: true,
  states: []
}

export const getters = {
  advertisingMediumBreakdown: state => state.advertisingMediumBreakdown,
  organisationStatusBreakdown: state => state.organisationStatusBreakdown,
  leadsWonBreakdown: state => state.leadsWonBreakdown,
  organisationStatusBreakdownArr: state => state.organisationStatusBreakdownArr,
  loading: state => state.loading,
  states: state => state.states
}

export const mutations = {
  [types.FETCH_ADVERTISING_MEDIUM] (state, {data, states}){
    state.advertisingMediumBreakdown = data
    state.states = states
    state.loading = false
  },

  [types.FETCH_ORGANISATION_STATUS] (state, {data, states}){
    state.organisationStatusBreakdown = data
    state.states = states
    state.loading = false
    state.organisationStatusBreakdownArr =  getSpanArr(state.organisationStatusBreakdown)
  },

  [types.FETCH_LEADS_WON] (state, {data, states}){
    state.leadsWonBreakdown = data
    state.states = states
    state.loading = false
  },
}

export const actions = {

  async exportLeadWonBreakdown( {commit}, payload){
    try{
      let param = { state: payload.states ? payload.states : "All States", from: payload.dates[0] ? payload.dates[0] : "",  to: payload.dates[1] ? payload.dates[1] : "", export: payload.export }
      const { data } = await axios.post(`/api/v1/admin/reports/export/lead-won-breakdown`, param, {responseType: 'blob'})
      return data
    }
    catch(e){console.log(e.message)}
  },

  async exportOrganisationStatusBreakdown( {commit}, payload){
    try{
      let param = { state: payload.states ? payload.states : "All States", from: payload.dates[0] ? payload.dates[0] : "",  to: payload.dates[1] ? payload.dates[1] : "", export: payload.export }
      const { data } = await axios.post(`/api/v1/admin/reports/export/organisation-status-breakdown`, param, {responseType: 'blob'})
      return data
    }
    catch(e){console.log(e.message)}
  },

  async exportAdvertisingMediumBreakdown( {commit}, payload){
    try{
      let param = { state: payload.states ? payload.states : "All States", from: payload.dates[0] ? payload.dates[0] : "",  to: payload.dates[1] ? payload.dates[1] : "", export: payload.export }
      console.log(param)
      const { data } = await axios.post(`/api/v1/admin/reports/export/advertising-medium-breakdown`, param , {responseType: 'blob'})
      return data
    }
    catch(e){console.log(e.message)}
  },

  async fetchAdvertisingMediumBreakdown( {commit}, payload){
    try{
      let param = { 
        "state" : payload.filters[0].value, 
        "from" : (payload.filters[1] && payload.filters[1].value && payload.filters[1].value[0]) ? payload.filters[1].value[0] : "", 
        "to" :  (payload.filters[1] && payload.filters[1].value && payload.filters[1].value[1]) ? payload.filters[1].value[1] : ""}
      state.loading = true
      const {data} = await axios.get('/api/v1/admin/reports/medium-breakdown', {params: param})
      commit(types.FETCH_ADVERTISING_MEDIUM, { data:data.data, states: data.states })
    }
    catch(error){console.log(error.message)}
  },

  async fetchOrganisationStatusBreakdown( {commit}, payload){
    try{
      let param = { 
        "state" : payload.filters[0].value, 
        "from" : (payload.filters[1] && payload.filters[1].value && payload.filters[1].value[0]) ? payload.filters[1].value[0] : "", 
        "to" :  (payload.filters[1] && payload.filters[1].value && payload.filters[1].value[1]) ? payload.filters[1].value[1] : ""}
      state.loading = true
      const {data} = await axios.get('/api/v1/admin/reports/organisation-breakdown', {params: param})
      commit(types.FETCH_ORGANISATION_STATUS, { data:data.data, states:data.states })
    }
    catch(error){console.log(error.message)}
  },

  async fetchLeadsWonBreakdown( {commit}, payload){
    try{
      let param = { 
        "state" : payload.filters[0].value, 
        "from" : (payload.filters[1] && payload.filters[1].value && payload.filters[1].value[0]) ? payload.filters[1].value[0] : "", 
        "to" :  (payload.filters[1] && payload.filters[1].value && payload.filters[1].value[1]) ? payload.filters[1].value[1] : ""}
      state.loading = true
      const {data} = await axios.get('/api/v1/admin/reports/leads-won-breakdown', {params: param})
      commit(types.FETCH_LEADS_WON, { data:data.data, states: data.states })
    }
    catch(error){console.log(error.message)}
  },
}
