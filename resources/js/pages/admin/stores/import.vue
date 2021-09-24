<template>
  <div>
    <el-row :gutter="24">
      <el-col :xs="24" :sm="24" :md="16" :lg="16" :xl="16">
        <el-card class="box-card">
          <div slot="header" class="clearfix">
            <span>{{ cardTitle }}</span>
            <el-button class="fl-right r-btn-reset" type="primary" @click="deleteLogs"
              >Delete Log</el-button
            >
          </div>

          <data-tables-server
            :data="logs"
            :total="logs_total"
            :loading="loading_logs"
            :pagination-props="{ pageSizes: [5, 10, 15] }"
            @selection-change="handleSelectionChange"
            @query-change="loadMore"
          >
            <el-table-column type="selection" width="55"> </el-table-column>

            <el-table-column prop="file_name" label="File Name" sortable>
            </el-table-column>

            <el-table-column prop="updated_at" label="Uploaded Date" sortable>
              <template slot-scope="{ row }">
                <span>
                {{ row.updated_at | moment("k:mm DD/MM/YYYY") }}
                </span>
              </template>
            </el-table-column>
          </data-tables-server>
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
        <el-card class="box-card">
          <el-upload
            class="upload-demo text-center"
            ref="upload"
            action="/api/v1/store/import"
            name="import_file"
            :auto-upload="false"
            accept=".csv,.xls,xlsx"
            :on-success="uploadSuccess"
            :on-error="uploadError"
            :limit="1"
          >
            <div class="el-upload__tip" slot="tip">
              NOTE: THE FILE MUST BE CSV OR XLS OR XLSX
            </div>
            <el-button slot="trigger" type="primary"
              >Select File</el-button
            >
            <el-button class="m-l-sm"  type="success" @click="submitUpload"
              >Upload to server</el-button
            >
          </el-upload>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import { DataTables, DataTablesServer } from "vue-data-tables";
import { mapGetters } from "vuex";
import Swal from "sweetalert2";

export default {
  name: "StoresImport",

  layout: "master",

  middleware: ["auth"],

  components: {
    DataTablesServer,
  },

  computed: mapGetters({
    logs: "store/logs",
    loading_logs: "store/loadingLogs",
    logs_total: "store/logsTotal",
  }),

  data: () => ({
    cardTitle: "Import Stores",
    marginClass: "mb-0",
    tableId: "import_postcodes",
    filter: "",
    perPage: 10,
    fileList: [],
    logIds: [],
    query: {}
  }),

  methods: {
    deleteLogs() {
      this.$store
        .dispatch("store/deleteLogs", this.logIds)
        .then(({ success, message }) => {
          if (success) {
            Swal.fire({
              title: "Success!",
              text: message,
              type: "success",
            }).then(() => {
              this.$store.dispatch("store/logs", this.query)
            });
          }
        });
    },

    handleSelectionChange(val) {
      this.logIds = [];
      for (var i = 0; i < val.length; i++) {
        this.logIds.push(val[i].id);
      }
    },

    uploadError(err, file, fileList){
      console.log(err)
    },

    uploadSuccess(file) {
      if(!file.success){
        Swal.fire({
          title: file.message,
          html: file.error,
          type: "error",
        })
      }
      this.$store.dispatch("store/logs", this.query)

      setTimeout(() => {
        this.$refs.upload.clearFiles();
      }, 1000);
    },

    submitUpload() {
      this.$refs.upload.submit();
    },

    async loadMore(queryInfo) {
      this.query = queryInfo
      this.$store.dispatch("store/logs", this.query)
    },
  },

  mounted(){
    this.$store.dispatch("store/logs", this.query);
  },
};
</script>

<style scoped>
  @media all and (max-width: 992px){
    .el-col{
      margin-top: 10px;
    }
  }

  @media all and (max-width: 426px){
    .r-btn-reset{
      float: none;
    }
  }
</style>
