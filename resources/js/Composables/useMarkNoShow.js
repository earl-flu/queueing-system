import { router } from "@inertiajs/vue3";

export function useMarkNoShow() {
    const markNoShow = (id) => {
        if (!confirm("Mark this patient as No Show?")) {
            return;
        }
        router.post(
            route("queue.no-show", id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ["queueItems"] });
                },
            }
        );
    };
    return { markNoShow };
}
