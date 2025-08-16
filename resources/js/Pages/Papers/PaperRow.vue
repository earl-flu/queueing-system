<script setup>
import { Link, router } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";

const props = defineProps({
  paper: Object,
});

const toast = useToast();

function deletePaper(paper) {
  router.delete(route("papers.destroy", paper), {
    onSuccess: () => {
      toast.success("Paper deleted successfully.", {
        timeout: 3000,
      });
    },
    onError: () => {
      toast.success("An error occurred while deleting the paper.", {
        timeout: 3000,
      });
    },
  });
}
</script>

<template>
  <tr>
    <td>
      <div class="d-flex align-items-center gap-3">
        <p class="mb-0 fw-bold">{{ paper.title }}</p>
      </div>
    </td>
    <td>{{ paper.office.name }}</td>
    <td>
      <div class="d-flex flex-column align-items-start gap-1">
        <div
          class="badge bg-grd-royal"
          v-for="tag in paper.tags"
          :key="tag.id"
        >
          {{ tag.name }}
        </div>
      </div>
    </td>
    <td>{{ paper.formatted_created_at }}</td>

    <td>
      <div class="d-flex align-items-center gap-1">
        <div class="dropdown">
          <button
            class="btn btn-sm dropdown-toggle dropdown-toggle-nocaret"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
          >
            <i class="bi bi-three-dots"></i>
          </button>
          <ul class="dropdown-menu" style="">
            <!-- <li>
              <a class="dropdown-item" href="javascript:;"
                ><i class="bi bi-eye-fill me-2"></i>View</a
              >
            </li> -->
            <Link :href="route('papers.edit', paper)">
              <a class="dropdown-item" href="javascript:;"
                ><i class="bi bi-pencil-square me-2"></i>Edit</a
              >
            </Link>
            <li class="dropdown-divider"></li>
            <li>
              <button
                type="button"
                class="dropdown-item text-danger"
                data-bs-toggle="modal"
                :data-bs-target="'#BasicModal' + paper.id"
                ><i class="bi bi-trash-fill me-2"></i>Delete</button
              >
            </li>
          </ul>
        </div>
      </div>
    </td>
  </tr>
  <!-- Modal -->
  <div
    class="modal fade"
    :id="'BasicModal' + paper.id"
    aria-hidden="true"
    style="display: none"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-bottom-0 py-2">
          <h5 class="modal-title">Delete Paper</h5>
          <a
            href="javascript:;"
            class="primaery-menu-close"
            data-bs-dismiss="modal"
          >
            <i class="material-icons-outlined">close</i>
          </a>
        </div>
        <div class="modal-body">
          Are you sure you want to delete "{{ paper.title }}"?
        </div>
        <div class="modal-footer border-top-0">
          <button
            @click="deletePaper(paper)"
            class="btn btn-grd-danger"
            data-bs-dismiss="modal"
          >
            Delete
          </button>
          <button
            type="button"
            class="btn btn-grd-royal"
            data-bs-dismiss="modal"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- End of modal -->
</template>