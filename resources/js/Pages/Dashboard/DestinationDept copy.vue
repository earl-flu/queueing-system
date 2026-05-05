<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import DataTable from "datatables.net";
import "datatables.net-bs5/js/dataTables.bootstrap5.mjs";
import "datatables.net-bs5/css/dataTables.bootstrap5.min.css";
import {
  ref,
  computed,
  watch,
  onMounted,
  onBeforeUnmount,
  nextTick,
} from "vue";

const props = defineProps({
  departments: {
    type: Array,
    default: () => [],
  },
  selectedDate: {
    type: String,
    default: () => new Date().toISOString().split("T")[0],
  },
});

const selectedDate = ref(
  props.selectedDate || new Date().toISOString().split("T")[0]
);
const modalDept = ref(null);
const deptTable = ref(null);
const deptTableWrapper = ref(null);
let deptDataTable = null;

const todayStr = computed(() => new Date().toISOString().split("T")[0]);

watch(
  () => props.selectedDate,
  (v) => {
    if (v) {
      selectedDate.value = v;
    }
  }
);

const updateDate = () => {
  router.get(
    route("dashboard.destination"),
    { date: selectedDate.value },
    { preserveScroll: true }
  );
};

const patientSearch = ref("");

const openPatientModal = (dept) => {
  patientSearch.value = "";
  modalDept.value = dept;
};

const closeModal = () => {
  modalDept.value = null;
  patientSearch.value = "";
};

function modalPatientSearchText(row) {
  const parts = [
    row.queue_number,
    row.patient_name,
    row.status,
    row.current_department_name,
    row.at_destination ? "yes" : "no",
    row.served_at_destination ? "yes" : "no",
    row.seconds_to_destination,
    row.waiting_seconds_at_destination,
    row.serving_seconds_at_destination,
    row.time_start,
    row.time_finished,
    row.total_seconds_finished,
    formatTime(row.seconds_to_destination),
    formatTime(row.waiting_seconds_at_destination),
    formatTime(row.serving_seconds_at_destination),
    formatTime(row.total_seconds_finished),
  ];
  return parts
    .map((p) => (p === null || p === undefined ? "" : String(p)))
    .join(" ")
    .toLowerCase();
}

const filteredModalPatients = computed(() => {
  const list = modalDept.value?.patients;
  if (!list?.length) {
    return [];
  }
  const q = patientSearch.value.trim().toLowerCase();
  if (!q) {
    return list;
  }
  return list.filter((row) => modalPatientSearchText(row).includes(q));
});

function printModalPatients() {
  document.body.classList.add("destination-printing");
  nextTick(() => {
    window.print();
    const end = () => document.body.classList.remove("destination-printing");
    window.addEventListener("afterprint", end, { once: true });
    setTimeout(end, 800);
  });
}

const formatTime = (seconds) => {
  if (seconds === null || seconds === undefined || Number.isNaN(seconds)) {
    return "—";
  }
  const s = Number(seconds);
  if (s < 0) {
    return "—";
  }
  const hours = Math.floor(s / 3600);
  const minutes = Math.floor((s % 3600) / 60);
  const secs = Math.round(s % 60);

  if (hours > 0) {
    return `${hours}:${minutes.toString().padStart(2, "0")}:${secs
      .toString()
      .padStart(2, "0")}`;
  }
  return `${minutes}:${secs.toString().padStart(2, "0")}`;
};

const getStatusColor = (status) => {
  switch (status) {
    case "waiting":
      return "text-warning";
    case "serving":
      return "text-primary";
    case "done":
      return "text-success";
    case "transferred":
      return "text-info";
    case "skipped":
      return "text-secondary";
    case "no_show":
      return "text-danger";
    default:
      return "text-muted";
  }
};

