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
        <label class="filter-labels">Filter by Escalation Type</label>
        <el-select
            v-model="filters[4].value"
            placeholder="Select Escalation Type"
            clearable
          >
          <el-option
            v-for="(type, index) in escalationTypes"
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
        <label class="filter-labels">Filter by Status</label>
        <el-select
            v-model="filters[3].value"
            placeholder="Select Account Status"
            clearable
          >
          <el-option
            v-for="(status, index) in statuses"
            :key="index"
            :value="status.value"
            :label="status.label"
            >{{ status.label }}</el-option
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
            v-model="filters[2].value"
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
          v-model="filters[1].value"
          placeholder="Postcode or Suburb"
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
        <label class="filter-labels">&nbsp;</label>
        <el-input
          v-model="filters[0].value"
          placeholder="Search..."
          clearable
        />
      </el-col>
    </el-row>
  </div>
</template>
<script>
import { mapGetters } from "vuex";
import { isAssignedRoles } from "~/helpers";
import ManualIcon from "~/components/ManualIcon.vue";

export default {
  name: "OrgsFilter",
  props: ["filters", "isAdmin"],
  data(){
    return {
      leadType: '',
      level: '',
      tempLeadType: [],
      statuses: [
        {
          value: 'suspended',
          label: 'Suspended'
        },
        {
          value: 'unsuspended',
          label: 'Unsuspended'
        }
      ],
      escalationTypes: [
        {
          value: 'auto',
          label: 'Auto'
        },
        {
          value: 'manual',
          label: 'Manual'
        }
      ]
    }
  },
   components: {
    ManualIcon
  },
  methods: {
    isAssignedRoles: isAssignedRoles,
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
