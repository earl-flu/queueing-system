<template>
  <Head :title="`${window.name} Display`" />

  <div class="container-fluid min-vh-100">
    <div class="flex justify-content-between p-3 border-b-2">
      <div>
        <div class="flex gap-3">
          <div style="width: 65px">
            <img src="/images/EBMC-logo.png" class="logo-img" alt="" />
          </div>
          <div>
            <h3 class="uppercase m-0 font-bold text-blue-950">
              Outpatient Department
            </h3>
            <p class="m-0">We care for you. Your health is our priority.</p>
          </div>
        </div>
      </div>
      <div>
        <div class="flex gap-3">
          <div>
            <i class="bi bi-clock" style="font-size: 32px"></i>
          </div>

          <div>
            <div id="clock">Loading time...</div>
          </div>
        </div>
      </div>
    </div>
    <!-- Display this if only ONE department is on the window -->
    <!-- <div>
      <div class="p-3">
        <h1 class="uppercase text-blue-950 font-bold flex gap-3">
          Billing <span class="text-lg capitalize">(Room 11)</span>
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
              <div
                class="text-center px-2 py-3 text-7xl bg-gray-100 border rounded-lg font-bold"
              >
                D 555
              </div>
              <div
                class="text-center px-2 py-3 text-7xl bg-gray-100 border rounded-lg font-bold"
              >
                AO 12
              </div>
              <div
                class="text-center px-2 py-3 text-7xl bg-gray-100 border rounded-lg font-bold"
              >
                D 2
              </div>
              <div
                class="text-center px-2 py-3 text-7xl bg-gray-100 border rounded-lg font-bold"
              >
                G 10
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
            <div class="skipped-ticker" ref="skippedTickerRef">
              <div
                ref="skippedTickerTrackRef"
                class="skipped-ticker__track"
                :class="{
                  'skipped-ticker__track--animate': skippedTickerOverflow,
                }"
              >
                <span
                  v-for="(number, index) in skippedTickerItems"
                  :key="`${number}-${index}`"
                  class="skipped-ticker__item"
                >
                  {{ number }}
                </span>
              </div>
            </div>
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
            <div
              class="grid grid-cols-2 p-4 bg-blue-50 text-blue-900 font-bold"
            >
              <div class="relative">
                <p class="mb-3 text-xl text-center">Priority</p>
                <div class="flex gap-3 flex-col px-4">
                  <div class="bg-blue-100 text-xl text-center p-2">O 1</div>
                  <div class="bg-blue-100 text-xl text-center p-2">O 2</div>
                  <div class="bg-blue-100 text-xl text-center p-2">O 3</div>
                  <div class="bg-blue-100 text-xl text-center p-2">O 4</div>
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
                  <div class="bg-blue-100 text-xl text-center p-2">D 1</div>
                  <div class="bg-blue-100 text-xl text-center p-2">D 2</div>
                  <div class="bg-blue-100 text-xl text-center p-2">D 3</div>
                  <div class="bg-blue-100 text-xl text-center p-2">D 4</div>
                  <div class="bg-blue-100 text-xl text-center p-2">D 5</div>
                  <div class="bg-blue-100 text-xl text-center p-2">D 10</div>
                  <div class="bg-blue-100 text-xl text-center p-2">D 15</div>
                  <div class="bg-blue-100 text-xl text-center p-2">G 3</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div> -->
    <!-- //Display this if only one department is on the window -->
    <!-- Display this if 2 or more department is on the window -->
    <div>
      <div class="row font-bold uppercase">
        <div class="col-sm">
          <div class="p-4">
            <div class="border rounded-lg overflow-hidden">
              <div class="text-2xl p-4 bg-green-900 text-white">
                Vital Signs
              </div>
              <div class="p-4">
                <div class="flex gap-2">
                  <div class="flex-1 relative pr-5 border-r border-green-600">
                    <p class="text-center text-green-900 text-xl mb-3">
                      Now Serving
                    </p>
                    <div class="grid grid-cols-2 gap-2">
                      <div
                        class="text-8xl text-center bg-green-100 text-green-900"
                      >
                        B 10
                      </div>
                      <div
                        class="text-8xl text-center bg-green-100 text-green-900"
                      >
                        B 11
                      </div>
                      <div
                        class="text-8xl text-center bg-green-100 text-green-900"
                      >
                        B 10
                      </div>
                      <div
                        class="text-8xl text-center bg-green-100 text-green-900"
                      >
                        B 10
                      </div>
                    </div>
                  </div>

                  <div class="p-2 text-green-900">
                    <p class="mb-3">Skipped</p>
                    <div class="flex flex-column gap-2" style="font-size: 18px">
                      <div class="text-center p-2 bg-green-100 rounded-md">
                        B 8
                      </div>
                      <div class="text-center p-2 bg-green-100 rounded-md">
                        B 9
                      </div>
                    </div>
                  </div>
                </div>
                <div
                  class="text-green-900 uppercase mt-5 my-4 flex items-center text-xl"
                >
                  Waiting
                  <div class="flex-1">
                    <div
                      style="height: 1.8px; width: 95%"
                      class="bg-green-500 m-auto"
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
                      <div class="bg-orange-100 rounded-md text-center p-2">
                        A 1
                      </div>
                      <div class="bg-orange-100 rounded-md text-center p-2">
                        A 3
                      </div>
                      <div class="bg-orange-100 rounded-md text-center p-2">
                        A 5
                      </div>
                    </div>
                  </div>
                  <div class="flex-1 text-blue-900">
                    <p class="mb-2">
                      <i class="bi bi-people-fill"></i> Regular
                    </p>
                    <div class="grid grid-cols-2 gap-2">
                      <div class="bg-blue-100 rounded-md text-center p-2">
                        A 1
                      </div>
                      <div class="bg-blue-100 rounded-md text-center p-2">
                        A 3
                      </div>
                      <div class="bg-blue-100 rounded-md text-center p-2">
                        A 5
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm">B</div>
        <!-- <div class="col-sm">B</div> -->
      </div>
    </div>
    <!-- //Display this if 2 or more department is on the window -->
  </div>
  <div class="w-100 h-100" style="background: black; color: #333">
    <div class="container-fluid py-4 border border-red">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div
          class="text-white-50 bg-white bg-opacity-10 px-3 py-2 rounded-pill"
        >
          <i class="fas fa-clock me-2"></i>
          Last updated: {{ lastUpdatedDisplay }}
        </div>
        <div>
          <button @click="enableSpeech" class="btn btn-sm">
            <i class="material-icons-outlined text-white-50">{{
              speechEnabled ? "volume_up" : "volume_off"
            }}</i>
          </button>
        </div>
      </div>
      <!-- Display this block if department length is 1 -->
      <!-- <div v-if="departments.length === 1">
        <div class="row-g4">
          <div class="col-12">
            <div
              class="card border-0 shadow-lg h-100"
              style="
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
              "
            >
              <div
                class="card-header border-0 d-flex gap-5 align-items-center"
                style="
                  background: #b81212;
                  background: linear-gradient(
                    90deg,
                    rgba(184, 18, 18, 1) 0%,
                    rgba(242, 80, 80, 1) 45%,
                    rgba(2, 6, 150, 1) 100%
                  );
                "
              >
                <h4 class="m-0 text-white font-extrabold uppercase text-3xl">
                  Dept Name
                </h4>
                <span
                  class="badge bg-white text-primary fw-bold px-3 py-2 uppercase"
                  >Room 1</span
                >
              </div>

              <div class="card-body p-4">
                <div class="row">
                  <div class="col-4">
                    <div class="mb-4">
                      <h6 class="text-uppercase text-muted fw-bold mb-3">
                        <i class="fas fa-user-check me-2 text-success"></i>
                        Now Serving
                      </h6>
                      <div
                        class="display-3 fw-bold text-center"
                        style="
                          color: #0e1e2f;
                          text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
                        "
                      >
                        <li class="list-none">
                          <span class="p-2 blink-bg-animation"> D1 </span>
                        </li>
                      </div>
                    </div>
                  </div>
                  <div class="col-8">
                    <div>
                      <h6 class="text-uppercase text-muted fw-bold mb-3">
                        <i class="fas fa-clock me-2 text-info"></i>
                        Up Next
                      </h6>
                      <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="bg-blue-100 p-4 rounded">
                          <div class="font-bold text-blue-900 mb-2 text-center">
                            Priority
                          </div>
                          <div class="flex flex-wrap gap-2 justify-center">
                            <span
                              class="badge fs-5 px-2 py-2 fw-bold bg-red-500"
                              style="
                                color: white;
                                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
                              "
                            >
                              D2
                            </span>
                          </div>
                        </div>
                        <div class="bg-green-100 p-4 rounded">
                          <div
                            class="font-bold text-green-900 mb-2 text-center"
                          >
                            Regular
                          </div>
                          <div class="flex flex-wrap gap-2 justify-center">
                            <span
                              class="badge fs-5 px-2 py-2 fw-bold"
                              style="
                                background: #0d1077;
                                color: white;
                                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
                              "
                            >
                              D3
                            </span>
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
      </div> -->
      <!-- Dislay this block if the department is more than 2 -->
      <!-- <div class="row g-4" v-if="departments.length > 2"> -->
      <div class="row g-4">
        <div
          v-for="dept in departments"
          :key="dept.id"
          class="col-12 col-md-6 col-xl-4"
        >
          <div
            class="card border-0 shadow-lg h-100"
            style="
              background: rgba(255, 255, 255, 0.95);
              backdrop-filter: blur(10px);
            "
          >
            <div
              class="card-header border-0 d-flex justify-content-between align-items-center"
              style="
                background: #b81212;
                background: linear-gradient(
                  90deg,
                  rgba(184, 18, 18, 1) 0%,
                  rgba(242, 80, 80, 1) 45%,
                  rgba(2, 6, 150, 1) 100%
                );
              "
            >
              <h4 class="m-0 text-white font-extrabold uppercase text-3xl">
                {{ dept.name }}
              </h4>
              <span
                class="badge bg-white text-primary fw-bold px-3 py-2 uppercase"
                >{{ dept.room || "N/A" }}</span
              >
            </div>
            <div class="card-body p-4">
              <div class="mb-4">
                <h6 class="text-uppercase text-muted fw-bold mb-3">
                  <i class="fas fa-user-check me-2 text-success"></i>
                  Tinatawag
                </h6>
                <div
                  class="display-3 fw-bold text-center"
                  style="
                    color: #0e1e2f;
                    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
                  "
                >
                  <div v-if="!getNowServing(dept.id)">-</div>
                  <div v-else>
                    <li
                      class="list-none"
                      v-for="serving in getNowServing(dept.id)"
                      :key="serving.queue_number"
                    >
                      <span
                        :id="serving.queue_number"
                        class="p-2 blink-bg-animation"
                        :class="{ 'text-red-500': serving.patient.is_priority }"
                      >
                        {{ serving.queue_number }}
                      </span>
                    </li>
                  </div>
                </div>
              </div>
              <div>
                <h6 class="text-uppercase text-muted fw-bold mb-3">
                  <i class="fas fa-clock me-2 text-info"></i>
                  Nakapila
                </h6>
                <div class="grid grid-cols-2 gap-3 mb-3">
                  <div class="bg-blue-100 p-4 rounded">
                    <div class="font-bold text-blue-900 mb-2 text-center">
                      Priority
                    </div>
                    <div class="flex flex-wrap gap-2 justify-center">
                      <template
                        v-if="
                          getUpNext(dept.id).filter(
                            (q) => q.patient.is_priority
                          ).length > 0
                        "
                      >
                        <span
                          v-for="q in getUpNext(dept.id).filter(
                            (q) => q.patient.is_priority
                          )"
                          :key="q.id"
                          class="badge fs-5 px-2 py-2 fw-bold bg-red-500"
                          style="
                            color: white;
                            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
                          "
                        >
                          {{ q.queue_number }}
                        </span>
                      </template>
                      <span v-else class="text-gray-400">-</span>
                    </div>
                  </div>
                  <div class="bg-green-100 p-4 rounded">
                    <div class="font-bold text-green-900 mb-2 text-center">
                      Regular
                    </div>
                    <div class="flex flex-wrap gap-2 justify-center">
                      <template
                        v-if="
                          getUpNext(dept.id).filter(
                            (q) => !q.patient.is_priority
                          ).length > 0
                        "
                      >
                        <span
                          v-for="q in getUpNext(dept.id).filter(
                            (q) => !q.patient.is_priority
                          )"
                          :key="q.id"
                          class="badge fs-5 px-2 py-2 fw-bold"
                          style="
                            background: #0d1077;
                            color: white;
                            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
                          "
                        >
                          {{ q.queue_number }}
                        </span>
                      </template>
                      <span v-else class="text-gray-400">-</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Dislay this block if the department is more than 2 -->
    </div>
  </div>
