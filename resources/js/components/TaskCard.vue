<template>
  <div>
    <el-card shadow="hover" class="task-card">
      <el-row :gutter="24">
        <el-col  :xs="16" :sm="16" :md="20" :lg="19" :xl="16">
          <p class="detail m-auto">{{task.name}}</p>
          <p class="date">{{ task.created_at | moment("k:mm DD/MM/YYYY")  }}</p>
        </el-col>
        <el-col :xs="8" :sm="8" :md="4" :lg="5" :xl="8">
          <AssignTaskPopover
          :id="task.id"
          :assigned_users="assigned_users"
          :type="'drawer'"
        />
        </el-col>
      </el-row>
      <div class="options">
        <el-row>
          <el-col>
            <el-dropdown>
              <span class="el-dropdown-link">
                <i class="el-icon-more"></i>
              </span>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item icon="el-icon-edit" @click.native="updateTask()"
                  >Edit</el-dropdown-item
                >
                <el-dropdown-item icon="el-icon-delete" @click.native="deleteTask()"
                  >Delete</el-dropdown-item
                >
              </el-dropdown-menu>
            </el-dropdown>
          </el-col>
        </el-row>
      </div>
    </el-card>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
import AssignTaskPopover from "~/components/AssignTaskPopover.vue";
import NewTask from "../pages/scrum/newtask.vue";
import Swal from "sweetalert2";

export default {
  name: 'TaskCard',

  components: {
    AssignTaskPopover,
    NewTask
  },

  computed: mapGetters({
    taskForm: "scrum/taskForm",
  }),

  props: {
    task: {
      type: Object,
      default: () => ({})
    },
  },

  data() {
    return {
      assigned_users: [],
      taskDialogName: 'Edit Task'
    };
  },

  mounted() {
  },

  methods: {
    deleteTask() {
      Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
      }).then((result) => {
        if (result.value) {
          return  this.$store.dispatch('scrum/deleteTask', {
            task_id: this.task.id
          }).then(({ success, message }) => {
              if (success) {
                Swal.fire({
                  title: "Success!",
                  text: message,
                  type: "success",
                });
              } else {
                Swal.fire({
                  title: "Oops!",
                  text: message,
                  type: "error",
                });
              }
            });
        }
      });
  },
  updateTask() {
    this.$store.dispatch('scrum/setDialog', {close: false})
    this.taskForm.id = this.task.id
    this.taskForm.name = this.task.name
    this.taskForm.user_ids = this.task.user_ids
    this.taskForm.status_id = this.task.status_id
  }
}
}
</script>

<style lang="scss" scoped>
  .m-auto {
    margin: auto;
  }

  .detail{
    margin-bottom: 0;
    color: gray;
    font-size: 14px;
  }

  .date {
    margin: 8px 0px 0px 0px;
    color: rgb(187, 187, 187);
    font-size: 12px;
  }

  .task-card {
    cursor: pointer;
    margin-bottom: 10px;
    position: relative;

    &:hover {
      .options {
        opacity: 1;
        transition: .2s;
      }
    }
  }

  .options {
    position: absolute;
    width: 100%;
    justify-content: flex-end;
    transition: .2s;
    opacity: 0;
    display: flex;
    margin-left: auto;
    margin-right: auto;
    left: 0;
    bottom: -8px;
    padding-right: 26px;
  }

  ::v-deep {
    .el-dropdown-link {
      font-size: 26px;
    }
  }
</style>
