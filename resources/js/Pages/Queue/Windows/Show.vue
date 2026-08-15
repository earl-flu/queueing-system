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
        <div class="flex gap-3 items-center">
          <div>
            <button @click="enableSpeech" class="btn btn-sm">
              <i
                style="font-size: 30px"
                :class="
                  speechEnabled
                    ? 'bi bi-volume-up text-green-600'
                    : 'bi bi-volume-mute text-red-500'
                "
              ></i>
            </button>
          </div>
          <div>
            <i class="bi bi-clock" style="font-size: 32px"></i>
          </div>
          <div>
            <div id="clock">Loading time...</div>
          </div>
          <!-- <div class="text-xs text-gray-500">
            Updated: {{ lastUpdatedDisplay }}
          </div> -->
        </div>
      </div>
    </div>

    <SingleDepartmentLayout
      v-if="isSingleDepartment"
      :department="departments[0]"
      :get-now-serving="getNowServing"
      :get-priority-up-next="getPriorityUpNext"
      :get-regular-up-next="getRegularUpNext"
      :get-skipped="getSkipped"
    />

    <MultiDepartmentLayout
      v-else
      :departments="departments"
      :get-now-serving="getNowServing"
      :get-priority-up-next="getPriorityUpNext"
      :get-regular-up-next="getRegularUpNext"
      :get-skipped="getSkipped"
    />
  </div>
</template>

<style scoped>
* {
  font-family: Roboto !important;
}
p {
  margin: 0;
}
</style>

<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { useWindowQueue } from "@/Composables/useWindowQueue";
import SingleDepartmentLayout from "@/Components/Queue/Windows/SingleDepartmentLayout.vue";
import MultiDepartmentLayout from "@/Components/Queue/Windows/MultiDepartmentLayout.vue";

const speechEnabled = ref(false);

const props = defineProps({
  window: Object,
  departments: Array,
  queuesByDepartment: Object,
});

const {
  lastUpdatedDisplay,
  getNowServing,
  getPriorityUpNext,
  getRegularUpNext,
  getSkipped,
  fetchData,
} = useWindowQueue(props);

const isSingleDepartment = computed(() => props.departments.length === 1);

let intervalId = null;
let clockIntervalId = null;

const fetchDataAndRefresh = async () => {
  await fetchData();
};

onMounted(() => {
  window.Echo.channel("queue").listen("CallPatient", (event) => {
    const patientDeptId = event.queueItem.current_department.id;
    const isDepartmentOnScreen = props.departments.some(
      (dept) => dept.id === patientDeptId
    );
    if (!isDepartmentOnScreen) {
      return;
    }

    if ("speechSynthesis" in window) {
      const msg = new SpeechSynthesisUtterance(
        `${event.queueItem.current_department.name}, ${event.queueItem.queue_number}`
      );
      const voices = window.speechSynthesis.getVoices();
      const filipinoVoice = voices.find(
        (v) =>
          (v.lang &&
            (v.lang.toLowerCase() === "fil-ph" ||
              v.lang.toLowerCase() === "tl-ph")) ||
          (v.name && v.name.toLowerCase().includes("filipino"))
      );
      if (filipinoVoice) {
        msg.voice = filipinoVoice;
      } else {
        msg.lang = "tl-PH";
      }
      window.speechSynthesis.speak(msg);
    }

    animateBlink(event.queueItem.queue_number);
    fetchData();
  });
});

onMounted(() => {
  intervalId = setInterval(fetchDataAndRefresh, 3000);

  if (document.documentElement.requestFullscreen) {
    document.documentElement.requestFullscreen().catch(() => {});
  }

  function updateClock() {
    const now = new Date();
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
    hour = hour ? hour : 12;
    const timeStr = `${hour}:${minute}:${second} ${ampm}`;
    const dateStr = `<span class="font-bold">${month} ${day}, ${year} | ${dayOfWeek}</span> <br> ${timeStr}`;
    const clockEl = document.getElementById("clock");
    if (clockEl) {
      clockEl.innerHTML = dateStr;
    }
  }

  updateClock();
  clockIntervalId = setInterval(updateClock, 1000);
});

onBeforeUnmount(() => {
  if (intervalId) clearInterval(intervalId);
  if (clockIntervalId) clearInterval(clockIntervalId);
});

function animateBlink(queue_number) {
  const span = document.getElementById(queue_number);
  if (!span) return;

  span.classList.remove("blink-bg-animation");
  setTimeout(() => {
    span.classList.add("blink-bg-animation");
  }, 800);
}

function enableSpeech() {
  speechEnabled.value = true;
  const msg = new SpeechSynthesisUtterance("Announcements Enabled");
  window.speechSynthesis.speak(msg);
}
</script>

<style>
html,
body,
#app {
  height: 100%;
}

.blink-bg-animation {
  animation: blink 0.9s 8;
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
