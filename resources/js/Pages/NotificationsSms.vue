<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    exams: {
        type: Array,
        default: () => [],
    },
    classLevels: {
        type: Array,
        default: () => [],
    },
    recent: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    type: 'exam_results',
    audience: 'all_parents',
    exam_id: '',
    class_level: '',
    message: '',
});

const submit = () => {
    form.post(route('notifications.sms.store'));
};

const selectedExam = computed(() => {
    if (!form.exam_id) return null;
    return (props.exams || []).find((e) => String(e.id) === String(form.exam_id)) || null;
});
</script>

<template>
    <Head title="Results SMS & Reminders" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Results SMS & Reminders
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Send exam results and important school information to parents and teachers via SMS.
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <div class="grid gap-4 lg:grid-cols-3">
                <!-- Compose SMS -->
                <div class="lg:col-span-2 rounded-xl bg-white p-5 text-xs text-gray-700 shadow-sm ring-1 ring-gray-100">
                    <h3 class="mb-3 text-sm font-semibold text-gray-800">
                        Compose Message
                    </h3>
                    <form class="space-y-3" @submit.prevent="submit">
                        <div class="grid gap-3 md:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-[11px] font-medium">Purpose</label>
                                <select
                                    v-model="form.type"
                                    class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                >
                                    <option value="exam_results">Exam Results</option>
                                    <option value="general">General Information</option>
                                    <option value="reminder">Reminder</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-[11px] font-medium">Audience</label>
                                <select
                                    v-model="form.audience"
                                    class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                >
                                    <option value="all_parents">All Parents / Guardians</option>
                                    <option value="class_parents">Parents by Class / Form</option>
                                    <option value="all_teachers">All Teachers</option>
                                </select>
                            </div>
                            <div v-if="form.audience === 'class_parents'">
                                <label class="mb-1 block text-[11px] font-medium">Class / Form</label>
                                <select
                                    v-model="form.class_level"
                                    class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                >
                                    <option value="">Select class / form</option>
                                    <option v-for="level in classLevels" :key="level" :value="level">
                                        {{ level }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div v-if="form.type === 'exam_results'" class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-[11px] font-medium">Exam</label>
                                <select
                                    v-model="form.exam_id"
                                    class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                >
                                    <option value="">Select exam</option>
                                    <option v-for="exam in exams" :key="exam.id" :value="exam.id">
                                        {{ exam.name }} ({{ exam.academic_year }})
                                    </option>
                                </select>
                            </div>
                            <div v-if="selectedExam" class="text-[11px] text-gray-500">
                                <p class="font-semibold text-gray-700">
                                    Selected Exam
                                </p>
                                <p>
                                    {{ selectedExam.name }} - {{ selectedExam.type }} ({{ selectedExam.academic_year }})
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Message</label>
                            <textarea
                                v-model="form.message"
                                rows="4"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-[11px] focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="Write your SMS message here. Example: Dear parent, exam results for TERM ONE 2025 are now available..."
                            />
                            <p class="mt-1 text-[10px] text-gray-500">
                                Keep the message short and clear. Long messages may be split into multiple SMS parts by your provider.
                            </p>
                        </div>

                        <div class="flex items-center justify-end gap-2">
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                                :disabled="form.processing"
                            >
                                Save & Queue SMS
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Context & recent messages -->
                <div class="space-y-4">
                    <div class="rounded-xl bg-white p-4 text-xs text-gray-700 shadow-sm ring-1 ring-gray-100">
                        <h3 class="mb-2 text-sm font-semibold text-gray-800">
                            Exams (recent)
                        </h3>
                        <div v-if="exams.length === 0" class="text-[11px] text-gray-500">
                            No exams found.
                        </div>
                        <ul v-else class="space-y-1 text-[11px]">
                            <li
                                v-for="exam in exams"
                                :key="exam.id"
                                class="flex items-center justify-between rounded border border-gray-100 bg-slate-50 px-2 py-1"
                            >
                                <div>
                                    <div class="font-semibold text-gray-800">
                                        {{ exam.name }}
                                    </div>
                                    <div class="text-[10px] text-gray-500">
                                        {{ exam.type }}  b7 {{ exam.academic_year }}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-xl bg-white p-4 text-xs text-gray-700 shadow-sm ring-1 ring-gray-100">
                        <h3 class="mb-2 text-sm font-semibold text-gray-800">
                            Recent SMS Notifications
                        </h3>
                        <div v-if="recent.length === 0" class="text-[11px] text-gray-500">
                            No SMS notifications have been created yet.
                        </div>
                        <ul v-else class="space-y-1 text-[11px] max-h-48 overflow-auto pr-1">
                            <li
                                v-for="item in recent"
                                :key="item.id"
                                class="rounded border border-gray-100 bg-slate-50 px-2 py-1"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-gray-800">
                                        {{ item.type === 'exam_results' ? 'Exam Results' : item.type === 'reminder' ? 'Reminder' : 'General' }}
                                    </span>
                                    <span class="text-[10px] text-gray-500">
                                        {{ new Date(item.created_at).toLocaleString() }}
                                    </span>
                                </div>
                                <div class="text-[10px] text-gray-500">
                                    Audience:
                                    <span class="font-medium text-gray-700">
                                        {{ item.audience === 'all_parents' ? 'All Parents' : item.audience === 'class_parents' ? 'Parents by Class' : 'All Teachers' }}
                                    </span>
                                    <span v-if="item.class_level">
                                         b7 {{ item.class_level }}
                                    </span>
                                </div>
                                <div class="mt-0.5 line-clamp-2 text-[10px] text-gray-600">
                                    {{ item.message }}
                                </div>
                                <div class="mt-0.5 text-[10px] text-gray-500">
                                    Status:
                                    <span class="font-medium" :class="item.status === 'sent' ? 'text-emerald-700' : item.status === 'failed' ? 'text-red-700' : 'text-amber-700'">
                                        {{ item.status }}
                                    </span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
