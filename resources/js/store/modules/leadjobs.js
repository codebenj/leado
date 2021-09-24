import * as types from '../mutation-types'
import axios from 'axios'
import Form from 'vform'

export const state = {
  leadjobs: [],
  leadJob: {},
  loading: false,
  total: 0,
  totalSales: 0,
  leadJobForm: new Form({
    lead_id: "",
    meters_gutter_edge: "",
    meters_valley: "",
    comments: "",
    sale: ""
  }),
  leadJobFormRules:{
    meters_gutter_edge: [
      { required: true, message: 'Please input meters of gutters edge', trigger: 'blur' },
    ],
    meters_valley: [
      { required: true, message: 'Please input meters of valley', trigger: 'blur' },
    ],
    comments: [
      { required: true, message: 'Please input comment', trigger: 'blur' },
    ],
    sale: [
      { required: true, message: 'Please input sale', trigger: 'blur' },
    ]
  }
}

export const getters = {
  leadJob: state => state.leadJob,
  leadjobs: state => state.leadjobs,
  loading: state => state.loading,
  total: state => state.total,
  leadJobForm: state => state.leadJobForm,
  leadJobFormRules: state => state.leadJobFormRules,
  totalSales: state => state.totalSales
}

export const actions = {
  async getTotalSales( {commit}, lead_id){
    const {data} = await axios.get(`/api/v1/lead/jobs/sales-total/${lead_id}`)
    commit(types.FETCH_LEAD_JOB_TOTAL_SALES, { data: data.data })
  },

  async getLeadJobs( {commit}, payload){
    state.loading = true
    var parameters = { "pageNo" : payload.query.page ? payload.query.page : 1, "pageSize" : payload.query.pageSize ? payload.query.pageSize : 10, "lead_id" : payload.lead_id }
    const {data} = await axios.get(`/api/v1/lead/jobs`, { params: parameters })
    commit(types.FETCH_LEAD_JOBS, { data: data.data, total: data.total})
  },

  async create( {commit}){
    try{
      const {data} = await state.leadJobForm.post('/api/v1/lead/jobs')
      commit(types.FETCH_LEAD_JOB, { data: data.data })
      return data
    }
    catch(error){console.log(error.message)}
  }
}

export const mutations = {
  [types.FETCH_LEAD_JOB_TOTAL_SALES] (state, {data}){
    state.totalSales = data
  },

  [types.FETCH_LEAD_JOB] (state, {data}){
    state.leadJob = data
  },

  [types.FETCH_LEAD_JOBS] (state, {data, total}){
    state.total = total ? total : 0
    state.leadjobs = data.length ? data : []
    state.loading = false
  }
}
