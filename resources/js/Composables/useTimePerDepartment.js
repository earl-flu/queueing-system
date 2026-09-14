import { ref, onMounted } from "vue";
import axios from "axios";

export function useTimePerDepartment(queueNumber) {
    const timePerDepartmentArr = ref([]);

    const fetchTimePerDepartment = async () => {
        const response = await axios.get("/api/patient/time-per-department", {
            params: {
                queueNumber: queueNumber,
            },
        });
        const data = response.data;
        timePerDepartmentArr.value = data;
    };

    onMounted(() => {
        fetchTimePerDepartment();
    });

    return timePerDepartmentArr;
}
