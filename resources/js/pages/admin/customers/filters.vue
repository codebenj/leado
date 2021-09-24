<template>
  <div>
    <el-row class="m-b-lg" :gutter="20">
      <el-col
        class="filter-spacings-at-column-type"
        :xs="24"
        :sm="24"
        :md="12"
        :lg="4"
        :xl="4"
      >
        <label class="filter-labels">Filter by Lead Type</label>
        <el-select
            v-model="filters[0].value"
            placeholder="Select Lead Type"
            clearable
            :disabled="disabled"
          >
          <el-option
            v-for="(type, index) in leadType"
            :key="index"
            :value="type.value"
            :label="type.label"
            >{{ type.label }}</el-option
          >
        </el-select>
      </el-col>

      <el-col
        class="filter-spacings-at-column-type"
        :xs="24"
        :sm="24"
        :md="12"
        :lg="4"
        :xl="4"
      >
        <label class="filter-labels">Filter by State</label>
        <el-select
            v-model="filters[1].value"
            placeholder="Select State"
            clearable
            :disabled="disabled"
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

      <el-col
        class="filter-spacings-at-column-type"
        :xs="24"
        :sm="24"
        :md="12"
        :lg="4"
        :xl="4"
      >
        <label class="filter-labels">Filter by Pcode or Suburb</label>
        <el-input
          v-model="filters[2].value"
          placeholder="Postcode or Suburb"
          clearable
          :disabled="disabled"
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
        <label class="filter-labels">&nbsp;</label>
        <el-input
          v-model="filters[3].value"
          placeholder="Search..."
          clearable
          :disabled="disabled"
        />
      </el-col>

    </el-row>
  </div>
</template>
<script>
import { mapGetters } from "vuex"

export default {
  name: "CustomerFilter",

  props: ['filters', 'isAdmin', 'disabled'],

  data(){
    return {
      leadType:[
        {
          value: 'Supply & Install',
          label: 'Supply & Install'
        },
        {
          value: 'Supply Only',
          label: 'Supply Only'
        },
        {
          value: 'General Enquiry',
          label: 'General Enquiry'
        }
      ]
    }
  },

   components: {
  },

  methods: {
  },

  computed: mapGetters({
    user: "auth/user",
    statesList: "leads/statesList",
  }),

  mounted(){

  }
};
</script>

<style scoped>
.filter-labels {
  color: grey;
  white-space: nowrap;
}

@media all and (max-width: 768px) {
  .filter-spacings-at-column-type {
    margin-top: 10px;
  }

  .filter-spacings-at-column-type:first-child {
    margin-top: 0px;
  }

  .filter-spacings-at-column-type:last-child {
    margin-top: -10px;
  }
}
</style>
