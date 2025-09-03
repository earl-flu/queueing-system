<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps({
  departments: Array,
  stats: Object,
  departmentStats: Array,
  performanceMetrics: Object,
  recentActivity: Array,
  hourlyStats: Array,
  selectedDate: String,
});

const selectedDate = ref(
  props.selectedDate || new Date().toISOString().split("T")[0]
);
const isLoading = ref(false);

const updateDate = () => {
  isLoading.value = true;
  window.location.href = `/dashboard/admin/${selectedDate.value}`;
};

const formatTime = (seconds) => {
  if (!seconds) return "0:00";
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  const secs = Math.round(seconds % 60);

  if (hours > 0) {
    return `${hours}:${minutes.toString().padStart(2, "0")}:${secs
      .toString()
      .padStart(2, "0")}`;
  }
  return `${minutes}:${secs.toString().padStart(2, "0")}`;
};

const formatEfficiencyScore = (score) => {
  if (score >= 80) return { text: `${score}%`, class: "text-success" };
  if (score >= 60) return { text: `${score}%`, class: "text-warning" };
  return { text: `${score}%`, class: "text-danger" };
};

const getStatusColor = (status) => {
  switch (status) {
    case "waiting":
      return "text-warning";
    case "serving":
      return "text-primary";
    case "done":
      return "text-success";
    case "transferred":
      return "text-info";
    case "no_show":
      return "text-danger";
    default:
      return "text-muted";
  }
};
</script>

