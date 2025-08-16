<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import ChartComponent from "@/Components/ChartComponent.vue";
import { ref, onMounted, onBeforeUnmount } from "vue";
import $ from "jquery";
import "metismenu/dist/metisMenu.css";
import "metismenu";

const $page = usePage();

// SIDEBAR
const isToggled = ref(false);
const isSidebarHovered = ref(false);

const toggleSidebar = () => {
  isToggled.value = !isToggled.value;
  document.body.classList.toggle("toggled", isToggled.value);
};

const onSidebarHover = () => {
  if (isToggled.value) {
    isSidebarHovered.value = true;
    document.body.classList.add("sidebar-hovered");
  }
};

const onSidebarLeave = () => {
  if (isToggled.value) {
    isSidebarHovered.value = false;
    document.body.classList.remove("sidebar-hovered");
  }
};

const closeSidebar = () => {
  isToggled.value = false;
  document.body.classList.remove("toggled");
};

// METISMENU
const sidenav = ref(null);

onMounted(() => {
  $(sidenav.value).metisMenu();
});

onBeforeUnmount(() => {
  $(sidenav.value).metisMenu("dispose");
});

// NAV BAR STICKY
const isSticky = ref(false);

const handleScroll = () => {
  isSticky.value = window.scrollY > 60;
};

onMounted(() => {
  window.addEventListener("scroll", handleScroll);
});

onBeforeUnmount(() => {
  window.removeEventListener("scroll", handleScroll);
});

// SEARCH
const isSearchOpen = ref(false);
const openSearch = () => {
  console.log("open");
  isSearchOpen.value = true;
};

const closeSearch = () => {
  isSearchOpen.value = false;
};

const openMobileSearch = () => {
  isSearchOpen.value = true;
};

const closeMobileSearch = () => {
  isSearchOpen.value = false;
};

// THEME
const currentTheme = ref(
  document.documentElement.getAttribute("data-bs-theme")
);
const changeTheme = (theme) => {
  document.documentElement.setAttribute("data-bs-theme", theme);
  currentTheme.value = theme;
  saveThemeToServer(theme);
};

const saveThemeToServer = (theme) => {
  router.post(route("theme.update"), { theme });
};

//ACTIVE MENU
function isActive(route) {
  if (route) return "mm-active";
  return "";
}
</script>

