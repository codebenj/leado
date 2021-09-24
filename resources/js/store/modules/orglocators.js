import axios from "axios";
import * as types from "../mutation-types";
import Form from "vform";

export const state = {
  orglocators: [],
  total: 0,
  orglocatorForm: new Form({
    id: "",
    org_id: "",
    name: "",
    street_address: "",
    suburb: "",
    state: "",
    postcode: "",
    phone: "",
    last_year_sales: "",
    year_to_date_sales: "",
    keeps_stock: "",
    pricing_book: "",
    priority: "",
    address_search: "",
  }),
  loading: true,
  loading_logs: true,
  orgLocatorFormRules: {
    org_id: [
      {
        required: true,
        message: "Please input organisation ID",
        trigger: "blur"
      }
    ],
    name: [
      {
        required: true,
        message: "Please input organisation name",
        trigger: "blur"
      }
    ],
    phone: [
      { required: true, message: "Please input phone number", trigger: "blur" }
    ],
    address_search: [
      {
        required: true,
        message: "Please input address",
        trigger: "blur"
      }
    ],
    // last_year_sales: [
    //   {
    //     required: true,
    //     message: "Please input last year sales",
    //     trigger: "blur"
    //   }
    // ],
    // year_to_date_sales: [
    //   {
    //     required: true,
    //     message: "Please input year to date sales",
    //     trigger: "blur"
    //   }
    // ],
    street_address: [
      { required: true, message: "Please input address", trigger: "blur" }
    ],
    suburb: [
      { required: true, message: "Please input suburb", trigger: "blur" }
    ],
    postcode: [
      { required: true, message: "Please input postcode", trigger: "blur" }
    ],
    state: [
      { required: true, message: "Please input state", trigger: "blur" }
    ],
    // keeps_stock: [
    //   { required: true, message: "Please input stock kits", trigger: "blur" }
    // ],
    // pricing_book: [
    //   { required: true, message: "Please input pricing book", trigger: "blur" }
    // ],
    // priority: [
    //   { required: true, message: "Please input priority", trigger: "blur" }
    // ]
  },
  states: ["", "NSW", "QLD", "VIC", "SA", "WA", "ACT", "TAS", "NT"],
  logs: [],
  logs_total: 0
};

export const getters = {
  orglocators: state => state.orglocators,
  orglocatorForm: state => state.orglocatorForm,
  orgLocatorFormRules: state => state.orgLocatorFormRules,
  loading: state => state.loading,
  states: state => state.states,
  logs: state => state.logs,
  loading_logs: state => state.loading_logs,
  total: state => state.total,
  logs_total: state => state.logs_total
};

export const mutations = {
  [types.FETCH_ORG_LOCATOR](state, { orglocators, total }) {
    state.orglocators = orglocators;
    state.total = total ? total : 0;
    state.loading = false;
  },

  [types.SAVE_ORG_LOCATOR](state, { data }) {
    const index = state.orglocators.findIndex(org => org.id === data.id);

    if (index !== -1) {
      state.orglocators.splice(index, 1, data);
    } else {
      state.orglocators.push(data);
    }
  },

  [types.EDIT_ORG_LOCATOR](state, { orglocators }) {
    state.orglocatorForm.reset();
    state.orglocatorForm.fill(orglocators);
    state.orglocatorForm.address_search = orglocators.metadata['address_search'];

  },

  [types.DELETE_ORG_LOCATOR](state, id) {
    const index = state.orglocators.findIndex(
      orglocator => orglocator.id === id
    );
    if (index !== -1) {
      state.orglocators.splice(index, 1);
    }
  },

  [types.IMPORT_ORG_LOCATOR](state, { data, total }) {
    state.logs = data.length ? data : [];
    state.logs_total = total ? total : 0;
    state.loading_logs = false;
  }
};

