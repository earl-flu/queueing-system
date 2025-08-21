import { router } from "@inertiajs/vue3";

export function useCallPatient() {
    const callPatient = (id) => {
        router.post(
            route("queue.call", id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ["queueItems"] });
                },
            }
        );
    };
    return { callPatient };
}
