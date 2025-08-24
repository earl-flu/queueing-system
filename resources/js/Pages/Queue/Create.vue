<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";

const props = defineProps({
  departments: {
    type: Array,
    default: () => [],
  },
  priority_reasons: {
    type: Array,
    default: () => [],
  },
});

const form = useForm({
  patient: {
    first_name: "",
    middle_name: "",
    last_name: "",
    suffix: "",
    phone: "",
    age: "",
    gender: "",
    priority_reason_id: "",
    is_priority: false,
  },
  final_department_id: "",
});

const submit = () => {
  form.post(route("queue.store"), {
    onSuccess: () => {
      const toast = useToast();
      toast.success("Patient added to queue successfully", {
        timeout: 3000,
      });
    },
  });
};

// Roles (could also come from props)
const roles = [
  { label: "Admin", value: "admin" },
  { label: "Doctor", value: "doctor" },
  { label: "Nurse", value: "nurse" },
];
</script>

<template>
  <Head title="Add to Queue" />

  <AuthenticatedLayout>
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Queue</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item">
              <a href="javascript:;"><i class="bx bx-home-alt"></i></a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Create</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
      <div class="col-12 col-xl-8">
        <div class="card">
          <div class="card-body p-4">
            <h5 class="mb-4">New Queue Entry</h5>
            <form class="row g-3" @submit.prevent="submit">
              <div class="col-md-3">
                <label for="last_name" class="form-label"
                  >Last Name <span class="text-sm text-red-500">*</span></label
                >
                <input
                  type="text"
                  class="form-control"
                  v-model="form.patient.last_name"
                  id="last_name"
                  autofocus
                />
                <div class="invalid-feedback d-block">
                  {{ form.errors["patient.last_name"] }}
                </div>
              </div>
              <div class="col-md-3">
                <label for="first_name" class="form-label"
                  >First Name <span class="text-sm text-red-500">*</span></label
                >
                <input
                  type="text"
                  class="form-control"
                  v-model="form.patient.first_name"
                  id="first_name"
                />
                <div class="invalid-feedback d-block">
                  {{ form.errors["patient.first_name"] }}
                </div>
              </div>
              <div class="col-md-3">
                <label for="middle_name" class="form-label">Middle Name</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="form.patient.middle_name"
                  id="middle_name"
                />
                <div class="invalid-feedback d-block">
                  {{ form.errors["patient.middle_name"] }}
                </div>
              </div>
              <div class="col-md-3">
                <label for="suffix" class="form-label">Suffix</label>
                <select
                  id="suffix"
                  class="form-select"
                  v-model="form.patient.suffix"
                >
                  <option value="">Select</option>
                  <option value="Jr.">Jr.</option>
                  <option value="Sr.">Sr.</option>
                  <option value="II">II</option>
                  <option value="III">III</option>
                  <option value="IV">IV</option>
                  <option value="V">V</option>
                </select>
                <div class="invalid-feedback d-block">
                  {{ form.errors["patient.suffix"] }}
                </div>
              </div>
              <div class="col-md-12">
                <label for="final_department_id" class="form-label"
                  >Destination Department
                  <span class="text-sm text-red-500">*</span></label
                >
                <v-select
                  v-model="form.final_department_id"
                  label="name"
                  :options="props.departments"
                  :reduce="(dept) => dept.id"
                  placeholder="Select Department"
                >
                  <!-- How each option is displayed in the dropdown -->
                  <template #option="{ name, description, queue_count }">
                    {{ name }} {{ description }}
                    <span
                      class="text-xs bg-green-400 text-white p-0.5 px-2 ml-5 rounded-md"
                      v-if="queue_count > 0"
                      >{{ queue_count }}</span
                    >
                  </template>

                  <!-- How the selected value is displayed -->
                  <template
                    #selected-option="{ name, description, queue_count }"
                  >
                    {{ name }} {{ description }}
                    <span
                      class="text-xs bg-green-400 text-white p-0.5 px-2 ml-5 rounded-md"
                      v-if="queue_count > 0"
                      >{{ queue_count }}</span
                    >
                  </template>
                </v-select>

                <div class="invalid-feedback d-block">
                  {{ form.errors.final_department_id }}
                </div>
              </div>

              <div class="col-md-6">
                <label for="phone" class="form-label">Phone</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="form.patient.phone"
                  id="phone"
                />
                <div class="invalid-feedback d-block">
                  {{ form.errors["patient.phone"] }}
                </div>
              </div>

              <div class="col-md-3">
                <label for="gender" class="form-label">Gender</label>
                <select
                  id="gender"
                  class="form-select"
                  v-model="form.patient.gender"
                >
                  <option value="">Select</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                </select>
                <div class="invalid-feedback d-block">
                  {{ form.errors["patient.gender"] }}
                </div>
              </div>

              <div class="col-md-3">
                <label for="age" class="form-label">Age</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="form.patient.age"
                  id="age"
                />
                <div class="invalid-feedback d-block">
                  {{ form.errors["patient.age"] }}
                </div>
              </div>

              <div class="col-md-12">
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    v-model="form.patient.is_priority"
                    id="is_priority"
                  />
                  <label class="form-check-label" for="is_priority"
                    >Is Priority</label
                  >
                </div>
                <div class="invalid-feedback d-block">
                  {{ form.errors["patient.is_priority"] }}
                </div>
              </div>

              <div class="col-md-12" v-if="form.patient.is_priority">
                <label for="priority_reason_id" class="form-label"
                  >Priority Category
                  <span class="text-sm text-red-500">*</span></label
                >
                <select
                  id="priority_reason_id"
                  class="form-select"
                  v-model="form.patient.priority_reason_id"
                >
                  <option value="">Select</option>
                  <option
                    v-for="reason in props.priority_reasons"
                    :key="reason.id"
                    :value="reason.id"
                  >
                    {{ reason.description }}
                  </option>
                </select>
                <div class="invalid-feedback d-block">
                  {{ form.errors["patient.priority_reason_id"] }}
                </div>
              </div>
              <div class="col-md-12 mt-4">
                <div class="d-md-flex d-grid align-items-center gap-3">
                  <button
                    class="btn btn-grd btn-grd-primary px-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                  >
                    Save
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>


