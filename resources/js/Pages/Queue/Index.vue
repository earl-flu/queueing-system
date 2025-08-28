<template>
  <Head title="Queue Management" />

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
            <li class="breadcrumb-item active" aria-current="page">
              Management
            </li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="d-flex gap-2 align-items-center mb-4">
      <h4 class="mb-0">Queue Management</h4>
      <p class="flex-1"></p>
      <template v-if="userDepartment">
        <Link
          :href="route('queue.department', userDepartment.id)"
          class="btn btn-grd btn-primary"
        >
          {{ userDepartment.name }} Department Queue
        </Link>
      </template>
      <template v-if="isReception">
        <Link :href="route('queue.create')" class="btn btn-grd btn-grd-primary">
          Add New Patient
        </Link>
      </template>
    </div>

    <div class="row">
      <div class="col-12">
        <!-- Filters -->
        <div class="card mb-4">
          <div class="card-body">
            <div class="row">
              <div class="col-md-3">
                <label class="form-label">Patient Full Name</label>
                <input
                  v-model="filterForm.patientFullname"
                  @keyup="applyFilters"
                  type="text"
                  class="form-control"
                />
              </div>
              <div class="col-md-3">
                <label class="form-label">Queue Number</label>
                <input
                  v-model="filterForm.queueNumber"
                  @keyup="applyFilters"
                  type="text"
                  class="form-control"
                />
              </div>
              <div class="col-md-3">
                <label class="form-label">Department</label>
                <select
                  v-model="filterForm.department"
                  @change="applyFilters"
                  class="form-select"
                >
                  <option value="">All Departments</option>
                  <option
                    v-for="dept in departments"
                    :key="dept.id"
                    :value="dept.id"
                  >
                    {{ dept.name }}
                  </option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Status</label>
                <select
                  v-model="filterForm.status"
                  @change="applyFilters"
                  class="form-select"
                >
                  <option value="all">All Status</option>
                  <option value="done">Done</option>
                  <option value="no_show">No Show</option>
                  <option value="serving">Serving</option>
                  <option value="skipped">Skipped</option>
                  <option value="transferred">Transferred</option>
                  <option value="waiting">Waiting</option>
                </select>
              </div>
              <div class="col-md-4 d-flex align-items-end mt-2">
                <button @click="clearFilters" class="btn btn-secondary">
                  Clear Filters
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Queue Items Table -->
        <div class="card">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Queue Number</th>
                    <th>Patient</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Time</th>
                    <th>Served By</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <QueueTableRow
                    v-for="item in queueItems.data"
                    :item="item"
                    :user="props.user"
                    :key="item.id"
                    :isReception="isReception"
                  />
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-4">
              <div class="text-muted">
                Showing {{ queueItems.from }} to {{ queueItems.to }} of
                {{ queueItems.total }} results
              </div>
              <nav>
                <ul class="pagination pagination-sm mb-0">
                  <li
                    v-for="link in queueItems.links"
                    :key="link.label"
                    :class="[
                      'page-item',
                      link.active ? 'active' : '',
                      !link.url ? 'disabled' : '',
                    ]"
                  >
                    <Link :href="link.url ?? ''" class="page-link">
                      <span v-html="link.label"></span>
                    </Link>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import QueueTableRow from "@/Components/QueueTableRow.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";

const userDepartment = computed(() => props.user?.departments[0]);
const isReception = computed(() => props.user?.role === "reception");

const props = defineProps({
  queueItems: Object,
  departments: Array,
  filters: Object,
  user: Object,
});

const filterForm = ref({
  department: props.filters.department || "",
  status: props.filters.status || "all",
  patientFullName: props.filters.patientFullName || "",
  queueNumber: props.filters.queueNumber || "",
});

const applyFilters = () => {
  console.log("triggered");
  router.get(route("queue.index"), filterForm.value, {
    preserveState: true,
    replace: true,
  });
};

const clearFilters = () => {
  filterForm.value = {
    department: "",
    status: "all",
    patientFullName: "",
    queueNumber: "",
  };
  applyFilters();
};

const callPatient = (id) => {
  router.post(
    route("queue.call", id),
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ["queueItems"] });
      },
    }
  );
};

const completeService = (id) => {
  router.post(
    route("queue.complete", id),
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ["queueItems"] });
      },
    }
  );
};
</script>