</template>

<style scoped>
* {
  font-family: Roboto !important;
}
p {
  margin: 0;
}
.blink-bg-animation {
  animation: blink 0.9s 8;
}

.skipped-ticker {
  overflow: hidden;
  width: 100%;
  margin-top: 0.5rem;
}

.skipped-ticker__track {
  display: flex;
  flex-wrap: nowrap;
  align-items: center;
  gap: 1rem;
  width: max-content;
}

.skipped-ticker__track--animate {
  animation: skipped-ticker-scroll 24s linear infinite;
}

.skipped-ticker__item {
  flex-shrink: 0;
  text-align: center;
  padding: 0.25rem 0.75rem;
  background-color: #ffedd5;
  border-radius: 0.25rem;
  white-space: nowrap;
}

@keyframes skipped-ticker-scroll {
  0% {
    transform: translateX(0);
  }
  100% {
    transform: translateX(-50%);
  }
}
</style>

<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from "vue";

const speechEnabled = ref(false);

const props = defineProps({
  window: Object,
  departments: Array,
  queuesByDepartment: Object,
});

const dataByDepartment = ref(props.queuesByDepartment);
const lastUpdatedAt = ref(new Date());
const lastUpdatedDisplay = computed(() =>
  lastUpdatedAt.value.toLocaleTimeString()
);

