<template>
  <Head title="Department Flows" />

  <AuthenticatedLayout>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="mb-0">Department Flows</h4>
        <p class="text-muted mb-0">
          Group by final department and expand to view steps
        </p>
      </div>
      <div v-if="$page.props.auth?.user?.is_admin">
        <Link
          :href="route('department-flows.create')"
          class="btn btn-grd btn-grd-primary"
        >
          Create Flow Step
        </Link>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Final Department</th>
                <th>Step Department</th>
                <th>Order</th>
                <th>Required</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="grouped.length === 0">
                <td colspan="5" class="text-center text-muted py-4">
                  No flows defined
                </td>
              </tr>

              <template v-for="group in grouped" :key="group.id">
                <tr
                  class="table-light"
                  style="cursor: pointer"
                  @click="toggle(group.id)"
                >
                  <td colspan="5">
                    <span
                      class="me-2 material-icons-outlined"
                      aria-hidden="true"
                    >
                      {{ isExpanded(group.id) ? "expand_less" : "expand_more" }}
                    </span>
                    <span class="fw-semibold">{{ group.name }}</span>
                    <span class="badge bg-light text-dark ms-2"
                      >{{ group.flows.length }} steps</span
                    >
                  </td>
                </tr>
                <tr
                  v-for="flow in group.flows"
                  :key="flow.id"
                  v-show="isExpanded(group.id)"
                >
                  <td></td>
                  <td class="ps-4">{{ flow.step_department?.name }}</td>
                  <td>
                    <span class="badge bg-light text-dark">{{
                      flow.step_order
                    }}</span>
                  </td>
                  <td>
                    <span
                      :class="
                        flow.is_required
                          ? 'badge bg-success'
                          : 'badge bg-secondary'
                      "
                    >
                      {{ flow.is_required ? "Yes" : "No" }}
                    </span>
                  </td>
                  <td class="text-end">
                    <div class="btn-group btn-group-sm" role="group">
                      <Link
                        v-if="$page.props.auth?.user?.is_admin"
                        :href="route('department-flows.edit', flow.id)"
                        class="btn btn-outline-secondary"
                        >Edit</Link
                      >
                      <button
                        v-if="$page.props.auth?.user?.is_admin"
                        class="btn btn-outline-danger"
                        @click.stop="confirmDelete(flow)"
                      >
                        Delete
                      </button>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const props = defineProps({
  flows: Array,
});

const flows = props.flows || [];

const expanded = ref(new Set());
const toggle = (id) => {
  const next = new Set(expanded.value);
  if (next.has(id)) next.delete(id);
  else next.add(id);
  expanded.value = next;
};
const isExpanded = (id) => expanded.value.has(id);

const grouped = computed(() => {
  const map = new Map();
  for (const f of flows) {
    const dept = f.final_department || {
      id: f.final_department_id,
      name: "Unknown",
    };
    if (!map.has(dept.id))
      map.set(dept.id, { id: dept.id, name: dept.name, flows: [] });
    map.get(dept.id).flows.push(f);
  }
  // Ensure consistent order by step_order per group
  const arr = Array.from(map.values());
  for (const g of arr)
    g.flows.sort((a, b) => (a.step_order || 0) - (b.step_order || 0));
  // Sort groups alphabetically
  arr.sort((a, b) => a.name.localeCompare(b.name));
  return arr;
});

const destroyForm = useForm({});
const confirmDelete = (flow) => {
  if (
    !confirm(
      `Delete flow step ${flow.step_order} for \"${flow.final_department?.name}\"?`
    )
  )
    return;
  destroyForm.delete(route("department-flows.destroy", flow.id));
};
</script>


