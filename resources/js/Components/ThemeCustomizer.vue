<template>
  <div>
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
            <ThemeOption
              v-for="theme in themes"
              :key="theme.value"
              :theme="theme"
              :currentTheme="currentTheme"
              @change-theme="$emit('change-theme', theme.value)"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import ThemeOption from "./ThemeOption.vue";

const themes = ref([
  { value: "blue-theme", label: "Blue", icon: "contactless" },
  { value: "light", label: "Light", icon: "light_mode" },
  { value: "dark", label: "Dark", icon: "dark_mode" },
  { value: "semi-dark", label: "Semi Dark", icon: "contrast" },
  { value: "bordered-theme", label: "Bordered", icon: "border_style" },
]);

const currentTheme = ref(
  document.documentElement.getAttribute("data-bs-theme")
);
</script>
