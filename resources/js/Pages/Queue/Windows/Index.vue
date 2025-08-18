<template>
  <Head title="Windows" />
  <AuthenticatedLayout>
    <h3 class="mb-3">Windows</h3>
    <div class="row g-3">
      <div v-for="w in windows" :key="w.id" class="col-12 col-md-6 col-xl-4">
        <div class="card h-100">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title">{{ w.name }}</h5>
            <p class="text-muted small">
              Departments:
              {{ w.departments.map((d) => d.name).join(", ") || "None" }}
            </p>
            <div class="mt-auto d-flex gap-2">
              <Link :href="route('windows.show', w.id)" class="btn btn-primary"
                >Open</Link
              >
              <Link
                :href="route('windows.edit', w.id)"
                class="btn btn-outline-secondary"
                >Configure</Link
              >
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
  windows: Array,
});
</script>


