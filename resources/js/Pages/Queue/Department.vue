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
        <p class="mb-0">
          Room: {{ department.room || "N/A" }} | Served:
          {{ todayServedCount }} | Serving: {{ todayServingCount }} | Skipped:
          {{ todaySkippedCount }} | Waiting: {{ todayWaitingCount }} | Coming:
          {{ todayComingCount }}
          <!-- | Coming: {{ todayComingCount }} -->
        </p>
      </div>
      <div class="d-flex gap-2">
        <template v-if="isReception">
          <Link
            :href="route('queue.create')"
            class="btn btn-grd btn-grd-primary"
          >
            Add Patient
          </Link>
        </template>
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
              <p class="">No patients in queue</p>
            </div>

            <div v-else class="row">
              <div class="col-md-3">
                <h6 class="text-center mb-4 font-extrabold text-2xl">
                  NOW SERVING
                </h6>
                <div
                  v-for="item in queueItems
                    .filter((i) => i.status === 'serving')
                    .sort(
                      (a, b) => new Date(b.called_at) - new Date(a.called_at)
                    )"
                  :key="item.id"
                  class="mb-3"
                >
                  <!-- COMPONENT HERE -->
                  <ServingCard :item="item" />
                </div>
              </div>
              <div class="col-md-3 border-x-4">
                <h6 class="text-center mb-4 font-extrabold text-2xl leading-5">
                  <span class="text-xs align-top">WAITING</span>
                  <br />
                  PRIORITY
                </h6>
                <div
                  v-for="(item, index) in queueItems.filter(
                    (i) =>
                      i.patient.is_priority &&
                      i.status !== 'serving' &&
                      i.status !== 'skipped'
                  )"
                  :key="item.id"
                  class="mb-3"
                >
                  <div class="card shadow border">
                    <div class="card-body">
                      <div
                        class="d-flex justify-content-between align-items-start mb-3"
                      >
                        <h3 class="card-title text-primary mb-0 font-bold">
                          {{ item.queue_number }}
                        </h3>
                        <span
                          :class="getStatusBadgeClass(item.status)"
                          class="badge"
                        >
                          {{ getStatusLabel(item.status) }}
                        </span>
                      </div>

                      <div class="mb-3">
                        <h6 class="card-subtitle mb-1 uppercase">
                          {{ item.patient.last_name }}
                          {{ item.patient.first_name }}
                          {{ item.patient.middle_name }}
                          {{ item.patient.suffix }}
                        </h6>
                        <p
                          v-if="item.patient.phone"
                          class="card-text small mb-1"
                        >
                          {{ item.patient.phone }}
                        </p>
                        <small class="card-subtitle card-text"
                          >Position: {{ item.queue_position }}</small
                        >
                        <p
                          v-if="item.patient.priority_reason"
                          class="card-text small mb-1 mt-3 text-white"
                        >
                          <span class="px-2 py-1 rounded-md bg-red-500">
                            {{ item.patient.priority_reason.description }}
                          </span>
                        </p>
                      </div>
                      <div class="gap-2 flex">
                        <button
                          v-if="item.status === 'waiting' && index === 0"
                          @click="callPatient(item.id)"
                          class="btn btn-success btn-sm flex-1"
                        >
                          Call
                        </button>
                        <!-- <button
                          v-if="
                            item.status === 'waiting' ||
                            item.status === 'serving'
                          "
                          @click="openTransferModal(item)"
                          class="btn btn-warning btn-sm flex-1"
                        >
                          Transfer
                        </button> -->
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-3 border-r-4">
                <h6 class="text-center mb-4 font-extrabold text-2xl leading-5">
                  <span class="text-xs align-top">WAITING</span>
                  <br />
                  REGULAR
                </h6>
                <div
                  v-for="(item, index) in queueItems.filter(
                    (i) =>
                      !i.patient.is_priority &&
                      i.status !== 'serving' &&
                      i.status !== 'skipped'
                  )"
                  :key="item.id"
                  class="mb-3"
                >
                  <div class="card shadow border">
                    <div class="card-body">
                      <div
                        class="d-flex justify-content-between align-items-start mb-3"
                      >
                        <h3 class="card-title text-primary mb-0 font-bold">
                          {{ item.queue_number }}
                        </h3>
                        <span
                          :class="getStatusBadgeClass(item.status)"
                          class="badge"
                        >
                          {{ getStatusLabel(item.status) }}
                        </span>
                      </div>

                      <div class="mb-3">
                        <h6 class="card-subtitle mb-1 uppercase">
                          {{ item.patient.last_name }}
                          {{ item.patient.first_name }}
                          {{ item.patient.middle_name }}
                          {{ item.patient.suffix }}
                        </h6>
                        <p
                          v-if="item.status === 'skipped'"
                          class="card-text small mb-1"
                        >
                          {{ getTimeAgo(item.skipped_at) }}
                        </p>
                        <p
                          v-if="item.patient.phone"
                          class="card-text small mb-1"
                        >
                          {{ item.patient.phone }}
                        </p>
                        <small class=""
                          >Position: {{ item.queue_position }}</small
                        >
                      </div>
                      <div class="gap-2 flex">
                        <button
                          v-if="item.status === 'waiting' && index === 0"
                          @click="callPatient(item.id)"
                          class="btn btn-success btn-sm flex-1"
                        >
                          Call
                        </button>
                        <!-- <button
                          v-if="
                            item.status === 'waiting' ||
                            item.status === 'serving'
                          "
                          @click="openTransferModal(item)"
                          class="btn btn-warning btn-sm flex-1"
                        >
                          Transfer
                        </button> -->
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <h6 class="text-center mb-4 font-extrabold text-2xl">
                  SKIPPED
                </h6>
                <div
                  v-for="item in queueItems.filter(
                    (i) => i.status === 'skipped'
                  )"
                  :key="item.id"
                  class="mb-3"
                >
                  <div class="card shadow border opacity-50 hover:bg-gray-100">
                    <div class="card-body">
                      <div
                        class="d-flex justify-content-between align-items-start mb-3"
                      >
                        <h3 class="card-title text-primary mb-0 font-bold">
                          {{ item.queue_number }}
                        </h3>
                        <span
                          :class="getStatusBadgeClass(item.status)"
                          class="badge"
                        >
                          {{ getStatusLabel(item.status) }}
                        </span>
                      </div>

                      <div class="mb-3">
                        <h6 class="card-subtitle mb-1 uppercase">
                          {{ item.patient.last_name }}
                          {{ item.patient.first_name }}
                          {{ item.patient.middle_name }}
                          {{ item.patient.suffix }}
                        </h6>
                        <p
                          v-if="item.status === 'skipped'"
                          class="card-text small mb-1 text-gray-500"
                        >
                          Skipped:
                          {{
                            new Date(item.skipped_at).toLocaleTimeString([], {
                              hour: "2-digit",
                              minute: "2-digit",
                            })
                          }}
                        </p>
                        <p
                          v-if="item.patient.phone"
                          class="card-text small mb-1"
                        >
                          {{ item.patient.phone }}
                        </p>
                        <small class="text-gray-300"
                          >Position: {{ item.queue_position }}</small
                        >
                        <p
                          v-if="item.patient.priority_reason"
                          class="card-text small mb-1 mt-3 text-white"
                        >
                          <span class="px-2 py-1 rounded-md bg-red-500">
                            {{ item.patient.priority_reason.description }}
                          </span>
                        </p>
                      </div>
                      <div class="gap-2 flex">
                        <button
                          v-if="item.status === 'skipped'"
                          @click="callPatient(item.id)"
                          class="btn btn-success btn-sm flex-1"
                        >
                          Call
                        </button>
                        <!-- <button
                          v-if="
                            item.status === 'waiting' ||
                            item.status === 'serving'
                          "
                          @click="openTransferModal(item)"
                          class="btn btn-warning btn-sm flex-1"
                        >
                          Transfer
                        </button> -->
                      </div>
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
import ServingCard from "@/Components/ServingCard.vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { useElapsedTime } from "@/Composables/useElapsedTime";

