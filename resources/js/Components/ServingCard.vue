<script setup>
import { useStatusBadge } from "@/Composables/useStatusBadge";
import { useStatusLabel } from "@/Composables/useStatusLabel";
import { useElapsedTime } from "@/Composables/useElapsedTime";
import { useCallPatient } from "@/Composables/useCallPatient";
import { useCallAgain } from "@/Composables/useCallAgain";
import { useMarkNoShow } from "@/Composables/useMarkNoShow";
import { useSkip } from "@/Composables/useSkip";
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
const { callAgain } = useCallAgain();
const { markNoShow } = useMarkNoShow();
const { skip } = useSkip();
const { completeAndTransfer } = useCompleteAndTransfer();
const elapsed = useElapsedTime(props.item.called_at);
</script>


<template>
  <div class="card shadow border">
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
        <small class="text-gray-400">Position: {{ item.queue_position }}</small>
        <br />
        <small class="text-gray-400">Call Count: {{ item.call_count }}</small>
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
        <!-- <button
          v-if="item.status === 'serving'"
          @click="markNoShow(item.id)"
          class="btn btn-outline-danger btn-sm flex-1"
        >
          No Show
        </button> -->
        <button
          v-if="item.status === 'serving'"
          @click="skip(item.id)"
          class="btn btn-outline-danger btn-sm flex-1"
        >
          Skip
        </button>

        <button
          @click="callAgain(item)"
          class="btn btn-outline-warning btn-sm flex-1"
        >
          <i class="material-icons-outlined text-gray-500">volume_up</i>
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
      <div class="flex mt-2">
        <button
          v-if="item.status === 'serving' && item.is_final_department"
          @click="completeService(item.id)"
          class="btn btn-primary btn-sm flex-1"
        >
          Complete
        </button>
        <button
          v-if="item.status === 'serving' && item.has_next_department"
          @click="completeAndTransfer(item)"
          class="btn btn-info btn-sm flex-1"
        >
          Done
        </button>
      </div>
    </div>
  </div>
</template>