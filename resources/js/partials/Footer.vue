<template>
    <el-footer>
      <el-row :gutter="24">
        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
          <p class="clearfix text-muted text-left px-2 ml-4">
            <span class="faded">
              Copyright &copy; 2020
              <a href="#" target="_blank" class="text-bold-800 primary darken-2">{{ appName }}</a>. All rights reserved.
            </span>
          </p>
        </el-col>
      </el-row>
    </el-footer>
</template>

<script>
export default {
  name: "Footer",
  data: () => ({
    appName: window.config.appName,
  }),
};
</script>

<style scoped>
.faded{
  color: #909399;
  font-size: 14px;
}

.ml-4{
  margin-left: 20px;
}
</style>
