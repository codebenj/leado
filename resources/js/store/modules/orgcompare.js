import axios from 'axios'
import * as types from '../mutation-types'

// states
export const state = {
  leadOrgPostCodes: [],
  leadOrgPostCodesLoading: true,
  leadOrgPostCodesTotal: 0,
  leadOrgPostCodesMonths: [],
}

// getters
export const getters = {
  leadOrgPostCodes: state => state.leadOrgPostCodes,
  leadOrgPostCodesLoading: state => state.leadOrgPostCodesLoading,
  leadOrgPostCodesTotal: state => state.leadOrgPostCodesTotal,
  leadOrgPostCodesMonths: state => state.leadOrgPostCodesMonths
}

// mutations
export const mutations = {
  [types.FETCH_LEAD_ORG_POST_CODES]( state, { data, months } ) {
    let new_data = [ ...new Map( data.map( x => [ x[ 'name' ], x ]) ).values() ]

    // Sorts Merged Orgs
    state.leadOrgPostCodes = new_data.sort((xVal, yVal) => {
        if(xVal.name < yVal.name) return -1;
        if(xVal.name > yVal.name) return 1;
        return 0;
    })
    state.leadOrgPostCodesLoading = false
    state.leadOrgPostCodesTotal = new_data.length
    state.leadOrgPostCodesMonths = months
  },
}

// actions
export const actions = {
  async fetchLeadOrgPostCodes( { commit }, { filters, org_ids, days = null } ) {
    try {
      let ids = []

      if ( org_ids.length > 0 ) {
        org_ids.forEach( el => {
          ids.push( el.id )
        } )
      } else {
        ids.push( null )
      }

      let param = {
        "from" : (filters[0] && filters[0][0]) ? filters[0][0] : "",
        "to" :  (filters[0] && filters[0][1]) ? filters[0][1] : "",
        "type": "org_stat",
        "days": days,
        "ids": ids,
      }

      state.loading = true
      state.leadOrgPostCodes = []
      const { data } = await axios.get( '/api/v1/admin/reports/organisation-breakdown', { params: param } )
      commit(types.FETCH_LEAD_ORG_POST_CODES, {
        data: data.data,
        months: data.months
      });

    } catch( error ) { console.log( error.message ) }
  },
}
