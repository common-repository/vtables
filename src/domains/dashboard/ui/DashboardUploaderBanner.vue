<template>
  <div class="limit-plan-message">
    <q-banner class="bg-amber-9 text-white text-center full-width">
      <h6 class="text-subtitle1 q-my-md">
        Available with
        <b class="text-weight-bolder">vTables PRO Add-on</b>.
      </h6>
      <q-btn
        class="text-weight-bold q-mb-md"
        color="white"
        text-color="primary"
        href="https://vtables.pro"
        target="_blank"
      >
        Download for free
      </q-btn>
    </q-banner>
  </div>
</template>

<style scoped>
.limit-plan-message {
  background: rgba(255, 255, 255, 0.7);
  display: flex;
  align-items: flex-end;
  height: 100%;
  width: 100%;
  position: absolute;
  z-index: 1;
  left: 0;
  top: 0;
}
</style>
