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

        const minutes = Math.floor(diff / 60);
        const seconds = diff % 60;

        elapsed.value =
            minutes > 0
                ? `${minutes}min:${seconds.toString().padStart(2, "0")}s`
                : `${seconds}s`;
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
