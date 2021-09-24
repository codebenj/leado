<template>
  <div class="slider-guesteur">
    <el-row :gutter="24">
      <el-col :span="24">
        <p class="x-swipe-msg mb-0">Rotate device for landscape view</p>
      </el-col>
    </el-row>

    <el-row :gutter="24" class="mt-3 mb-3">
      <el-col :span="12">
        <div class="x-swipe-msg">Swipe table below</div>
      </el-col>
      <el-col :span="12" class="before-after-curret">
        <span class="x-swipe-indicator">
          <i class="x-hand fa fa-hand-pointer-o"></i>
        </span>
      </el-col>
    </el-row>
  </div>
</template>

<script>
export default {
  name: 'SliderGesture',

  data: () => ({
    //
  })
}
</script>

<style lang="scss" scoped>
.mb-0 {
  margin-bottom: 0px !important;
}

.mt-3 {
  margin-top: .5em !important;
}

.slider-guesteur {
  padding: 0 0 15px 0;
}

.x-hand {
  color:#000;
  font-size: 3em;
  left: calc(50% - 22px);
  opacity: .8;
  position: absolute;
  top: calc(50% - 5px);
  transform-origin: bottom;
  /* height: 100px;
  width: 100px; */
}

.x-swipe-msg{
  /* position: absolute;
  top: calc(50% - 30px);
  left : 40%; */
  font-family:'Verdana';
  font-weight : bold;
}

.x-swipe-indicator {
  position: absolute;
  /* top: calc(50% - 60px); */
  top: -5px;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  border: 5px dotted transparent;
  animation: swipe-animation-tablet 2s linear infinite;
}

.before-after-curret::before {
  content: '\2039';
  position: absolute;
  top: 20%;
  font-weight: 900;
  /* z-index: 999999; */
  font-size: 2em !important;
}

.before-after-curret::after {
  content: '\203A';
  position: absolute;
  right: 10px;
  top: 20%;
  font-weight: 900;
  /* z-index: 999999; */
  font-size: 2em !important;
}

@keyframes swipe-animation-lg {
  0% {
    left: 55%;
  }
  40% {
    border-color: rgba(187, 187, 187, 0.15);
  }
  50% {
    left: 90%;
  }
  60% {
    border-color: #bbb;
  }
  90% {
    border-color: rgba(187, 187, 187, 0.5);
  }
  100% {
    left: 55%;
  }
}

@keyframes swipe-animation-md {
  0% {
    left: 55%;
  }
  40% {
    border-color: rgba(187, 187, 187, 0.15);
  }
  50% {
    left: 83%;
  }
  60% {
    border-color: #bbb;
  }
  90% {
    border-color: rgba(187, 187, 187, 0.5);
  }
  100% {
    left: 55%;
  }
}

@keyframes swipe-animation-sm {
  0% {
    left: 60%;
  }
  40% {
    border-color: rgba(187, 187, 187, 0.15);
  }
  50% {
    left: 75%;
  }
  60% {
    border-color: #bbb;
  }
  90% {
    border-color: rgba(187, 187, 187, 0.5);
  }
  100% {
    left: 60%;
  }
}

@media screen and ( min-width: 569px ) and ( max-width: 768px ) {
  .x-swipe-indicator {
    animation: swipe-animation-lg 2s linear infinite !important;
  }
}

@media screen and ( min-width: 425px ) and ( max-width: 568px ) {
  .x-swipe-indicator {
    animation: swipe-animation-md 2s linear infinite !important;
  }
}

@media screen and ( min-width: 150px ) and ( max-width: 424px ) {
  .x-swipe-indicator {
    animation: swipe-animation-sm 2s linear infinite !important;
  }
}

.mb-5 {
  margin-bottom : 4.5rem !important;
}

.mb-4 {
  margin-bottom : 2.25rem !important;
}

.mb-3 {
  margin-bottom : 1.5rem !important;
}
</style>
