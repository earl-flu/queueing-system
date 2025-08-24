import "./bootstrap";
import "../css/app.css";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";

import PerfectScrollbarDirective from "./directives/v-perfect-scrollbar"; // Adjust the path as needed
import Toast, { POSITION } from "vue-toastification";
import "vue-toastification/dist/index.css";
import VueApexCharts from "vue3-apexcharts";

// Import Bootstrap bundle (CSS + JS)
import "bootstrap/dist/css/bootstrap.min.css";
import "bootstrap/dist/js/bootstrap.bundle.min.js";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(Toast, { position: POSITION.TOP_RIGHT })
            .use(VueApexCharts)
            .use(ZiggyVue)
            .directive("perfect-scrollbar", PerfectScrollbarDirective) //added from theme template
            .mount(el);
    },
    progress: {
        color: "#4B5563",
    },
});
