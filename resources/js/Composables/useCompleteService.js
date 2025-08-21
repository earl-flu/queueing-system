import { router } from "@inertiajs/vue3";

export function useCompleteService() {
    const completeService = (id) => {
        if (!confirm("Are you sure you want to complete this service?")) {
            return;
        }
        router.post(
            route("queue.complete", id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ["queueItems"] });
                },
            }
        );
    };
    return { completeService };
}
