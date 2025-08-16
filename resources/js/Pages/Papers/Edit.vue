<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import { useToast } from "vue-toastification";

const props = defineProps({
  paper: Object,
  tags: Array,
  offices: Array,
});

const form = useForm({
  title: props.paper.title,
  remarks: props.paper.remarks,
  type: props.paper.type,
  tags: props.paper.tags,
  office_id: props.paper.office_id,
});

const submit = () => {
  form.tags = form.tags.map((tag) => tag.id);
  form.put(route("papers.update", props.paper.id), {
    onSuccess: () => {
      const toast = useToast();
      toast.success("Updated Successfully", {
        timeout: 2000,
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
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <!-- sub breadcrumb -->
    <div
      class="product-count d-flex align-items-center gap-3 gap-lg-4 mb-4 fw-bold flex-wrap font-text1"
    >
      <Link :href="route('papers.index')"
        ><span class="me-1">Papers</span></Link
      >
      <div>></div>
      <div><span class="me-1">Edit</span></div>
      <div>></div>
      <div>
        <span class="me-1">{{ paper.title }}</span
        ><span class="text-secondary">({{ paper.id }})</span>
      </div>
    </div>
    <!-- end sub breadcrumb -->

    <div class="row mt-5">
      <div class="col-12 col-xl-6">
        <div class="card">
          <div class="card-body p-4">
            <h5 class="mb-4">Paper Update Form</h5>
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
                <select
                  id="office_id"
                  class="form-select"
                  v-model="form.office_id"
                >
                  <option
                    v-for="office in offices"
                    :key="office.id"
                    :value="office.id"
                  >
                    {{ office.name }}
                  </option>
                </select>
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
                <!-- <select
                  id="tags"
                  class="form-select"
                  v-model="form.tags"
                  multiple
                >
                  <option v-for="tag in tags" :key="tag.id" :value="tag.id">
                    {{ tag.name }}
                  </option>
                </select> -->
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
                <!-- <div v-for="(tag, index) in form.tags" :key="index">
                  <select v-model="form.tags[index]" class="form-select">
                    <option
                      v-for="tagOption in tags"
                      :value="tagOption.id"
                      :key="tagOption.id"
                    >
                      {{ tagOption.name }}
                    </option>
                  </select>
                  <button type="button" @click="removeTag(index)">
                    Remove
                  </button>
                </div> -->
                <!-- <button type="button" @click="addTag">Add Tag</button> -->
                <div class="invalid-feedback d-block">
                  {{ form.errors.tags }}
                </div>
              </div>

              <div class="col-md-12 mt-4">
                <div class="d-md-flex d-grid align-items-center gap-3">
                  <button
                    class="btn btn-grd-primary px-4"
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
