<template>
    <el-tooltip :content="tooltip" :placement="placement" v-if="displayOnly">
        <img
            src="/app-assets/img/svg/postcode.svg"
            alt="No Postcode"
        />
    </el-tooltip>
</template>

<script>

export default {
  name: "Section",
  props: {
    org: {
      type: Object,
      default: null,
    },
    show: {
      type: Boolean,
      default: true,
    },
    tooltip: {
      type: String,
      default: 'Postcodes have not been assigned.',
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
