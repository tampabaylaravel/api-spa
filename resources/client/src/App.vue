<template>
  <v-app>
    <v-toolbar app>
      <v-toolbar-title class="headline text-uppercase">Laravel SPA</v-toolbar-title>
      <v-spacer></v-spacer>
      <v-btn :to="{name:'home'}" flat>Home</v-btn>
      <v-btn :to="{name:'about'}" flat>About</v-btn>
    </v-toolbar>

    <v-content>
      <router-view/>
    </v-content>
  </v-app>
</template>

<script>
export default {
  name: "App",
  data() {
    return {
      //
    };
  }
};
</script>
