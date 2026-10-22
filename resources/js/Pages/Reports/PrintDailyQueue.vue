<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { computed, nextTick, onBeforeUnmount, onMounted } from "vue";
import { useFormatting } from "@/Composables/useFormatting";

const props = defineProps({
  filters: Object,
  departments: Array,
  patients: Array,
  totalData: Object,
  avgData: Object,
});

const printedAt = new Date().toLocaleString();

const { formatTime } = useFormatting();

const selectedDepartmentName = computed(() => {
  const dept = props.departments.find(
    (d) => d.id === Number(props.filters?.department_id)
  );
  return dept ? dept.name : "";
});

const formattedDate = computed(() => {
  const value = props.filters?.date;
  if (!value) return "";
  const date = new Date(value);
  if (isNaN(date.getTime())) return value;
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
});

const sortedPatients = computed(() => {
  return [...(props.patients || [])].sort((b, a) =>
    String(b.queue_number).localeCompare(String(a.queue_number), undefined, {
      numeric: true,
    })
  );
});

function getDepartmentName(deptId) {
  const dept = props.departments.find((d) => d.id === Number(deptId));
  return dept ? dept.name : "";
}

function patientName(row) {
  return `${row.last_name || ""} ${row.first_name || ""}`.trim();
}

function formatStatus(status) {
  if (!status) return "";
  return status.charAt(0).toUpperCase() + status.slice(1).toLowerCase();
}

const formatDisplayTime = (datetimeStr) => {
  if (!datetimeStr) return "";
  const date = new Date(datetimeStr);
  if (isNaN(date.getTime())) return "";
  let hours = date.getHours();
  const minutes = date.getMinutes();
  const ampm = hours >= 12 ? "PM" : "AM";
  hours = hours % 12;
  hours = hours ? hours : 12;
  const minutesStr = minutes < 10 ? "0" + minutes : minutes;
  return `${hours}:${minutesStr} ${ampm}`;
};

const printReport = () => {
  window.print();
};

onMounted(() => {
  document.body.classList.add("print-daily-queue");
  nextTick(() => {
    window.print();
  });
});

onBeforeUnmount(() => {
  document.body.classList.remove("print-daily-queue");
});
</script>

