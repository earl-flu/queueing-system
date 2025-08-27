import { router } from "@inertiajs/vue3";

export function useCompleteAndTransfer() {
    const completeAndTransfer = (item) => {
        if (
            !confirm(
                `Are you sure you want to complete ${item.queue_number} service and transfer to next department?`
            )
        ) {
            return;
        }

        router.post(
            route("queue.complete-and-transfer", item),
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
