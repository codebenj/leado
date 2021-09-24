<template>
  <Section className="org-locator" :pageTitle="pageTitle">
    <template v-slot:button>
      <el-button-group class="fl-right r-btn-reset">
        <el-button
          type="primary"
          @click="$router.push({ name: 'orglocator.create' })"
          >Add New Org.</el-button
        >
        <el-button
          type="primary"
          @click="$router.push({ name: 'orglocator.import' })"
          >Import</el-button
        >

        <el-button type="primary" v-on:click="export_orglocator"
          >Export</el-button
        >
        <el-button type="primary" v-on:click="delete_orglocator"
          >Delete</el-button
        >
        <el-button type="primary" v-on:click="delete_all_orglocator"
          >Delete All</el-button
        >
      </el-button-group>
    </template>
    <template v-slot:content>
      <el-card class="box-card b-none" shadow="none">

        <!-- <el-row class="m-b-lg p-r-xl" :gutter="20">
          <el-col :xs="24" :sm="24" :md="4" :lg="4" :xl="4">
            <el-input
              v-model="filters[0].value"
              placeholder="Search..."
              clearable
            />
          </el-col>
        </el-row> -->

        <el-row class="m-b-lg p-r-xl" :gutter="20">
          <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
              <label class="el-form-item__label">Search</label>
              <el-input
                type="text"
                v-model="filters[0].value"
                placeholder="Search..."
                clearable
              ></el-input>

          </el-col>

          <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
              <label class="el-form-item__label">Filter by Pcode or Suburb</label>
              <el-input
                type="text"
                v-model="filters[1].value"
                placeholder="Postcode"
                clearable
              ></el-input>
          </el-col>

        </el-row>

        <el-row class="m-b-lg p-r-xl" :gutter="20">
          <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
              <label class="el-form-item__label">YTD Sales greater than $</label>
              <el-input
                type="text"
                v-model="filters[2].value"
                placeholder="YTD Sales greater than $"
              ></el-input>
          </el-col>
          <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
            <label class="el-form-item__label">LY Sales greater than $</label>
              <el-input
                type="text"
                v-model="filters[3].value"
                placeholder="LY Sales greater than $"
                clearable
              ></el-input>
          </el-col>
        </el-row>

        <el-row class="m-b-lg p-r-xl" :gutter="20">
          <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
            <label class="el-form-item__label">Priority</label>
              <el-input
                type="text"
                v-model="filters[4].value"
                placeholder="Priority"
                clearable
              ></el-input>
          </el-col>

          <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
            <label class="el-form-item__label">State</label>
              <el-select
                popper-class="state_popper"
                  v-model="filters[5].value"
                  placeholder="Select State"
                  clearable
                >
                <el-option
                  v-for="(state, index) in statesList"
                  :key="index"
                  :value="state.value"
                  :label="state.label"
                  >{{ state.label }}</el-option
                >
              </el-select>

          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
            <el-button type="primary" @click="filterOrgLocator"
              >Filter</el-button
            >
          </el-col>
        </el-row>

        <data-tables-server
          :data="orglocators"
          :loading="loading"
          :total="total"
          :pageSize="100"
          :pagination-props="{ pageSizes: [100, 50, 20] }"
          @selection-change="handleSelectionChange"
          @query-change="loadMore"
          @row-dblclick="doubleClick"
        >
          <el-table-column type="selection" width="55"> </el-table-column>

          <el-table-column prop="org_id" label="Org ID" width="80">
          </el-table-column>

          <el-table-column prop="name" label="Org"> </el-table-column>

          <el-table-column prop="street_address" label="Address">
          </el-table-column>

          <el-table-column prop="suburb" label="Suburb"> </el-table-column>

          <el-table-column prop="state" label="State"> </el-table-column>

          <el-table-column prop="postcode" label="Postcode"> </el-table-column>

          <el-table-column prop="phone" label="Phone"> </el-table-column>

          <el-table-column prop="last_year_sales" label="LYSales">
          </el-table-column>

          <el-table-column prop="year_to_date_sales" label="YTDSales">
          </el-table-column>


          <el-table-column prop="pricing_book" label="PBook">
          </el-table-column>

          <el-table-column prop="priority" label="Priority"> </el-table-column>

          <el-table-column label="" width="55" class-name="action b-none">
            <template slot-scope="row">
              <el-dropdown trigger="click">
                <span class="el-dropdown-link">
                    <i class="el-icon-caret-bottom"></i>
                  </span>
                <el-dropdown-menu slot="dropdown">
                  <el-dropdown-item
                    icon="el-icon-edit"
                    @click.native="editOrgLocator(row)"
                    >Edit</el-dropdown-item
                  >
                  <el-dropdown-item
                    icon="el-icon-delete"
                    @click.native="deleteOrgLocator(row)"
                    >Delete</el-dropdown-item
                  >
                </el-dropdown-menu>
              </el-dropdown>


            </template>
          </el-table-column>
        </data-tables-server>
      </el-card>
    </template>
  </Section>
