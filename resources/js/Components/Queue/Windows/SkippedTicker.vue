<template>
  <div class="skipped-ticker" ref="containerRef">
    <div
      ref="trackRef"
      class="skipped-ticker__track"
      :class="{ 'skipped-ticker__track--animate': hasOverflow }"
    >
      <span
        v-for="(number, index) in displayItems"
        :key="`${number}-${index}`"
        class="skipped-ticker__item"
      >
        {{ number }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from "vue";

const props = defineProps({
  items: {
    type: Array,
    default: () => [],
  },
});

const containerRef = ref(null);
const trackRef = ref(null);
const hasOverflow = ref(false);
let resizeObserver = null;

const displayItems = computed(() =>
  hasOverflow.value ? [...props.items, ...props.items] : props.items
);

const updateOverflow = async () => {
  await nextTick();
  const container = containerRef.value;
  const track = trackRef.value;
  if (!container || !track || props.items.length === 0) {
    hasOverflow.value = false;
    return;
  }

  const itemCount = props.items.length;
  const itemElements = track.querySelectorAll(".skipped-ticker__item");
  const singleSetItems = Array.from(itemElements).slice(0, itemCount);

  let contentWidth = 0;
  singleSetItems.forEach((item, index) => {
    contentWidth += item.getBoundingClientRect().width;
    if (index < singleSetItems.length - 1) {
      contentWidth += 16;
    }
  });

  const overflow = contentWidth > container.clientWidth;
  if (overflow !== hasOverflow.value) {
    hasOverflow.value = overflow;
    if (overflow) {
      await nextTick();
    }
  }
};

watch(
  () => props.items,
  () => updateOverflow(),
  { deep: true }
);

onMounted(() => {
  updateOverflow();
  window.addEventListener("resize", updateOverflow);

  if (typeof ResizeObserver !== "undefined" && containerRef.value) {
    resizeObserver = new ResizeObserver(updateOverflow);
    resizeObserver.observe(containerRef.value);
  }
});

onBeforeUnmount(() => {
  window.removeEventListener("resize", updateOverflow);
  resizeObserver?.disconnect();
});
</script>

<style scoped>
.skipped-ticker {
  overflow: hidden;
  width: 100%;
  margin-top: 0.5rem;
}

.skipped-ticker__track {
  display: flex;
  flex-wrap: nowrap;
  align-items: center;
  gap: 1rem;
  width: max-content;
}

.skipped-ticker__track--animate {
  animation: skipped-ticker-scroll 24s linear infinite;
}

.skipped-ticker__item {
  flex-shrink: 0;
  text-align: center;
  padding: 0.25rem 0.75rem;
  background-color: #ffedd5;
  border-radius: 0.25rem;
  white-space: nowrap;
}

@keyframes skipped-ticker-scroll {
  0% {
    transform: translateX(0);
  }
  100% {
    transform: translateX(-50%);
  }
}
</style>
