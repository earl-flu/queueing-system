<template>
  <Head title="Create Department Flow" />

  <AuthenticatedLayout>
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Department Flows</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item">
              <Link :href="route('department-flows.index')"
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
        <h5 class="card-title mb-4">Create Flow Step</h5>

        <form @submit.prevent="submit">
          <div class="row g-3">
            <div class="col-md-6">
              <InputLabel for="final_department_id" value="Final Department" />
              <select
                id="final_department_id"
                v-model="form.final_department_id"
                class="form-select mt-1"
              >
                <option disabled value="">Select department</option>
                <option v-for="d in departments" :key="d.id" :value="d.id">
                  {{ d.name }}
                </option>
              </select>
              <InputError
                class="mt-1"
                :message="form.errors.final_department_id"
              />
            </div>

            <div class="col-md-6">
              <InputLabel for="step_department_id" value="Step Department" />
              <select
                id="step_department_id"
                v-model="form.step_department_id"
                class="form-select mt-1"
              >
                <option disabled value="">Select department</option>
                <option v-for="d in departments" :key="d.id" :value="d.id">
                  {{ d.name }}
                </option>
              </select>
              <InputError
                class="mt-1"
                :message="form.errors.step_department_id"
              />
            </div>

            <div class="col-md-6">
              <InputLabel for="step_order" value="Step Order" />
              <TextInput
                id="step_order"
                v-model.number="form.step_order"
                type="number"
                min="1"
                class="mt-1 block w-full"
              />
              <InputError class="mt-1" :message="form.errors.step_order" />
            </div>

            <div class="col-md-6 d-flex align-items-center">
              <div class="form-check mt-4">
                <input
                  id="is_required"
                  class="form-check-input"
                  type="checkbox"
                  v-model="form.is_required"
                />
                <label class="form-check-label" for="is_required"
                  >Required</label
                >
              </div>
            </div>
          </div>

          <div class="mt-4 d-flex gap-2">
            <PrimaryButton :disabled="form.processing">Create</PrimaryButton>
            <Link
              :href="route('department-flows.index')"
              class="btn btn-secondary"
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
  departments: Array,
});

const departments = props.departments || [];

const form = useForm({
  final_department_id: "",
  step_department_id: "",
  step_order: 1,
  is_required: true,
});

const submit = () => {
  form.post(route("department-flows.store"));
};
</script>


