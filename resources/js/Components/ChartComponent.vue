<!-- resources/js/Components/ChartComponent.vue -->
<script setup>
import VueApexCharts from "vue3-apexcharts";
import { ref } from "vue";

const chartOptions = ref({
  chart: {
    height: 180,
    type: "radialBar",
    toolbar: {
      show: false,
    },
  },
  plotOptions: {
    radialBar: {
      startAngle: -115,
      endAngle: 115,
      hollow: {
        margin: 0,
        size: "80%",
        background: "transparent",
        image: undefined,
        imageOffsetX: 0,
        imageOffsetY: 0,
        position: "front",
        dropShadow: {
          enabled: false,
          top: 3,
          left: 0,
          blur: 4,
          opacity: 0.24,
        },
      },
      track: {
        background: "rgba(0, 0, 0, 0.1)",
        strokeWidth: "67%",
        margin: 0, // margin is in pixels
        dropShadow: {
          enabled: false,
          top: -3,
          left: 0,
          blur: 4,
          opacity: 0.35,
        },
      },
      dataLabels: {
        show: true,
        name: {
          offsetY: -10,
          show: false,
          color: "#888",
          fontSize: "17px",
        },
        value: {
          offsetY: 10,
          color: "#111",
          fontSize: "24px",
          show: true,
        },
      },
    },
  },
  fill: {
    type: "gradient",
    gradient: {
      shade: "dark",
      type: "horizontal",
      shadeIntensity: 0.5,
      gradientToColors: ["#ffd200"],
      inverseColors: true,
      opacityFrom: 1,
      opacityTo: 1,
      stops: [0, 100],
    },
  },
  colors: ["#ee0979"],
  stroke: {
    lineCap: "round",
  },
  labels: ["Total Orders"],
});

const series = ref([78]);
</script>


<template>
  <div>
    <VueApexCharts :options="chartOptions" :series="series"></VueApexCharts>
  </div>
</template>

<style>
/* Add any necessary styles */
</style>