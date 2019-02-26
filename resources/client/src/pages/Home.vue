<template>
  <v-container fill-height>
    <v-layout align-center justify-center fill-height>
      <v-flex xs6>
        <v-card>
          <v-card-title>
            <h1 class="heading">Sign Up!</h1>
          </v-card-title>
          <form @submit.prevent="submit">
            <v-card-text>
              <v-alert :value="error" type="error">{{error}}</v-alert>
              <v-text-field
                name="Email"
                label="Email"
                v-model="formData.email"
                v-validate="'required|email'"
                :error-messages="errors.first('Email')"
              />
            </v-card-text>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn type="submit" color="primary" :disabled="submitting">Sign up</v-btn>
            </v-card-actions>
          </form>
        </v-card>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
export default {
  name: "Home",
  data() {
    return {
      formData: {
        email: ""
      },
      submitting: false,
      error: null
    };
  },
  methods: {
    async submit() {
      const isValid = await this.$validator.validateAll();
      if (!this.submitting && isValid) {
        this.submitting = true;
        try {
          //Async call goes here
          this.error = null;
        } catch (e) {
          this.error = e.response.data.error;
        }
        this.submitting = false;
      }
    }
  }
};
</script>