function escapeHtml(value) {
  if (value === null || value === undefined) {
    return "";
  }
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function buildDeptColumns() {
  return [
    {
      data: "name",
      title: "Department",
      render: (data, type, row) => {
        if (type !== "display") {
          return data;
        }
        const code = row.code
          ? ` <span class="text-muted small">(${escapeHtml(row.code)})</span>`
          : "";
        return `<span class="fw-semibold">${escapeHtml(data)}</span>${code}`;
      },
    },
    {
      data: "total_patients",
      title: "Total patients",
      className: "text-nowrap",
      render: (data, type, row) => {
        if (type !== "display") {
          return data;
        }
        if (data === 0) {
          return "0";
        }
        return `<button type="button" class="btn btn-link p-0 align-baseline fw-semibold js-dept-patients" data-dept-id="${escapeHtml(
          String(row.id)
        )}">${escapeHtml(String(data))}</button>`;
      },
    },
    {
      data: "total_served",
      title: "Total served",
    },
    {
      data: null,
      title: "Details (averages)",
      orderable: false,
      className: "small",
      render: (_data, type, row) => {
        if (type !== "display") {
          return "";
        }
        const s = escapeHtml(formatTime(row.avg_serving_seconds));
        const w = escapeHtml(formatTime(row.avg_waiting_seconds));
        const t = escapeHtml(formatTime(row.avg_time_to_destination_seconds));
        return `<div>Avg. time served (at destination): ${s}</div><div>Avg. waiting (at destination): ${w}</div><div>Avg. time until arrival at destination: ${t}</div>`;
      },
    },
  ];
}

function initDeptDataTable() {
  if (!deptTable.value) {
    return;
  }
  deptDataTable?.destroy();
  deptDataTable = null;

  deptDataTable = new DataTable(deptTable.value, {
    data: props.departments,
    columns: buildDeptColumns(),
    order: [[0, "asc"]],
    pageLength: 25,
    lengthMenu: [10, 25, 50, 100],
    autoWidth: false,
    language: {
      emptyTable: "No active departments.",
    },
  });
}

function onDeptTableClick(e) {
  const btn = e.target.closest(".js-dept-patients");
  if (!btn || !deptTableWrapper.value?.contains(btn)) {
    return;
  }
  const id = Number(btn.getAttribute("data-dept-id"));
  const dept = props.departments.find((d) => d.id === id);
  if (dept) {
    openPatientModal(dept);
  }
}

onMounted(() => {
  deptTableWrapper.value?.addEventListener("click", onDeptTableClick);
  nextTick(() => initDeptDataTable());
});

watch(
  () => props.departments,
  () => nextTick(() => initDeptDataTable()),
  { deep: true }
);

onBeforeUnmount(() => {
  deptTableWrapper.value?.removeEventListener("click", onDeptTableClick);
  deptDataTable?.destroy();
  deptDataTable = null;
});
</script>

<template>
  <Head title="Destination Dashboard" />

  <AuthenticatedLayout>
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Destination Dashboard</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item">
              <a href="javascript:;"><i class="bx bx-home-alt"></i></a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
              Destination (original department)
              <span v-if="selectedDate !== todayStr" class="ms-2 text-muted">
                ({{ selectedDate }})
              </span>
            </li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-12">
        <div class="card radius-10">
          <div class="card-body py-3">
            <div
              class="d-flex flex-wrap align-items-center justify-content-between gap-2"
            >
              <div>
                <h6 class="mb-0">Date</h6>
                <small class="text-muted">
                  Patient counts use each queue row’s original department (the
                  destination chosen when the ticket was created).
                </small>
              </div>
              <input
                type="date"
                v-model="selectedDate"
                class="form-control"
                style="max-width: 200px"
                @change="updateDate"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card radius-10">
          <div class="card-header">
            <h6 class="mb-0">By destination department</h6>
          </div>
          <div class="card-body">
            <div
              ref="deptTableWrapper"
              class="table-responsive destination-dept-dt"
            >
              <table
                ref="deptTable"
                class="table table-striped table-hover w-100"
              ></table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Patient list: destination cohort for selected department -->
    <div
      v-if="modalDept"
      class="modal fade show d-block"
      tabindex="-1"
      style="background-color: rgba(0, 0, 0, 0.45)"
      @click.self="closeModal"
    >
      <div
        class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable"
      >
        <div class="modal-content destination-modal-print">
          <div class="modal-header">
            <h5 class="modal-title">
              Patients — {{ modalDept.name }}
              <span class="text-muted fw-normal small ms-2">{{
                selectedDate
              }}</span>
            </h5>
            <button
              type="button"
              class="btn-close d-print-none"
              aria-label="Close"
              @click="closeModal"
            />
          </div>
          <div class="modal-body">
            <p class="text-muted small">
              One row per patient. Status and current department reflect where
              they are in the flow. Time to destination queue is from the first
              ticket that day until the patient enters this department’s queue;
              waiting and serving use the visit row at the destination. “Served
              here” is yes if any step that day shows this department as both
              journey destination and current location with status transferred
              or done.
            </p>
            <div
              class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 d-print-none"
            >
              <div class="flex-grow-1" style="min-width: 200px">
                <label class="form-label small text-muted mb-1 d-block"
                  >Search</label
                >
                <input
                  v-model="patientSearch"
                  type="search"
                  class="form-control form-control-sm"
                  placeholder="Queue #, name, status, department…"
                  autocomplete="off"
                />
              </div>
              <div class="small text-muted align-self-end pb-1">
                <span v-if="modalDept.patients?.length">
                  Showing {{ filteredModalPatients.length }} of
                  {{ modalDept.patients.length }}
                </span>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead>
                  <tr>
                    <th>Queue #</th>
                    <th>Patient</th>
                    <th>Status</th>
                    <th>Current<br />department</th>
                    <th class="text-nowrap">Time to<br />dest. queue</th>
                    <th class="text-nowrap">Waiting <br />@ dest.</th>
                    <th class="text-nowrap">Serving <br />@ dest.</th>
                    <th class="text-nowrap">Time<br />start</th>
                    <th class="text-nowrap">Time<br />finished</th>
                    <th class="text-nowrap">Total<br />to finish</th>
                    <th>Served here</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(row, idx) in filteredModalPatients"
                    :key="`${row.queue_number}-${idx}`"
                  >
                    <td>{{ row.queue_number }}</td>
                    <td>{{ row.patient_name }}</td>
                    <td>
                      <span :class="getStatusColor(row.status)">{{
                        row.status
                      }}</span>
                    </td>
                    <td>{{ row.current_department_name }}</td>
                    <td class="text-nowrap small">
                      {{ formatTime(row.seconds_to_destination) }}
                    </td>
                    <td class="text-nowrap small">
                      {{ formatTime(row.waiting_seconds_at_destination) }}
                    </td>
                    <td class="text-nowrap small">
                      {{ formatTime(row.serving_seconds_at_destination) }}
                    </td>
                    <td class="text-nowrap small">
                      {{ row.time_start || "—" }}
                    </td>
                    <td class="text-nowrap small">
                      {{ row.time_finished || "—" }}
                    </td>
                    <td class="text-nowrap small">
                      {{ formatTime(row.total_seconds_finished) }}
                    </td>
                    <td>
                      <span
                        :class="
                          row.served_at_destination
                            ? 'text-success'
                            : 'text-muted'
                        "
                      >
                        {{ row.served_at_destination ? "Yes" : "No" }}
                      </span>
                    </td>
                  </tr>
                  <tr
                    v-if="
                      modalDept.patients?.length &&
                      !filteredModalPatients.length
                    "
                  >
                    <td colspan="12" class="text-center text-muted py-3">
                      No rows match your search.
                    </td>
                  </tr>
                  <tr v-if="!modalDept.patients?.length">
                    <td colspan="12" class="text-center text-muted py-3">
                      No patients for this destination on this date.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer d-print-none">
            <button
              type="button"
              class="btn btn-outline-primary"
              @click="printModalPatients"
            >
              <i class="bx bx-printer me-1"></i>
              Print
            </button>
            <button type="button" class="btn btn-secondary" @click="closeModal">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.card {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.destination-dept-dt :deep(table thead th) {
  border-top: none;
  font-weight: 600;
  color: #495057;
}
</style>

<!-- Print: hide entire page except the modal report (Vue scoped styles do not apply here). -->
<style>
@media print {
  body.destination-printing * {
    visibility: hidden;
  }
  body.destination-printing .destination-modal-print,
  body.destination-printing .destination-modal-print * {
    visibility: visible;
  }
  body.destination-printing .destination-modal-print {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    max-width: 100%;
    margin: 0;
    border: none;
    box-shadow: none;
  }
  body.destination-printing .modal-dialog {
    max-width: 100%;
    margin: 0;
  }
}
</style>
