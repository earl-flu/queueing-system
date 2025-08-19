<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { Link } from "@inertiajs/vue3";
import $ from "jquery";
import "metismenu";

const props = defineProps({
  isToggled: {
    type: Boolean,
  },
  isSidebarHovered: {
    type: Boolean,
  },
});

const emit = defineEmits(["update:isSidebarHovered", "close-sidebar"]);

const sidenav = ref(null);

const onSidebarHover = () => {
  if (props.isToggled) {
    emit("update:isSidebarHovered", true);
    document.body.classList.add("sidebar-hovered");
  }
};

//ACTIVE MENU
function isActive(route) {
  if (route) return "mm-active";
  return "";
}

const onSidebarLeave = () => {
  if (props.isToggled) {
    emit("update:isSidebarHovered", false);
    document.body.classList.remove("sidebar-hovered");
  }
};

onMounted(() => {
  $(sidenav.value).metisMenu();
});

onBeforeUnmount(() => {
  $(sidenav.value).metisMenu("dispose");
});
</script>


<template>
  <aside
    class="sidebar-wrapper"
    :class="{ 'sidebar-hovered': isSidebarHovered }"
    @mouseover="onSidebarHover"
    @mouseleave="onSidebarLeave"
    data-simplebar="true"
  >
    <div class="sidebar-header">
      <div class="logo-icon">
        <img src="assets/images/logo-icon.png" class="logo-img" alt="" />
      </div>
      <div class="logo-name flex-grow-1">
        <h5 class="mb-0">Norben</h5>
      </div>
      <div @click="$emit('close-sidebar')" class="sidebar-close">
        <span class="material-icons-outlined">close</span>
      </div>
    </div>
    <div class="sidebar-nav">
      <!-- Navigation content here -->
      <!--navigation-->
      <ul class="metismenu" id="sidenav" ref="sidenav">
        <li>
          <a href="javascript:;" class="has-arrow">
            <div class="parent-icon">
              <i class="material-icons-outlined">home</i>
            </div>
            <div class="menu-title">Dashboard</div>
          </a>
          <ul>
            <!-- <li :class="isActive(route().current('papers.dashboard'))">
              <Link :href="route('papers.dashboard')"
                ><i class="material-icons-outlined">arrow_right</i
                >Papers</Link
              >
            </li> -->
          </ul>
        </li>
        <li>
          <a href="javascript:;" class="has-arrow">
            <div class="parent-icon">
              <i class="material-icons-outlined">list_alt</i>
            </div>
            <div class="menu-title">Queues</div>
          </a>

          <ul>
            <li :class="isActive(route().current('queue.index'))">
              <Link :href="route('queue.index')"
                ><i class="material-icons-outlined">arrow_right</i>All</Link
              >
            </li>
            <li
              v-if="
                $page.props.auth.user.is_admin ||
                $page.props.auth.user.is_reception
              "
              :class="isActive(route().current('queue.create'))"
            >
              <Link :href="route('queue.create')"
                ><i class="material-icons-outlined">arrow_right</i>Create</Link
              >
            </li>
          </ul>
        </li>

        <li class="menu-label">Library</li>

        <li>
          <a href="javascript:;" class="has-arrow">
            <div class="parent-icon">
              <i class="material-icons-outlined">inventory_2</i>
            </div>
            <div class="menu-title">Departments</div>
          </a>
          <ul>
            <li
              :class="
                isActive(
                  route().current('departments.index') ||
                    route().current('departments.edit')
                )
              "
            >
              <Link :href="route('departments.index')"
                ><i class="material-icons-outlined">arrow_right</i>All</Link
              >
            </li>
            <li :class="isActive(route().current('departments.create'))">
              <Link :href="route('departments.create')"
                ><i class="material-icons-outlined">arrow_right</i>Add
                Department</Link
              >
            </li>
            <li
              :class="
                isActive(
                  route().current('department-flows.index') ||
                    route().current('department-flows.edit')
                )
              "
            >
              <Link :href="route('department-flows.index')"
                ><i class="material-icons-outlined">arrow_right</i>Department
                Flows</Link
              >
            </li>
          </ul>
        </li>

        <li>
          <a href="javascript:;" class="has-arrow">
            <div class="parent-icon">
              <i class="material-icons-outlined">inventory_2</i>
            </div>
            <div class="menu-title">Screens</div>
          </a>
          <ul>
            <li :class="isActive(route().current('windows.index'))">
              <Link :href="route('windows.index')"
                ><i class="material-icons-outlined">arrow_right</i>All</Link
              >
            </li>
            <!-- <li
              :class="
                isActive(
                  route().current('tags.index') || route().current('tags.edit')
                )
              "
            >
              <Link :href="route('tags.index')"
                ><i class="material-icons-outlined">arrow_right</i>Tags</Link
              >
            </li> -->
          </ul>
        </li>
      </ul>
      <!--end navigation-->
    </div>
  </aside>
</template>

