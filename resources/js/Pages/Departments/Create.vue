<template>
  <Head title="Create Department" />

  <AuthenticatedLayout>
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Departments</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item">
              <Link :href="route('departments.index')"
                ><i class="bx bx-home-alt"></i
              ></Link>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Create</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <h5 class="card-title mb-4">Create Department</h5>

        <form @submit.prevent="submit">
          <div class="row g-3">
            <div class="col-md-6">
              <InputLabel for="name" value="Name" />
              <TextInput
                id="name"
                v-model="form.name"
                type="text"
                class="mt-1 block w-full"
                autocomplete="off"
              />
              <InputError class="mt-1" :message="form.errors.name" />
            </div>

            <div class="col-md-6">
              <InputLabel for="code" value="Code" />
              <TextInput
                id="code"
                v-model="form.code"
                type="text"
                class="mt-1 block w-full text-uppercase"
                autocomplete="off"
              />
              <InputError class="mt-1" :message="form.errors.code" />
            </div>

            <div class="col-md-6">
              <InputLabel for="room" value="Room (optional)" />
              <TextInput
                id="room"
                v-model="form.room"
                type="text"
                class="mt-1 block w-full"
                autocomplete="off"
              />
              <InputError class="mt-1" :message="form.errors.room" />
            </div>

            <div class="col-md-6">
              <InputLabel for="users" value="Assigned Staff (optional)" />
              <select
                id="users"
                v-model="form.users"
                class="form-select mt-1"
                multiple
              >
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
              <InputError class="mt-1" :message="form.errors.users" />
            </div>

            <div class="col-md-6 d-flex align-items-center">
              <div class="form-check mt-4">
                <input
                  id="is_active"
                  class="form-check-input"
                  type="checkbox"
                  v-model="form.is_active"
                />
                <label class="form-check-label" for="is_active">Active</label>
              </div>
            </div>
          </div>

          <div class="mt-4 d-flex gap-2">
            <PrimaryButton :disabled="form.processing">Create</PrimaryButton>
            <Link :href="route('departments.index')" class="btn btn-secondary"
              >Cancel</Link
            >
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
  users: Array,
});

const form = useForm({
  name: "",
  code: "",
  room: "",
  users: [],
  is_active: true,
});

const submit = () => {
  if (form.code) form.code = form.code.toUpperCase();
  form.post(route("departments.store"));
};

const users = props.users || [];
</script>

<style scoped>
.text-uppercase {
  text-transform: uppercase;
}
</style>


