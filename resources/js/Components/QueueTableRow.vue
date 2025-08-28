<script setup>
import { Link } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { usePrintQueue } from "@/Composables/usePrintQueue";

const { printQueueTicket } = usePrintQueue();

const props = defineProps({
  item: Object,
  user: Object,
  isReception: Boolean,
});

const isAdmin = computed(() => props.user?.role === "admin");

const hasAccessToDepartment = (department) => {
  if (props.isReception) return false;
  if (isAdmin.value) return true;
  return (
    department.users && department.users.some((u) => u.id === props.user.id)
  );
};

const getStatusBadgeClass = (status) => {
  const classes = {
    waiting: "bg-warning text-dark",
    serving: "bg-success text-white",
    done: "bg-secondary text-white",
    transferred: "bg-info text-white",
    no_show: "bg-danger text-white",
  };
  return classes[status] || "bg-secondary text-white";
};

const getStatusLabel = (status) => {
  const map = {
    waiting: "WAITING",
    serving: "SERVING",
    done: "DONE",
    transferred: "TRANSFERRED",
    no_show: "NO SHOW",
  };
  return map[status] || status.toUpperCase();
};

const formatTime = (datetime) => {
  return new Date(datetime).toLocaleTimeString("en-US", {
    hour: "2-digit",
    minute: "2-digit",
  });
};

const handlePrint = (q) => {
  printQueueTicket({
    queueNumber: q.queue_number,
    firstName: q.patient.first_name,
    lastName: q.patient.last_name,
    isPriority: q.patient.is_priority, // or from form.patient.is_priority
    flowDepartments: q.department_flow_names,
    queueDate: q.created_at,
  });
};
</script>

<template>
  <tr>
    <td class="fw-bold">
      {{ item.queue_number }}
    </td>
    <td>
      <div class="fw-medium uppercase">
        <!-- {{ item.patient.full_name }} -->
        {{ item.patient.last_name }}
        {{ item.patient.first_name }}
        {{ item.patient.middle_name }} {{ item.patient.suffix }}
        <p v-if="item.patient.is_priority">
          <span class="text-xs bg-danger text-white px-2 rounded-md"
            >priority</span
          >
        </p>
      </div>
      <div v-if="item.patient.phone" class="text-muted small">
        {{ item.patient.phone }}
      </div>
    </td>
    <td>
      <div class="fw-medium">
        {{ item.current_department.name }}
      </div>
      <div v-if="item.current_department.room" class="text-muted small">
        {{ item.current_department.room }}
      </div>
      <div
        v-if="
          item.queue_number.substring(0, 3) !== item.current_department.code
        "
        class="text-info small"
      >
        Originally: {{ item.original_department.name }}
      </div>
    </td>
    <td>
      <span :class="getStatusBadgeClass(item.status)" class="badge">
        {{ getStatusLabel(item.status) }}
      </span>
    </td>
    <td class="text-muted">
      <div>{{ formatTime(item.created_at) }}</div>
      <div v-if="item.called_at" class="small">
        Called: {{ formatTime(item.called_at) }}
      </div>
    </td>
    <td>
      {{ item.served_by_user?.name || "-" }}
    </td>
    <td>
      <div
        class="btn-group"
        role="group"
        v-if="hasAccessToDepartment(item.current_department)"
      >
        <!-- <button
                          v-if="item.status === 'waiting'"
                          @click="callPatient(item.id)"
                          class="btn btn-success btn-sm"
                        >
                          Call
                        </button> -->
        <!-- <button
                          v-if="item.status === 'serving'"
                          @click="completeService(item.id)"
                          class="btn btn-primary btn-sm"
                        >
                          Complete
                        </button> -->
        <Link
          :href="route('queue.department', item.current_department.id)"
          class="btn btn-info btn-sm"
        >
          View
        </Link>
        <button @click="handlePrint(item)">
          <i class="material-icons-outlined">printer</i>Print Queue Ticket
        </button>
      </div>
    </td>
  </tr>
</template>
