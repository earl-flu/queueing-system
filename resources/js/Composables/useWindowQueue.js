import { ref, computed } from "vue";

export function useWindowQueue(props) {
  const dataByDepartment = ref(props.queuesByDepartment);
  const lastUpdatedAt = ref(new Date());

  const lastUpdatedDisplay = computed(() =>
    lastUpdatedAt.value.toLocaleTimeString()
  );

  const getItems = (departmentId) =>
    dataByDepartment.value[departmentId] || [];

  const getNowServing = (departmentId) => {
    const list = getItems(departmentId).filter((i) => i.status === "serving");
    return list.length ? list.slice(0, 5) : null;
  };

  const getUpNext = (departmentId) => {
    return getItems(departmentId)
      .filter((i) => i.status === "waiting")
      .sort((a, b) => a.queue_position - b.queue_position)
      .slice(0, 20);
  };

  const getPriorityUpNext = (departmentId) =>
    getUpNext(departmentId).filter((q) => q.patient.is_priority);

  const getRegularUpNext = (departmentId) =>
    getUpNext(departmentId).filter((q) => !q.patient.is_priority);

  const getSkipped = (departmentId) => {
    return getItems(departmentId)
      .filter((i) => i.status === "skipped")
      .sort(
        (a, b) =>
          new Date(b.skipped_at || 0).getTime() -
          new Date(a.skipped_at || 0).getTime()
      )
      .slice(0, 20);
  };

  const fetchData = async () => {
    try {
      const response = await fetch(route("windows.data", props.window.id));
      if (!response.ok) return;
      const json = await response.json();
      const map = {};
      json.forEach((entry) => {
        map[entry.department.id] = entry.items;
      });
      dataByDepartment.value = map;
      lastUpdatedAt.value = new Date();
    } catch (e) {
      // ignore
    }
  };

  return {
    dataByDepartment,
    lastUpdatedDisplay,
    getNowServing,
    getUpNext,
    getPriorityUpNext,
    getRegularUpNext,
    getSkipped,
    fetchData,
  };
}
