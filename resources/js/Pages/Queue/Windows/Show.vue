<template>
  <Head :title="`${window.name} Display`" />
  <div class="w-100 h-100" style="background: #000; color: #fff">
    <div class="container-fluid py-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="m-0">{{ window.name }}</h1>
        <div class="text-muted">Last updated: {{ lastUpdatedDisplay }}</div>
      </div>
      <div class="row g-3">
        <div
          v-for="dept in departments"
          :key="dept.id"
          class="col-12 col-md-6 col-xl-4"
        >
          <div class="card bg-dark text-white h-100">
            <div
              class="card-header d-flex justify-content-between align-items-center"
            >
              <h4 class="m-0">{{ dept.name }}</h4>
              <span class="badge bg-secondary">{{ dept.room || "N/A" }}</span>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <h6 class="text-uppercase text-muted">Now Serving</h6>
                <div class="display-4 fw-bold">
                  {{ getNowServing(dept.id) ?? "—" }}
                </div>
              </div>
              <div>
                <h6 class="text-uppercase text-muted">Up Next</h6>
                <div class="d-flex flex-wrap gap-2">
                  <span
                    v-for="q in getUpNext(dept.id)"
                    :key="q.id"
                    class="badge bg-info fs-5"
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

<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, computed, onMounted, onBeforeUnmount } from "vue";

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
  intervalId = setInterval(fetchData, 3000);
  // go fullscreen automatically
  if (document.documentElement.requestFullscreen) {
    document.documentElement.requestFullscreen().catch(() => {});
  }
});

onBeforeUnmount(() => {
  if (intervalId) clearInterval(intervalId);
});

const getNowServing = (departmentId) => {
  const list = (dataByDepartment.value[departmentId] || []).filter(
    (i) => i.status === "serving"
  );
  return list.length ? list[0].queue_number : null;
};

const getUpNext = (departmentId) => {
  return (dataByDepartment.value[departmentId] || [])
    .filter((i) => i.status !== "serving")
    .sort((a, b) => a.queue_position - b.queue_position)
    .slice(0, 5);
};
</script>

<style>
html,
body,
#app {
  height: 100%;
}
</style>


