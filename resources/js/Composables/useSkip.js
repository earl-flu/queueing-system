import { router } from "@inertiajs/vue3";

export function useSkip() {
    const skip = (id) => {
        if (!confirm("Skip this patient?")) {
            return;
        }
        router.post(
            route("queue.skip", id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ["queueItems"] });
                },
            }
        );
    };
    return { skip };
}
