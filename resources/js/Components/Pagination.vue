<script setup>
import { Link } from "@inertiajs/vue3";

const props = defineProps({
  links: {
    type: Array,
    required: true,
  },
});
</script>
<template>
  <nav aria-label="Page navigation">
    <ul class="pagination mt-5">
      <Link
        class="page-item"
        v-for="link in props.links"
        :key="link.label"
        :href="link.url ? link.url : ''"
        :class="{ 'active': link.active, 'opacity-25 pe-none': !link.url }"
      >
        <a class="page-link" href="javascript:;" v-html="link.label"></a>
      </Link>
    </ul>
  </nav>
</template>