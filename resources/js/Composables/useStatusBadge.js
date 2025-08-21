export function useStatusBadge() {
    const getStatusBadgeClass = (status) => {
        const classes = {
            waiting: "bg-warning text-dark",
            serving: "bg-success text-white",
            done: "bg-secondary text-white",
            transferred: "bg-info text-white",
            no_show: "bg-danger text-white",
        };
        return classes[status] || "bg-secondary text-white";
    };

    return { getStatusBadgeClass };
}
