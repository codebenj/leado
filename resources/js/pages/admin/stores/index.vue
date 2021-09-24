<template>
  <Section :pageTitle="pageTitle">
    <template v-slot:button>
      <el-button-group class="fl-right r-btn-reset">
        <el-button type="primary" v-on:click="export_stores">
          Export
        </el-button>
        <el-button type="primary" v-on:click="import_stores">
          Import
        </el-button>
        <el-button type="primary" v-on:click="sent_enquirer">
          Sent To Enquirer
        </el-button>
        <el-button type="primary" v-on:click="sent_history">
          Sent History
        </el-button>
        <el-button type="primary" v-on:click="delete_stores">
          Delete
        </el-button>
        <el-button type="primary" v-on:click="addStore">
          Add Store
        </el-button>
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

        <store-filter
          :isAdmin="isAssignedRoles(user, ['super_admin', 'administrator', 'user'])"
          :filters.sync="filters"
          :disabled="isFilterSave"
        />

        <data-tables-server
          :data="stores"
          :total="total"
          :loading="loading"
          :filters="filters"
          :pagination-props="{ pageSizes: [100, 50] }"
          @selection-change="selectionChange"
          @query-change="queryChange"
        >

          <el-table-column type="selection" width="40" />

          <el-table-column label="Store Code" prop="code" />

          <el-table-column label="Store" prop="code">
            <template slot-scope="{ row }">
              <b>{{ row.name }}</b><br>
              <p>{{ row.street_address}}</p>
            </template>
          </el-table-column>

          <el-table-column label="Call" prop="phone_number" />

          <el-table-column label="Distance" prop="distance">
            <template slot-scope="{ row }">
              {{ row.distance }}km
            </template>
          </el-table-column>

          <el-table-column label="Last Year Sales" prop="last_year_sales" />

          <el-table-column label="Year To Date Sales" prop="year_to_date_sales" />

          <el-table-column label="Pricing Book" prop="pricing_book" />

          <el-table-column label="Priority" prop="priority" />

          <el-table-column label="Stock Kits" prop="stock_kits" />

          <el-table-column label="" width="55" class-name="action b-none">
            <template slot-scope="row">
              <el-dropdown trigger="click">
                <span class="el-dropdown-link">
                    <i class="el-icon-caret-bottom"></i>
                  </span>
                <el-dropdown-menu slot="dropdown">
                  <el-dropdown-item
                    icon="el-icon-edit"
                    @click.native="editStore(row)"
                    >Edit</el-dropdown-item
                  >
                </el-dropdown-menu>
              </el-dropdown>
            </template>
          </el-table-column>

        </data-tables-server>

      </el-card>

      <template v-if="user.user_role.name == 'administrator' && showStore">
        <el-dialog
          :title="title"
          v-dialogDrag
          ref="dialog__wrapper"
          :visible.sync="showStore"
          width="40%"
          :show-close="true"
          append-to-body
          :before-close="beforeClose"

        >
          <Store @closeStore="closeStore" />
        </el-dialog>
      </template>

      <template v-if="user.user_role.name == 'administrator' && showSentToEnquirer">
        <el-dialog
          title="Sent Store To Enquirer"
          v-dialogDrag
          :visible.sync="showSentToEnquirer"
          width="30%"
          :destroy-on-close="false"
          :append-to-body="true"
          :before-close="closeSentToEnquirer">

          <el-card class="box-card b-none" shadow="never">
            <el-form
              label-position="top"
              label-width="120px"
              :model="enquirerForm"
              :rules="enquirerFormRules"
              status-icon
              ref="enquirerForm"
            >
              <el-row :gutter="20">
                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                  <el-form-item
                    label="Email Enquirers"
                    prop="emails"
                    :error="
                      enquirerForm.errors.errors.emails
                        ? enquirerForm.errors.errors.emails[0]
                        : ''
                    ">
                    <el-input type="text" v-model="enquirerForm.emails"></el-input>
                  </el-form-item>
                </el-col>

                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                  <el-form-item
                    label="Message"
                    prop="messages"
                    :error="
                      enquirerForm.errors.errors.messages
                        ? enquirerForm.errors.errors.messages[0]
                        : ''
                    ">
                    <el-input type="textarea" v-model="enquirerForm.messages"></el-input>
                  </el-form-item>
                </el-col>
              </el-row>

              <el-row :gutter="20">
                <el-col>
                  <div class="el-form-item">
                    <el-button
                      dusk="organisation-save"
                      type="primary"
                      :loading="loadingSent"
                      @click="saveEnquirer('enquirerForm')"
                      >Submit</el-button
                    >
                    <el-button
                      type="danger"
                      @click="closeEnquirer"
                    >
                      Cancel
                    </el-button>
                  </div>
                </el-col>
              </el-row>

            </el-form>
          </el-card>
        </el-dialog>
      </template>

      <template v-if="user.user_role.name == 'administrator' && showSentHistory">
        <el-dialog
          title="Sent History"
          v-dialogDrag
          :visible.sync="showSentHistory"
          width="70%"
          :destroy-on-close="false"
          :append-to-body="true"
          :before-close="closeSentHistory">

          <History @closeSentHistory="closeSentHistory"/>
        </el-dialog>
      </template>

    </template>
  </Section>
</template>