</template>
<script>
import Section from "~/components/Section";
import { mapGetters } from "vuex";
import { DataTables, DataTablesServer } from "vue-data-tables";
import Swal from "sweetalert2";

export default {
  name: "OrgLocator",
  layout: "master",
  middleware: ["auth"],

  computed: mapGetters({
    orglocators: "orglocators/orglocators",
    loading: "orglocators/loading",
    total: "orglocators/total",
    statesList: "leads/statesList",
  }),

  components: {
    Section,
    DataTablesServer,
  },

  data() {
    return {
      pageTitle: "Organisation Locator",
      ids: [],
      filters: [
        {
          props: "search",
          value: "",
        },
        {
          props: "postcode",
          value: "",
        },
        {
          props: "ytd_sales_greater_than",
          value: "",
        },
        {
          props: "ly_sales_greater_than",
          value: "",
        },
        {
          props: "priority",
          value: "",
        },
        {
          props: "priority",
          value: "",
        },
      ],
      query: {},
    };
  },

  methods: {
    filterLevel(value) {},

    filterOrgLocator() {
      this.query.postcode = this.filters[1].value
      this.query.ytd_sale = this.filters[2].value
      this.query.ly_sale = this.filters[3].value
      this.query.priority = this.filters[4].value
      this.query.state = this.filters[5].value
      this.query.keyword = this.filters[0].value

      this.$store.dispatch("orglocators/fetchSearchOrgLocators", this.query);
    },

    export_orglocator() {
      this.$store.dispatch("orglocators/export", this.ids).then((response) => {
        const url = window.URL.createObjectURL(new Blob([response]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", "org-locator.xlsx");
        document.body.appendChild(link);
        link.click();
      });
    },

    delete_all_orglocator(){
      Swal.fire({
        title: "Are you sure you want to delete all Organisations in the Org Locator?",
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
      }).then((result) => {
        if (result.value) {
          this.$store
            .dispatch("orglocators/deleteAll")
            .then(({ success, message }) => {
              if (success) {
                Swal.fire({
                  title: "Success!",
                  text: message,
                  type: "success",
                }).then(() => {
                  this.$store.dispatch(
                    "orglocators/fetchOrgLocators",
                    this.payload
                  );
                });
              }
            });
        }
      });
    },

    delete_orglocator() {
      Swal.fire({
        title: "Are you sure you want to procced?",
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
      }).then((result) => {
        if (result.value) {
          this.$store
            .dispatch("orglocators/massDelete", this.ids)
            .then(({ success, message }) => {
              if (success) {
                Swal.fire({
                  title: "Success!",
                  text: message,
                  type: "success",
                }).then(() => {
                  this.$store.dispatch(
                    "orglocators/fetchOrgLocators",
                    this.payload
                  );
                });
              }
            });
        }
      });
    },

    doubleClick(row){
      this.$router.push({
        name: "orglocator.update",
        params: {
          id: row.id,
        },
      })
    },

    editOrgLocator({row}) {
      this.$router.push({
        name: "orglocator.update",
        params: {
          id: row.id,
        },
      })
    },

    deleteOrgLocator({ row }) {
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
          return this.$store
            .dispatch("orglocators/deleteOrgLocator", row.id)
            .then(({ success, message }) => {
              if (success) {
                this.$store.dispatch(
                  "orglocators/fetchOrgLocators",
                  this.payload
                );
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

    handleSelectionChange(val) {
      this.ids = [];
      for (var i = 0; i < val.length; i++) {
        this.ids.push(val[i].id);
      }
    },

    async loadMore(queryInfo) {
      this.payload = queryInfo;
      this.$store.dispatch("orglocators/fetchOrgLocators", queryInfo);
    },
  },
};
</script>
