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
              <i class="material-icons-outlined">widgets</i>
            </div>
            <div class="menu-title">Papers</div>
          </a>

          <ul>
            <!-- <li
              :class="
                isActive(
                  route().current('papers.index') ||
                    route().current('papers.edit')
                )
              "
            >
              <Link :href="route('papers.index')"
                ><i class="material-icons-outlined">arrow_right</i>All</Link
              > 
            </li>
            <li :class="isActive(route().current('papers.create'))">
              <Link :href="route('papers.create')"
                ><i class="material-icons-outlined">arrow_right</i>Create</Link
              >
            </li>-->
          </ul>
        </li>

        <li class="menu-label">Library</li>

        <li>
          <a href="javascript:;" class="has-arrow">
            <div class="parent-icon">
              <i class="material-icons-outlined">inventory_2</i>
            </div>
            <div class="menu-title">Office</div>
          </a>
          <ul>
            <!-- <li :class="isActive(route().current('offices.create'))">
              <Link :href="route('offices.create')"
                ><i class="material-icons-outlined">arrow_right</i>Add
                Office</Link
              >
            </li> -->
            <!-- <li
              :class="
                isActive(
                  route().current('offices.index') ||
                    route().current('offices.edit')
                )
              "
            >
              <Link :href="route('offices.index')"
                ><i class="material-icons-outlined">arrow_right</i>Offices</Link
              >
            </li> -->
          </ul>
        </li>

        <li>
          <a href="javascript:;" class="has-arrow">
            <div class="parent-icon">
              <i class="material-icons-outlined">inventory_2</i>
            </div>
            <div class="menu-title">Tags</div>
          </a>
          <ul>
            <!-- <li :class="isActive(route().current('tags.create'))">
              <Link :href="route('tags.create')"
                ><i class="material-icons-outlined">arrow_right</i>Add Tag</Link
              >
            </li> -->
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

