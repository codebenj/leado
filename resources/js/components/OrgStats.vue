<template>
  <el-card class="box-card">
    <div slot="header" class="clearfix">
      <span>{{ org_name }}</span>
    </div>

    <el-table :data="org_stats">
      <el-table-column
        v-for="column in org_columns"
        :key="column.pro"
        :prop="column.prop"
        :label="column.label"
        :align="column.align"
        width="200"
      />
    </el-table>
  </el-card>
</template>

<script>
import { DataTables, DataTablesServer } from "vue-data-tables"

export default {
  name: 'OrgStats',
  
  middleware: 'auth',

  components: {
    DataTables,
    DataTablesServer,
  },

  props: [ 'org_id' ],

  data: () => ({
    cardTitle: 'Lead Stats',
    org_stats: null,
    org_name: null,
    org_dates: null,
    org_columns: null,
  }),

  beforeMount() {
    this.$store.dispatch( 'stats/fetchOrganizationStats', this.org_id ).then( res => {
      let data = res.data

      this.org_stats = data.stats
      this.org_name = data.organisation_name[0]
      this.org_dates = data.dates
      this.org_columns = data.columns
    } )
  },
};
</script>
