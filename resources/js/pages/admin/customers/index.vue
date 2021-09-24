<template>
  <Section dusk="organisationPage" className="organisation" :pageTitle="pageTitle">
    <template v-slot:button>
      <el-button-group class="fl-right r-btn-reset">
        <el-button type="primary" v-on:click="export_enquirers"
          >Export</el-button
        >
      </el-button-group>
    </template>

    <template v-slot:content>
          <el-card class="box-card b-none" shadow="never">

            <el-row v-if="isAssignedRoles(user, ['super_admin', 'administrator', 'user'])" style="margin-bottom: 30px;" :gutter="24">
              <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <el-switch
                v-model="isFilterSave"
                @change="queryModActive"
                active-text="Save Filter"
                inactive-text="Clear Filter"
                ></el-switch>
              </el-col>
            </el-row>

            <enquirer-filter
              :isAdmin="isAssignedRoles(user, ['super_admin', 'administrator', 'user'])"
              :filters.sync="filters"
              :disabled="isFilterSave"
            />

            <data-tables-server
              :data="customers"
              :total="total"
              :loading="loading"
              :filters="filters"
              :pagination-props="{ pageSizes: [100, 50] }"
              @selection-change="handleSelectionChange"
              @query-change="loadMore"
              @row-click="clickRow"
            >
              <el-table-column type="selection" width="40" />

              <el-table-column label="Enquirer Name" prop="first_name">
                <template slot-scope="{ row }">
                  {{ row.first_name }} {{ row.last_name }}
                </template>
              </el-table-column>

              <el-table-column label="Contract Number" prop="contact_number" />

              <el-table-column label="Email" prop="email" />

              <el-table-column label="Suburb" prop="address.suburb" />

              <el-table-column label="States" prop="address.state" />

              <el-table-column label="Pcode" prop="address.postcode" />

              <el-table-column label="Created Date" prop="created_at">
                <template slot-scope="{ row }">
                  {{ row.created_at | moment("DD/MM/YYYY") }}
                </template>
              </el-table-column>

              <el-table-column label="Lead Type" prop="lead.customer_type" />

            </data-tables-server>
          </el-card>

    </template>
  </Section>
</template>
<script>
import Section from "~/components/Section"
import { DataTablesServer } from "vue-data-tables"
import { mapGetters } from "vuex"
import { isAssignedRoles } from "~/helpers"
import EnquirerFilter from "~/pages/admin/customers/filters.vue"
import Swal from "sweetalert2"

export default {
  name: "Customers",

  components: {
    Section,
    DataTablesServer,
    EnquirerFilter
  },

  layout: "master",

  middleware: ["auth"],

  data: () => ({
    customerIds: [],
    isFilterSave: false,
    pageTitle: 'Enquirers',
    filtersActive: [],
    filters: [
      {
        props: "lead_type",
        value: "",
      },
      {
        props: "state",
        value: "",
      },
      {
        props: "suburb",
        value: "",
      },
      {
        props: "search",
        value: ""
      },
    ],
    payloadActive: {},
  }),

  computed: mapGetters({
    user: 'auth/user',
    customers: 'customer/customers',
    total: 'customer/total',
    loading: 'customer/loading'
  }),

  methods: {
    isAssignedRoles: isAssignedRoles,

    export_enquirers(){
      if (this.customerIds.length === 0) {
        Swal.fire({
          title: "Error",
          text: "Please select customer to export",
          type: "error",
        });
        return;
      }

      this.$store
        .dispatch("customer/export", this.customerIds)
        .then((response) => {
          const url = window.URL.createObjectURL(new Blob([response]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", "customers.xlsx");
          document.body.appendChild(link);
          link.click();
        }
      );
    },

    async loadMore( queryInfo ) {
      clearTimeout( this.timeoutActive )

      this.payloadActive = queryInfo

      var self = this;

      this.timeoutActive = setTimeout( function () {
        // enter this block of code after 1 second
        // handle stuff, call search API etc.
        self.$store.dispatch('customer/fetchCustomers', self.payloadActive)
      }, 1000 )
    },

    clickRow(){},

    queryModActive(val) {
      if(val){
        this.saveQueryActive()
      }
      else{
        this.removeSavedQueryActive()
      }
    },

    saveQueryActive(){
      localStorage.setItem( "enquiryFilters", JSON.stringify( this.payloadActive ) )
    },

    removeSavedQueryActive(){
      localStorage.removeItem( "enquiryFilters")
    },

    handleSelectionChange(val) {
      this.customerIds = [];
      for (var i = 0; i < val.length; i++) {
        this.customerIds.push(val[i].id);
      }
    },
  },

  mounted(){
    let enquiry_filters = localStorage.getItem( 'enquiryFilters' )

    if(enquiry_filters !== null && enquiry_filters !== ''){
      this.isFilterSave = true

      let filter_values = localStorage.getItem( 'enquiryFilters' )
      filter_values = JSON.parse(filter_values)

      this.filters[0].value = filter_values.filters[0].value;
      this.filters[1].value = filter_values.filters[1].value;
      this.filters[2].value = filter_values.filters[2].value;
      this.filters[3].value = filter_values.filters[3].value;
    }
  },

};
</script>

<style scoped>
  @media all and (max-width: 768px){
    .r-btn-reset{
      float: none;
    }
  }

  .organisation-status{
    border-radius: 25px;
    font-family: "SF UI Display Light";
    color: #303133;
  }
</style>
