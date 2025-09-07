<template>
  <Head :title="`${window.name} Display`" />
  <div class="w-100 h-100" style="background: black; color: #333">
    <div class="container-fluid py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div
          class="text-white-50 bg-white bg-opacity-10 px-3 py-2 rounded-pill"
        >
          <i class="fas fa-clock me-2"></i>
          Last updated: {{ lastUpdatedDisplay }}
        </div>
        <div>
          <button @click="enableSpeech">Enable Announcements</button>
        </div>
      </div>
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
                  Now Serving
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
                  Up Next
                </h6>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                  <span
                    v-for="q in getUpNext(dept.id)"
                    :key="q.id"
                    class="badge fs-5 px-4 py-3 fw-bold"
                    :style="{
                      background: q.patient.is_priority ? '#DFA620' : '#0d1077',
                      color: 'white',
                      boxShadow: '0 4px 15px rgba(0,0,0,0.2)',
                    }"
                  >
                    {{ q.queue_number }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.blink-bg-animation {
  animation: blink 0.9s 8;
}
</style>

<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, computed, onMounted, onBeforeUnmount } from "vue";

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
        `Now calling patient ${event.queueItem.queue_number}. Please proceed to ${event.queueItem.current_department.name}`
      );
      msg.lang = "en-US"; // or "fil-PH" if you want Filipino accent
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
});

onBeforeUnmount(() => {
  if (intervalId) clearInterval(intervalId);
});

function animateBlink(queue_number) {
  const span = document.getElementById(queue_number);
  console.log(span);
  span.classList.remove("blink-bg-animation");

  setTimeout(() => {
    span.classList.add("blink-bg-animation");
  }, 500); // Delay adding the class by 1 second to ensure the animation restarts visibly
  console.log(span);
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
  return list.length ? list.slice(0, 2) : null;
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


