<template>
  <Section className="organisation-form">
    <template v-slot:content>
      <el-card class="box-card b-none" shadow="never" style="margin-top: -50px">
        <el-row class="m-b-lg" :gutter="20">
          <el-col
            class="filter-spacings-at-column-type"
            :xs="24"
            :sm="24"
            :md="12"
            :lg="4"
            :xl="4"
          >
            <label class="filter-labels">Code</label>
            <el-input
              v-model="filters[0].value"
              placeholder=""
              clearable
            />
          </el-col>

          <el-col
            class="filter-spacings-at-column-type"
            :xs="24"
            :sm="24"
            :md="12"
            :lg="4"
            :xl="4"
          >
            <label class="filter-labels">Store name</label>
            <el-input
              v-model="filters[1].value"
              placeholder=""
              clearable
            />
          </el-col>

          <el-col
            class="filter-spacings-at-column-type"
            :xs="24"
            :sm="24"
            :md="12"
            :lg="4"
            :xl="4"
          >
            <label class="filter-labels">Enquirer Email</label>
            <el-input
              v-model="filters[2].value"
              placeholder=""
              clearable
            />
          </el-col>

          <el-col
            class="filter-spacings-at-column-type"
            :xs="24"
            :sm="24"
            :md="12"
            :lg="4"
            :xl="4"
          >
            <label class="filter-labels">Suburb/Pcode</label>
            <el-input
              v-model="filters[3].value"
              placeholder=""
              clearable
            />
          </el-col>

          <el-col
            class="filter-spacings-at-column-type"
            :xs="24"
            :sm="24"
            :md="12"
            :lg="4"
            :xl="4"
          >
            <label class="filter-labels">State</label>
            <el-select popper-class="state_popper" v-model="filters[4].value" placeholder="Select State" clearable>
              <el-option
                v-for="item in states"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>

            <!-- <el-input
              v-model="filters[4].value"
              placeholder=""
              clearable
            /> -->
          </el-col>

        </el-row>

        <data-tables-server
              :data="histories"
              :total="total"
              :loading="loading"
              :filters="filters"
              :pagination-props="{ pageSizes: [100, 50] }"
              @query-change="loadMore"
            >

              <el-table-column label="Store Code" prop="code">
                <template slot-scope="{ row }">
                  {{ row.store.code }}
                </template>
              </el-table-column>

              <el-table-column label="Store Name" prop="code">
                <template slot-scope="{ row }">
                  {{ row.store.name }}
                </template>
              </el-table-column>

              <el-table-column label="Enquirer Email" prop="email_enquirers" />

              <el-table-column label="Suburb" prop="suburb">
                <template slot-scope="{ row }">
                  {{ row.store.suburb }}
                </template>
              </el-table-column>

              <el-table-column label="State" prop="suburb">
                <template slot-scope="{ row }">
                  {{ row.store.state }}
                </template>
              </el-table-column>

              <el-table-column label="Postcode" prop="suburb">
                <template slot-scope="{ row }">
                  {{ row.store.postcode }}
                </template>
              </el-table-column>

              <el-table-column label="Created Date" prop="created_at">
                <template slot-scope="{ row }">
                  {{ row.created_at | moment("DD/MM/YYYY") }}
                </template>
              </el-table-column>

            </data-tables-server>

          <el-row :gutter="20">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
              <el-button
                type="danger"
                @click="closeHistory()"
              >
                Cancel
              </el-button>
            </el-col>
          </el-row>

      </el-card>
    </template>
  </Section>
</template>

<script>
import Section from "~/components/Section"
import { DataTablesServer } from "vue-data-tables"
import { mapGetters } from "vuex"

export default {
  components: {
    Section,
    DataTablesServer
  },

  computed: mapGetters({
    user: 'auth/user',
    states: "leads/statesList",
    histories: 'store/histories',
    total: 'store/historyTotal',
    loading: 'store/loading'
  }),

  data: () => ({
    query: {},
    filters:[
      {
        props: "code",
        value: "",
      },
      {
        props: "name",
        value: "",
      },
      {
        props: "enquirer_email",
        value: "",
      },
      {
        props: "suburb_postcode",
        value: "",
      },
      {
        props: "state",
        value: "",
      },
    ]
  }),

  methods: {
    loadMore(query){
      this.query = query
      this.$store.dispatch('store/history', this.query)
    },

    closeHistory(){
      this.$emit('closeSentHistory')
    }
  },

  mounted(){
    this.$store.dispatch('store/history', this.query)
  }
}
</script>

<style>

</style>
