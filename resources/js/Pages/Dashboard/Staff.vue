<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps({
  departments: Array,
  userStats: Object,
  departmentStats: Array,
  recentActivity: Array,
});

const formatTime = (seconds) => {
  if (!seconds) return "0:00";
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  const secs = seconds % 60;

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
  <Head title="Staff Dashboard" />

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
              Staff Dashboard
            </li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
      <!-- Personal Performance Cards -->
      <div class="col-12">
        <div class="row">
          <div class="col-12 col-lg-3 col-md-6 col-sm-12">
            <div class="card radius-10">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="">
                    <h4 class="mb-1 text-primary">
                      {{ userStats.total_served_today }}
                    </h4>
                    <p class="mb-0 text-secondary">Patients Served Today</p>
                  </div>
                  <div class="ms-auto fs-2 text-primary">
                    <i class="bx bx-user-check"></i>
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
                    <h4 class="mb-1 text-warning">
                      {{ userStats.current_serving }}
                    </h4>
                    <p class="mb-0 text-secondary">Currently Serving</p>
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
                    <h4 class="mb-1 text-info">
                      {{ formatTime(userStats.avg_serving_time) }}
                    </h4>
                    <p class="mb-0 text-secondary">Avg Serving Time</p>
                  </div>
                  <div class="ms-auto fs-2 text-info">
                    <i class="bx bx-timer"></i>
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
                    <h4 class="mb-1 text-success">
                      <span
                        :class="
                          formatEfficiencyScore(userStats.efficiency_score)
                            .class
                        "
                      >
                        {{
                          formatEfficiencyScore(userStats.efficiency_score).text
                        }}
                      </span>
                    </h4>
                    <p class="mb-0 text-secondary">Efficiency Score</p>
                  </div>
                  <div class="ms-auto fs-2 text-success">
                    <i class="bx bx-trending-up"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Department Queue Status -->
      <div class="col-12 col-lg-8">
        <div class="card radius-10">
          <div class="card-header">
            <h6 class="mb-0">Your Department Queues</h6>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th>Department</th>
                    <th>Waiting</th>
                    <th>Serving</th>
                    <th>Next Queue</th>
                    <th>Avg Wait Time</th>
                    <th>Action</th>
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
                      <span
                        v-if="dept.next_queue_number > 0"
                        class="badge bg-info"
                      >
                        #{{ dept.next_queue_number }}
                      </span>
                      <span v-else class="text-muted">None</span>
                    </td>
                    <td>{{ formatTime(dept.avg_waiting_time) }}</td>
                    <td>
                      <a
                        :href="`/queue/department/${dept.id}`"
                        class="btn btn-sm btn-primary"
                      >
                        <i class="bx bx-show"></i> View Queue
                      </a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="col-12 col-lg-4">
        <div class="card radius-10">
          <div class="card-header">
            <h6 class="mb-0">Your Recent Activity</h6>
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
                      {{ activity.queue_number }}
                      <span
                        :class="getStatusColor(activity.status)"
                        class="ms-2"
                      >
                        {{ activity.status }}
                      </span>
                    </p>
                    <small v-if="activity.serving_time" class="text-muted">
                      Serve Time: {{ activity.serving_time }}
                    </small>
                  </div>
                </div>
              </div>
              <div
                v-if="recentActivity.length === 0"
                class="text-center text-muted py-4"
              >
                <i class="bx bx-info-circle fs-1"></i>
                <p class="mt-2">No recent activity</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="col-12">
        <div class="card radius-10">
          <div class="card-header">
            <h6 class="mb-0">Quick Actions</h6>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-3 col-sm-6 mb-3">
                <a href="/queue" class="btn btn-primary w-100">
                  <i class="bx bx-list-ul me-2"></i>
                  View All Queues
                </a>
              </div>
              <div class="col-md-3 col-sm-6 mb-3">
                <a href="/patients" class="btn btn-success w-100">
                  <i class="bx bx-user-plus me-2"></i>
                  Add Patient
                </a>
              </div>
              <div class="col-md-3 col-sm-6 mb-3">
                <a href="/queue/call" class="btn btn-warning w-100">
                  <i class="bx bx-volume-full me-2"></i>
                  Call Next Patient
                </a>
              </div>
              <div class="col-md-3 col-sm-6 mb-3">
                <a href="/reports" class="btn btn-info w-100">
                  <i class="bx bx-bar-chart-alt-2 me-2"></i>
                  View Reports
                </a>
              </div>
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

.btn {
  border-radius: 0.375rem;
}

.btn i {
  font-size: 1.1em;
}
</style>
