<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import Multiselect from "vue-multiselect";
import VueSelect from "vue-select";
import "vue-multiselect/dist/vue-multiselect.css";
import "vue-select/dist/vue-select.css";
import { onMounted, ref } from "vue";
import { useToast } from "vue-toastification";

const props = defineProps({
  tags: Array,
  offices: Array,
});

const officeOptions = ref([]);

onMounted(() => {
  officeOptions.value = props.offices.map((ofc) => ({
    value: ofc.id,
    label: ofc.name,
  }));
});

const form = useForm({
  title: "",
  remarks: "",
  type: "incoming",
  office_id: null,
  tags: [],
});

const submit = () => {
  if (form.office_id && typeof form.office_id === "object") {
    form.office_id = form.office_id.value;
  }
  form.tags = form.tags.map((tag) => tag.id);
  form.post(route("papers.store"), {
    onSuccess: () => {
      const toast = useToast();
      toast.success("Saved Successfully", {
        timeout: 3000,
      });
    },
  });
};
</script>

<template>
  <Head title="Create Paper" />

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
            <li class="breadcrumb-item active" aria-current="page">Create</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
      <div class="col-12 col-xl-6">
        <div class="card">
          <div class="card-body p-4">
            <h5 class="mb-4">Paper Form</h5>
            <form class="row g-3" @submit.prevent="submit">
              <div class="col-md-12">
                <label for="title" class="form-label">Title</label>
                <input
                  type="text"
                  class="form-control"
                  id="title"
                  placeholder="Title"
                  v-model="form.title"
                />
                <div class="invalid-feedback d-block">
                  {{ form.errors.title }}
                </div>
              </div>
              <div class="col-md-12">
                <label for="remarks" class="form-label">Remarks</label>
                <textarea
                  class="form-control"
                  id="remarks"
                  placeholder="Remarks ..."
                  rows="3"
                  v-model="form.remarks"
                ></textarea>
                <div class="invalid-feedback d-block">
                  {{ form.errors.remarks }}
                </div>
              </div>
              <div class="col-md-12">
                <label for="office_id" class="form-label">Office</label>
                <vue-select
                  v-model="form.office_id"
                  :options="officeOptions"
                  class="custom-vue-select"
                ></vue-select>
                <div class="invalid-feedback d-block">
                  {{ form.errors.office_id }}
                </div>
              </div>

              <div class="col-md-12">
                <label for="type" class="form-label">Type</label>
                <select id="type" class="form-select" v-model="form.type">
                  <option value="incoming">Incoming</option>
                  <option value="outgoing">Outgoing</option>
                </select>
                <div class="invalid-feedback d-block">
                  {{ form.errors.type }}
                </div>
              </div>
              <div class="col-md-12">
                <label for="tags" class="form-label">Tags</label>
                <multiselect
                  v-model="form.tags"
                  :options="tags"
                  :multiple="true"
                  :close-on-select="false"
                  placeholder="Select tags"
                  label="name"
                  track-by="id"
                  :preselect-first="false"
                />
                <div class="invalid-feedback d-block">
                  {{ form.errors.tags }}
                </div>
              </div>

              <div class="col-md-12 mt-4">
                <div class="d-md-flex d-grid align-items-center gap-3">
                  <button
                    class="btn btn-grd btn-grd-primary px-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                  >
                    Save
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
</style>