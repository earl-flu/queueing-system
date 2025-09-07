<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";

defineProps({
  canResetPassword: {
    type: Boolean,
  },
  status: {
    type: String,
  },
});

const form = useForm({
  email: "",
  password: "",
  remember: false,
});

const submit = () => {
  form.post(route("login"), {
    onFinish: () => form.reset("password"),
    onSuccess: () => {
      // Force reload
      window.location.reload();
    },
  });
};
</script>

<template>
  <Head title="Log in" />
  <!-- Login 8 - Bootstrap Brain Component -->
  <section
    class="p-3 p-md-4 p-xl-5 min-vh-100 d-flex align-items-center justify-content-center"
  >
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-12 col-xxl-11">
          <div class="card shadow">
            <div class="row g-0">
              <div class="col-12 col-md-6">
                <img
                  class="img-fluid rounded-start w-100 h-100 object-fit-cover"
                  loading="lazy"
                  src="https://images.pexels.com/photos/317157/pexels-photo-317157.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1"
                />
              </div>
              <div
                class="col-12 col-md-6 d-flex align-items-center justify-content-center"
              >
                <div class="col-12 col-lg-11 col-xl-10">
                  <div class="card-body p-3 p-md-4 p-xl-5">
                    <div class="row">
                      <div class="col-12">
                        <div class="mb-5">
                          <!-- <div class="mb-4">
                            <a href="#!">
                              <img
                                src="https://images.pexels.com/photos/430205/pexels-photo-430205.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1"
                                alt="BootstrapBrain Logo"
                                width="175"
                                height="57"
                              />
                            </a>
                          </div> -->
                          <h4 class="fw-bold">Get Started Now</h4>
                          <p class="mb-0">
                            Enter your credentials to login your account
                          </p>
                        </div>
                      </div>
                    </div>
                    <form @submit.prevent="submit">
                      <div class="row gy-3 overflow-hidden">
                        <div class="col-12">
                          <label for="email" class="form-label">Email</label>
                          <input
                            type="email"
                            class="form-control"
                            id="email"
                            v-model="form.email"
                            autofocus
                            required
                          />
                          <InputError
                            class="mt-2"
                            :message="form.errors.email"
                          />
                        </div>
                        <div class="col-12">
                          <label for="password" class="form-label"
                            >Password</label
                          >
                          <input
                            type="password"
                            class="form-control"
                            id="password"
                            v-model="form.password"
                            required
                          />
                          <InputError
                            class="mt-2"
                            :message="form.errors.password"
                          />
                        </div>
                        <div class="col-12 mt-4">
                          <div class="d-grid">
                            <button
                              :class="{ 'opacity-25': form.processing }"
                              :disabled="form.processing"
                              class="btn btn-grd btn-grd-primary px-5"
                            >
                              Login
                            </button>
                          </div>
                        </div>
                      </div>
                    </form>
                    <div class="row">
                      <div class="col-12">
                        <div class="mt-5">
                          <Link :href="route('register')">
                            Create new account
                          </Link>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
