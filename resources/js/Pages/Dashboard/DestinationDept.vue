<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DepartmentTable from "@/Components/DepartmentTable.vue";
import PatientModal from "@/Components/PatientModal.vue";
import { Head, router } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";

const props = defineProps({
  departments: { type: Array, default: () => [] },
  selectedDate: {
    type: String,
    default: () => new Date().toISOString().split("T")[0],
  },
});

const selectedDate = ref(props.selectedDate);
const modalDept = ref(null);
const todayStr = computed(() => new Date().toISOString().split("T")[0]);

watch(
  () => props.selectedDate,
  (v) => {
    if (v) selectedDate.value = v;
  }
);

const updateDate = () => {
  router.get(
    route("dashboard.destination"),
    { date: selectedDate.value },
    { preserveScroll: true }
  );
};
</script>

<template>
  <Head title="Destination Dashboard" />

  <AuthenticatedLayout>
    <!-- Breadcrumb -->
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
              <span v-if="selectedDate !== todayStr" class="ms-2 text-muted"
                >({{ selectedDate }})</span
              >
            </li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Date picker -->
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
                  Patient counts use each queue row's original department (the
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

    <!-- Department table -->
    <div class="row">
      <div class="col-12">
        <div class="card radius-10">
          <div class="card-header">
            <h6 class="mb-0">By destination department</h6>
          </div>
          <div class="card-body">
            <DepartmentTable
              :departments="departments"
              @open-modal="modalDept = $event"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Patient modal -->
    <PatientModal
      v-if="modalDept"
      :dept="modalDept"
      :selected-date="selectedDate"
      @close="modalDept = null"
    />
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
</style>

<style>
</style>