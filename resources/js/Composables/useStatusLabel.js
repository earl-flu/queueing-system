export function useStatusLabel() {
    const getStatusLabel = (status) => {
        const map = {
            waiting: "WAITING",
            serving: "SERVING",
            done: "DONE",
            transferred: "TRANSFERRED",
            no_show: "NO SHOW",
        };
        return map[status] || status.toUpperCase();
    };

    return { getStatusLabel };
}
