<template>
  <el-container id="login" class="gradient-pastel-blue w-100 h-100-vh">
    <el-row type="flex" class="w-100 jc-center ai-center">
      <el-card class="box-card">
        <div slot="header" class="clearfix">
          <span>Organisation Stats</span>
        </div>

        <el-table :data="stats">
          <el-table-column
            v-for="column in columns"
            :key="column.pro"
            :prop="column.prop"
            :label="column.label"
            :align="column.align"
            width="200"
          />
        </el-table>
      </el-card>
    </el-row>
  </el-container>
</template>

<script>
import Card from "~/components/Card";
import { mapGetters } from "vuex";
import { DataTables, DataTablesServer } from "vue-data-tables";

export default {
  layout: "basic",
  middleware: "auth",
  components: {
    DataTables,
    DataTablesServer,
  },

  computed: mapGetters({
    stats: "stats/stats",
    organisation_name: "stats/organisation_name",
    dates: "stats/dates",
    columns: "stats/columns",
  }),

  data() {
    return {
      cardTitle: "Lead Stats",
    };
  },

  methods: {
  },

  mounted() {},

  beforeMount() {
    const org_id = this.$route.params.organization_id
      ? this.$route.params.organization_id
      : 0;
    this.$store.dispatch("stats/fetchOrganizationStats", org_id);
  },
};
</script>
