<template>
  <div>
    <div class="p-3">
      <h1 class="uppercase text-blue-950 font-bold flex gap-3">
        {{ department.name }}
        <span class="text-lg capitalize">({{ department.room || "N/A" }})</span>
      </h1>
    </div>
    <div class="row uppercase">
      <div class="col-md-6 border p-4">
        <div class="border rounded-2xl overflow-hidden">
          <div
            class="flex p-3 px-4 gap-3 bg-green-700 text-white items-center"
          >
            <i
              class="bi bi-people-fill bg-white py-1 px-2 rounded-full text-green-700"
              style="font-size: 12px; margin-top: -2px"
            ></i>
            <h5 class="m-0 font-bold">Now Serving</h5>
          </div>
          <div class="grid grid-cols-2 gap-4 p-4 bg-green-50 text-green-700">
            <template v-if="nowServing?.length">
              <div
                v-for="serving in nowServing"
                :key="serving.id"
                class="text-center px-2 py-3 text-7xl bg-gray-100 border rounded-lg font-bold"
              >
                <span
                  :id="serving.queue_number"
                  class="blink-bg-animation inline-block"
                  :class="{ 'text-red-500': serving.patient.is_priority }"
                >
                  {{ serving.queue_number }}
                </span>
              </div>
            </template>
            <div
              v-else
              class="col-span-2 text-center px-2 py-3 text-5xl bg-gray-100 border rounded-lg font-bold text-gray-400"
            >
              -
            </div>
          </div>
        </div>

        <div
          class="border rounded-2xl overflow-hidden p-4 py-3 mt-3 text-orange-600 font-bold bg-orange-50"
        >
          <div class="flex gap-2">
            <div><i class="bi bi-arrow-clockwise"></i></div>
            <p>SKIPPED</p>
          </div>
          <SkippedTicker
            v-if="skippedNumbers.length"
            :items="skippedNumbers"
          />
          <p v-else class="mt-2 text-gray-400 font-normal normal-case">-</p>
        </div>

        <div
          class="border rounded-2xl overflow-hidden p-4 py-3 mt-3 font-medium text-blue-900 bg-blue-50"
        >
          <div class="flex gap-2 text-xs items-center">
            <div
              class="rounded-full p-2.5 bg-blue-900 text-white text-center"
            >
              <i class="bi bi-person-workspace" style="font-size: 15px"></i>
            </div>
            <div>
              <p class="m-0">PLEASE ASK HELPDESK FOR ASSISTANCE</p>
              <p class="m-0 normal-case font-normal">
                Thank you for your patience.
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6 border p-4">
        <div class="border rounded-2xl overflow-hidden">
          <div
            class="flex p-3 px-4 gap-3 bg-blue-900 text-white items-center"
          >
            <i
              class="bi bi-people-fill bg-white py-1 px-2 rounded-full text-blue-900"
              style="font-size: 12px; margin-top: -2px"
            ></i>
            <h5 class="m-0 font-bold">Waiting</h5>
          </div>
          <div class="grid grid-cols-2 p-4 bg-blue-50 text-blue-900 font-bold">
            <div class="relative">
              <p class="mb-3 text-xl text-center">Priority</p>
              <div class="flex gap-3 flex-col px-4">
                <template v-if="priorityWaiting.length">
                  <div
                    v-for="q in priorityWaiting"
                    :key="q.id"
                    class="bg-blue-100 text-xl text-center p-2 text-red-500"
                  >
                    {{ q.queue_number }}
                  </div>
                </template>
                <div v-else class="text-gray-400 text-center">-</div>
              </div>
              <span
                class="after-border"
                style="
                  content: '';
                  position: absolute;
                  top: 0;
                  right: 0;
                  width: 2px;
                  height: 100%;
                  background-color: #e0e7ef;
                  display: block;
                  z-index: 10;
                "
              ></span>
            </div>
            <div>
              <p class="mb-3 text-xl text-center">Regular</p>
              <div class="flex gap-3 flex-col px-4">
                <template v-if="regularWaiting.length">
                  <div
                    v-for="q in regularWaiting"
                    :key="q.id"
                    class="bg-blue-100 text-xl text-center p-2"
                  >
                    {{ q.queue_number }}
                  </div>
                </template>
                <div v-else class="text-gray-400 text-center">-</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import SkippedTicker from "./SkippedTicker.vue";

const props = defineProps({
  department: Object,
  getNowServing: Function,
  getPriorityUpNext: Function,
  getRegularUpNext: Function,
  getSkipped: Function,
});

const nowServing = computed(() => props.getNowServing(props.department.id));
const priorityWaiting = computed(() =>
  props.getPriorityUpNext(props.department.id)
);
const regularWaiting = computed(() =>
  props.getRegularUpNext(props.department.id)
);
const skippedNumbers = computed(() =>
  props.getSkipped(props.department.id).map((q) => q.queue_number)
);
</script>
