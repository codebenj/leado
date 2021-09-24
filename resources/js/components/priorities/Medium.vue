<template>
    <el-tooltip :content="tooltip" :placement="placement" v-if="displayOnly">
        <img
            src="/app-assets/img/svg/medium.svg"
            alt="Low Priority"
        />
    </el-tooltip>
</template>

<script>

export default {
  name: "Medium",
  props: {
    show: {
      type: Boolean,
      default: true,
    },
    tooltip: {
      type: String,
      default: 'Medium Priority',
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
