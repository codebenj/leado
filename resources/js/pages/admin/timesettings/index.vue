<template>
  <Section className="time-settings" :pageTitle="pageTitle">
    <template v-slot:button>
       <el-button
          type="primary"
          class="fl-right"
          @click="addNewSettings()"
          >Add New Time</el-button
        >
    </template>
    <template v-slot:content>
      <el-card class="box-card b-none" shadow="never">
        <h4>Pause Timers</h4>

        <el-row :gutter="20">
            <TimeSettingItem v-for="timesetting in timesettings" :key="timesetting.id" :timeSetting="timesetting" />
        </el-row>
      </el-card>
      <el-dialog
        :title="dialogTitle"
        v-dialogDrag
        ref="dialog__wrapper"
        :visible.sync="dialogVisible"
        :show-close="false"
        append-to-body
        width="35%"
      >
        <TimesettingForm />
      </el-dialog>
    </template>
  </Section>
</template>
<script>
import { mapGetters } from "vuex";
import Swal from "sweetalert2";
import Modal from "~/components/Modal";
import Section from "~/components/Section";
import TimesettingForm from "~/components/timesettings/Form";
import TimeSettingItem from "~/components/timesettings/TimeSettingItem";

export default {
  layout: "master",
  middleware: ["auth"],

  components: {
    Modal,
    Section,
    TimesettingForm,
    TimeSettingItem
  },

  data: () => ({
    pageTitle: "Time Settings",
    filters: [
      {
        props: "search",
        value: "",
      },
    ],
  }),

  computed: mapGetters({
    timesettings: "timesetting/timesettings",
    total: "timesetting/total",
    loading: "timesetting/loading",
    dialogVisible: "timesetting/dialogVisible",
    dialogTitle: "timesetting/dialogTitle",
  }),

  methods: {
    addNewSettings() {
      this.$store.dispatch('timesetting/editTimesetting')
    },
  },

  beforeMount() {
     this.$store.dispatch("timesetting/fetchTimesettings")

  }
};
</script>
