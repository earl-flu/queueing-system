<template>
  <Head :title="`Edit ${form.name}`" />
  <AuthenticatedLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="m-0">Edit Screens</h3>
      <Link :href="route('windows.show', window.id)" class="btn btn-secondary"
        >View</Link
      >
    </div>

    <div class="card">
      <div class="card-body">
        <form @submit.prevent="submit">
          <div class="row g-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label">Name</label>
              <input v-model="form.name" class="form-control" />
            </div>
            <div class="col-md-2 form-check mt-4">
              <input
                id="active"
                type="checkbox"
                class="form-check-input"
                v-model="form.is_active"
              />
              <label class="form-check-label" for="active">Active</label>
            </div>
            <div class="col-md-6 text-end">
              <button class="btn btn-primary" :disabled="form.processing">
                Save
              </button>
            </div>
          </div>

          <hr />
          <div class="row">
            <div class="col-md-6">
              <h5>Assigned Departments (drag to sort)</h5>
              <ul class="list-group">
                <li
                  v-for="(d, idx) in form.departments"
                  :key="d.id"
                  class="list-group-item d-flex justify-content-between align-items-center"
                >
                  <span>{{ d.name }}</span>
                  <div>
                    <button
                      type="button"
                      class="btn btn-sm btn-light me-1"
                      @click="move(idx, -1)"
                      :disabled="idx === 0"
                    >
                      ▲
                    </button>
                    <button
                      type="button"
                      class="btn btn-sm btn-light me-2"
                      @click="move(idx, 1)"
                      :disabled="idx === form.departments.length - 1"
                    >
                      ▼
                    </button>
                    <button
                      type="button"
                      class="btn btn-sm btn-danger"
                      @click="remove(idx)"
                    >
                      Remove
                    </button>
                  </div>
                </li>
              </ul>
            </div>
            <div class="col-md-6">
              <h5>All Departments</h5>
              <div class="row g-2">
                <div
                  v-for="dept in availableToAdd"
                  :key="dept.id"
                  class="col-6"
                >
                  <button
                    type="button"
                    class="btn w-100 btn-outline-primary"
                    @click="add(dept)"
                  >
                    {{ dept.name }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
  window: Object,
  allDepartments: Array,
  assigned: Array,
});

const form = useForm({
  name: props.window.name,
  is_active: props.window.is_active,
  departments: props.assigned.map((d) => ({
    id: d.id,
    name: d.name,
    position: d.position,
  })),
});

const availableToAdd = computed(() => {
  const assignedIds = new Set(form.departments.map((d) => d.id));
  return props.allDepartments.filter((d) => !assignedIds.has(d.id));
});

const move = (index, delta) => {
  const target = index + delta;
  if (target < 0 || target >= form.departments.length) return;
  const tmp = form.departments[target];
  form.departments[target] = form.departments[index];
  form.departments[index] = tmp;
};

const remove = (index) => {
  form.departments.splice(index, 1);
};

const add = (dept) => {
  form.departments.push({
    id: dept.id,
    name: dept.name,
    position: form.departments.length,
  });
};

const submit = () => {
  // Normalize positions
  form.departments = form.departments.map((d, i) => ({ ...d, position: i }));
  form.put(route("windows.update", props.window.id));
};
</script>