export const actions = {
  async fetchOrgLocators({ commit }, queryInfo) {
    try {
    state.loading = true;

    const params = {
        pageNo: queryInfo.page ? queryInfo.page : 1,
        pageSize: queryInfo.pageSize ? queryInfo.pageSize : 10,
        search: queryInfo.filters && queryInfo.filters[0] ? queryInfo.filters[0].value : ""
      };

	  const { data } = await axios.get(`/api/v1/org-locator`, { params });

	  commit(types.FETCH_ORG_LOCATOR, {
        orglocators: data.data.org_locator,
        total: data.data.total
      });
    } catch (error) {
      console.log(error.message);
    }
  },

  async fetchSearchOrgLocators({ commit }, queryInfo) {
    try {
      state.loading = true;

      const params = {
        pageNo: queryInfo.page ? queryInfo.page : 1,
        pageSize: queryInfo.pageSize ? queryInfo.pageSize : 10,
        search: queryInfo.filters && queryInfo.filters[0] ? queryInfo.filters[0].value : "",
        keyword: queryInfo.keyword ?? "",
        kilometer: queryInfo.kilometer ?? "",
        ly_sale: queryInfo.ly_sale ?? "",
        postcode: queryInfo.postcode ?? "",
        priority: queryInfo.priority ?? "",
        //stock_kits: queryInfo.stock_kits ?? "",
        state: queryInfo.state ?? "",
        ytd_sale: queryInfo.ytd_sale ?? "",
      };

      const { data } = await axios.get(`/api/v1/org-locator`, { params });
      commit(types.FETCH_ORG_LOCATOR, {
        orglocators: data.data.org_locator,
        total: data.data.total
      });
    } catch (error) {
      console.log(error.message);
    }
  },

  async editOrgLocator({ commit }, id) {
    try {
      const { data } = await axios.get(`/api/org-locator/get/${id}`);
      commit(types.EDIT_ORG_LOCATOR, { orglocators: data.data });
      return data;
    } catch (error) {
      console.log(error.message);
    }
  },

  async saveOrgLocator({ commit }) {
    try {
      const { data } = await state.orglocatorForm.post("/api/org-locator/save");
      commit(types.SAVE_ORG_LOCATOR, { data: data.data });
      return data;
    } catch (error) {
      console.log(error.message);
    }
  },

  async deleteOrgLocator({ commit }, id) {
    try {
      const { data } = await axios.post(`/api/org-locator/delete`, { id: id });

      commit(types.DELETE_ORG_LOCATOR, id);
      return data;
    } catch (e) {
      console.log(e);
      return e;
    }
  },

  async massDelete({ commit }, org_ids) {
    try {
      const { data } = await axios.post(`/api/v1/org-locator/delete`, {
        ids: org_ids
      });
      return data;
    } catch (e) {
      console.log(e.message);
    }
  },

  async deleteAll({ commit }) {
    try {
      const { data } = await axios.post(`/api/v1/org-locator/delete-all`)
      return data;
    } catch (e) {
      console.log(e.message);
    }
  },

  async export({ commit }, org_ids) {
    try {
      const { data } = await axios.post(
        `/api/v1/org-locator/export`,
        { ids: org_ids },
        { responseType: "blob" }
      );
      return data;
    } catch (e) {
      console.log(e.message);
    }
  },

  async fetchImport({ commit }, query) {
    try {
      var parameters = { pageNo: query.page, pageSize: query.pageSize };

      const { data } = await axios.get("/api/v1/org-locator/logs", {
        params: parameters
      });
      commit(types.IMPORT_ORG_LOCATOR, {
        data: data.data.data,
        total: data.data.total
      });
      return data
    } catch (error) {
      console.log(error.message);
    }
  },

  async deleteLogs({ commit }, ids) {
    try {
      var parameters = { ids: ids };
      const { data } = await axios.post(`/api/v1/org-locator/logs/delete`, {
        ids: ids
      });

      return data;
    } catch (e) {
      console.log(e.message);
    }
  }
};
