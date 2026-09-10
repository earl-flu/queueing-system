<template>
  <div>
    <div class="p-3">
      <h1 class="uppercase text-blue-950 font-bold flex gap-3">
        {{ department.name }}
        <span v-if="department.room" class="text-lg capitalize">
          ({{ department.room }})
        </span>
      </h1>
    </div>
    <div class="row uppercase">
      <div class="col-md-6 border p-4">
        <div class="border rounded-2xl overflow-hidden">
          <div class="flex p-3 px-4 gap-3 bg-green-700 text-white items-center">
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
            <p>SKIPPED / NILAKTAWAN</p>
          </div>
          <SkippedTicker v-if="skippedNumbers.length" :items="skippedNumbers" />
          <p v-else class="mt-2 text-gray-400 font-normal normal-case">-</p>
        </div>

        <div
          class="border rounded-2xl overflow-hidden p-4 py-3 mt-3 font-medium text-blue-900 bg-blue-50"
        >
          <div class="flex gap-2 text-xs items-center">
            <div class="rounded-full p-2.5 bg-blue-900 text-white text-center">
              <i class="bi bi-person-workspace" style="font-size: 15px"></i>
            </div>
            <div>
              <p class="m-0">MANGYARING HUMINGI NG TULONG SA HELPDESK</p>
              <p class="m-0 normal-case font-normal">
                Maraming salamat sa iyong pagpapasensya
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6 border p-4">
        <div class="border rounded-2xl overflow-hidden">
          <div class="flex p-3 px-4 gap-3 bg-blue-900 text-white items-center">
            <i
              class="bi bi-people-fill bg-white py-1 px-2 rounded-full text-blue-900"
              style="font-size: 12px; margin-top: -2px"
            ></i>
            <h5 class="m-0 font-bold">Waiting</h5>
            <span class="opacity-50">|</span><span class="flex-1"></span>
            <h5 v-if="currentGroup" class="mx-auto font-semibold m-0">
              {{ currentGroup.name }}
            </h5>
          </div>
          <div class="relative bg-blue-50 min-h-[280px]">
            <Transition name="dept-fade" mode="out-in">
              <div
                v-if="currentGroup"
                :key="currentGroup.id"
                class="grid grid-cols-2 p-4 text-blue-900 font-bold"
              >
                <div class="relative">
                  <p class="mb-3 text-xl text-center">Priority</p>
                  <div class="flex gap-3 flex-col px-4">
                    <template v-if="currentGroup.priority.length">
                      <div
                        v-for="q in currentGroup.priority"
                        :key="q.id"
                        class="bg-blue-100 text-center p-2 text-2xl"
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
                    <template v-if="currentGroup.regular.length">
                      <div
                        v-for="q in currentGroup.regular"
                        :key="q.id"
                        class="bg-blue-100 text-2xl text-center p-2"
                      >
                        {{ q.queue_number }}
                      </div>
                    </template>
                    <div v-else class="text-gray-400 text-center">-</div>
                  </div>
                </div>
              </div>
              <div
                v-else
                key="empty-waiting"
                class="grid grid-cols-2 p-4 text-blue-900 font-bold"
              >
                <div class="relative">
                  <p class="mb-3 text-xl text-center">Priority</p>
                  <div class="text-gray-400 text-center">-</div>
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
                  <div class="text-gray-400 text-center">-</div>
                </div>
              </div>
            </Transition>
          </div>
          <div
            v-if="waitingGroups.length > 1"
            class="flex justify-center gap-2 pb-3 bg-blue-50"
          >
            <span
              v-for="(group, index) in waitingGroups"
              :key="group.id"
              class="inline-block rounded-full"
              :class="index === currentIndex ? 'bg-blue-900' : 'bg-blue-300'"
              :title="group.name"
              style="width: 8px; height: 8px"
            ></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import SkippedTicker from "./SkippedTicker.vue";

const ROTATE_MS = 8000;

const props = defineProps({
  department: Object,
  getNowServing: Function,
  getPriorityUpNext: Function,
  getRegularUpNext: Function,
  getSkipped: Function,
});

const currentIndex = ref(0);
let rotateIntervalId = null;

const nowServing = computed(() => props.getNowServing(props.department.id));
const skippedNumbers = computed(() =>
  props.getSkipped(props.department.id).map((q) => q.queue_number)
);

const waitingGroups = computed(() => {
  const waiting = [
    ...props.getPriorityUpNext(props.department.id),
    ...props.getRegularUpNext(props.department.id),
  ];
  const groups = new Map();

  waiting.forEach((q) => {
    const id = q.original_department_id ?? 0;
    if (!groups.has(id)) {
      groups.set(id, {
        id,
        name: q.original_department?.name || "Unknown",
        priority: [],
        regular: [],
      });
    }

    const group = groups.get(id);
    if (q.patient?.is_priority) {
      group.priority.push(q);
    } else {
      group.regular.push(q);
    }
  });

  return Array.from(groups.values()).sort((a, b) =>
    a.name.localeCompare(b.name)
  );
});

const currentGroup = computed(
  () => waitingGroups.value[currentIndex.value] || null
);

watch(
  () => waitingGroups.value.length,
  (length) => {
    if (length === 0) {
      currentIndex.value = 0;
      return;
    }
    if (currentIndex.value >= length) {
      currentIndex.value = 0;
    }
  }
);

onMounted(() => {
  rotateIntervalId = setInterval(() => {
    const length = waitingGroups.value.length;
    if (length <= 1) return;
    currentIndex.value = (currentIndex.value + 1) % length;
  }, ROTATE_MS);
});

onBeforeUnmount(() => {
  if (rotateIntervalId) clearInterval(rotateIntervalId);
});
</script>

<style scoped>
.dept-fade-enter-active,
.dept-fade-leave-active {
  transition: opacity 0.6s ease;
}

.dept-fade-enter-from,
.dept-fade-leave-to {
  opacity: 0;
}
</style>
