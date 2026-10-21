<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DataTable from "datatables.net-vue3";
import DataTablesCore from "datatables.net-bs5";
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";

DataTable.use(DataTablesCore);

const props = defineProps({
  patients: Array,
  department: Object,
  selectedDate: String,
  totalLogged: Number,
  totalCompleted: Number,
});

const formattedSelectedDate = computed(() => {
  const date = new Date(props.selectedDate);
  if (isNaN(date.getTime())) return props.selectedDate;
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
});

// ---------- Shared formatting helpers ----------
const formatTime = (data) => {
  if (!data) return "-";
  const date = new Date(data);
  if (isNaN(date.getTime())) return "-";
  return date.toLocaleTimeString([], {
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  });
};

const computeAverageDuration = computed(() => {
  const patients = props.patients;
  if (!Array.isArray(patients) || patients.length === 0) return "-";
  let totalMs = 0;
  let count = 0;
  for (const patient of patients) {
    const { start_time, finished_time } = patient;
    if (start_time && finished_time) {
      const start = new Date(start_time);
      const end = new Date(finished_time);
      if (!isNaN(start.getTime()) && !isNaN(end.getTime()) && end > start) {
        totalMs += end - start;
        count++;
      }
    }
  }
  if (count === 0) return "-";
  const avgMs = totalMs / count;
  const seconds = Math.floor(avgMs / 1000) % 60;
  const minutes = Math.floor(avgMs / (1000 * 60)) % 60;
  const hours = Math.floor(avgMs / (1000 * 60 * 60));
  return [
    hours > 0 ? `${hours}h` : "",
    minutes > 0 ? `${minutes}m` : "",
    `${seconds}s`,
  ]
    .filter(Boolean)
    .join(" ");
});

const formatDuration = (startTime, finishedTime) => {
  if (!startTime || !finishedTime) return "-";
  const start = new Date(startTime);
  const end = new Date(finishedTime);
  if (isNaN(start.getTime()) || isNaN(end.getTime())) return "";
  const diffMs = end - start;
  if (diffMs < 0) return "";
  const seconds = Math.floor(diffMs / 1000) % 60;
  const minutes = Math.floor(diffMs / (1000 * 60)) % 60;
  const hours = Math.floor(diffMs / (1000 * 60 * 60));
  return [
    hours > 0 ? `${hours}h` : "",
    minutes > 0 ? `${minutes}m` : "",
    `${seconds}s`,
  ]
    .filter(Boolean)
    .join(" ");
};

const columns = [
  { data: "queue_number", title: "Queue Number" },
  {
    data: "full_name",
    title: "Patient Name",
    render: (data) => (typeof data === "string" ? data.toUpperCase() : data),
  },
  { data: "dept", title: "Current Department" },
  { data: "status", title: "Status" },
  { data: "start_time", title: "Started", render: formatTime },
  { data: "finished_time", title: "Completed", render: formatTime },
  {
    data: null,
    title: "Total Time",
    render: (data, type, row) =>
      formatDuration(row.start_time, row.finished_time),
  },
];

const options = {
  ordering: true,
  pageLength: 10,
  order: [[0, "desc"]],
  columnDefs: [{ targets: "_all", className: "dt-left" }],
};

// ---------- Print ----------
const escapeHtml = (value) =>
  String(value ?? "").replace(
    /[&<>"']/g,
    (c) =>
      ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;",
      }[c])
  );

