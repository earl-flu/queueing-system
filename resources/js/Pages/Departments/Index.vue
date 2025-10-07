<template>
  <Head title="Departments" />

  <AuthenticatedLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="mb-0">Departments</h4>
        <p class="text-muted mb-0">Manage departments and view queue status</p>
      </div>
      <div v-if="$page.props.auth?.user?.is_admin">
        <Link
          :href="route('departments.create')"
          class="btn btn-grd btn-grd-primary"
        >
          Create Department
        </Link>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Name</th>
                <th>Code</th>
                <th>Room</th>
                <th>Status</th>
                <th>Staff</th>
                <th>Active Queue</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="departments.length === 0">
                <td colspan="7" class="text-center text-muted py-4">
                  No departments found
                </td>
              </tr>
              <tr v-for="dept in departments" :key="dept.id">
                <td class="fw-semibold">{{ dept.name }}</td>
                <td>
                  <span class="badge bg-light text-dark">{{ dept.code }}</span>
                </td>
                <td>{{ dept.room || "—" }}</td>
                <td>
                  <span
                    :class="
                      dept.is_active ? 'badge bg-success' : 'badge bg-secondary'
                    "
                  >
                    {{ dept.is_active ? "Active" : "Inactive" }}
                  </span>
                </td>
                <td>{{ (dept.users || []).length }}</td>
                <td>
                  {{ dept.active_queue ? dept.active_queue.length : 0 }}
                </td>
                <td class="text-end">
                  <div class="btn-group btn-group-sm" role="group">
                    <Link
                      :href="route('queue.department', dept.id)"
                      class="btn btn-outline-primary"
                      >Queue</Link
                    >
                    <Link
                      v-if="$page.props.auth?.user?.is_admin"
                      :href="route('departments.edit', dept.id)"
                      class="btn btn-outline-secondary"
                      >Edit</Link
                    >
                    <!-- <button
                      v-if="$page.props.auth?.user?.is_admin"
                      class="btn btn-outline-danger"
                      @click="confirmDelete(dept)"
                    >
                      Delete
                    </button> -->
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
  departments: Array,
});

const departments = props.departments || [];

const destroyForm = useForm({});
const confirmDelete = (dept) => {
  if (!confirm(`Delete department "${dept.name}"? This cannot be undone.`))
    return;
  destroyForm.delete(route("departments.destroy", dept.id));
};
</script>


