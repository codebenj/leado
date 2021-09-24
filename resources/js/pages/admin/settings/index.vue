<template>
  <Section className="time-settings" :pageTitle="pageTitle">
    <template v-slot:button>
      <el-button class="fl-right" type="primary" @click="addNewSettings()">
        Add New Setting
      </el-button>
    </template>
    <template v-slot:content>
      <el-card class="box-card b-none" shadow="never">
        <el-row class="m-b-lg p-r-xl" :gutter="20">
          <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
            <el-input
              v-model="filters[0].value"
              placeholder="Search..."
              clearable
            />
          </el-col>
        </el-row>

        <data-tables-server
          :data="settings"
          :total="total"
          :loading="loading"
          :filters="filters"
          :pagination-props="{ pageSizes: [10, 15, 20] }"
          @selection-change="handleSelectionChange"
          @query-change="loadMore"
        >
          <el-table-column type="selection" width="55" />
          <el-table-column prop="metadata.level" label="Escalation Level" width="150" />
          <el-table-column prop="metadata.status" label="Escalation Status" />
          <el-table-column prop="name" label="Name" width="300" />
          <el-table-column prop="key" label="Key" width="250" />
          <el-table-column prop="value" label="Value" />
          <el-table-column prop="metadata.type" label="Type" />
          <el-table-column prop="metadata.admin_tooltip" label="Admin Tooltip">
            <template slot-scope="{ row }">
              <el-tooltip
                class="item"
                effect="dark"
                :content="`${
                  row.metadata.admin_tooltip
                    ? row.metadata.admin_tooltip
                    : 'No description'
                }`"
                placement="top-start"
              >
                <i class="el-icon-info" />
              </el-tooltip>
            </template>
          </el-table-column>

          <el-table-column prop="metadata.org_tooltip" label="Org Tooltip">
            <template slot-scope="{ row }">
              <el-tooltip
                class="item"
                effect="dark"
                :content="`${
                  row.metadata.org_tooltip
                    ? row.metadata.org_tooltip
                    : 'No description'
                }`"
                placement="top-start"
              >
                <i class="el-icon-info" />
              </el-tooltip>
            </template>
          </el-table-column>

          <el-table-column
            label=""
            width="55"
            align="center"
            class-name="action b-none"
          >
            <template slot-scope="row">
              <el-dropdown trigger="click">
                <span class="el-dropdown-link">
                  <i class="el-icon-caret-bottom"></i>
                </span>
                <el-dropdown-menu slot="dropdown">
                  <el-dropdown-item
                    icon="el-icon-edit"
                    @click.native="editSettings(row)"
                    >Edit</el-dropdown-item
                  >
                  <el-dropdown-item
                    icon="el-icon-delete"
                    @click.native="deleteSettings(row)"
                    >Delete</el-dropdown-item
                  >
                </el-dropdown-menu>
              </el-dropdown>
            </template>
          </el-table-column>
        </data-tables-server>
      </el-card>
      <el-dialog
        :title="dialogTitle"
        v-dialogDrag
        ref="dialog__wrapper"
        :visible.sync="dialogVisible"
        :show-close="false"
        append-to-body
        width="30%"
      >
        <SettingsForm />
      </el-dialog>
    </template>
  </Section>
</template>
<script>
import { mapGetters } from "vuex";
import { DataTables, DataTablesServer } from "vue-data-tables";
import Swal from "sweetalert2";
import Modal from "~/components/Modal";
import SettingsForm from "~/pages/admin/settings/form";
import Section from "~/components/Section";

export default {
  layout: "master",
  middleware: ["auth"],

  components: {
    Modal,
    SettingsForm,
    DataTablesServer,
    Section,
  },

  data: () => ({
    pageTitle: "Escalation Settings",
    dialogTitle: "Add New Settings",
    filters: [
      {
        props: "search",
        value: "",
      },
    ],
  }),

  computed: mapGetters({
    settings: "settings/settings",
    total: "settings/total",
    loading: "settings/loading",
    dialogVisible: "settings/dialogVisible",
  }),

  methods: {
    async loadMore(queryInfo) {
      this.$store.dispatch("settings/fetchSettings", queryInfo);
    },

    handleClose(done) {
      this.$store.dispatch("settings/setDialog", true);
    },

    handleSelectionChange() {},

    addNewSettings() {
      this.$store.dispatch("settings/editSettings");
    },

    editSettings({ row }) {
      // dispatch the modal open
      this.dialogTitle = "Edit Settings";
      this.$store.dispatch("settings/editSettings", row);
    },

    deleteSettings({ row }) {
      Swal.fire({
        title: "Are you sure to delete this?",
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
      }).then((result) => {
        if (result.value) {
          this.$store
            .dispatch("settings/deleteSettings", row.id)
            .then(({ success, message }) => {
              if (success) {
                Swal.fire({
                  title: "Success!",
                  text: message,
                  type: "success",
                });
              }
            });
        }
      });
    },
  },
};
</script>