<template>
  <!--start header-->
  <header class="top-header">
    <nav
      class="navbar navbar-expand align-items-center gap-4"
      :class="{ 'sticky-header': isSticky }"
    >
      <div class="btn-toggle" @click="toggleSidebar">
        <a href="javascript:;"><i class="material-icons-outlined">menu</i></a>
      </div>
      <div class="search-bar flex-grow-1">
     
      </div>
      <ul class="navbar-nav gap-1 nav-right-links align-items-center">
    
        <!-- ACCOUNT -->
        <li class="nav-item dropdown">
          <a
            href="javascrpt:;"
            class="dropdown-toggle dropdown-toggle-nocaret"
            data-bs-toggle="dropdown"
          >
            <img
              src="assets/images/avatars/01.png"
              class="rounded-circle p-1 border"
              width="45"
              height="45"
              alt=""
            />
          </a>
          <div class="dropdown-menu dropdown-user dropdown-menu-end shadow">
            <a class="dropdown-item gap-2 py-2" href="javascript:;">
              <div class="text-center">
                <img
                  src="assets/images/avatars/01.png"
                  class="rounded-circle p-1 shadow mb-3"
                  width="90"
                  height="90"
                  alt=""
                />
                <h5 class="user-name mb-0 fw-bold">Hello, {{ $page.props.auth.user.name }}</h5>
              </div>
            </a>
            <hr class="dropdown-divider" />
            <a
              class="dropdown-item d-flex align-items-center gap-2 py-2"
              href="javascript:;"
              ><i class="material-icons-outlined">person_outline</i>Profile</a
            >
            <a
              class="dropdown-item d-flex align-items-center gap-2 py-2"
              href="javascript:;"
              ><i class="material-icons-outlined">local_bar</i>Setting</a
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
  <!--end top header-->

  <!--start sidebar-->
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
        <h5 class="mb-0">Maxton</h5>
      </div>
      <div @click="closeSidebar" class="sidebar-close">
        <span class="material-icons-outlined">close</span>
      </div>
    </div>
    <div class="sidebar-nav">
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
            <li :class="isActive(route().current('dashboard'))">
              <Link :href="route('dashboard')"
                ><i class="material-icons-outlined">arrow_right</i
                >Analysisss</Link
              >
            </li>
            <li>
              <a href="index2.html"
                ><i class="material-icons-outlined">arrow_right</i>eCommerce</a
              >
            </li>
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
            <li :class="isActive(route().current('papers.index'))">
              <Link :href="route('papers.index')"
                ><i class="material-icons-outlined">arrow_right</i>All</Link
              >
            </li>
            <li :class="isActive(route().current('papers.create'))">
              <Link :href="route('papers.create')"
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
            <div class="menu-title">Office</div>
          </a>
          <ul>
            <li :class="isActive(route().current('offices.create'))">
              <Link :href="route('offices.create')"
                ><i class="material-icons-outlined">arrow_right</i>Add
                Office</Link
              >
            </li>
            <li :class="isActive(route().current('offices.index'))">
              <Link :href="route('offices.index')"
                ><i class="material-icons-outlined">arrow_right</i>Offices</Link
              >
            </li>
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
            <li :class="isActive(route().current('tags.create'))">
              <Link :href="route('tags.create')"
                ><i class="material-icons-outlined">arrow_right</i>Add Tag</Link
              >
            </li>
            <li :class="isActive(route().current('tags.index'))">
              <Link :href="route('tags.index')"
                ><i class="material-icons-outlined">arrow_right</i>Tags</Link
              >
            </li>
          </ul>
        </li>
    
      </ul>
      <!--end navigation-->
    </div>
  </aside>
  <!--end sidebar-->

  <!--start main wrapper-->
  <main class="main-wrapper">
    <div class="main-content">
      <slot />
    </div>
  </main>
  <!--end main wrapper-->

  <!--start overlay-->
  <div class="overlay btn-toggle" @click="toggleSidebar"></div>
  <!--end overlay-->

  <!--start footer-->
  <footer class="page-footer">
    <p class="mb-0">Copyright © 2024. All right reserved. PHO ICT Office</p>
  </footer>
  <!--end footer-->

  <!--start cart-->
  
  <!--end cart-->

  <!--start switcher-->
  <button
    class="btn btn-grd btn-grd-primary position-fixed bottom-0 end-0 m-3 d-flex align-items-center gap-2"
    type="button"
    id="testonly"
    data-bs-toggle="offcanvas"
    data-bs-target="#staticBackdrop"
  >
    <i class="material-icons-outlined">tune</i>Customize
  </button>

  <div
    class="offcanvas offcanvas-end"
    data-bs-scroll="true"
    tabindex="-1"
    id="staticBackdrop"
  >
    <div class="offcanvas-header border-bottom h-70">
      <div class="">
        <h5 class="mb-0">Theme Customizer</h5>
        <p class="mb-0">Customize your theme</p>
      </div>
      <a
        href="javascript:;"
        class="primaery-menu-close"
        data-bs-dismiss="offcanvas"
      >
        <i class="material-icons-outlined">close</i>
      </a>
    </div>
    <div class="offcanvas-body">
      <div>
        <p>Theme variation</p>

        <div class="row g-3">
          <div class="col-12 col-xl-6">
            <input
              type="radio"
              class="btn-check"
              name="theme-options"
              id="BlueTheme"
              @click="changeTheme('blue-theme')"
              :checked="currentTheme === 'blue-theme'"
            />
            <label
              class="btn btn-outline-secondary d-flex flex-column gap-1 align-items-center justify-content-center p-4"
              for="BlueTheme"
            >
              <span class="material-icons-outlined">contactless</span>
              <span>Blue</span>
            </label>
          </div>
          <div class="col-12 col-xl-6">
            <input
              type="radio"
              class="btn-check"
              name="theme-options"
              id="LightTheme"
              @click="changeTheme('light')"
              :checked="currentTheme === 'light'"
            />
            <label
              class="btn btn-outline-secondary d-flex flex-column gap-1 align-items-center justify-content-center p-4"
              for="LightTheme"
            >
              <span class="material-icons-outlined">light_mode</span>
              <span>Light</span>
            </label>
          </div>
          <div class="col-12 col-xl-6">
            <input
              type="radio"
              class="btn-check"
              name="theme-options"
              id="DarkTheme"
              @click="changeTheme('dark')"
              :checked="currentTheme === 'dark'"
            />
            <label
              class="btn btn-outline-secondary d-flex flex-column gap-1 align-items-center justify-content-center p-4"
              for="DarkTheme"
            >
              <span class="material-icons-outlined">dark_mode</span>
              <span>Dark</span>
            </label>
          </div>
          <div class="col-12 col-xl-6">
            <input
              type="radio"
              class="btn-check"
              name="theme-options"
              id="SemiDarkTheme"
              @click="changeTheme('semi-dark')"
              :checked="currentTheme === 'semi-dark'"
            />
            <label
              class="btn btn-outline-secondary d-flex flex-column gap-1 align-items-center justify-content-center p-4"
              for="SemiDarkTheme"
            >
              <span class="material-icons-outlined">contrast</span>
              <span>Semi Dark</span>
            </label>
          </div>
          <div class="col-12 col-xl-6">
            <input
              type="radio"
              class="btn-check"
              name="theme-options"
              id="BorderedTheme"
              @click="changeTheme('bordered-theme')"
              :checked="currentTheme === 'bordered-theme'"
            />
            <label
              class="btn btn-outline-secondary d-flex flex-column gap-1 align-items-center justify-content-center p-4"
              for="BorderedTheme"
            >
              <span class="material-icons-outlined">border_style</span>
              <span>Bordered</span>
            </label>
          </div>
        </div>
        <!--end row-->
      </div>
    </div>
  </div>
</template>
