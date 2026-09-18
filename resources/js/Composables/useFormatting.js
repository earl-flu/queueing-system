export function useFormatting() {
    const formatTime = (seconds) => {
        if (seconds === null || seconds === undefined || Number.isNaN(seconds))
            return "—";
        const total = Math.round(Number(seconds));
        if (total < 0) return "—";
        const h = Math.floor(total / 3600);
        const m = Math.floor((total % 3600) / 60);
        const sec = total % 60;

        if (h > 0) {
            let result = `${h} hr${h > 1 ? "s" : ""}`;
            if (m > 0) result += ` ${m} mins`;
            return result;
        } else if (m > 0) {
            let result = `${m} min`;
            if (m > 1) result += "s";
            // if (sec > 0) result += ` ${sec} sec${sec > 1 ? "s" : ""}`;
            return result;
        } else {
            return `${sec} sec${sec !== 1 ? "s" : ""}`;
        }
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