<script>
import Section from "~/components/Section"
import History from "~/components/stores/history"
import { DataTablesServer } from "vue-data-tables"
import { mapGetters } from "vuex"
import { isAssignedRoles } from "~/helpers"
import StoreFilter from "~/pages/admin/stores/filters.vue"
import Store from "~/components/stores/form"
import Swal from "sweetalert2"

export default {
  name: "Stores",

  layout: "master",

  middleware: ["auth"],

  components: {
    Section,
    DataTablesServer,
    StoreFilter,
    Store,
    History
  },

  computed: mapGetters({
    user: 'auth/user',
    stores: 'store/stores',
    total: 'store/total',
    loading: 'store/loading',
    showStore: 'store/showStore',
    enquirerForm: 'store/enquirerForm',
    enquirerFormRules: 'store/enquirerFormRules',
  }),

  data: () => ({
    showSentHistory: false,
    loadingSent: false,
    showSentToEnquirer: false,
    storeIds: [],
    title: 'Add New Store',
    isFilterSave: false,
    pageTitle: 'Stores',
    activeFilter: {},
    filters: [
      {
        props: "code",
        value: "",
      },
      {
        props: "postcode",
        value: "",
      },
      {
        props: "year_to_date_sales",
        value: "",
      },
      {
        props: "last_year_sales",
        value: "",
      },
      {
        props: "priority",
        value: "",
      },
      {
        props: "stock_kits",
        value: "",
      },
      {
        props: "distance",
        value: "",
      },
    ],
  }),

  methods: {
    isAssignedRoles: isAssignedRoles,

    import_stores(){
      this.$router.push({ name: 'admin.stores.import' })
    },

    sent_history(){
      this.showSentHistory = true
    },

    closeSentHistory(){
      this.showSentHistory = false
    },

    saveEnquirer(formName){
      this.loadingSent = true
      this.$refs[formName].validate((valid) => {
        if (valid) {
          // dispatch form submit
          this.$store
            .dispatch('store/sent', this.storeIds)
            .then(({ success, message, errors }) => {
              if (success) {
                Swal.fire({
                  title: "Success!",
                  text: message,
                  type: "success",
                  onClose: () => {
                    this.loadingSent = false
                    this.closeSentToEnquirer()
                  }
                })
              }
            })
        } else {
          return false
        }
      });
    },

    closeEnquirer(){
      this.enquirerForm.reset()
      this.showSentToEnquirer = false
    },

    closeSentToEnquirer(){
      this.enquirerForm.reset()
      this.showSentToEnquirer = false
    },

    sent_enquirer(){
      if (this.storeIds.length === 0) {
        Swal.fire({
          title: "Error",
          text: "Please select store to send to enquirer.",
          type: "error",
        });
        return;
      }

      this.showSentToEnquirer = true
    },

    export_stores() {
      if (this.storeIds.length === 0) {
        Swal.fire({
          title: "Error",
          text: "Please select store to export.",
          type: "error",
        });
        return;
      }

      this.$store.dispatch("store/export", this.storeIds).then((response) => {
        const url = window.URL.createObjectURL(new Blob([response]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", "store.xlsx");
        document.body.appendChild(link);
        link.click();
      });
    },

    delete_stores(){
      if (this.storeIds.length === 0) {
        Swal.fire({
          title: "Error",
          text: "Please select store to delete.",
          type: "error",
        });
        return;
      }

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
          this.$store.dispatch("store/massDelete", this.storeIds).then(({ success, message }) => {
            if (success) {
              Swal.fire({
                title: "Success!",
                text: message,
                type: "success",
              })
            }
          })
        }
      })
    },

    editStore(row){
      this.title = 'Edit Store'

      this.$store.dispatch('store/getStore',  row.row.id).then(result => {
        this.$store.dispatch('store/openStore', row.row.id)
      })

    },

    addStore(){
      this.title = 'Add New Store'
      this.$store.dispatch('store/openStore', 0)
    },

    closeStore(){
      this.$store.dispatch('store/closeStore')
    },

    beforeClose(){
      this.$store.dispatch('store/closeStore')
    },

    queryModActive(val){
      if(val){
        this.saveQueryActive()
      }
      else{
        this.removeSavedQueryActive()
      }
    },

    saveQueryActive(){
      localStorage.setItem( "storeFilters", JSON.stringify( this.activeFilter ) )
    },

    removeSavedQueryActive(){
      localStorage.removeItem( "storeFilters")
    },

    queryChange( queryInfo ){
      this.activeFilter = queryInfo

      clearTimeout( this.timeoutActive )
      var self = this;
      this.timeoutActive = setTimeout( function () {
        self.$store.dispatch('store/fetch', self.activeFilter)
      }, 1000 )
    },

    selectionChange(val) {
      this.storeIds = [];
      for (var i = 0; i < val.length; i++) {
        this.storeIds.push(val[i].id);
      }
    },

  },

  mounted(){
    let store_filters = localStorage.getItem( 'storeFilters' )

    if(store_filters !== null && store_filters !== ''){
      this.isFilterSave = true

      let filter_values = localStorage.getItem( 'storeFilters' )
      filter_values = JSON.parse(filter_values)

      this.filters[1].value = filter_values.filters[1].value;
      this.filters[2].value = filter_values.filters[2].value;
      this.filters[3].value = filter_values.filters[3].value;
      this.filters[4].value = filter_values.filters[4].value;
      this.filters[5].value = filter_values.filters[5].value;
      this.filters[6].value = filter_values.filters[6].value;
    }
  }
}
</script>

<style>

</style>
