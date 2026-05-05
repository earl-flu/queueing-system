export function useFormatting() {
    const formatTime = (seconds) => {
        if (seconds === null || seconds === undefined || Number.isNaN(seconds))
            return "—";
        const s = Number(seconds);
        if (s < 0) return "—";
        const h = Math.floor(s / 3600);
        const m = Math.floor((s % 3600) / 60);
        const sec = Math.round(s % 60);
        if (h > 0)
            return `${h}:${String(m).padStart(2, "0")}:${String(sec).padStart(2, "0")}`;
        return `${m}:${String(sec).padStart(2, "0")}`;
    };

    const getStatusColor = (status) => {
        const map = {
            waiting: "text-warning",
            serving: "text-primary",
            done: "text-success",
            transferred: "text-info",
            skipped: "text-secondary",
            no_show: "text-danger",
        };
        return map[status] ?? "text-muted";
    };

    return { formatTime, getStatusColor };
}
