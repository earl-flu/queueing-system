<script setup>
import DataTable from "datatables.net";
import "datatables.net-bs5/js/dataTables.bootstrap5.mjs";
import "datatables.net-bs5/css/dataTables.bootstrap5.min.css";
import { ref, watch, onMounted, onBeforeUnmount, nextTick } from "vue";
import { useFormatting } from "@/Composables/useFormatting";

const props = defineProps({
  departments: { type: Array, default: () => [] },
});

const emit = defineEmits(["open-modal"]);

const { formatTime } = useFormatting();

const tableEl = ref(null);
const wrapperEl = ref(null);
let dt = null;

function escapeHtml(value) {
  if (value === null || value === undefined) return "";
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function buildColumns() {
  return [
    {
      data: "name",
      title: "Department",
      render: (data, type, row) => {
        if (type !== "display") return data;
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
        if (type !== "display") return data;
        if (data === 0) return "0";
        return `<button type="button" class="btn btn-link p-0 align-baseline fw-semibold js-dept-patients"
          data-dept-id="${escapeHtml(String(row.id))}">${escapeHtml(
          String(data)
        )}</button>`;
      },
    },
    { data: "total_served", title: "Total served" },
    {
      data: null,
      title: "Details (averages)",
      orderable: false,
      className: "small",
      render: (_data, type, row) => {
        if (type !== "display") return "";
        return [
          `<div>Avg. time served (at destination): ${escapeHtml(
            formatTime(row.avg_serving_seconds)
          )}</div>`,
          `<div>Avg. waiting (at destination): ${escapeHtml(
            formatTime(row.avg_waiting_seconds)
          )}</div>`,
          `<div>Avg. time until arrival at destination: ${escapeHtml(
            formatTime(row.avg_time_to_destination_seconds)
          )}</div>`,
        ].join("");
      },
    },
  ];
}

function initTable() {
  if (!tableEl.value) return;
  dt?.destroy();
  dt = new DataTable(tableEl.value, {
    data: props.departments,
    columns: buildColumns(),
    order: [[0, "asc"]],
    pageLength: 25,
    lengthMenu: [10, 25, 50, 100],
    autoWidth: false,
    language: { emptyTable: "No active departments." },
  });
}

function onTableClick(e) {
  const btn = e.target.closest(".js-dept-patients");
  if (!btn || !wrapperEl.value?.contains(btn)) return;
  const dept = props.departments.find(
    (d) => d.id === Number(btn.dataset.deptId)
  );
  if (dept) emit("open-modal", dept);
}

onMounted(() => {
  wrapperEl.value?.addEventListener("click", onTableClick);
  nextTick(initTable);
});

watch(
  () => props.departments,
  () => nextTick(initTable),
  { deep: true }
);

onBeforeUnmount(() => {
  wrapperEl.value?.removeEventListener("click", onTableClick);
  dt?.destroy();
  dt = null;
});
</script>

<template>
  <div ref="wrapperEl" class="table-responsive destination-dept-dt">
    <table ref="tableEl" class="table table-striped table-hover w-100"></table>
  </div>
</template>

<style scoped>
.destination-dept-dt :deep(table thead th) {
  border-top: none;
  font-weight: 600;
  color: #495057;
}
</style>