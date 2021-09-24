<template>
  <el-main>
    <transition name="el-fade-in">
      <slot>
        <router-view />
      </slot>
    </transition>
  </el-main>
</template>

<script>
export default {
  name: 'Child'
}
</script>