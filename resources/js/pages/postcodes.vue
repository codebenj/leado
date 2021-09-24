<template>
  <Section className="postcodes" :pageTitle="pageTitle">
    <template v-slot:button>
      <el-button class="fl-right r-btn-reset" type="primary" @click="deleteLogs"
        >Delete Log</el-button
      >
    </template>
    <template v-slot:content>
      <el-row :gutter="20">
        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
          <el-card class="box-card b-" shadow="never">
            <el-upload
              class="upload-demo"
              ref="upload"
              action="/api/v1/organisation/postcode/import"
              name="import_file"
              :auto-upload="false"
              accept=".csv,.xls,xlsx"
              :on-success="uploadSuccess"
              :limit="1"
            >
              <div class="el-upload__tip" slot="tip">
                NOTE: THE FILE MUST BE CSV OR XLS OR XLSX
              </div>
              <el-button slot="trigger" type="primary">Select File</el-button>
              <el-button type="success" @click="submitUpload"
                >Upload to server</el-button
              >
            </el-upload>
          </el-card>
        </el-col>
        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
          <el-card class="box-card b-none" shadow="never">
            <data-tables-server
              :data="logs"
              :total="logs.length"
              :loading="loading"
              :pagination-props="{ pageSizes: [10, 15, 20] }"
              @selection-change="handleSelectionChange"
              @query-change="loadMore"
            >
              <el-table-column type="selection" width="55" />

              <el-table-column prop="file_name" label="File Name"  sortable/>

              <el-table-column prop="updated_at" label="Uploaded Date" sortable>
                <template slot-scope="{ row }">
                  {{ row.created_at | moment("k:mm DD/MM/YYYY") }}
                </template>
              </el-table-column>

              <el-table-column
                label=""
                width="55"
                align="center"
                class-name="action b-none"
              >
              </el-table-column>
            </data-tables-server>
          </el-card>
        </el-col>
      </el-row>
    </template>
  </Section>
</template>

<script>
import { DataTables, DataTablesServer } from "vue-data-tables";
import { mapGetters } from "vuex";
import Swal from "sweetalert2";
import Section from "~/components/Section";

export default {
  name: "Postcodes",

  layout: "master",

  middleware: ["auth"],

  components: {
    Section,
    DataTablesServer,
  },

  computed: mapGetters({
    logs: "organisations/logs",
    loading: "organisations/loading",
  }),

  data: () => ({
    pageTitle: "Import Postcodes",
    marginClass: "mb-0",
    tableId: "import_postcodes",
    filter: "",
    perPage: 10,
    fileList: [],
    logIds: [],
  }),

  methods: {
    filenameFilterHandler(){

    },

    deleteLogs() {
      if (this.logIds.length) {
        this.$store
          .dispatch("organisations/deleteLogs", this.logIds)
          .then(({ success, message }) => {
            if (success) {
              Swal.fire({
                title: "Success!",
                text: message,
                type: "success",
              }).then(() => {
                this.$store.dispatch(
                  "organisations/fetchImportPostCodeLogs",
                  []
                );
              });
            }
          });
      } else {
        Swal.fire({
          title: "Oops!",
          text: "Please select logs to delete",
          type: "error",
        });
      }
    },

    handleSelectionChange(val) {
      this.logIds = [];
      for (var i = 0; i < val.length; i++) {
        this.logIds.push(val[i].id);
      }
    },

    uploadSuccess(file) {
      this.$store.dispatch("organisations/fetchImportPostCodeLogs", []);

      setTimeout(() => {
        this.$refs.upload.clearFiles();
      }, 1000);
    },

    submitUpload() {
      this.$refs.upload.submit();
    },

    async loadMore(queryInfo) {
      this.$store.dispatch("organisations/fetchImportPostCodeLogs", queryInfo);
    },
  },

  beforeMount() {
    this.$store.dispatch("organisations/fetchImportPostCodeLogs", []);
  },
};
</script>

<style scoped>
  @media all and (max-width: 426px){
    .r-btn-reset{
      float: none;
    }
  }
</style>
