<script setup>
const props = defineProps({
    variant: {
        type: String,
        default: 'info', // info, danger, success, warning, dark
    },
    title: {
        type: String,
        default: '',
    },
    message: {
        type: String,
        default: '',
    },
});

const variantClasses = {
    info: 'text-fg-brand-strong bg-brand-softer',
    danger: 'text-fg-danger-strong bg-danger-soft',
    success: 'text-fg-success-strong bg-success-soft',
    warning: 'text-fg-warning bg-warning-soft',
    dark: 'text-heading bg-neutral-secondary-medium',
};
</script>

<template>
    <div
        class="flex items-start sm:items-center p-4 mb-4 text-sm rounded-base"
        :class="variantClasses[variant] || variantClasses.info"
        role="alert"
    >
        <svg
            class="w-4 h-4 me-2 shrink-0 mt-0.5 sm:mt-0"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
        >
            <path
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M10 11h2v5m-2 0h4m-2.592-8.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
            />
        </svg>
        <p>
            <span v-if="title" class="font-medium me-1">{{ title }}</span>
            <slot>
                {{ message }}
            </slot>
        </p>
    </div>
</template>
