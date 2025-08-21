import { router } from "@inertiajs/vue3";

export function useCompleteAndTransfer() {
    const completeAndTransfer = (id) => {
        if (
            !confirm(
                "Are you sure you want to complete this service and transfer to next department?"
            )
        ) {
            return;
        }

        router.post(
            route("queue.complete-and-transfer", id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ["queueItems"] });
                },
            }
        );
    };
    return { completeAndTransfer };
}
