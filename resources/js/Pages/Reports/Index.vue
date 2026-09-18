<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DataTable from "datatables.net-vue3";
import DataTablesCore from "datatables.net-bs5";
import { ref, watch } from "vue";
import { Head, router, Link } from "@inertiajs/vue3";
import { useFormatting } from "@/Composables/useFormatting";
import { computed } from "vue";

DataTable.use(DataTablesCore);

const props = defineProps({
  filters: Object,
  departments: Array,
  patients: Array,
  totalData: Object,
  avgData: Object,
});

const { formatTime } = useFormatting();

const form = ref({
  department_id: props.filters.department_id || "",
  date: props.filters.date || "",
});

const updateFilters = () => {
  router.get(
    "/reports/department",
    {
      department_id: form.value.department_id,
      date: form.value.date,
    },
    {
      preserveState: true, // Keeps the user's typing and scroll position
      replace: true, // Updates the URL without creating a massive back-button history
    }
  );
};

const selectedDepartmentName = computed(() => {
  const dept = props.departments.find(
    (d) => d.id === Number(form.value.department_id)
  );
  return dept ? dept.name : "";
});

function getDepartmentName(deptId) {
  const dept = props.departments.find((d) => d.id === Number(deptId));
  return dept ? dept.name : "";
}

const formatDisplayTime = (datetimeStr) => {
  if (!datetimeStr) return "";
  const date = new Date(datetimeStr);
  if (isNaN(date.getTime())) return "";
  let hours = date.getHours();
  const minutes = date.getMinutes();
  const ampm = hours >= 12 ? "PM" : "AM";
  hours = hours % 12;
  hours = hours ? hours : 12; // the hour '0' should be '12'
  const minutesStr = minutes < 10 ? "0" + minutes : minutes;
  return `${hours}:${minutesStr} ${ampm}`;
};

const columns = [
  { data: "queue_number", title: "Queue Number" },
  {
    data: null,
    title: "Name",
    className: "uppercase",
    render: (data) => {
      if (!data) return "";

      if (typeof data === "object" && data !== null) {
        return `${data.last_name || ""} ${data.first_name || ""}`.trim();
      }
      return String(data);
    },
  },
  {
    data: "created_at",
    title: "Queue Time",
    render: function (data, type, row) {
      // If DataTables is sorting, filtering, or doing type detection, return the raw number
      if (type === "sort" || type === "type") {
        return data;
      }
      // Otherwise, return your formatted string for display
      return formatDisplayTime(data);
    },
  },
  {
    data: "is_priority",
    title: "Is Priority",
    render: (data) => {
      return data.is_priority ? "Yes" : "No";
    },
  },
  {
    data: "waiting_duration_seconds",
    title: "Waiting Time",
    render: function (data, type, row) {
      // If DataTables is sorting, filtering, or doing type detection, return the raw number
      if (type === "sort" || type === "type") {
        return data;
      }
      // Otherwise, return your formatted string for display
      return formatTime(data);
    },
  },
  {
    data: "serving_duration_seconds",
    title: "Serving Time",
    render: function (data, type, row) {
      // If DataTables is sorting, filtering, or doing type detection, return the raw number
      if (type === "sort" || type === "type") {
        return data;
      }
      // Otherwise, return your formatted string for display
      return formatTime(data);
    },
  },
  {
    data: "skipped_at",
    title: "Skip Record",
    render: (data) => {
      if (!data || data.skipped_at) return "No";
      return data.skipped_at ? "Yes" : "No";
    },
  },
  {
    data: null,
    title: "Service",
    render: (data) => getDepartmentName(data.original_department_id),
  },
  { data: "status", title: "Status" },
];

// Configuration options for DataTables
const options = {
  responsive: true,
  pageLength: 10,
  order: [[0, "desc"]], // Sort by ID descending by default
};
</script>

<template>
  <Head :title="`Department Report`" />

  <AuthenticatedLayout>
    <div class="card">
      <div class="card-body">
        <h5 class="card-title mb-4 text-3xl">
          Department Report ({{ selectedDepartmentName }})
        </h5>

        <!-- FILTER CONTROLS -->
        <div class="flex gap-4 mb-10">
          <!-- Department Dropdown -->
          <div>
            <label class="block text-sm font-medium">Department</label>
            <select
              v-model="form.department_id"
              @change="updateFilters"
              class="border p-2 rounded"
            >
              <option
                v-for="department in departments"
                :key="department.id"
                :value="department.id"
              >
                {{ department.name }}
              </option>
            </select>
          </div>

          <!-- Date Picker -->
          <div>
            <label class="block text-sm font-medium">Date</label>
            <input
              type="date"
              v-model="form.date"
              @change="updateFilters"
              class="border p-2 rounded"
            />
          </div>
        </div>
        <div
          class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6"
        >
          <div class="bg-white shadow rounded p-4">
            <div class="text-gray-900 text-sm mb-2 uppercase font-bold">
              Volume Overview
            </div>
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
            </table>
          </div>
          <div class="bg-white shadow rounded p-4">
            <div class="text-gray-900 text-sm mb-2 uppercase font-bold">
              Queue Timelines
            </div>
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
          <div class="bg-white shadow rounded p-4">
            <div class="text-gray-900 text-sm mb-2 uppercase font-bold">
              Call Timelines
            </div>
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
          <div class="bg-white shadow rounded p-4">
            <div class="text-gray-900 text-sm mb-2 uppercase font-bold">
              Performace
            </div>
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

        <!-- DATA TABLE -->
        <div class="mt-10">
          <DataTable
            :data="patients"
            :columns="columns"
            :options="options"
            class="display width-full border border-gray-200 class-table-styles"
          >
            <thead>
              <tr>
                <th class="border px-1">Queue Number</th>
                <th class="border px-1">Name</th>
                <th class="border px-1">Queue Time</th>
                <th class="border px-1">Is Priority</th>
                <th class="border px-1">Waiting Time</th>
                <th class="border px-1">Serving Time</th>
                <th class="border px-1">Skip Record</th>
                <th class="border px-1">Service</th>
                <th class="border px-1">Status</th>
              </tr>
            </thead>
          </DataTable>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
<style>
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