let intervalId = null;
let skippedTickerResizeObserver = null;

const skippedQueueNumbers = ref([
  "D1",
  "D2",
  "D3",
  "D4",
  "D5",
  "D6",
  "D4",
  "D5",
  "D6",
  "D4",
  "D5",
]);
const skippedTickerRef = ref(null);
const skippedTickerTrackRef = ref(null);
const skippedTickerOverflow = ref(false);

const skippedTickerItems = computed(() =>
  skippedTickerOverflow.value
    ? [...skippedQueueNumbers.value, ...skippedQueueNumbers.value]
    : skippedQueueNumbers.value
);

const updateSkippedTickerOverflow = async () => {
  await nextTick();
  const container = skippedTickerRef.value;
  const track = skippedTickerTrackRef.value;
  if (!container || !track) return;

  const itemCount = skippedQueueNumbers.value.length;
  const itemElements = track.querySelectorAll(".skipped-ticker__item");
  const singleSetItems = Array.from(itemElements).slice(0, itemCount);

  let contentWidth = 0;
  singleSetItems.forEach((item, index) => {
    contentWidth += item.getBoundingClientRect().width;
    if (index < singleSetItems.length - 1) {
      contentWidth += 16;
    }
  });

  const hasOverflow = contentWidth > container.clientWidth;
  if (hasOverflow !== skippedTickerOverflow.value) {
    skippedTickerOverflow.value = hasOverflow;
    if (hasOverflow) {
      await nextTick();
    }
  }
};

