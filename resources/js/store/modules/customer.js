import axios from 'axios'
import * as types from '../mutation-types'

export const state = {
  customers: [],
  total: 0,
  loading: false
}

export const getters = {
  customers: state => state.customers,
  total: state => state.total,
  loading: state => state.loading
}

export const mutations = {
  [types.GET_CUSTOMERS] (state, {customers, total}){
    state.customers = customers
    state.total = total
    state.loading = false
  },
}

export const actions = {
  async fetchCustomers( {commit}, queryInfo ){
    try{
      state.loading = true
      let params = {
				pageNo: queryInfo.page ? queryInfo.page : 1,
				pageSize: queryInfo.pageSize ? queryInfo.pageSize : 20,
				lead_type: (queryInfo.filters != null) ? ((queryInfo.filters[0].value != null) ? queryInfo.filters[0].value : "") : "",
				state: (queryInfo.filters != null) ? ((queryInfo.filters[1].value != null) ? queryInfo.filters[1].value : "") : "",
				suburb: (queryInfo.filters != null) ? ((queryInfo.filters[2].value != null) ? queryInfo.filters[2].value : "") : "",
				search: (queryInfo.filters != null) ? ((queryInfo.filters[3].value != null) ? queryInfo.filters[3].value : "") : "",
			};
      const { data } = await axios.get(`/api/v1/customers`, { params })
      commit(types.GET_CUSTOMERS, { customers: data.data.customers, total: data.data.total })
    }catch(err){
      console.log(err.message)
    }
  },

  async export( {commit}, ids){
    try{
      const { data } = await axios.post(`/api/v1/customers/export`, { ids: ids }, {responseType: 'blob'})
      return data
    }
    catch(e){
      console.log(e.message)
    }
  },
}
