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
  departments: Array,
  selectedDate: String,
});

const form = ref({
  date: props.selectedDate || new Date().toISOString().slice(0, 10),
});

const updateFilters = () => {
  router.get(
    route("log-book.index"),
    {
      date: form.value.date,
    },
    {
      preserveState: true, // Keeps the user's typing and scroll position
      replace: true, // Updates the URL without creating a massive back-button history
    }
  );
};

const columns = [
  { data: "name", title: "Department" },
  { data: "total_logged_patient", title: "Total Logged Patients" },
  {
    data: null,
    title: "Actions",
    orderable: false,
    searchable: false,
    defaultContent: "",
  },
];

// Configuration options for DataTables
const options = {
  ordering: true,
  pageLength: 10,
  order: [[0, "desc"]], // Sort by ID descending by default
  columnDefs: [{ targets: "_all", className: "dt-left" }],
};
</script>

<template>
  <Head :title="`Log Book`" />

  <AuthenticatedLayout>
    <div class="card">
      <div class="card-body">
        <h5 class="card-title mb-4 text-3xl">Log Book</h5>

        <!-- FILTER CONTROLS -->
        <div class="flex gap-4 mb-10">
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

        <!-- DATA TABLE -->
        <div class="mt-10">
          <div class="max-w-2xl">
            <DataTable
              :data="departments"
              :columns="columns"
              :options="options"
              class="display border border-gray-200 class-table-styles"
            >
              <template #column-2="{ rowData }">
                <Link
                  :href="
                    route('log-book.show', {
                      id: rowData.id,
                      date: form.date,
                    })
                  "
                  class="text-blue-600 hover:text-blue-800 underline"
                >
                  View
                </Link>
              </template>
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