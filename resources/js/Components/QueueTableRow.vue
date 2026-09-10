<script setup>
import { Link, useForm } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import { usePrintQueue } from "@/Composables/usePrintQueue";
import { useToast } from "vue-toastification";

const { printQueueTicket } = usePrintQueue();
const toast = useToast();

const props = defineProps({
  item: Object,
  user: Object,
  isReceptionist: Boolean,
  priorityReasons: {
    type: Array,
    default: () => [],
  },
});

const isAdmin = computed(() => props.user?.role === "admin");
const isEditOpen = ref(false);

const canEdit = computed(() => {
  if (props.isReceptionist || isAdmin.value) return true;
  return hasAccessToDepartment(props.item.current_department);
});

const hasAccessToDepartment = (department) => {
  if (props.isReceptionist) return true;
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
    isPriority: q.patient.is_priority,
    flowDepartments: q.department_flow_names,
    queueDate: q.created_at,
  });
};

const editForm = useForm({
  last_name: "",
  first_name: "",
  middle_name: "",
  suffix: "",
  is_priority: false,
  priority_reason_id: "",
});

const openEdit = () => {
  editForm.clearErrors();
  editForm.last_name = props.item.patient.last_name || "";
  editForm.first_name = props.item.patient.first_name || "";
  editForm.middle_name = props.item.patient.middle_name || "";
  editForm.suffix = props.item.patient.suffix || "";
  editForm.is_priority = Boolean(props.item.patient.is_priority);
  editForm.priority_reason_id = props.item.patient.priority_reason_id || "";
  isEditOpen.value = true;
};

const closeEdit = () => {
  isEditOpen.value = false;
};

watch(
  () => editForm.is_priority,
  (isPriority) => {
    if (!isPriority) {
      editForm.priority_reason_id = "";
    }
  }
);

const submitEdit = () => {
  editForm.patch(route("queue.update-patient", props.item.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success("Patient details updated.");
      closeEdit();
    },
  });
};
</script>

<template>
  <Teleport to="body">
    <div
      v-if="isEditOpen"
      class="modal fade show d-block"
      tabindex="-1"
      style="background-color: rgba(0, 0, 0, 0.5)"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Patient</h5>
            <button type="button" class="btn-close" @click="closeEdit"></button>
          </div>
          <form @submit.prevent="submitEdit">
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Last Name</label>
                  <input
                    v-model="editForm.last_name"
                    type="text"
                    class="form-control uppercase"
                  />
                  <div class="invalid-feedback d-block">
                    {{ editForm.errors.last_name }}
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">First Name</label>
                  <input
                    v-model="editForm.first_name"
                    type="text"
                    class="form-control uppercase"
                  />
                  <div class="invalid-feedback d-block">
                    {{ editForm.errors.first_name }}
                  </div>
                </div>
                <div class="col-md-8">
                  <label class="form-label">Middle Name</label>
                  <input
                    v-model="editForm.middle_name"
                    type="text"
                    class="form-control uppercase"
                  />
                  <div class="invalid-feedback d-block">
                    {{ editForm.errors.middle_name }}
                  </div>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Suffix</label>
                  <select v-model="editForm.suffix" class="form-select">
                    <option value="">None</option>
                    <option value="Jr.">Jr.</option>
                    <option value="Sr.">Sr.</option>
                    <option value="II">II</option>
                    <option value="III">III</option>
                    <option value="IV">IV</option>
                    <option value="V">V</option>
                  </select>
                  <div class="invalid-feedback d-block">
                    {{ editForm.errors.suffix }}
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-check">
                    <input
                      :id="`is_priority_${item.id}`"
                      type="checkbox"
                      class="form-check-input"
                      v-model="editForm.is_priority"
                    />
                    <label
                      class="form-check-label"
                      :for="`is_priority_${item.id}`"
                    >
                      Priority
                    </label>
                  </div>
                  <div class="invalid-feedback d-block">
                    {{ editForm.errors.is_priority }}
                  </div>
                </div>
                <div class="col-12" v-if="editForm.is_priority">
                  <label class="form-label">Priority Reason</label>
                  <select
                    v-model="editForm.priority_reason_id"
                    class="form-select"
                  >
                    <option value="">Select</option>
                    <option
                      v-for="reason in priorityReasons"
                      :key="reason.id"
                      :value="reason.id"
                    >
                      {{ reason.description }}
                    </option>
                  </select>
                  <div class="invalid-feedback d-block">
                    {{ editForm.errors.priority_reason_id }}
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-secondary"
                @click="closeEdit"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="btn btn-primary"
                :disabled="editForm.processing"
              >
                Save
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </Teleport>
  <tr class="hover:bg-gray-100">
    <td class="fw-bold">
      {{ item.queue_number }}
    </td>
    <td>
      <div class="fw-medium uppercase">
        {{ item.patient.last_name }}
        {{ item.patient.first_name }}
        {{ item.patient.middle_name }} {{ item.patient.suffix }}
        <p v-if="item.patient.is_priority" class="mb-0">
          <span class="text-xs text-orange-300 rounded-md">priority</span>
          <span
            v-if="item.patient.priority_reason"
            class="text-muted small ms-1"
          >
            ({{ item.patient.priority_reason.description }})
          </span>
        </p>
      </div>
      <div v-if="item.patient.phone" class="text-muted small">
        {{ item.patient.phone }}
      </div>
    </td>
    <td>
      <div
        v-if="
          item.queue_number.substring(0, 3) !== item.current_department.code
        "
        class="text-info small"
      >
        {{ item.original_department.name }}
      </div>
    </td>
    <td>
      <div class="fw-medium">
        {{ item.current_department.name }}
      </div>
      <div v-if="item.current_department.room" class="text-muted small">
        {{ item.current_department.room }}
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
        <Link
          :href="route('queue.department', item.current_department.id)"
          class="btn btn-info btn-sm"
        >
          View
        </Link>
        <button
          v-if="canEdit"
          type="button"
          class="btn btn-outline-primary btn-sm"
          @click="openEdit"
        >
          Edit
        </button>
        <button @click="handlePrint(item)" class="ml-2">
          <i class="material-icons-outlined">printer</i>
        </button>
      </div>
    </td>
  </tr>
</template>