const props = defineProps({
  department: Object,
  queueItems: Array,
  canTransfer: Array,
  todayCount: Number,
  todayComingCount: Number,
  todayServedCount: Number,
  todayWaitingCount: Number,
  todayServingCount: Number,
  todaySkippedCount: Number,
});

const isReception = computed(() => props.user?.role === "reception");
const showTransferModal = ref(false);
const showResetModal = ref(false);
const selectedItem = ref(null);

let intervalId = null;

const reloadQueueItems = () => {
  router.reload({ only: ["queueItems", "todayWaitingCount"] });
};

onMounted(() => {
  intervalId = setInterval(() => {
    reloadQueueItems();
  }, 3000); // every 3 seconds
});

onBeforeUnmount(() => {
  if (intervalId) clearInterval(intervalId);
});

const transferForm = useForm({
  to_department_id: "",
  reason: "",
});

const resetForm = useForm({
  date: new Date().toISOString().split("T")[0],
});

// Helper function to check if an item is in the final department
const isFinalDepartment = (item) => {
  return item.is_final_department;
};

// Helper function to check if there's a next department in the flow
const hasNextDepartment = (item) => {
  return item.has_next_department;
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
  if (!confirm("Are you sure you want to complete this service?")) {
    return;
  }
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

const completeAndTransfer = (id) => {
  if (
    !confirm(
      "Are you sure you want to complete this service and transfer to next department?"
    )
  ) {
    return;
  }

  router.post(
    route("queue.complete-and-transfer", id),
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

const markNoShow = (id) => {
  if (!confirm("Mark this patient as No Show?")) {
    return;
  }
  router.post(
    route("queue.no-show", id),
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ["queueItems"] });
      },
    }
  );
};
</script>
