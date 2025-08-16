<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import axios from "axios";
// import ApexCharts from "vue3-apexcharts";

// Calculate default start and end dates
const today = new Date().toISOString().split("T")[0]; // Today's date in YYYY-MM-DD format
const oneMonthAgo = new Date();
oneMonthAgo.setMonth(oneMonthAgo.getMonth() - 1);
const startOfLastMonth = oneMonthAgo.toISOString().split("T")[0]; // Date one month ago in YYYY-MM-DD format

const startDate = ref(startOfLastMonth);
const endDate = ref(today);
const series = ref([]);
const officeSeries = ref([]);

const chartOptions = ref({
  chart: {
    id: "papers-chart",
  },
  xaxis: {
    type: "datetime",
    labels: {
      formatter: function (value) {
        const date = new Date(value);
        // Format date to a readable string, e.g., YYYY-MM-DD
        return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(
          2,
          "0"
        )}-${String(date.getDate()).padStart(2, "0")}`;
      },
      style: {
        colors: "#cdcdcd", // Change this to your desired color
      },
    },
  },
  yaxis: {
    labels: {
      formatter: (value) => Math.round(value), // Format y-axis labels as whole numbers
    },
  },
  fill: {
    type: "gradient",
    gradient: {
      shade: "dark",
      gradientToColors: ["#009efd"],
      shadeIntensity: 1,
      type: "vertical",
      opacityFrom: 1,
      opacityTo: 1,
      stops: [0, 100, 100, 100],
    },
  },
  colors: ["#2af598"],
  tooltip: {
    theme: "dark",
    y: {
      formatter: (value) => Math.round(value), // Format tooltip values as whole numbers
    },
  },
});

const officeChartOptions = ref({
  chart: {
    id: "office-papers-chart",
  },
  xaxis: {
    categories: [], // This will be updated dynamically
    labels: {
      style: {
        colors: "#000", // Customize color as needed
      },
    },
  },
  yaxis: {
    title: {
      style: {
        color: "#000", // Customize color as needed
      },
    },
    labels: {
      formatter: (value) => Math.round(value), // Format y-axis labels as whole numbers
    },
  },
  tooltip: {
    theme: "dark",
    y: {
      formatter: (value) => Math.round(value), // Format tooltip values as whole numbers
    },
  },
  fill: {
    type: "gradient",
    gradient: {
      shade: "dark",
      gradientToColors: ["#009efd"],
      shadeIntensity: 1,
      type: "vertical",
      opacityFrom: 1,
      opacityTo: 1,
      stops: [0, 100, 100, 100],
    },
  },
  colors: ["#56CCF2"],
});

const fetchData = async () => {
  const response = await axios.get("/papers/data", {
    params: {
      start_date: startDate.value,
      end_date: endDate.value,
    },
  });
  const data = response.data;

  series.value = [
    {
      name: "Papers",
      data: data.map((item) => ({
        x: new Date(item.date),
        y: item.count,
      })),
    },
  ];
};

const fetchOfficeData = async () => {
  const response = await axios.get("/offices/data", {
    params: {
      start_date: startDate.value,
      end_date: endDate.value,
    },
  });
  const data = response.data;

  officeSeries.value = [
    {
      name: "Papers by Office",
      data: data.map((item) => item.count),
    },
  ];

  officeChartOptions.value.xaxis.categories = data.map((item) => item.name);
  // Trigger reactivity manually
  officeChartOptions.value = { ...officeChartOptions.value };
};

onMounted(() => {
  fetchData();
  fetchOfficeData();
});

const handleChange = () => {
  fetchData();
  fetchOfficeData();
};
</script>

<template>
  <Head title="Paper Dashboard" />

  <AuthenticatedLayout>
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Dashboard</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item">
              <a href="javascript:;"><i class="bx bx-home-alt"></i></a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Papers</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->
    <div class="row">
      <div class="col-xl-6 d-flex align-items-stretch">
        <div class="card w-100 rounded-4">
          <div class="card-body">
            <div class="text-center">
              <h6 class="mb-0">Number of Papers</h6>
            </div>
            <apexchart
              class="mt-4"
              type="line"
              :options="chartOptions"
              :series="series"
            ></apexchart>
          </div>
        </div>
      </div>
      <div class="col-xl-6 d-flex align-items-stretch">
        <div class="card w-100 rounded-4">
          <div class="card-body">
            <div class="text-center">
              <h6 class="mb-0">Number of Papers Sent by Offices</h6>
            </div>
            <apexchart
              class="mt-4"
              type="bar"
              :options="officeChartOptions"
              :series="officeSeries"
            ></apexchart>
          </div>
        </div>
      </div>
    </div>
    <input type="date" v-model="startDate" @change="handleChange" />
    <input type="date" v-model="endDate" @change="handleChange" />
  </AuthenticatedLayout>
</template>

<style>
.apexcharts-text {
  fill: var(--bs-body-color) !important;
  opacity: 0.6 !important;
}
</style>
