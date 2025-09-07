import { router } from "@inertiajs/vue3";

export function useCallAgain() {
    const callAgain = (item) => {
        if (!confirm(`Call ${item.queue_number} again?`)) {
            return;
        }
        router.post(
            route("queue.call-again", item),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ["queueItems"] });
                },
            }
        );
    };
    return { callAgain };
}
