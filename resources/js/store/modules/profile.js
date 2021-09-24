import axios from 'axios'
import * as types from '../mutation-types'
import Form from 'vform'

export const state = {
  profile: {},
  profileForm: new Form({
    company_name: "",
    first_name: "",
    last_name: "",
    email: "",
    phone: "",
    address_search: "",
    address: "",
    city: "",
    suburb: "",
    state: "",
    postcode: "",
    country_id: "",
    country: "",
  }),
  profileFormRules: {
    company_name: [
      { required: true, message: 'Please input company name', trigger: 'blur' },
    ],
    first_name: [
      { required: true, message: 'Please input first name', trigger: 'blur' },
    ],
    last_name: [
      { required: true, message: 'Please input last name', trigger: 'blur' },
    ],
    email: [
      { required: true, message: 'Please input email address', trigger: 'blur' },
    ],
    phone: [
      { required: true, message: 'Please input phone/contact number', trigger: 'blur' },
    ],
    // address: [
    //   { required: true, message: 'Please input address', trigger: 'blur' },
    // ],

		// address_search: [
		// 	{ required: true, message: 'Please input complete address (should have state, postcode, and country)', trigger: 'blur' },
		// ],
    // city: [
    //   { required: true, message: 'Please input city', trigger: 'blur' },
    // ],
    // suburb: [
    //   { required: true, message: 'Please input suburb', trigger: 'blur' },
    // ],
    // state: [
    //   { required: true, message: 'Please input state', trigger: 'blur' },
    // ],
    // postcode: [
    //   { required: true, message: 'Please input postcode', trigger: 'blur' },
    // ],
    // country_id: [
    //   { required: true, message: 'Please input country', trigger: 'blur' },
    // ]
  }
}

export const getters = {
  profile: state => state.profile,
  profileForm: state => state.profileForm,
  profileFormRules: state => state.profileFormRules,
}

export const mutations = {
  [types.SAVE_PROFILE] (state, {data}){
    state.profile = data
  },

  [types.FETCH_PROFILE] (state, {data, roles}){
    state.profileForm.first_name = data.first_name
    state.profileForm.last_name = data.last_name
    state.profileForm.email = data.email
    //state.profileForm.phone = data.phone ?? ""
    const is_organisation = roles.findIndex(role => role === 'organisation')

    if(is_organisation !== -1){
      state.profileForm.phone = data.organisation_user.organisation.contact_number ?? ""
      state.profileForm.state = data.organisation_user.organisation.address ? data.organisation_user.organisation.address.state : ""
      state.profileForm.postcode = data.organisation_user.organisation.address ? data.organisation_user.organisation.address.postcode : ""
      //state.profileForm.company_name = data.organisation_user.organisation.name ? data.organisation_user.organisation.name : ""
      //state.profileForm.address = data.address ? data.address.address : ""
      //state.profileForm.city = data.address ? data.address.city : ""
      //state.profileForm.suburb = data.address ? data.address.suburb : ""
      //state.profileForm.country_id = data.address ? data.address.country_id : ""
    }else{
      state.profileForm.phone = data.phone
      state.profileForm.state = data.address.state ?? ''
      state.profileForm.postcode = data.address.postcode ?? ''
      //state.profileForm.company_name = data.metadata['company_name'] ?? ""
    }

    //state.profileForm.address_search = data.address.metadata['address_search'] ?? ""
    //state.profileForm.state = data.address ? data.address.state : ""
    //state.profileForm.postcode = data.address ? data.address.postcode : ""
  },
}

export const actions = {
  async get( {commit}){
    try{
      const {data} = await state.profileForm.get(`/api/v1/users/profile`)
      commit(types.FETCH_PROFILE, {data: data.data, roles: data.roles})
      return data
    }
    catch(e){console.log(e.message)}
  },

  async save( {commit}, id){
    try{
      const {data} = await state.profileForm.post(`/api/v1/users/profile`)
      commit(types.SAVE_PROFILE, {data: data.data})
      return data
    }
    catch(e){console.log(e.message)}
  }
}
