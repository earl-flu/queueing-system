<template>
  <Head :title="`${department.name} - Queue`" />

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
              {{ department.name }}
            </li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-0">{{ department.name }} Queue</h4>
        <p class="text-muted mb-0">
          Room: {{ department.room || "N/A" }} | Today's Count: {{ todayCount }}
        </p>
      </div>
      <div class="d-flex gap-2">
        <Link :href="route('queue.create')" class="btn btn-grd btn-grd-primary">
          Add Patient
        </Link>
        <button
          v-if="$page.props.auth.user.is_admin"
          @click="showResetModal = true"
          class="btn btn-grd btn-grd-danger"
        >
          Reset Counter
        </button>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title mb-4">Current Queue</h5>

            <div v-if="queueItems.length === 0" class="text-center py-5">
              <p class="text-muted">No patients in queue</p>
            </div>

            <div v-else class="row">
              <div
                v-for="item in queueItems"
                :key="item.id"
                class="col-md-6 col-lg-4 mb-3"
              >
                <div
                  class="card h-100"
                  :class="{
                    'border-success bg-light': item.status === 'serving',
                    'border-warning bg-light': item.status === 'waiting',
                  }"
                >
                  <div class="card-body">
                    <div
                      class="d-flex justify-content-between align-items-start mb-3"
                    >
                      <h3 class="card-title text-primary mb-0">
                        {{ item.queue_number }}
                      </h3>
                      <span
                        :class="getStatusBadgeClass(item.status)"
                        class="badge"
                      >
                        {{ item.status.toUpperCase() }}
                      </span>
                    </div>

                    <div class="mb-3">
                      <h6 class="card-subtitle mb-1">
                        {{ item.patient.first_name }}
                      </h6>
                      <p
                        v-if="item.patient.phone"
                        class="card-text text-muted small mb-1"
                      >
                        {{ item.patient.phone }}
                      </p>
                      <small class="text-muted"
                        >Position: {{ item.queue_position }}</small
                      >
                    </div>

                    <div class="d-grid gap-2">
                      <button
                        v-if="item.status === 'waiting'"
                        @click="callPatient(item.id)"
                        class="btn btn-success btn-sm"
                      >
                        Call
                      </button>
                      <button
                        v-if="item.status === 'serving'"
                        @click="completeService(item.id)"
                        class="btn btn-primary btn-sm"
                      >
                        Complete
                      </button>
                      <button
                        v-if="
                          item.status === 'waiting' || item.status === 'serving'
                        "
                        @click="openTransferModal(item)"
                        class="btn btn-warning btn-sm"
                      >
                        Transfer
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Transfer Modal -->
    <Modal :show="showTransferModal" @close="closeTransferModal">
      <div class="modal-header">
        <h5 class="modal-title">
          Transfer Patient {{ selectedItem?.queue_number }}
        </h5>
        <button
          type="button"
          class="btn-close"
          @click="closeTransferModal"
        ></button>
      </div>
      <div class="modal-body">
        <form @submit.prevent="submitTransfer">
          <div class="mb-3">
            <label class="form-label">Transfer to Department</label>
            <select
              v-model="transferForm.to_department_id"
              class="form-select"
              required
            >
              <option value="">Select Department</option>
              <option
                v-for="dept in canTransfer"
                :key="dept.id"
                :value="dept.id"
              >
                {{ dept.name }}
              </option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Reason (Optional)</label>
            <textarea
              v-model="transferForm.reason"
              class="form-control"
              rows="3"
              placeholder="Reason for transfer..."
            ></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button
          type="button"
          @click="closeTransferModal"
          class="btn btn-secondary"
        >
          Cancel
        </button>
        <button
          type="submit"
          :disabled="transferForm.processing"
          class="btn btn-warning"
          @click="submitTransfer"
        >
          Transfer
        </button>
      </div>
    </Modal>

    <!-- Reset Counter Modal -->
    <Modal :show="showResetModal" @close="showResetModal = false">
      <div class="modal-header">
        <h5 class="modal-title">Reset Daily Counter</h5>
        <button
          type="button"
          class="btn-close"
          @click="showResetModal = false"
        ></button>
      </div>
      <div class="modal-body">
        <form @submit.prevent="submitReset">
          <div class="mb-3">
            <label class="form-label">Date to Reset</label>
            <input
              v-model="resetForm.date"
              type="date"
              class="form-control"
              :max="new Date().toISOString().split('T')[0]"
            />
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button
          type="button"
          @click="showResetModal = false"
          class="btn btn-secondary"
        >
          Cancel
        </button>
        <button
          type="submit"
          :disabled="resetForm.processing"
          class="btn btn-danger"
          @click="submitReset"
        >
          Reset Counter
        </button>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Modal from "@/Components/Modal.vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
  department: Object,
  queueItems: Array,
  canTransfer: Array,
  todayCount: Number,
});

const showTransferModal = ref(false);
const showResetModal = ref(false);
const selectedItem = ref(null);

const transferForm = useForm({
  to_department_id: "",
  reason: "",
});

const resetForm = useForm({
  date: new Date().toISOString().split("T")[0],
});

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

const openTransferModal = (item) => {
  selectedItem.value = item;
  transferForm.reset();
  showTransferModal.value = true;
};

const closeTransferModal = () => {
  showTransferModal.value = false;
  selectedItem.value = null;
  transferForm.reset();
};

const submitTransfer = () => {
  transferForm.post(route("queue.transfer", selectedItem.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      closeTransferModal();
      router.reload({ only: ["queueItems"] });
    },
  });
};

const submitReset = () => {
  resetForm.post(route("queue.reset-counter", props.department.id), {
    preserveScroll: true,
    onSuccess: () => {
      showResetModal.value = false;
      router.reload();
    },
  });
};

const getStatusBadgeClass = (status) => {
  const classes = {
    waiting: "bg-warning text-dark",
    serving: "bg-success text-white",
    done: "bg-secondary text-white",
    transferred: "bg-info text-white",
  };
  return classes[status] || "bg-secondary text-white";
};
</script>
