import axios from 'axios'
import * as types from '../mutation-types'
import Form from 'vform'

export const state = {
  tasks: [],
  status: [],
  loading: false,
	taskForm: new Form({
		'id': '',
		'name': '',
		'user_ids': '',
    'status_id': '',
		'created_at': '',
	}),
  taskFormRules: {
		name: [
			{ required: true, message: 'Please add a task name' },
		]
	},
  newTaskDialogVisible: false,
  newTaskType: 1,
  assignUserToTaskForm: new Form({
		allUsers: []
	}),
  assignedUsers: [],
}

export const getters = {
  tasks: state => state.tasks,
  status: state => state.status,
	taskForm: state => state.taskForm,
	taskFormRules: state => state.taskFormRules,
	newTaskDialogVisible: state => state.newTaskDialogVisible,
	newTaskType: state => state.newTaskType,
	assignUserToTaskForm: state => state.assignUserToTaskForm,
	assignedUsers: state => state.assignedUsers,
}

export const mutations = {
  // fetch tasks
  [types.FETCH_TASKS](state, { tasks }) {
    state.tasks = tasks.data;
    state.loading = false;
  },

  // fetch status
  [types.FETCH_STATUS](state, { status }) {
    state.status = status.data;
		state.loading = false;
  },

  [types.SAVE_TASK] (state, {taskDetails}) {
	  try{
    state.tasks.map((status, index) => {
      let taskIndex = status.tasks.findIndex(task => task.id === taskDetails.id)
      if ( taskIndex !== -1) {
        state.tasks[index].tasks.splice(taskIndex, 1, taskDetails)
      } else if (taskDetails.status_id === status.id) {
        state.tasks[index].tasks.push(taskDetails)
      }
    });
	  }
	  catch(error){}

		// state.loading = false
	},
  [types.DELETE_TASK] (state, taskDetails) {
    state.tasks.map((status, index) => {
      let taskIndex = status.tasks.findIndex(task => task.id === taskDetails.id)
      if ( taskIndex !== -1) {
        state.tasks[index].tasks.splice(taskIndex, 1)
      }
    });
  },

  [types.GET_ASSIGNED_TASK_USERS] ( state, data ) {
      let ids = []
      if ( data.length > 0 ) {
        data.forEach( el => {
          ids.push( el.id )
        } )
      }

      state.assignUserToTaskForm.allUsers = ids
      state.assignedUsers = data;
  },

}

export const actions = {

  async fetchScrumTasks({ commit }, payload) {
    try {
      state.loading = true;

      const { data } = await axios.get(`/api/v1/scrum/tasks`);

      commit(types.FETCH_TASKS, {
        tasks: data
      });
    } catch (error) {}
  },

  async fetchScrumStatuses({ commit }, payload) {
    try {
      state.loading = true;

      const { data } = await axios.get(`/api/v1/scrum/status`);

      commit(types.FETCH_STATUS, {
        status: data
      });
    } catch (error) {}
	},

  async saveTask ({ commit }) {
		try {
			state.loading = true
      console.log(state.taskForm)
			const saveURL = state.taskForm.id ? `/api/v1/scrum/tasks/${state.taskForm.id}` : `/api/v1/scrum/tasks`
			const { data } = await (state.taskForm.id ? state.taskForm.put(saveURL) : state.taskForm.post(saveURL))

			commit(types.SAVE_TASK, { taskDetails: data.data })
			return data;
		} catch (error) {console.log(error.message)}
	},

  async setDialog({ commit }, { close }) {
    state.newTaskDialogVisible = !close
	},

  async setNewTaskType({ commit }, { type }) {
    state.newTaskType = type
	},

  async deleteTask({ commit }, { task_id }) {
    try {
			const { data } = await axios.post(`/api/v1/scrum/task/delete`, {
        id: task_id
      });

			commit(types.DELETE_TASK, {id: task_id})
			return data;
		} catch (error) {console.log(error.message)}
	},

  async updateTaskStatus({ commit }, { task_id, new_status_id }) {
    try {
			const { data } = await axios.post(`/api/v1/scrum/update/task_status`, {
        id: task_id,
        new_status_id: new_status_id
      });

			return data;
		} catch (error) {console.log(error.message)}
	},

    async assignUserToTask({ commit }, obj) {
		try{
			state.assignUserToTaskForm.allUsers = obj.ids

			const { data } = await state.assignUserToTaskForm.post(`/api/v1/scrum/assign_user_to_task/${obj.task_id}`);
			return data.data
		}
		catch(error){
			console.log(error.message)
		}
	},

	async fetchAssignedUsers({ commit }, id) {
		try {
			const { data } = await axios.get( `/api/v1/scrum/fetch_assigned_users/${id}` )
			commit( types.GET_ASSIGNED_TASK_USERS, data.data )
			return data.data
		} catch( error ) {
			console.log( error.message )
		}
	},

	async removeAssignedUser({ commit }, obj) {
		try {
			state.assignUserToTaskForm.allUsers = obj.ids

      const { data } = await state.assignUserToTaskForm.post(`/api/v1/scrum/assign_user_to_task/${obj.task_id}`)
			return data.data

		} catch (error) {
			console.log(error.message)
		}
	},

}