<template>
  <Head title="Admin Dashboard" />

  <AuthenticatedLayout>
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Dashboard</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item">
              <a href="javascript:;"><i class="bx bx-home-alt"></i></a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
              Admin Dashboard
              <span
                v-if="selectedDate !== new Date().toISOString().split('T')[0]"
                class="ms-2 text-muted"
              >
                ({{ selectedDate }})
              </span>
            </li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <!-- Date Filter -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="card radius-10">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <h6 class="mb-0">
                  Date Filter
                  <span
                    v-if="
                      selectedDate !== new Date().toISOString().split('T')[0]
                    "
                    class="ms-2 text-primary"
                  >
                    - {{ selectedDate }}
                  </span>
                </h6>
                <small class="text-muted"
                  >Select a date to view dashboard data</small
                >
              </div>
              <div class="d-flex align-items-center gap-2">
                <input
                  type="date"
                  v-model="selectedDate"
                  class="form-control"
                  @change="updateDate"
                />
                <button
                  @click="updateDate"
                  class="btn btn-primary"
                  type="button"
                  :disabled="isLoading"
                >
                  <i v-if="!isLoading" class="bx bx-search"></i>
                  <i v-else class="bx bx-loader-alt bx-spin"></i>
                  {{ isLoading ? "Loading..." : "Update" }}
                </button>
                <button
                  @click="
                    () => {
                      selectedDate = new Date().toISOString().split('T')[0];
                      updateDate();
                    }
                  "
                  class="btn btn-outline-secondary"
                  type="button"
                >
                  <i class="bx bx-calendar"></i> Today
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--end Date Filter-->

    <div class="row">
      <!-- Today's Overview Cards -->
      <div class="col-12">
        <div class="row">
          <div class="col-12 col-lg-3 col-md-6 col-sm-12">
            <div class="card radius-10">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="">
                    <h4 class="mb-1 text-primary">
                      {{ stats.total_patients }}
                    </h4>
                    <p class="mb-0 text-secondary">Total Patients</p>
                  </div>
                  <div class="ms-auto fs-2 text-primary">
                    <i class="bx bx-user"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-lg-3 col-md-6 col-sm-12">
            <div class="card radius-10">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="">
                    <h4 class="mb-1 text-warning">{{ stats.waiting }}</h4>
                    <p class="mb-0 text-secondary">Currently Waiting</p>
                  </div>
                  <div class="ms-auto fs-2 text-warning">
                    <i class="bx bx-time"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-lg-3 col-md-6 col-sm-12">
            <div class="card radius-10">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="">
                    <h4 class="mb-1 text-success">{{ stats.completed }}</h4>
                    <p class="mb-0 text-secondary">Completed</p>
                  </div>
                  <div class="ms-auto fs-2 text-success">
                    <i class="bx bx-check-circle"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-lg-3 col-md-6 col-sm-12">
            <div class="card radius-10">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="">
                    <h4 class="mb-1 text-info">
                      {{ formatTime(stats.avg_waiting_time) }}
                    </h4>
                    <p class="mb-0 text-secondary">Avg Wait Time</p>
                  </div>
                  <div class="ms-auto fs-2 text-info">
                    <i class="bx bx-timer"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Department Performance Table -->
      <div class="col-12 col-lg-8">
        <div class="card radius-10">
          <div class="card-header">
            <h6 class="mb-0">Department Performance</h6>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th>Department</th>
                    <th>Waiting</th>
                    <th>Serving</th>
                    <th>Completed</th>
                    <th>Avg Wait</th>
                    <th>Avg Serve</th>
                    <th>Efficiency</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="dept in departmentStats" :key="dept.id">
                    <td>
                      <div>
                        <h6 class="mb-0">{{ dept.name }}</h6>
                        <small class="text-muted">{{ dept.code }}</small>
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-warning text-dark">{{
                        dept.waiting
                      }}</span>
                    </td>
                    <td>
                      <span class="badge bg-primary">{{ dept.serving }}</span>
                    </td>
                    <td>
                      <span class="badge bg-success">{{ dept.completed }}</span>
                    </td>
                    <td>{{ formatTime(dept.avg_waiting_time) }}</td>
                    <td>{{ formatTime(dept.avg_serving_time) }}</td>
                    <td>
                      <span
                        :class="
                          formatEfficiencyScore(dept.efficiency_score).class
                        "
                      >
                        {{ formatEfficiencyScore(dept.efficiency_score).text }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Performance Metrics -->
      <div class="col-12 col-lg-4">
        <div class="card radius-10">
          <div class="card-header">
            <h6 class="mb-0">Performance Metrics</h6>
          </div>
          <div class="card-body">
            <div class="mb-4">
              <h6 class="text-primary">Today</h6>
              <div class="row">
                <div class="col-6">
                  <small class="text-muted">Patients</small>
                  <h6>{{ performanceMetrics.daily.total_patients }}</h6>
                </div>
                <div class="col-6">
                  <small class="text-muted">Completion Rate</small>
                  <h6>{{ performanceMetrics.daily.completion_rate }}%</h6>
                </div>
              </div>
              <div class="row">
                <div class="col-6">
                  <small class="text-muted">Avg Wait</small>
                  <h6>
                    {{ formatTime(performanceMetrics.daily.avg_waiting_time) }}
                  </h6>
                </div>
                <div class="col-6">
                  <small class="text-muted">Avg Serve</small>
                  <h6>
                    {{ formatTime(performanceMetrics.daily.avg_serving_time) }}
                  </h6>
                </div>
              </div>
            </div>

            <div class="mb-4">
              <h6 class="text-warning">This Week</h6>
              <div class="row">
                <div class="col-6">
                  <small class="text-muted">Patients</small>
                  <h6>{{ performanceMetrics.weekly.total_patients }}</h6>
                </div>
                <div class="col-6">
                  <small class="text-muted">Completion Rate</small>
                  <h6>{{ performanceMetrics.weekly.completion_rate }}%</h6>
                </div>
              </div>
            </div>

            <div>
              <h6 class="text-success">This Month</h6>
              <div class="row">
                <div class="col-6">
                  <small class="text-muted">Patients</small>
                  <h6>{{ performanceMetrics.monthly.total_patients }}</h6>
                </div>
                <div class="col-6">
                  <small class="text-muted">Completion Rate</small>
                  <h6>{{ performanceMetrics.monthly.completion_rate }}%</h6>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="col-12 col-lg-6">
        <div class="card radius-10">
          <div class="card-header">
            <h6 class="mb-0">Recent Activity</h6>
          </div>
          <div class="card-body">
            <div class="activity-feed">
              <div
                v-for="activity in recentActivity"
                :key="activity.id"
                class="activity-item mb-3"
              >
                <div class="d-flex align-items-center">
                  <div class="activity-icon me-3">
                    <i class="bx bx-user-circle fs-4"></i>
                  </div>
                  <div class="flex-grow-1">
                    <div
                      class="d-flex justify-content-between align-items-center"
                    >
                      <h6 class="mb-0">{{ activity.patient_name }}</h6>
                      <small class="text-muted">{{
                        activity.created_at
                      }}</small>
                    </div>
                    <p class="mb-0 text-muted">
                      {{ activity.queue_number }} - {{ activity.department }}
                      <span
                        :class="getStatusColor(activity.status)"
                        class="ms-2"
                      >
                        {{ activity.status }}
                      </span>
                    </p>
                    <small
                      v-if="activity.waiting_time || activity.serving_time"
                      class="text-muted"
                    >
                      Wait: {{ activity.waiting_time || "0:00" }} | Serve:
                      {{ activity.serving_time || "0:00" }}
                    </small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Hourly Statistics -->
      <div class="col-12 col-lg-6">
        <div class="card radius-10">
          <div class="card-header">
            <h6 class="mb-0">Hourly Statistics</h6>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Hour</th>
                    <th>New Patients</th>
                    <th>Completed</th>
                    <th>Avg Wait</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="hour in hourlyStats" :key="hour.hour">
                    <td>{{ hour.hour }}</td>
                    <td>{{ hour.new_patients }}</td>
                    <td>{{ hour.completed }}</td>
                    <td>{{ formatTime(hour.avg_waiting_time) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.activity-feed {
  max-height: 400px;
  overflow-y: auto;
}

.activity-item {
  border-left: 3px solid #e9ecef;
  padding-left: 15px;
}

.activity-item:hover {
  border-left-color: #007bff;
}

.activity-icon {
  color: #6c757d;
}

.card {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.table th {
  border-top: none;
  font-weight: 600;
  color: #495057;
}

.badge {
  font-size: 0.75em;
}

.bx-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
