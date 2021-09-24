<template>
  <section :class="className">
    <el-card class="box-card b-none page-header" shadow="never">
      <h1 v-if="pageTitle.length > 0">
        <span>{{ pageTitle }}</span>
        <slot name="extra" />
        <slot name="button" />
      </h1>
      <slot name="extra-2" />
    </el-card>
    <slot name="content" />
  </section>
</template>

<script>
export default {
  name: "Section",
  props: {
    className: {
      type: String,
      default: "",
    },
    pageTitle: {
      type: String,
      default: "",
    },
  },
};
</script>