const printLog = () => {
  // Same default order as the table: queue number descending
  const rows = [...props.patients].sort((a, b) =>
    String(b.queue_number).localeCompare(String(a.queue_number), undefined, {
      numeric: true,
    })
  );

  const bodyRows = rows
    .map(
      (p) => `
      <tr>
        <td>${escapeHtml(p.queue_number)}</td>
        <td>${escapeHtml(String(p.full_name ?? "").toUpperCase())}</td>
        <td>${escapeHtml(p.dept)}</td>
        <td>${escapeHtml(p.status)}</td>
        <td>${escapeHtml(formatTime(p.start_time))}</td>
        <td>${escapeHtml(formatTime(p.finished_time))}</td>
        <td>${escapeHtml(formatDuration(p.start_time, p.finished_time))}</td>
      </tr>`
    )
    .join("");

  const html = `
    <!DOCTYPE html>
    <html>
      <head>
        <title>${escapeHtml(props.department.name)} Log Book - ${escapeHtml(
    formattedSelectedDate.value
  )}</title>
        <style>
          @page { size: A4 landscape; margin: 12mm; }
          body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
          h1 { font-size: 20px; margin: 0; text-transform: uppercase; }
          p { margin: 4px 0 16px; font-weight: bold; color: #555; text-transform: uppercase; }
          table { width: 100%; border-collapse: collapse; }
          th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
          th { background: #eee; }
          thead { display: table-header-group; }
          tr { page-break-inside: avoid; }
        </style>
      </head>
      <body>
      <div style="display:flex; gap:10px; margin-bottom:35px;">
       <div class="logo-icon">
        <img src="/images/EBMC-logo.png" class="logo-img" alt="" style="width:60px;" />
      </div>
      <div>
       <p style="margin-bottom:0;">EASTERN BICOL MEDICAL CENTER</p>
        <p style="margin-bottom:0;">OUTPATIENT DEPARTMENT</p>
      </div>
      </div>
      
        <h1>${escapeHtml(props.department.name)} Log Book</h1>
        <p>${escapeHtml(formattedSelectedDate.value)}</p>

        <table style="width:auto; margin-bottom:15px;">
            <tr>
              <td>TOTAL</td>
              <td class="px-2">:</td>
              <td>${escapeHtml(props.totalLogged)}</td>
            </tr>
            <tr>
              <td>COMPLETED</td>
              <td class="px-2">:</td>
              <td>${escapeHtml(props.totalCompleted)}</td>
            </tr>
            <tr
              title="From when it is encoded/entered into the queue until it is completed"
            >
              <td>AVG TOTAL DURATION</td>
              <td class="px-2">:</td>
              <td>${escapeHtml(computeAverageDuration.value)}</td>
            </tr>
          </table>
        <table>
          <thead>
            <tr>${columns
              .map((c) => `<th>${escapeHtml(c.title)}</th>`)
              .join("")}</tr>
          </thead>
          <tbody>
            ${
              bodyRows ||
              `<tr><td colspan="${columns.length}">No records found.</td></tr>`
            }
          </tbody>
        </table>
      </body>
    </html>`;

  const iframe = document.createElement("iframe");
  iframe.style.cssText =
    "position:fixed;right:0;bottom:0;width:0;height:0;border:0;visibility:hidden;";
  document.body.appendChild(iframe);

  const doc = iframe.contentWindow.document;
  doc.open();
  doc.write(html);
  doc.close();

  setTimeout(() => {
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
    setTimeout(() => document.body.removeChild(iframe), 1000);
  }, 250);
};
</script>

<template>
  <Head :title="`Log Book for ${department.name}`" />

  <AuthenticatedLayout>
    <div class="card">
      <div class="card-body">
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <Link :href="route('log-book.index')">Log Book</Link>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
              {{ department.name }}
            </li>
          </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h5 class="card-title text-3xl uppercase mb-0">
              {{ department.name }} Log Book
            </h5>
            <p class="uppercase font-bold text-gray-500 text-lg">
              {{ formattedSelectedDate }}
            </p>
          </div>
        </div>
        <button
          type="button"
          class="btn btn-sm btn-primary d-flex align-items-center gap-1"
          @click="printLog"
          style="font-size: 0.95rem; padding: 0.25rem 0.6rem"
        >
          <i class="material-icons-outlined">print</i>
          Print
        </button>

        <!-- DATA TABLE -->
        <div class="mt-10">
          <table>
            <tr>
              <td>TOTAL</td>
              <td class="px-2">:</td>
              <td>{{ totalLogged }}</td>
            </tr>
            <tr>
              <td>COMPLETED</td>
              <td class="px-2">:</td>
              <td>{{ totalCompleted }}</td>
            </tr>
            <tr
              title="From when it is encoded/entered into the queue until it is completed"
            >
              <td>AVG TOTAL DURATION</td>
              <td class="px-2">:</td>
              <td>{{ computeAverageDuration }}</td>
            </tr>
          </table>
          <div class="max-w-4xl">
            <DataTable
              :data="patients"
              :columns="columns"
              :options="options"
              class="display border border-gray-200 class-table-styles"
            >
            </DataTable>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style>
@import "bootstrap/dist/css/bootstrap.css";
@import "datatables.net-bs5/css/dataTables.bootstrap5.css";
.dataTable tr td {
  border: 1px solid #cdcdcd;
  padding: 0 4px;
}

.dataTable tbody tr:hover {
  background-color: #e3e1e1;
}

.dataTable thead th {
  cursor: pointer;
}
</style>