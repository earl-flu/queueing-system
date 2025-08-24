<script setup>
import { useStatusBadge } from "@/Composables/useStatusBadge";
import { useStatusLabel } from "@/Composables/useStatusLabel";
import { useElapsedTime } from "@/Composables/useElapsedTime";
import { useCallPatient } from "@/Composables/useCallPatient";
import { useMarkNoShow } from "@/Composables/useMarkNoShow";
import { useCompleteAndTransfer } from "@/Composables/useCompleteAndTransfer";
import { useCompleteService } from "@/Composables/useCompleteService";

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
});

const { getStatusBadgeClass } = useStatusBadge();
const { getStatusLabel } = useStatusLabel();
const { completeService } = useCompleteService();
const { callPatient } = useCallPatient();
const { markNoShow } = useMarkNoShow();
const { completeAndTransfer } = useCompleteAndTransfer();
const elapsed = useElapsedTime(props.item.called_at);
</script>


<template>
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <h3 class="card-title text-primary mb-0 font-bold">
          {{ item.queue_number }}
        </h3>
        <span :class="getStatusBadgeClass(item.status)" class="badge">
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
        <p v-if="item.patient.phone" class="card-text small mb-1">
          {{ item.patient.phone }}
        </p>
        <p class="text-right" style="margin-bottom: 0">{{ elapsed }}</p>
        <small class="">Position: {{ item.queue_position }}</small>
        <p
          v-if="item.patient.priority_reason"
          class="card-text small mb-1 mt-3 text-white"
        >
          <span class="px-2 py-1 rounded-md bg-red-500">
            {{ item.patient.priority_reason.description }}
          </span>
        </p>
        <!-- <p
          v-if="item.patient.priority_reason.description"
          class="card-text small mb-1 mt-3 text-white"
        >
          <span class="px-2 py-1 rounded-md bg-red-500">
            {{ item.patient.priority_reason.description }}
          </span>
        </p> -->
      </div>
      <div class="gap-2 flex">
        <button
          v-if="item.status === 'waiting'"
          @click="callPatient(item.id)"
          class="btn btn-success btn-sm flex-1"
        >
          Call
        </button>
        <button
          v-if="item.status === 'serving'"
          @click="markNoShow(item.id)"
          class="btn btn-outline-danger btn-sm flex-1"
        >
          No Show
        </button>
        <button
          v-if="item.status === 'serving' && item.is_final_department"
          @click="completeService(item.id)"
          class="btn btn-primary btn-sm flex-1"
        >
          Complete
        </button>
        <button
          v-if="item.status === 'serving' && item.has_next_department"
          @click="completeAndTransfer(item.id)"
          class="btn btn-info btn-sm flex-1"
        >
          Done
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
</template>