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
      <Link :href="route('queue.create')" class="btn btn-grd btn-primary">
        Department View
      </Link>
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
                  <option value="waiting">Waiting</option>
                  <option value="serving">Serving</option>
                  <option value="done">Done</option>
                  <option value="transferred">Transferred</option>
                  <option value="no_show">No Show</option>
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
                  <tr v-for="item in queueItems.data" :key="item.id">
                    <td class="fw-bold">
                      {{ item.queue_number }}
                    </td>
                    <td>
                      <div class="fw-medium uppercase">
                        <!-- {{ item.patient.full_name }} -->
                        {{ item.patient.last_name }}
                        {{ item.patient.first_name }}
                        {{ item.patient.middle_name }} {{ item.patient.suffix }}
                        <p v-if="item.patient.is_priority">
                          <span
                            class="text-xs bg-danger text-white px-2 rounded-md"
                            >priority</span
                          >
                        </p>
                      </div>
                      <div v-if="item.patient.phone" class="text-muted small">
                        {{ item.patient.phone }}
                      </div>
                    </td>
                    <td>
                      <div class="fw-medium">
                        {{ item.current_department.name }}
                      </div>
                      <div
                        v-if="item.current_department.room"
                        class="text-muted small"
                      >
                        {{ item.current_department.room }}
                      </div>
                      <div
                        v-if="
                          item.queue_number.substring(0, 3) !==
                          item.current_department.code
                        "
                        class="text-info small"
                      >
                        Originally: {{ item.original_department.name }}
                      </div>
                    </td>
                    <td>
                      <span
                        :class="getStatusBadgeClass(item.status)"
                        class="badge"
                      >
                        {{ getStatusLabel(item.status) }}
                      </span>
                    </td>
                    <td class="text-muted">
                      <div>{{ formatTime(item.created_at) }}</div>
                      <div v-if="item.called_at" class="small">
                        Called: {{ formatTime(item.called_at) }}
                      </div>
                    </td>
                    <td>
                      {{ item.served_by_user?.name || "-" }}
                    </td>
                    <td>
                      <div
                        class="btn-group"
                        role="group"
                        v-if="hasAccessToDepartment(item.current_department)"
                      >
                        <!-- <button
                          v-if="item.status === 'waiting'"
                          @click="callPatient(item.id)"
                          class="btn btn-success btn-sm"
                        >
                          Call
                        </button> -->
                        <!-- <button
                          v-if="item.status === 'serving'"
                          @click="completeService(item.id)"
                          class="btn btn-primary btn-sm"
                        >
                          Complete
                        </button> -->
                        <Link
                          :href="
                            route(
                              'queue.department',
                              item.current_department.id
                            )
                          "
                          class="btn btn-info btn-sm"
                        >
                          View
                        </Link>
                      </div>
                    </td>
                  </tr>
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
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";

const props = defineProps({
  queueItems: Object,
  departments: Array,
  filters: Object,
  user: Object,
});

const isAdmin = computed(() => props.user?.role === "admin");
const isReception = computed(() => props.user?.role === "reception");

const hasAccessToDepartment = (department) => {
  if (isReception.value) return false;
  if (isAdmin.value) return true;
  return (
    department.users && department.users.some((u) => u.id === props.user.id)
  );
};

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

const getStatusBadgeClass = (status) => {
  const classes = {
    waiting: "bg-warning text-dark",
    serving: "bg-success text-white",
    done: "bg-secondary text-white",
    transferred: "bg-info text-white",
    no_show: "bg-danger text-white",
  };
  return classes[status] || "bg-secondary text-white";
};

const getStatusLabel = (status) => {
  const map = {
    waiting: "WAITING",
    serving: "SERVING",
    done: "DONE",
    transferred: "TRANSFERRED",
    no_show: "NO SHOW",
  };
  return map[status] || status.toUpperCase();
};

const formatTime = (datetime) => {
  return new Date(datetime).toLocaleTimeString("en-US", {
    hour: "2-digit",
    minute: "2-digit",
  });
};
</script>