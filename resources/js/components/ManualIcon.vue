<template>
    <el-tooltip :content="tooltip" :placement="placement" v-if="displayOnly || isManualUpdateEnabled(org)">
        <img
            src="/app-assets/img/svg/group.svg"
            alt="Manual Escalation"
        />
    </el-tooltip>
</template>

<script>
import { isManualUpdateEnabled } from "~/helpers";

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
      default: 'This Org requires manual escalation and Org notifications are disabled',
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
    isManualUpdateEnabled: isManualUpdateEnabled
  }
};
</script>
