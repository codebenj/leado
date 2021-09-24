import axios from "axios";
import * as types from "../mutation-types";
import Form from "vform";

// state
export const state = {
  timesettings: [],
  total: 0,
  loading: true,
  dialogVisible: false,
  dialogTitle: 'Add New Time Settings',
  timesettingForm: new Form({
    id: "",
    name: '',
    type: true, // if true then recurring
    start_date: '',
    start_time: '',
    stop_date: '',
    stop_time: '',
    start_day: '',
    stop_day: '',
    is_active: true,
  }),
  timesettingFormRules: {
    name: [
      { required: true, message: "Please input name", trigger: "blur" }
    ],
  },
  timesettingFormRuleOneTime: {
    start_date: [
        { required: true, message: "Please pick start date", trigger: "blur" }
    ],
    start_time: [
        { required: true, message: "Please pick start time", trigger: "blur" }
    ],
    stop_date: [
        { required: true, message: "Please pick stop date", trigger: "blur" }
    ],
    stop_time: [
        { required: true, message: "Please pick stop time", trigger: "blur" }
    ],
  },
  timesettingFormRuleRecurring: {
    start_day: [
        { required: true, message: "Please pick start day", trigger: "blur" }
    ],
    stop_day: [
        { required: true, message: "Please pick start day", trigger: "blur" }
    ],
  },
};

// getters
export const getters = {
  timesettings: state => state.timesettings,
  total: state => state.total,
  loading: state => state.loading,
  timesettingForm: state => state.timesettingForm,
  timesettingFormRules: state => state.timesettingFormRules,
  dialogVisible: state => state.dialogVisible,
  dialogTitle: state => state.dialogTitle,
};

// mutations
export const mutations = {
  // fetch timesettings
  [types.FETCH_TIME_SETTINGS](state, { timesettings }) {
    state.loading = false;
    state.timesettings = timesettings;
    state.loading = false;
  },

  // save timesetting
  [types.SAVE_TIME_SETTING](state, { data }) {
    const index = state.timesettings.findIndex(ts => ts.id === data.id);

    if (index !== -1) {
      state.timesettings.splice(index, 1, data);
    } else {
      state.timesettings.push(data);
    }

    state.timesettingForm.reset();
  },

  // edit timesetting
  [types.EDIT_TIME_SETTING](state, { timesetting }) {
    state.timesettingForm.reset();
    state.dialogTitle = timesetting ? 'Edit Time Setting' : 'Add New Time'

    if ( timesetting ) {
      state.timesettingForm.fill(timesetting);
      state.timesettingForm.type = timesetting.type == 'recurring'
    }
  },

  [types.DELETE_TIME_SETTING](state, { data }) {
    console.log(state);
    console.log(data);
  },

   // edit timesetting
  [types.CHANGE_TIME_SETTING_RULES](state) {
        state.timesettingFormRules =  state.timesettingForm.type 
            ? { ...state.timesettingFormRules, ...state.timesettingFormRuleOneTime }
            : { ...state.timesettingFormRules, ...state.timesettingFormRuleRecurring }
  },

  // clear timesetting form
  [types.CLEAR_TIME_SETTING_FORM](state) {
    state.timesettingForm.reset();
  },

  [types.DIALOG_SETTINGS_STATE] (state, close) {
		state.dialogVisible = close
  },  
};

// actions
export const actions = {
  // fetchTimesettings
  async fetchTimesettings({ commit }, query) {
    try {
      state.loading = true;
      const { data } = await axios.get(`/api/v1/admin/time-setting`);
      commit(types.FETCH_TIME_SETTINGS, {
        timesettings: data.data,
      });
      return { data };
    } catch (e) {
      return e;
    }
  },

  // editTimesetting
  async editTimesetting({ commit }, timesetting = null) {
    try {
      commit(types.EDIT_TIME_SETTING, { timesetting });
      commit(types.DIALOG_SETTINGS_STATE, true)

      return data;
    } catch (e) {
      return e;
    }
  },

  async deleteTimesetting({ commit }, id) {
    try {
      const { data } = await axios.post(`/api/v1/admin/time-setting/delete/${id}`);
      commit(types.DELETE_TIME_SETTING, { data });
      return data;
    } catch (e) {
      return e;
    }
  },

  // saveTimesetting
  async saveTimesetting({ commit }) {
    try {
      const { data } = await state.timesettingForm.post("/api/v1/admin/time-setting/save");
      commit(types.SAVE_TIME_SETTING, { data: data.data });
      return data;
    } catch (e) {
      console.log(e)
      return state.timesettingForm.errors;
    }
  },

  async changeTimesettingStatus({ commit }, id) {
    try {
      const { data } = await axios.post(`/api/v1/admin/time-setting/change-status/${id}`);
      commit(types.SAVE_TIME_SETTING, { data: data.data });
      return data;
    } catch (e) {
      console.log(e)
    }
  },

  // changetimesettingRule
  async changeTimesettingRules({ commit }) {
    try {
      commit(types.CHANGE_TIME_SETTING_RULES);
      return data;
    } catch (e) {}
  },

  // clearTimesettingsForm
  async clearTimesettingsForm({ commit }, id) {
    commit(types.CLEAR_TIME_SETTING_FORM);
  },

  async setDialog({ commit }, close) {
      commit(types.DIALOG_SETTINGS_STATE, close)
  },
};
