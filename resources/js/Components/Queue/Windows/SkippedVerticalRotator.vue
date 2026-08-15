<template>
  <div v-if="!items.length" class="text-gray-400 text-center">-</div>
  <div
    v-else
    ref="containerRef"
    class="skipped-vertical-ticker"
    :style="containerStyle"
  >
    <div
      ref="trackRef"
      class="skipped-vertical-ticker__track flex flex-column gap-2"
      :class="{ 'skipped-vertical-ticker__track--animate': shouldAnimate }"
      :style="trackStyle"
    >
      <div
        v-for="(item, index) in displayItems"
        :key="`${item.id}-${index}`"
        class="skipped-vertical-ticker__item text-center p-2 rounded-md"
        :class="itemClass"
      >
        {{ item.queue_number }}
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  ref,
  computed,
  watch,
  onMounted,
  onBeforeUnmount,
  nextTick,
} from "vue";

const props = defineProps({
  items: {
    type: Array,
    default: () => [],
  },
  itemClass: {
    type: [String, Object, Array],
    default: "",
  },
  maxVisible: {
    type: Number,
    default: 4,
  },
  secondsPerItem: {
    type: Number,
    default: 3,
  },
});

const SLOT_HEIGHT = 42;
const SLOT_GAP = 8;

const containerRef = ref(null);
const trackRef = ref(null);
const shouldAnimate = ref(false);
let resizeObserver = null;

const containerStyle = computed(() => ({
  height: `${
    props.maxVisible * SLOT_HEIGHT + (props.maxVisible - 1) * SLOT_GAP
  }px`,
}));

const displayItems = computed(() =>
  shouldAnimate.value ? [...props.items, ...props.items] : props.items
);

const trackStyle = computed(() => {
  if (!shouldAnimate.value) return {};
  return {
    animationDuration: `${props.items.length * props.secondsPerItem}s`,
  };
});

const updateAnimation = async () => {
  await nextTick();
  shouldAnimate.value = props.items.length > props.maxVisible;
};

watch(
  () => props.items,
  () => updateAnimation(),
  { deep: true }
);

onMounted(() => {
  updateAnimation();

  if (typeof ResizeObserver !== "undefined" && containerRef.value) {
    resizeObserver = new ResizeObserver(updateAnimation);
    resizeObserver.observe(containerRef.value);
  }
});

onBeforeUnmount(() => {
  resizeObserver?.disconnect();
});
</script>

<style scoped>
.skipped-vertical-ticker {
  overflow: hidden;
  width: 100%;
}

.skipped-vertical-ticker__track {
  width: 100%;
}

.skipped-vertical-ticker__track--animate {
  animation: skipped-vertical-scroll linear infinite;
}

.skipped-vertical-ticker__item {
  flex-shrink: 0;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
}

@keyframes skipped-vertical-scroll {
  0% {
    transform: translateY(0);
  }
  100% {
    transform: translateY(-50%);
  }
}
</style>
