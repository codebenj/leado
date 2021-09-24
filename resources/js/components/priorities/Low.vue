<template>
    <el-tooltip :content="tooltip" :placement="placement" v-if="displayOnly">
        <img
            src="/app-assets/img/svg/low.svg"
            alt="Low Priority"
        />
    </el-tooltip>
</template>

<script>

export default {
  name: "Low",
  props: {
    show: {
      type: Boolean,
      default: true,
    },
    tooltip: {
      type: String,
      default: 'Low Priority',
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

  methods: {
  }
};
</script>