<template>
  <Head :title="`Print Department Report (${selectedDepartmentName})`" />

  <div class="print-daily-queue-page">
    <div class="no-print print-toolbar">
      <Link
        :href="
          route('reports.department.index', {
            department_id: filters.department_id,
            date: filters.date,
          })
        "
        class="btn btn-sm btn-secondary d-flex align-items-center gap-1"
      >
        Back
      </Link>
      <button
        type="button"
        class="btn btn-sm btn-primary d-flex align-items-center gap-1"
        @click="printReport"
      >
        <i class="material-icons-outlined">print</i>
        Print
      </button>
    </div>
    <div style="display: flex; gap: 10px; margin-bottom: 35px">
      <div class="logo-icon">
        <img
          src="/images/EBMC-logo.png"
          class="logo-img"
          alt=""
          style="width: 60px"
        />
      </div>
      <div>
        <p style="margin-bottom: 0">EASTERN BICOL MEDICAL CENTER</p>
        <p style="margin-bottom: 0">OUTPATIENT DEPARTMENT</p>
      </div>
    </div>
    <h1 class="print-title">
      Department Report ({{ selectedDepartmentName }})
    </h1>
    <p class="logbook-date" style="margin: 0">{{ formattedDate }}</p>
    <p class="print-date">Printed: {{ printedAt }}</p>
    <div class="summary-grid">
      <div class="summary-card">
        <div class="summary-heading">Volume Overview</div>
        <table>
          <tr>
            <td>Total Queues</td>
            <td class="px-2">:</td>
            <td>{{ totalData.patients }}</td>
          </tr>
          <tr>
            <td>Waiting</td>
            <td class="px-2">:</td>
            <td>{{ totalData.waiting }}</td>
          </tr>
          <tr>
            <td>Skipped</td>
            <td class="px-2">:</td>
            <td>{{ totalData.skipped }}</td>
          </tr>
          <tr>
            <td>With Skip Record</td>
            <td class="px-2">:</td>
            <td>{{ totalData.with_skip_history }}</td>
          </tr>
        </table>
      </div>

      <div class="summary-card">
        <div class="summary-heading">Queue Timelines</div>
        <table>
          <tr>
            <td>First</td>
            <td class="px-2">:</td>
            <td>{{ formatDisplayTime(totalData.first_waiting_time) }}</td>
          </tr>
          <tr>
            <td>Last</td>
            <td class="px-2">:</td>
            <td>{{ formatDisplayTime(totalData.last_waiting_time) }}</td>
          </tr>
        </table>
      </div>

      <div class="summary-card">
        <div class="summary-heading">Call Timelines</div>
        <table>
          <tr>
            <td>First</td>
            <td class="px-2">:</td>
            <td>{{ formatDisplayTime(totalData.first_called_time) }}</td>
          </tr>
          <tr>
            <td>Last</td>
            <td class="px-2">:</td>
            <td>{{ formatDisplayTime(totalData.last_called_time) }}</td>
          </tr>
        </table>
      </div>

      <div class="summary-card">
        <div class="summary-heading">Performace</div>
        <table>
          <tr>
            <td>Avg Wait</td>
            <td class="px-2">:</td>
            <td>{{ formatTime(avgData.avg_wait) }}</td>
          </tr>
          <tr>
            <td>Avg Serve</td>
            <td class="px-2">:</td>
            <td>{{ formatTime(avgData.avg_serve) }}</td>
          </tr>
        </table>
      </div>
    </div>

    <table class="report-table">
      <thead>
        <tr>
          <th>Queue Number</th>
          <th>Name</th>
          <th>Start Time</th>
          <th>End Time</th>
          <th>Status</th>
          <th>Is Priority</th>
          <th>Waiting Time</th>
          <th>Serving Time</th>
          <th>Skip Record</th>
          <th>Target Service</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, index) in sortedPatients" :key="index">
          <td>{{ row.queue_number }}</td>
          <td class="uppercase">{{ patientName(row) }}</td>
          <td>{{ formatDisplayTime(row.created_at) }}</td>
          <td>{{ formatDisplayTime(row.completed_at) }}</td>
          <td>{{ formatStatus(row.status) }}</td>
          <td>{{ row.is_priority ? "Yes" : "No" }}</td>
          <td>{{ formatTime(row.waiting_duration_seconds) }}</td>
          <td>{{ formatTime(row.serving_duration_seconds) }}</td>
          <td>{{ row.skipped_at ? "Yes" : "No" }}</td>
          <td>{{ getDepartmentName(row.original_department_id) }}</td>
        </tr>
        <tr v-if="!sortedPatients.length">
          <td colspan="10" class="empty-row">
            No queue records for this date.
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style>
.print-daily-queue-page {
  background: #fff;
  color: #111;
  padding: 24px;
  max-width: 1200px;
  margin: 0 auto;
}

.print-toolbar {
  display: flex;
  gap: 8px;
  margin-bottom: 16px;
}

.print-title {
  font-size: 1.75rem;
  font-weight: 700;
  margin: 0 0 4px;
}

.logbook-date {
  margin: 0 0 20px;
  font-weight: 600;
  color: #555;
  text-transform: uppercase;
}

.print-date {
  font-size: 10px;
  color: #cdcdcd;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 12px;
  margin-bottom: 24px;
}

.summary-card {
  /* border: 1px solid #cdcdcd; */
  border-radius: 6px;
  padding: 12px;
  break-inside: avoid;
}

.summary-heading {
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  margin-bottom: 8px;
}

.report-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}

.report-table th,
.report-table td {
  border: 1px solid #cdcdcd;
  padding: 4px 6px;
  text-align: left;
}

.report-table thead th {
  background: #f3f3f3;
  font-weight: 600;
}

.empty-row {
  text-align: center;
  color: #666;
}

@media print {
  .no-print {
    display: none !important;
  }

  @page {
    size: landscape;
  }

  header,
  aside,
  footer,
  .overlay,
  .theme-customizer,
  .main-footer {
    display: none !important;
  }

  body.print-daily-queue,
  .print-daily-queue-page {
    background: #fff !important;
    padding: 0;
    max-width: none;
    margin: 0;
  }

  .summary-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }

  .report-table thead {
    display: table-header-group;
  }

  .report-table tr {
    break-inside: avoid;
  }
}

@media (max-width: 900px) {
  .summary-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}
</style>
