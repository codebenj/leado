<template>
  <div>
    <el-row class="m-b-lg" :gutter="20">
      <!-- <el-col
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
          :disabled="disabled"
        />
      </el-col> -->

      <el-col
        class="filter-spacings-at-column-type"
        :xs="24"
        :sm="24"
        :md="12"
        :lg="12"
        :xl="12"
      >
        <label class="filter-labels">Postcode</label>
        <el-input
          v-model="filters[1].value"
          placeholder="2766 default postcode"
          clearable
          :disabled="disabled"
        />
      </el-col>

      <el-col
        class="filter-spacings-at-column-type"
        :xs="24"
        :sm="24"
        :md="12"
        :lg="12"
        :xl="12"
      >
        <label class="filter-labels">Distance</label>
        <el-input
          v-model="filters[6].value"
          placeholder="Radius in KM from Postcode (500km)"
          clearable
          :disabled="disabled"
        />
      </el-col>

      <el-col
        class="filter-spacings-at-column-type"
        :xs="24"
        :sm="24"
        :md="12"
        :lg="12"
        :xl="12"
      >
        <label class="filter-labels">YTD Sales greater than $</label>
        <el-input
          v-model="filters[2].value"
          placeholder=""
          clearable
          :disabled="disabled"
        />
      </el-col>

      <el-col
        class="filter-spacings-at-column-type"
        :xs="24"
        :sm="24"
        :md="12"
        :lg="12"
        :xl="12"
      >
        <label class="filter-labels">LY Sales greater than $</label>
        <el-input
          v-model="filters[3].value"
          placeholder=""
          clearable
          :disabled="disabled"
        />
      </el-col>

      <el-col
        class="filter-spacings-at-column-type"
        :xs="24"
        :sm="24"
        :md="12"
        :lg="12"
        :xl="12"
      >
        <label class="filter-labels">Priority</label>
        <el-input
          v-model="filters[4].value"
          placeholder=""
          clearable
          :disabled="disabled"
        />
      </el-col>

      <el-col
        class="filter-spacings-at-column-type"
        :xs="24"
        :sm="24"
        :md="12"
        :lg="12"
        :xl="12"
      >
        <label class="filter-labels">Stock Kits</label>
        <el-input
          v-model="filters[5].value"
          placeholder=""
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

.filter-spacings-at-column-type{
  margin-bottom: 10px;
}

@media all and (max-width: 768px) {
  .filter-spacings-at-column-type {
    margin-top: 10px;
    margin-bottom: 10px;
  }

  .filter-spacings-at-column-type:first-child {
    margin-top: 0px;
  }

  .filter-spacings-at-column-type:last-child {
    margin-top: 0px;
  }
}
</style>