const fetchData = async () => {
  try {
    const response = await fetch(route("windows.data", props.window.id));
    if (!response.ok) return;
    const json = await response.json();
    const map = {};
    json.forEach((entry) => {
      map[entry.department.id] = entry.items;
    });
    dataByDepartment.value = map;
    lastUpdatedAt.value = new Date();
  } catch (e) {
    // ignore
  }
};

onMounted(() => {
  window.Echo.channel("queue").listen("CallPatient", (event) => {
    console.log("Patient called:", event.queueItem);

    const patientDeptId = event.queueItem.current_department.id;
    const isDepartmentOnScreen = props.departments.some(
      (dept) => dept.id === patientDeptId
    );
    if (!isDepartmentOnScreen) {
      return;
    }

    // Speak out patient's name using speech synthesis
    if ("speechSynthesis" in window) {
      const msg = new SpeechSynthesisUtterance(
        `${event.queueItem.current_department.name}, ${event.queueItem.queue_number}`
      );
      // Try Filipino (Tagalog/Philippines) accent/voice
      // Best-effort: not all browsers support "fil-PH" or "tl-PH", so select available Filipino voice
      const voices = window.speechSynthesis.getVoices();
      // Try to find a Filipino voice; fallback to default
      let filipinoVoice = voices.find(
        (v) =>
          (v.lang &&
            (v.lang.toLowerCase() === "fil-ph" ||
              v.lang.toLowerCase() === "tl-ph")) ||
          (v.name && v.name.toLowerCase().includes("filipino"))
      );
      if (filipinoVoice) {
        msg.voice = filipinoVoice;
      } else {
        // fallback: try setting lang to "tl-PH"
        msg.lang = "tl-PH";
      }
      window.speechSynthesis.speak(msg);
    } else {
      console.warn("Speech synthesis not supported in this browser.");
    }

    animateBlink(event.queueItem.queue_number);
  });
});

