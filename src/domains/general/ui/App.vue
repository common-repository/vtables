<template>
  <div>
    <router-view />
  </div>
</template>

<style lang="scss">
body {
  top: initial !important;

  input[type='date'],
  input[type='datetime-local'],
  input[type='datetime'],
  input[type='email'],
  input[type='month'],
  input[type='number'],
  input[type='password'],
  input[type='search'],
  input[type='tel'],
  input[type='text'],
  input[type='time'],
  input[type='url'],
  input[type='week'] {
    padding: initial;
    line-height: initial;
    min-height: initial;
    box-shadow: initial;
    border: initial;
    border-color: initial;
    border-radius: initial;
    background-color: initial;
    color: initial;

    &:focus {
      // padding: initial;
      line-height: initial;
      min-height: initial;
      box-shadow: initial;
      border: initial;
      border-color: initial;
      border-radius: initial;
      background-color: initial;
      color: initial;
    }
  }
}
#q-app {
  .q-layout,
  .q-page {
    min-height: initial !important;
  }
}
</style>
