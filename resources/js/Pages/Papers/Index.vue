<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import PaperRow from "./PaperRow.vue";
import { Head, router, Link } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { debounce } from "lodash";
import VueSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";

const props = defineProps({
  papers: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
  },
  offices: {
    type: Array,
    required: true,
  },
  tags: {
    type: Array,
    required: true,
  },
  // flash: {
  //   required: false,
  // },
});

let title = ref(props.filters.title);
let office = ref(props.filters.office);
let date = ref(props.filters.date);
let selectedTags = ref(props.filters.selectedTags);

const debouncedFetch = debounce((title, office, date, sTags) => {
  router.get(
    route("papers.index"),
    { title: title, office: office, date: date, selectedTags: sTags },
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
}, 300);

watch([title, office, date, selectedTags], (values) => {
  const [title, office, date, sTags] = values;
  debouncedFetch(title, office, date, sTags);
});
</script>

<template>
  <Head title="All Papers" />

  <AuthenticatedLayout>
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
      <div class="breadcrumb-title pe-3">Papers</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item">
              <a href="javascript:;"><i class="bx bx-home-alt"></i></a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">All</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
      <div class="d-flex align-items-stretch">
        <div class="card w-100 rounded-4">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between mb-4">
              <div class="">
                <h5 class="mb-0">Recent Papers</h5>
              </div>
            </div>

            <!-- Search inputs -->
            <form class="row g-3 mb-5">
              <div class="col-md-4">
                <label for="title" class="form-label">Title</label>
                <input
                  type="text"
                  class="form-control"
                  id="title"
                  placeholder="Title"
                  v-model="title"
                />
              </div>
              <div class="col-md-4">
                <label for="office" class="form-label">Office</label>
                <vue-select
                  v-model="office"
                  :options="offices"
                  class="custom-vue-select"
                ></vue-select>
              </div>
              <div class="col-md-4">
                <label for="date" class="form-label">Date Recorded</label>
                <input
                  type="date"
                  class="form-control"
                  id="date"
                  placeholder="Date"
                  v-model="date"
                />
              </div>
              <div class="col-md-4">
                <label for="date" class="form-label">Tag</label>
                <multiselect
                  v-model="selectedTags"
                  :options="tags"
                  :multiple="true"
                  :close-on-select="false"
                  placeholder="Select Tag(s)"
                  :preselect-first="false"
                />
              </div>
            </form>
            <!-- End of search inputs -->

            <!-- Add paper -->
            <div class="d-flex align-items-start justify-content-between mb-4">
              <div></div>
              <Link class="btn btn-primary px-4" :href="route('papers.create')">
                <i class="bi bi-plus-lg me-2"></i>Add Paper
              </Link>
            </div>
            <!-- End of add paper -->

            <!-- Table -->
            <div class="table-responsive">
              <table class="table align-middle table-hover">
                <thead class="table-dark">
                  <tr>
                    <th>Title</th>
                    <th>Office</th>
                    <th>Tags</th>
                    <th>Date Recorded</th>
                    <th>Options</th>
                  </tr>
                </thead>
                <tbody>
                  <PaperRow
                    v-for="paper in papers.data"
                    :key="paper.id"
                    :paper="paper"
                  />
                </tbody>
              </table>
              <Pagination :links="papers.links" />
            </div>
          </div>
          <!-- End of table -->
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
