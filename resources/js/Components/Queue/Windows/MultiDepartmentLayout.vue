<template>
  <div>
    <div class="row font-bold uppercase">
      <DepartmentQueueColumn
        v-for="(dept, index) in departments"
        :key="dept.id"
        :department="dept"
        :variant="index % 2 === 0 ? 'green' : 'blue'"
        :get-now-serving="getNowServing"
        :get-priority-up-next="getPriorityUpNext"
        :get-regular-up-next="getRegularUpNext"
        :get-skipped="getSkipped"
      />
    </div>
    <div class="row p-3">
      <div class="col">
        <div
          class="border-blue-900 rounded-3xl bg-blue-50 text-blue-900 flex uppercase p-4 py-2 font-bold gap-3 items-center"
        >
          <div>
            <i class="bi bi-person-rolodex" style="font-size: 40px"></i>
          </div>
          <div>
            <p>Please ask helpdesk for assistance</p>
            <p class="normal-case font-normal">Thank you for your patience</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import DepartmentQueueColumn from "./DepartmentQueueColumn.vue";

defineProps({
  departments: Array,
  getNowServing: Function,
  getPriorityUpNext: Function,
  getRegularUpNext: Function,
  getSkipped: Function,
});
</script>
