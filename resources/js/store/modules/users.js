import axios from "axios";
import * as types from "../mutation-types";
import Form from "vform";

// state
export const state = {
  users: [],
  total: 0,
  loading: true,
  userForm: new Form({
    user_id: "",
    first_name: "",
    last_name: "",
    email: "",
    password: "",
    is_admin: false
  }),
  userFormRules: {
    first_name: [
      { required: true, message: "Please input first name", trigger: "blur" }
    ],
    last_name: [
      { required: true, message: "Please input last name", trigger: "blur" }
    ],
    email: [
      {
        required: true,
        message: "Please input email address",
        trigger: "blur"
      },
      { type: "email", trigger: ["blur", "change"] }
    ]
  }
};

// getters
export const getters = {
  users: state => state.users,
  total: state => state.total,
  loading: state => state.loading,
  userForm: state => state.userForm,
  userFormRules: state => state.userFormRules
};

// mutations
export const mutations = {
  // fetch users
  [types.FETCH_USERS](state, { users, total }) {
    state.loading = false;
    state.users = users;
    state.total = total ? total : 0;
    state.loading = false;
  },

  // save users
  [types.SAVE_USER](state, { data }) {
    const index = state.users.findIndex(user => user.id === data.id);

    if (index !== -1) {
      state.users.splice(index, 1, data);
    } else {
      state.users.push(data);
    }
  },

  // edit users
  [types.EDIT_USER](state, { user }) {
    state.userForm.reset();
    state.userForm.fill(user);
    state.userForm.user_id = user.id;
    state.userForm.is_admin = (user.role_id == 2) ? true : false;

    state.userFormRules.password = id
      ? []
      : [{ required: true, message: "Please input password", trigger: "blur" }];
  },

  // clear user form
  [types.CLEAR_USER_FORM](state) {
    state.userForm.reset();
  },

  // delete users
  [types.DELETE_USER](state, id) {
    const index = state.users.findIndex(user => user.id === id);
    if (index !== -1) {
      state.users.splice(index, 1);
    }
  }
};

// actions
export const actions = {
  // featchUsers
  async fetchUsers({ commit }, query) {
    try {
      state.loading = true;
      let parameters = {
        pageNo: query.page ? query.page : 1,
        pageSize: query.pageSize ? query.pageSize : 10,
        search: query.filters[0] ? query.filters[0].value : "",
        role: query.filters[1] ? query.filters[1].value : "",
      };

      const { data } = await axios.get(`/api/v1/users`, { params: parameters });
      commit(types.FETCH_USERS, {
        users: data.data.users,
        total: data.data.total
      });
      return { data };
    } catch (e) {
      return e;
    }
  },

  // saveUser
  async saveUser({ commit }) {
    try {
      const { data } = await state.userForm.post("/api/admin/user/save");
      commit(types.SAVE_USER, { data: data.data });
      return data;
    } catch (e) {
      return state.userForm.errors;
    }
  },

  // clearUserForm
  async clearForm({ commit }, id) {
    commit(types.CLEAR_USER_FORM);
  },

  // editUser
  async editUser({ commit }, id) {
    try {
      const { data } = await axios.get(`/api/admin/user/get/${id}`);
      commit(types.EDIT_USER, { user: data.data });

      return data;
    } catch (e) {
      return e;
    }
  },

  // deleteUser
  async deleteUser({ commit }, id) {
    try {
      const { data } = await axios.post(`/api/admin/user/delete/`, {
        user_id: id
      });

      commit(types.DELETE_USER, id);
      return data;
    } catch (e) {
      console.log(e);
      return e;
    }
  },

  async deleteUsers( {commit}, ids){
    try{
      const {data} = await axios.post(`/api/v1/users/delete`, { ids: ids })
      return data

    }catch(e){console.log(e.message)}
  },

  async export( {commit}, ids){
    try{
      const { data } = await axios.post(`/api/v1/users/export`, { ids: ids }, {responseType: 'blob'})
      return data
    }
    catch(e){
      console.log(e.message)
    }
  },
};
