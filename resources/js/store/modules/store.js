import axios from 'axios'
import * as types from '../mutation-types'
import Form from 'vform'

export const state = {
  store: {},
  stores: [],
  histories: [],
  historyTotal: 0,
  loading: false,
  total: 0,
  storeForm: new Form({
    id: 0,
    name: '',
    street_address: '',
    suburb: '',
    state: '',
    postcode: '',
    phone_number: '',
    contact: '',
    keep_stock: '',
    code: '',
    last_year_sales: '',
    year_to_date_sales: '',
    pricing_book: '',
    priority: '',
    stock_kits: ''
  }),
  storeFormRules: {
    name: [
      { required: true, message: 'Please input store name', trigger: 'blur' },
    ],
    street_address: [
      { required: true, message: 'Please input store address', trigger: 'blur' },
    ],
    suburb: [
      { required: true, message: 'Please input store suburb', trigger: 'blur' },
    ],
    postcode: [
      { required: true, message: 'Please input store postcode', trigger: 'blur' },
    ],
    phone_number: [
      { required: true, message: 'Please input store contact number', trigger: 'blur' },
    ],
    code: [
      { required: true, message: 'Please input store code', trigger: 'blur' },
    ],
  },
  enquirerForm: new Form({
    emails: '',
    messages: '',
    ids: []
  }),
  enquirerFormRules: {
    emails: [
      { required: true, message: 'Please input emails', trigger: 'blur' },
    ],
  },
  showStore: false,
  showId: 0,
  logs: [],
  loadingLogs: false,
  logsTotal: 0
}

export const getters = {
  store: state => state.store,
  stores: state => state.stores,
  loading: state => state.loading,
  total: state => state.total,
  storeForm: state => state.storeForm,
  storeFormRules: state => state.storeFormRules,
  showStore: state => state.showStore,
  showId: state => state.showId,
  enquirerForm: state => state.enquirerForm,
  enquirerFormRules: state => state.enquirerFormRules,
  histories: state => state.histories,
  historyTotal: state => state.historyTotal,
  logs: state => state.logs,
  loadingLogs: state => state.loadingLogs,
  logsTotal: state => state.logsTotal,
}

export const mutations = {
  [types.GET_STORES_LOGS] (state, {data, total}){
    state.logsTotal = total
    state.logs = data
    state.loadingLogs = false
  },

  [types.GET_STORES] (state, { data, total }){
    state.stores = data
    state.total = total
    state.loading = false
  },

  [types.SHOW_STORES] (state, { show, show_id }){
    state.showStore = show
    state.showId = show_id
  },

  [types.SAVE_STORES] (state, { data }){
    const index = state.stores.findIndex(store => store.id === data.id);

    if (index !== -1) {
      state.stores.splice(index, 1, data);
    } else {
      state.stores.push(data);
    }
    state.storeForm.reset()
    state.loading = false
  },

  [types.SAVE_STORE] (state, {data}){
    state.storeForm.fill(data)
  },

  [types.DELETE_STORES] (state, {data}){
    data.forEach(function (item) {
      const index = state.stores.findIndex(store => store.id === item.id);

      if (index !== -1) {
        state.stores.splice(index, 1)
      }
    })
  },

  [types.GET_HISTORIES] (state, {data, total}){
    state.histories = data
    state.historyTotal = total
    state.loading = false
  }
}

