import { ref, onMounted, onUnmounted } from "vue";

export function useElapsedTime(startTime) {
    const elapsed = ref("0s");
    let interval = null;

    const updateElapsed = () => {
        if (!startTime) return;

        const start = new Date(startTime); // parses ISO string fine
        const now = new Date();

        let diff = Math.floor((now.getTime() - start.getTime()) / 1000);

        if (diff < 0) diff = 0; // avoid negative if UTC > local time

        const hours = Math.floor(diff / 3600);
        const minutes = Math.floor((diff % 3600) / 60);
        const seconds = diff % 60;

        if (hours > 0) {
            elapsed.value = `${hours.toString().padStart(2, "0")}:${minutes
                .toString()
                .padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`;
        } else if (minutes > 0) {
            elapsed.value = `${minutes}min:${seconds.toString().padStart(2, "0")}s`;
        } else {
            elapsed.value = `${seconds}s`;
        }
    };

    onMounted(() => {
        updateElapsed();
        interval = setInterval(updateElapsed, 1000);
    });

    onUnmounted(() => {
        clearInterval(interval);
    });

    return elapsed;
}
