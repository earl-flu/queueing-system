<script setup>
import { ref, computed } from "vue";
import { useFormatting } from "@/Composables/useFormatting";

const props = defineProps({
  dept: { type: Object, required: true },
  selectedDate: { type: String, required: true },
});

const emit = defineEmits(["close"]);

const { formatTime, getStatusColor } = useFormatting();
const patientSearch = ref("");
const modalBodyRef = ref(null);

const filteredPatients = computed(() => {
  const list = props.dept?.patients;
  if (!list?.length) return [];
  const q = patientSearch.value.trim().toLowerCase();
  if (!q) return list;
  return list.filter((row) => searchText(row).includes(q));
});

function searchText(row) {
  return [
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
  ]
    .map((p) => (p == null ? "" : String(p)))
    .join(" ")
    .toLowerCase();
}

function printModalBody() {
  if (!modalBodyRef.value) return;

  const printWindow = window.open("", "_blank", "width=1200,height=800");
  if (!printWindow) return;

  const content = modalBodyRef.value.innerHTML;
  const title = `Patients - ${props.dept?.name || "Department"} (${
    props.selectedDate
  })`;

  printWindow.document.open();
  printWindow.document.write(`
    <!doctype html>
    <html>
      <head>
        <meta charset="utf-8" />
        <title>${title}</title>
        <style>
          body { font-family: Arial, sans-serif; margin: 20px; color: #222; }
          p { margin-bottom: 12px; }
          table { width: 100%; border-collapse: collapse; font-size: 12px; }
          th, td { border: 1px solid #ccc; padding: 6px; text-align: left; vertical-align: top; }
          thead th { background: #f5f5f5; }
          .text-success { color: #198754; }
          .text-muted { color: #6c757d; }
          .small border { font-size: 12px; }
          .text-nowrap { white-space: nowrap; }
          .uppercase { text-transform: uppercase; }
          .form-label, input { display: none !important; }
          @media print {
            body { margin: 0; }
          }
        </style>
      </head>
      <body>
        <h3>${title}</h3>
        ${content}
      </body>
    </html>
  `);
  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
  printWindow.close();
}
</script>

<template>
  <div
    class="modal fade show d-block"
    tabindex="-1"
    style="background-color: rgba(0, 0, 0, 0.45)"
    @click.self="emit('close')"
  >
    <div
      class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable"
    >
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            Patients — {{ dept.name }}
            <span class="text-muted fw-normal small ms-2">{{
              selectedDate
            }}</span>
          </h5>
          <button
            type="button"
            class="btn-close"
            aria-label="Close"
            @click="emit('close')"
          />
        </div>

        <div ref="modalBodyRef" class="modal-body">
          <p class="text-muted small">
            One row per patient. Status and current department reflect where
            they are in the flow. Time to destination queue is from the first
            ticket that day until the patient enters this department's queue;
            waiting and serving use the visit row at the destination. "Served
            here" is yes if any step that day shows this department as both
            journey destination and current location with status transferred or
            done.
          </p>

          <div
            class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3"
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
            <div
              v-if="dept.patients?.length"
              class="small text-muted align-self-end pb-1"
            >
              Showing {{ filteredPatients.length }} of
              {{ dept.patients.length }}
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-sm table-striped">
              <thead>
                <tr>
                  <th class="border">#</th>
                  <th class="border">Queue #</th>
                  <th class="border">Patient</th>
                  <th class="border">Current<br />department</th>
                  <th class="border">Status</th>
                  <th class="border text-nowrap">Time to<br />dest. queue</th>
                  <th class="border text-nowrap">Waiting<br />@ dest.</th>
                  <th class="border text-nowrap">Serving<br />@ dest.</th>
                  <th class="border text-nowrap">Time<br />start</th>
                  <th class="border text-nowrap">Time<br />finished</th>
                  <th class="border text-nowrap">Total<br />to finish</th>
                  <th class="border">Served here</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(row, idx) in filteredPatients"
                  :key="`${row.queue_number}-${idx}`"
                >
                  <td class="border">{{ idx + 1 }}</td>
                  <td class="border">{{ row.queue_number }}</td>
                  <td class="uppercase">{{ row.patient_name }}</td>
                  <td class="border">{{ row.current_department_name }}</td>
                  <td class="border">
                    <span :class="getStatusColor(row.status)">{{
                      row.status
                    }}</span>
                  </td>
                  <td class="text-nowrap small border">
                    {{ formatTime(row.seconds_to_destination) }}
                  </td>
                  <td class="text-nowrap small border">
                    {{ formatTime(row.waiting_seconds_at_destination) }}
                  </td>
                  <td class="text-nowrap small border">
                    {{ formatTime(row.serving_seconds_at_destination) }}
                  </td>
                  <td class="text-nowrap small border">
                    {{ row.time_start || "—" }}
                  </td>
                  <td class="text-nowrap small border">
                    {{ row.time_finished || "—" }}
                  </td>
                  <td class="text-nowrap small border">
                    {{ formatTime(row.total_seconds_finished) }}
                  </td>
                  <td class="border">
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
                <tr v-if="dept.patients?.length && !filteredPatients.length">
                  <td colspan="11" class="text-center text-muted py-3">
                    No rows match your search.
                  </td>
                </tr>
                <tr v-if="!dept.patients?.length">
                  <td colspan="11" class="text-center text-muted py-3">
                    No patients for this destination on this date.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-outline-primary"
            @click="printModalBody"
          >
            Print
          </button>
          <button
            type="button"
            class="btn btn-secondary"
            @click="emit('close')"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>