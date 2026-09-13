<script setup>
import { useElapsedTime } from "@/Composables/useElapsedTime";
import { useCallPatient } from "@/Composables/useCallPatient";
import { router } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
  hasBillingAccess: {
    type: Boolean,
    required: true,
  },
  queueItems: {
    type: Array,
    required: true,
  },
});

const { callPatient } = useCallPatient();

const firstWaitingIdsByOriginalDepartment = computed(() => {
  const firstByDepartment = new Map();

  for (const item of props.queueItems || []) {
    if (item.status !== "waiting") continue;

    const departmentId = item.original_department_id;
    const currentFirst = firstByDepartment.get(departmentId);

    if (!currentFirst || item.queue_position < currentFirst.queue_position) {
      firstByDepartment.set(departmentId, item);
    }
  }

  return new Set([...firstByDepartment.values()].map((item) => item.id));
});

const canCallPatient = (item) =>
  item.status === "waiting" &&
  firstWaitingIdsByOriginalDepartment.value.has(item.id);

let intervalId = null;

const reloadQueueItems = () => {
  router.reload({ only: ["queueItems"] });
};

const elapsed = useElapsedTime(props.item.waiting_started_at);
</script>

<template>
  <div class="card shadow border">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <h3 class="card-title text-primary mb-0 font-bold">
          {{ item.queue_number }}
        </h3>
      </div>

      <div class="mb-3">
        <h6 class="card-subtitle mb-1 uppercase">
          {{ item.patient.last_name }}
          {{ item.patient.first_name }}
          {{ item.patient.middle_name }}
          {{ item.patient.suffix }}
        </h6>
        <p v-if="item.status === 'skipped'" class="card-text small mb-1">
          {{ getTimeAgo(item.skipped_at) }}
        </p>
        <p v-if="item.patient.phone" class="card-text small mb-1">
          {{ item.patient.phone }}
        </p>
        <p class="text-right" style="margin-bottom: 0">{{ elapsed }}</p>
        <small class="">Position: {{ item.queue_position }}</small>
      </div>
      <div class="gap-2 flex">
        <button
          v-if="hasBillingAccess || canCallPatient(item)"
          @click="callPatient(item.id)"
          class="btn btn-success btn-sm flex-1"
        >
          Call
        </button>
      </div>
    </div>
  </div>
</template>