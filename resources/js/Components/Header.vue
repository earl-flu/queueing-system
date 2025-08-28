<script setup>
import { computed } from "vue";
import { usePage, Link } from "@inertiajs/vue3";

const props = defineProps({
  isSticky: {
    type: Boolean,
    required: true,
  },
});

const $page = usePage();
const user = computed(() => $page.props.auth.user);
</script>


<template>
  <header class="top-header">
    <nav
      class="navbar navbar-expand align-items-center gap-4"
      :class="{ 'sticky-header': isSticky }"
    >
      <div class="btn-toggle" @click="$emit('toggle-sidebar')">
        <a href="javascript:;"><i class="material-icons-outlined">menu</i></a>
      </div>
      <div class="search-bar flex-grow-1"></div>
      <ul class="navbar-nav gap-1 nav-right-links align-items-center">
        <li class="nav-item dropdown">
          <a
            href="javascript:;"
            class="dropdown-toggle dropdown-toggle-nocaret"
            data-bs-toggle="dropdown"
          >
            <img
              src="https://images.pexels.com/photos/1022922/pexels-photo-1022922.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1"
              class="rounded-circle p-1 border"
              width="45"
              style="height: 45px !important"
              alt=""
            />
          </a>
          <div class="dropdown-menu dropdown-user dropdown-menu-end shadow">
            <a class="dropdown-item gap-2 py-2" href="javascript:;">
              <div class="text-center d-flex flex-column align-items-center">
                <img
                  src="https://images.pexels.com/photos/1022922/pexels-photo-1022922.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1"
                  class="rounded-circle p-1 shadow mb-3"
                  width="90"
                  style="height: 90px !important"
                  alt=""
                />
                <h5 class="user-name mb-0 fw-bold">Hello, {{ user.name }}</h5>
              </div>
            </a>
            <hr class="dropdown-divider" />
            <!-- <a
              class="dropdown-item d-flex align-items-center gap-2 py-2"
              href="javascript:;"
              ><i class="material-icons-outlined">local_bar</i>Setting</a
            > -->
            <Link
              :href="route('profile.edit')"
              class="dropdown-item d-flex align-items-center gap-2 py-2"
              ><i class="material-icons-outlined">person_outline</i
              >Profile</Link
            >
            <hr class="dropdown-divider" />
            <Link
              :href="route('logout')"
              method="post"
              as="button"
              class="dropdown-item d-flex align-items-center gap-2 py-2"
              ><i class="material-icons-outlined">power_settings_new</i
              >Logout</Link
            >
          </div>
        </li>
      </ul>
    </nav>
  </header>
</template>

