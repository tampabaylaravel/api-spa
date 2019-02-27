<template>
  <v-container>
    <v-layout>
      <v-flex md6>
        <v-card>
          <v-card-title>
            <h1 class="headline">Register</h1>
          </v-card-title>
          <form @submit.prevent="submit">
            <v-card-text>
              <v-alert :value="error" type="error">{{error}}</v-alert>
              <v-text-field
                label="Name"
                v-model="formData.name"
                v-validate="'required'"
                name="Name"
                :error-messages="errors.first('Name')"
              />
              <v-text-field
                label="email"
                v-model="formData.email"
                v-validate="'required|email'"
                name="Email"
                :error-messages="errors.first('Email')"
              />
              <v-text-field
                label="password"
                v-model="formData.password"
                type="password"
                v-validate="'required|min:5'"
                name="Password"
                :error-messages="errors.first('Password')"
              />
              <v-text-field
                label="password confirmation"
                v-model="formData.password_confirmation"
                type="password"
                v-validate="'required'"
                name="Password Confirmation"
                :error-messages="errors.first('Password Confirmation')"
              />
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn type="submit" color="primary" :disabled="submitting">register</v-btn>
            </v-card-actions>
          </form>
        </v-card>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
import axios from "axios";

export default {
  name: "HomePage",
  data() {
    return {
      formData: {
        name: "",
        email: "",
        password: "",
        password_confirmation: ""
      },
      submitting: false,
      error: null
    };
  },
  methods: {
    async submit() {
      if (!this.submitting && (await this.$validator.validateAll())) {
        this.submitting = true;
        try {
          const response = await axios.post("/api/register", {
            ...this.formData,
            grant_type: "password",
            client_id: "2",
            client_secret: "gkZvxmBUt3zsC1JHXviVbIhNzhX20OMKdTl3UkKw",
            scope: "*"
          });

          this.error = null;

          axios.defaults.headers["Authorization"] = `Bearer ${
            response.data.access_token
          }`;

          const userResponse = await axios.get("/api/user");
          console.log(userResponse.data);
        } catch (e) {
          console.log(e.response);
          this.error = e.response.data.message;
        }
        this.submitting = false;
      }
    }
  }
};
</script>
