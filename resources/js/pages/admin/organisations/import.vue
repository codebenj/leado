<template>
	<div>
		<el-row :gutter="24">
			<el-col :xs="24" :sm="24" :md="16" :lg="16" :xl="16">
				<el-card class="box-card">
					<div slot="header" class="clearfix">
						<span>{{ cardTitle }}</span>
            <el-button class="fl-right r-btn-reset" type="primary" @click="deleteLogs" >Delete Log</el-button>
					</div>

					<data-tables-server
						:data="logs"
						:total="log_count"
						:loading="loading"
						:pagination-props="{ pageSizes: [5, 10, 15] }"
						@selection-change="handleSelectionChange"
            @query-change="loadMore"
					>
						<el-table-column
							type="selection"
							width="55">
						</el-table-column>

						<el-table-column
							prop="file_name"
							label="File Name">
						</el-table-column>

						<el-table-column
							prop="updated_at"
							label="Uploaded Date">
						</el-table-column>

					</data-tables-server>
				</el-card>
			</el-col>
			<el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
				<el-card class="box-card">
					<el-upload
						class="upload-demo text-center"
						ref="upload"
						action="/api/v1/organisations/import"
            name="import_file"
						:auto-upload="false"
            accept=".csv,.xls,xlsx"
            :on-success="uploadSuccess"
            :limit="1">
						<div class="el-upload__tip" slot="tip">NOTE: THE FILE MUST BE CSV OR XLS OR XLSX </div>
						<el-button slot="trigger" type="primary" >Select File</el-button>
						<el-button class="m-l-sm" type="success"  @click="submitUpload">Upload to server</el-button>
					</el-upload>
				</el-card>
			</el-col>
		</el-row>
	</div>
</template>

<script>
import { DataTables, DataTablesServer } from "vue-data-tables";
import { mapGetters } from 'vuex'
import Swal from 'sweetalert2'

export default {
  layout: 'master',

  middleware: ['auth'],

	components: {
		DataTablesServer,
  },

  computed: mapGetters({
    logs: 'organisations/logs',
    loading: 'organisations/loading',
    log_count: 'organisations/log_count'
  }),

  data: () => ({
    cardTitle: 'Import Organisations',
    marginClass: 'mb-0',
		tableId: 'import_postcodes',
		filter: '',
    perPage: 10,
    fileList: [],
    logIds: []
  }),

  beforeMount(){
    this.$store.dispatch('organisations/fetchOrganizationsLogs', [])
  },

  methods: {
    async loadMore(queryInfo) {
      this.$store.dispatch('organisations/fetchOrganizationsLogs', queryInfo)
    },

    deleteLogs(){
      this.$store.dispatch('organisations/deleteLogs', this.logIds).then( ({success, message}) => {
        if(success){
          this.$store.dispatch('organisations/fetchOrganizationsLogs', [])
          Swal.fire({
            title: 'Success!',
            text: message,
            type: 'success',
          })
        }
      })
    },

		handleSelectionChange(val) {
      this.logIds = []
      for(var i=0; i<val.length; i++){
        this.logIds.push(val[i].id)
      }
    },

    submitUpload() {
      this.$refs.upload.submit();
    },

    uploadSuccess(res, file){
      this.$store.dispatch('organisations/fetchOrganizationsLogs', [])
      if(res.success){
        Swal.fire({
          title: 'Success!',
          text: res.message,
          type: 'success',
        })
      }else{
        Swal.fire({
          title: 'Error!',
          html: res.message+"<br>"+res.data[0],
          type: 'error',
        })
      }


      setTimeout(() => {
        this.$refs.upload.clearFiles()
      }, 1000);
    },
  },

}
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