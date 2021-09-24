<template>
  <Section :pageTitle="pageTitle">
    <template v-slot:content>
      <el-card class="box-card b-none" shadow="never">
        <div :key="componentKey">
          <el-row :gutter="24" >
            <el-col style="margin-bottom: 10px" :xs="24" :sm="24" :md="8" :lg="8" :xl="8" v-for="(stats, index) in tasks"
                      :key="index">
            <div class="board-col-container">
                <p>{{ stats.name }}</p>
                <div class="board-col">
                <div class="drag-content">
                  <draggable :list="stats.tasks" @change ="updateTaskStatus($event, stats.id)" :animation="200" ghost-class="ghost-card" group="tasks">
                    <!-- Each element from here will be draggable and animated. Note :key is very important here to be unique both for draggable and animations to be smooth & consistent. -->
                    <TaskCard
                      v-for="(task) in stats.tasks"
                      :key="task.id"
                      :task="task"
                      class="mt-3 cursor-move"
                    />
                    <div class="new-task">
                      <p class="detail " @click="newTask(stats)"><i class="el-icon-plus"></i> New Task</p>
                    </div>
                    <!-- </transition-group> -->
                  </draggable>
                </div>
                </div>
            </div>
            </el-col>
          </el-row>
        </div>
      <el-dialog
          :title="taskForm.id ? 'Edit Task' : taskDialogName"
          :visible="newTaskDialogVisible"
          :show-close="false"
          width="30%"
          v-dialogDrag
          ref="dialog__wrapper"
          append-to-body
        >
          <NewTask />
        </el-dialog>
      </el-card>
    </template>
  </Section>
</template>

<script>
import Section from "~/components/Section";
import TaskCard from "~/components/TaskCard";
import NewTask from "./scrum/newtask";
import { mapGetters } from "vuex";
import draggable from "vuedraggable";

export default {
  name: "ScrumBoard",

  layout: "master",

  middleware: ["auth"],

  components: {
    Section,
    TaskCard,
    NewTask,
    draggable,
  },

  computed: mapGetters({
    newTaskDialogVisible: "scrum/newTaskDialogVisible",
    filteredTasks: "scrum/filteredTasks",
    status: "scrum/status",
    tasks: "scrum/tasks",
    taskForm: "scrum/taskForm",
  }),

  data() {
    return {
      pageTitle: "Scrum Board",
      users: [],
      assigned_users: [],
      componentKey: 0,
      taskDialogName: ''
    };
  },
  methods: {
    updateTaskStatus(event, status_id) {
      if (event.hasOwnProperty('added')) {
        this.$store.dispatch('scrum/updateTaskStatus', {
          task_id: event.added.element.id,
          new_status_id: status_id,
        })
      }
    },

    newTask(_status) {
      this.taskForm.reset();
      this.taskDialogName = 'New ' +  _status.name + ' Task';
      this.$store.dispatch('scrum/setNewTaskType', {type: _status.id})
      this.$store.dispatch('scrum/setDialog', {close: false})
    },

    setTasks(status_id) {
      console.log(this.tasks.filter(x => x.status_id === status_id))
      return this.tasks.filter(x => x.status_id === status_id);
    }
  },

  async created() {
      await this.$store.dispatch("scrum/fetchScrumStatuses");
      await this.$store.dispatch("scrum/fetchScrumTasks").then(() => {
        this.componentKey += 1;
      });
  },

  mounted() {

  },
};
</script>

<style lang="scss" scoped>
  .detail{
    margin-bottom: 0;
    color: gray;
    font-size: 14px;
  }

  .new-task {
    padding: 0px 5px;
    transition: .3s;

    &:hover, :hover.el-icon-plus, :hover.detail {
      color: #303133;
      transition: .3s;
    }

    .detail {
      width: fit-content;
      cursor: pointer;
      transition: .3s;
    }
  }

  .el-col {
    border-radius: 6px;
  }

  // .drag-content {
  //   overflow: scroll;
  //   display: inline-block;
  //   vertical-align: top;
  //   height: 300px;
  // }

  .board-col-container {
    padding: 15px 10px;
    background: #F2F6FC;
    border-radius: 4px;
  }

  .board-col {
    max-height: 62vh;
    overflow: auto;
  }

  .ghost-card {
    opacity: 0.5;
    background: #F7FAFC;
  }

</style>
