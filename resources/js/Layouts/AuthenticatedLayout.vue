<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import $ from "jquery";
import "metismenu/dist/metisMenu.css";
import "metismenu";

import Header from "@/Components/Header.vue";
import Sidebar from "@/Components/Sidebar.vue";
import Footer from "@/Components/Footer.vue";
import ThemeCustomizer from "@/Components/ThemeCustomizer.vue";

const $page = usePage();
const user = computed(() => $page.props.auth.user);

const isToggled = ref(JSON.parse(localStorage.getItem("isToggled")) || false);
const isSidebarHovered = ref(false);
const isSticky = ref(false);
const currentTheme = ref(
  document.documentElement.getAttribute("data-bs-theme")
);

const updateIsSidebarHovered = (value) => {
  isSidebarHovered.value = value;
};

const toggleSidebar = () => {
  isToggled.value = !isToggled.value;
  document.body.classList.toggle("toggled", isToggled.value);
  localStorage.setItem("isToggled", JSON.stringify(isToggled.value));
};

const closeSidebar = () => {
  isToggled.value = false;
  document.body.classList.remove("toggled");
  localStorage.setItem("isToggled", JSON.stringify(isToggled.value));
};

const handleScroll = () => {
  isSticky.value = window.scrollY > 60;
};

onMounted(() => {
  window.addEventListener("scroll", handleScroll);
  document.body.classList.toggle("toggled", isToggled.value);
});

onBeforeUnmount(() => {
  window.removeEventListener("scroll", handleScroll);
});

const changeTheme = (theme) => {
  document.documentElement.setAttribute("data-bs-theme", theme);
  currentTheme.value = theme;
  saveThemeToServer(theme);
};

const saveThemeToServer = (theme) => {
  router.post(route("theme.update"), { theme });
};
</script>

<template>
  <div>
    <Header :user="user" :isSticky="isSticky" @toggle-sidebar="toggleSidebar" />
    <Sidebar
      @update:isSidebarHovered="updateIsSidebarHovered"
      :isToggled="isToggled"
      :isSidebarHovered="isSidebarHovered"
      @close-sidebar="closeSidebar"
    />
    <main class="main-wrapper">
      <div class="main-content">
        <slot />
      </div>
    </main>
    <div class="overlay btn-toggle" @click="toggleSidebar"></div>
    <Footer />
    <ThemeCustomizer :currentTheme="currentTheme" @change-theme="changeTheme" />
  </div>
</template>


