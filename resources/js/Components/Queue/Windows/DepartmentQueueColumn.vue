<template>
  <div class="col-sm border-end">
    <div class="p-3">
      <div class="border rounded-lg overflow-hidden">
        <div
          class="text-2xl p-4 text-white"
          :class="headerClass"
        >
          {{ department.name }}
        </div>
        <div class="p-4">
          <div class="flex gap-2">
            <div
              class="flex-1 relative pr-5"
              :class="accentBorderClass"
            >
              <p
                class="text-center text-xl mb-3"
                :class="accentTextClass"
              >
                Now Serving
              </p>
              <div class="grid grid-cols-2 gap-2">
                <template v-if="nowServing?.length">
                  <div
                    v-for="serving in nowServing"
                    :key="serving.id"
                    class="text-6xl text-center rounded-md"
                    :class="servingBgClass"
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
                  class="col-span-2 text-4xl text-center text-gray-400"
                >
                  -
                </div>
              </div>
            </div>

            <div class="p-2" :class="accentTextClass">
              <p class="mb-3">Skipped</p>
              <SkippedVerticalRotator
                :items="skipped"
                :item-class="servingBgClass"
              />
            </div>
          </div>

          <div
            class="uppercase mt-5 my-4 flex items-center text-xl"
            :class="accentTextClass"
          >
            Waiting
            <div class="flex-1">
              <div
                style="height: 1.8px; width: 95%"
                class="m-auto"
                :class="dividerClass"
              ></div>
            </div>
          </div>

          <div class="flex gap-4">
            <div
              class="flex-1 text-orange-500 border-r-2 border-gray-300 pr-5"
            >
              <p class="mb-2">
                <i class="bi bi-people-fill"></i> Priority
              </p>
              <div class="grid grid-cols-2 gap-2">
                <template v-if="priorityWaiting.length">
                  <div
                    v-for="q in priorityWaiting"
                    :key="q.id"
                    class="bg-orange-100 rounded-md text-center p-2"
                  >
                    {{ q.queue_number }}
                  </div>
                </template>
                <div v-else class="text-gray-400 col-span-2 text-center">-</div>
              </div>
            </div>
            <div class="flex-1 text-blue-900">
              <p class="mb-2">
                <i class="bi bi-people-fill"></i> Regular
              </p>
              <div class="grid grid-cols-2 gap-2">
                <template v-if="regularWaiting.length">
                  <div
                    v-for="q in regularWaiting"
                    :key="q.id"
                    class="bg-blue-100 rounded-md text-center p-2"
                  >
                    {{ q.queue_number }}
                  </div>
                </template>
                <div v-else class="text-gray-400 col-span-2 text-center">-</div>
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
import SkippedVerticalRotator from "./SkippedVerticalRotator.vue";

const props = defineProps({
  department: Object,
  variant: {
    type: String,
    default: "green",
    validator: (v) => ["green", "blue"].includes(v),
  },
  getNowServing: Function,
  getPriorityUpNext: Function,
  getRegularUpNext: Function,
  getSkipped: Function,
});

const isGreen = computed(() => props.variant === "green");

const headerClass = computed(() =>
  isGreen.value ? "bg-green-900" : "bg-blue-900"
);
const accentTextClass = computed(() =>
  isGreen.value ? "text-green-900" : "text-blue-900"
);
const accentBorderClass = computed(() =>
  isGreen.value ? "border-r border-green-600" : "border-r border-blue-600"
);
const servingBgClass = computed(() =>
  isGreen.value ? "bg-green-100 text-green-900" : "bg-blue-100 text-blue-900"
);
const dividerClass = computed(() =>
  isGreen.value ? "bg-green-500" : "bg-blue-500"
);

const nowServing = computed(() => props.getNowServing(props.department.id));
const priorityWaiting = computed(() =>
  props.getPriorityUpNext(props.department.id)
);
const regularWaiting = computed(() =>
  props.getRegularUpNext(props.department.id)
);
const skipped = computed(() => props.getSkipped(props.department.id));
</script>