onMounted(() => {
  intervalId = setInterval(fetchData, 3000);
  // go fullscreen automatically
  if (document.documentElement.requestFullscreen) {
    document.documentElement.requestFullscreen().catch(() => {});
  }

  function updateClock() {
    const now = new Date();
    // Update the HTML element with the current time string
    // Format: Aug. 15, 2026 | Saturday <br> 2:11:30 PM
    const monthNames = [
      "Jan.",
      "Feb.",
      "Mar.",
      "Apr.",
      "May",
      "Jun.",
      "Jul.",
      "Aug.",
      "Sep.",
      "Oct.",
      "Nov.",
      "Dec.",
    ];
    const dayNames = [
      "Sunday",
      "Monday",
      "Tuesday",
      "Wednesday",
      "Thursday",
      "Friday",
      "Saturday",
    ];
    const month = monthNames[now.getMonth()];
    const day = now.getDate();
    const year = now.getFullYear();
    const dayOfWeek = dayNames[now.getDay()];

    let hour = now.getHours();
    const minute = now.getMinutes().toString().padStart(2, "0");
    const second = now.getSeconds().toString().padStart(2, "0");
    const ampm = hour >= 12 ? "PM" : "AM";
    hour = hour % 12;
    hour = hour ? hour : 12; // the hour '0' should be '12'
    const timeStr = `${hour}:${minute}:${second} ${ampm}`;
    const dateStr = `<span class="font-bold">${month} ${day}, ${year} | ${dayOfWeek}</span> <br> ${timeStr}`;
    document.getElementById("clock").innerHTML = dateStr;
  }

  // Run the function immediately, then repeat every 1000ms (1 second)
  updateClock();
  setInterval(updateClock, 1000);

  updateSkippedTickerOverflow();
  window.addEventListener("resize", updateSkippedTickerOverflow);

  if (typeof ResizeObserver !== "undefined" && skippedTickerRef.value) {
    skippedTickerResizeObserver = new ResizeObserver(
      updateSkippedTickerOverflow
    );
    skippedTickerResizeObserver.observe(skippedTickerRef.value);
  }
});

onBeforeUnmount(() => {
  if (intervalId) clearInterval(intervalId);
  window.removeEventListener("resize", updateSkippedTickerOverflow);
  skippedTickerResizeObserver?.disconnect();
});

function animateBlink(queue_number) {
  const span = document.getElementById(queue_number);
  span.classList.remove("blink-bg-animation");

  setTimeout(() => {
    span.classList.add("blink-bg-animation");
  }, 800); // Delay adding the class by 1 second to ensure the animation restarts visibly
}

function enableSpeech() {
  speechEnabled.value = true;

  // say a test phrase to "unlock" speech
  const msg = new SpeechSynthesisUtterance("Announcements Enabled");
  window.speechSynthesis.speak(msg);
}

const getNowServing = (departmentId) => {
  const list = (dataByDepartment.value[departmentId] || []).filter(
    (i) => i.status === "serving"
  );
  return list.length ? list.slice(0, 5) : null;
  // console.log(test);
  // return list.length ? list[0].queue_number : null;
};

const getUpNext = (departmentId) => {
  return (dataByDepartment.value[departmentId] || [])
    .filter((i) => i.status !== "serving")
    .sort((a, b) => a.queue_position - b.queue_position)
    .slice(0, 20);
};
</script>

<style>
html,
body,
#app {
  height: 100%;
}

.card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1) !important;
}

.badge {
  transition: all 0.3s ease;
}

.badge:hover {
  transform: scale(1.1);
}

.display-3 {
  font-weight: 700;
  letter-spacing: -0.02em;
}

.card-header {
  border-radius: 15px 15px 0 0 !important;
}

.card {
  border-radius: 15px !important;
  overflow: hidden;
}

@keyframes blink {
  0% {
    background-color: yellow;
  }
  50% {
    background-color: transparent;
  }
  100% {
    background-color: yellow;
  }
}
</style>


