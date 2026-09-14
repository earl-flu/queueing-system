<script setup>
import { useElapsedTime } from "@/Composables/useElapsedTime";
import { useCallPatient } from "@/Composables/useCallPatient";
import { useTimePerDepartment } from "@/Composables/useTimePerDepartment";
import { router } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
});

const { callPatient } = useCallPatient();
const elapsed = useElapsedTime(props.item.waiting_started_at);
const timePerDepartmentArr = useTimePerDepartment(props.item.queue_number);
</script>

 <template>
  <div class="card shadow border">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <h3 class="card-title text-primary mb-0 font-bold">
          {{ item.queue_number }}
        </h3>
      </div>

      <div class="mb-3">
        <h6 class="card-subtitle mb-1 uppercase">
          {{ item.patient.last_name }}
          {{ item.patient.first_name }}
          {{ item.patient.middle_name }}
          {{ item.patient.suffix }}
        </h6>
        <p v-if="item.patient.phone" class="card-text small mb-1">
          {{ item.patient.phone }}
        </p>
        <p title="Waiting Time" class="text-right" style="margin-bottom: 0">
          {{ elapsed }}
        </p>

        <small class="card-subtitle card-text"
          >Position: {{ item.queue_position }}</small
        >
        <p
          v-if="item.patient.priority_reason"
          class="card-text small mb-1 mt-3 text-white mb-3"
        >
          <span class="px-2 py-1 rounded-md bg-red-500">
            {{ item.patient.priority_reason.description }}
          </span>
        </p>
        <table
          v-if="timePerDepartmentArr.length"
          class="text-xs border w-full opacity-50 mb-3"
        >
          <tr>
            <th class="border">Dept.</th>
            <th class="border">Time Spent</th>
          </tr>
          <tr v-for="(dept, idx) in timePerDepartmentArr" :key="idx">
            <td class="border">{{ dept.department_name }}</td>
            <td class="border">{{ dept.time_spent }}</td>
          </tr>
        </table>
      </div>
      <div class="gap-2 flex">
        <button
          @click="callPatient(item.id)"
          class="btn btn-success btn-sm flex-1"
        >
          Call
        </button>
      </div>
    </div>
  </div>
</template>