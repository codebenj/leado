<template>
	<div>
		<el-card class="box-card b-none" shadow="never">
      <el-row :gutter="20" v-if="orgPostcodes.length > 0">
        <el-col :span="4" v-for="postcodes in orgPostcodes" :key="postcodes.id">
          <el-tag type="info" style="margin-bottom: 5px">{{ postcodes.postcode }}</el-tag>
        </el-col>
      </el-row>
      <el-row :gutter="20" v-else>
        <div>No Postcode found.</div>
      </el-row>
		</el-card>
	</div>
</template>

<script>
import { mapGetters } from "vuex";
import { DataTablesServer } from "vue-data-tables";

export default {
  props: {
    orgId: Number
  },
	name: "OrganizationPostcodes",
	components: {
		DataTablesServer,
	},

	computed: mapGetters({
		// <ANY_LABEL_TO_BE_USED_IN_LAYOUT>: "<MODULE_FILE_NAME>/<GETTER>"
		orgPostcodes: "organisations/orgPostcodes",
		orgPostcodesTotal: "organisations/orgPostcodesTotal",
		orgPostcodesLoading: "organisations/orgPostcodesLoading",
	}),

	data: () => ({}),

	methods: {
		async loadMore(queryInfo) {
			let orgId = this.orgId ? this.orgId : this.$route.params.id
			this.$store.dispatch("organisations/fetchOrgPostcodes", orgId)
		}
	},

  beforeMount(){
    let orgId = this.orgId ? this.orgId : this.$route.params.id
		this.$store.dispatch("organisations/fetchOrgPostcodes", orgId)
  }
}
</script>

<style scoped>
	/* SCOPED STYLE HERE */
  .el-row {
    margin-bottom: 20px;
    &:last-child {
      margin-bottom: 5px;
    }
  }
  .el-col {
    border-radius: 4px;
  }
  .bg-purple-dark {
    background: #99a9bf;
  }
  .bg-purple {
    background: #d3dce6;
  }
  .bg-purple-light {
    background: #e5e9f2;
  }
  .grid-content {
    border-radius: 4px;
    min-height: 36px;
  }
  .row-bg {
    padding: 10px 0;
    background-color: #f9fafc;
  }
</style>
