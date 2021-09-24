<template>
    <el-tooltip :content="tooltip+' Priority'" :placement="placement" v-if="displayOnly">
        <img v-if="priority == 'High'"
            src="/app-assets/img/svg/high.svg"
            alt="High Priority"
        />
        <img v-if="priority == 'Low'"
            src="/app-assets/img/svg/low.svg"
            alt="Low Priority"
        />
        <img v-if="priority == 'Medium'"
            src="/app-assets/img/svg/medium.svg"
            alt="Medium Priority"
        />
    </el-tooltip>
</template>

<script>

export default {
  name: "High",
  props: {
    priority: {
      type: String,
      default: 'None'
    },
    show: {
      type: Boolean,
      default: true,
    },
    tooltip: {
      type: String,
      default: 'High',
    },
    placement: {
      type: String,
      default: 'top',
    },
    displayOnly: {
      type: Boolean,
      default: false,
    },
  },

  data: () => ({
  }),

  methods: {
  }
};
</script>