export const actions = {
  async deleteLogs({ commit }, ids) {
    try {
      const { data } = await axios.post(`/api/v1/store/import/logs/delete`, { ids: ids})
      return data;
    } catch (e) {
      console.log(e.message);
    }
  },

  async logs( {commit}, query){
    try {
      let parameters = { pageNo: query.page, pageSize: query.pageSize };

      const { data } = await axios.get(`/api/v1/store/import/logs`, { params: parameters })
      commit(types.GET_STORES_LOGS, { data: data.data.data, total: data.data.total })
    } catch (e) {
      console.log(e.message);
    }
  },

  async history( {commit}, query){
    try {
      state.loading = true
      let params = {
        pageNo: query.page,
        pageSize: query.pageSize ,
        code: query.filters[0] ? query.filters[0].value : '',
        name: query.filters[1] ? query.filters[1].value : '',
        enquirer_email: query.filters[2] ? query.filters[2].value : '',
        suburb_postcode: query.filters[3] ? query.filters[3].value : '',
        state: query.filters[4] ? query.filters[4].value : '',
      }

      const { data } = await axios.get(`/api/v1/store/sent/history`, { params: params })
      commit(types.GET_HISTORIES, { data: data.data.sents, total: data.data.total })

      return data
    } catch (e) {
      console.log(e.message);
    }
  },

  async sent( {commit}, store_ids ){
    try {
      state.enquirerForm.ids = store_ids
			const { data } = await state.enquirerForm.post(`/api/v1/store/sent`)
      return data
    } catch (e) {
      console.log(e.message);
    }
  },

  async export({ commit }, ids) {
    try {
      const { data } = await axios.post(`/api/v1/store/export`, { ids: ids }, { responseType: "blob" } )

      return data
    } catch (e) {
      console.log(e.message);
    }
  },

  async massDelete( {commit}, show_ids){
    try {
      console.log(show_ids)
      const { data } = await axios.post(`/api/v1/store/delete/mass`, { ids: show_ids })
      commit(types.DELETE_STORES, { data: data.data })

      return data
    } catch (e) {
      console.log(e.message)
    }
  },

  async openStore( {commit}, show_id){
    commit(types.SHOW_STORES, { show: true, show_id: show_id })
  },

  async closeStore( {commit}){
    commit(types.SHOW_STORES, { show: false, show_id: 0})
  },

  async getStore( {commit}, show_id ){
    try{
      const { data } = await axios.get(`/api/v1/stores/${show_id}`)

      commit(types.SAVE_STORE, { data: data.data })
      return data
    }
    catch(e){
      console.log(e.message)
    }
  },

  async saveStore({ commit }) {
		try {
			state.loading = true
			const url = state.storeForm.id ? `/api/v1/stores/${state.storeForm.id}` : `/api/v1/stores`
			const { data } = await (state.storeForm.id ? state.storeForm.put(url) : state.storeForm.post(url))
			commit(types.SAVE_STORES, { data: data.data })

      return data;
		} catch (error) {
			return state.storeForm.errors
		}
	},

  async fetch( {commit}, queryInfo ){
    try{
      state.loading = true

      let params = {
				pageNo: queryInfo.page ? queryInfo.page : 1,
				pageSize: queryInfo.pageSize ? queryInfo.pageSize : 20,
        code: (queryInfo.filters != null) ? ((queryInfo.filters[0].value != null) ? queryInfo.filters[0].value : "") : "",
        postcode: (queryInfo.filters != null) ? ((queryInfo.filters[1].value != null) ? queryInfo.filters[1].value : "") : "",
        year_to_date_sales: (queryInfo.filters != null) ? ((queryInfo.filters[2].value != null) ? queryInfo.filters[2].value : "") : "",
        last_year_sales: (queryInfo.filters != null) ? ((queryInfo.filters[3].value != null) ? queryInfo.filters[3].value : "") : "",
        priority: (queryInfo.filters != null) ? ((queryInfo.filters[4].value != null) ? queryInfo.filters[4].value : "") : "",
        stock_kits: (queryInfo.filters != null) ? ((queryInfo.filters[5].value != null) ? queryInfo.filters[5].value : "") : "",
        distance: (queryInfo.filters != null) ? ((queryInfo.filters[6].value != null) ? queryInfo.filters[6].value : "") : "",
			};

      const { data } = await axios.get(`/api/v1/stores`, { params })
      commit(types.GET_STORES, {data: data.data.stores, total: data.data.total})
    }catch(e){ console.log(e.message) }
  },
}
