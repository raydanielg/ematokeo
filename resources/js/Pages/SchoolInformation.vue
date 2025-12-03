<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Alert from '@/Components/Alert.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { onMounted, reactive, ref } from 'vue';

const props = defineProps({
    school: {
        type: Object,
        default: () => ({}),
    },
    subjects: {
        type: Array,
        default: () => [],
    },
});

const form = reactive({
    name: props.school.name || '',
    registration_number: props.school.registration_number || '',
    school_code: props.school.school_code || '',
    level: props.school.level || '',
    ownership: props.school.ownership || '',
    phone: props.school.phone || '',
    email: props.school.email || '',
    address: props.school.address || '',
    district: props.school.district || '',
    region: props.school.region || '',
    head_teacher_name: props.school.head_teacher_name || '',
    head_teacher_phone: props.school.head_teacher_phone || '',
});

const saving = reactive({});
const savingAll = ref(false);
const saveStatus = ref(null); // 'success' | 'error' | null
const saveError = ref(null);
const showRegistrationSuccess = ref(false);
const errors = ref({});

const page = usePage();

onMounted(() => {
    const flash = page.props.value.flash || {};
    if (flash.registration_success) {
        showRegistrationSuccess.value = true;
    }
});

const saveField = (field) => {
    if (typeof form[field] === 'string' && !['level', 'ownership'].includes(field)) {
        form[field] = form[field].toUpperCase();
    }

    saving[field] = true;
    errors.value = {};
    router.put(
        route('settings.school-information.update'),
        { [field]: form[field] },
        {
            preserveScroll: true,
            onError: (err) => {
                errors.value = err;
            },
            onFinish: () => {
                saving[field] = false;
            },
        },
    );
};

const saveAll = () => {
    savingAll.value = true;
    saveStatus.value = null;
    saveError.value = null;
    errors.value = {};

    const payload = { ...form };

    Object.keys(payload).forEach((key) => {
        if (typeof payload[key] === 'string' && !['level', 'ownership'].includes(key)) {
            payload[key] = payload[key].toUpperCase();
        }
    });

    router.put(
        route('settings.school-information.update'),
        payload,
        {
            preserveScroll: true,
            onSuccess: () => {
                saveStatus.value = 'success';
                saveError.value = null;
            },
            onError: (err) => {
                saveStatus.value = 'error';
                errors.value = err;
                if (err.name) {
                    saveError.value = err.name[0];
                }
            },
            onFinish: () => {
                savingAll.value = false;
            },
        },
    );
};

const deleteSubject = (id) => {
    if (!confirm('Delete this subject from the school?')) return;

    router.delete(route('subjects.destroy', id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="School Information" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                    School Information
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Core details about your school used across E-Matokeo. You typically set this once and only update when something changes.
                </p>
            </div>
        </template>

        <div class="space-y-6">
            <Alert
                variant="info"
                title="Fill in your school details"
                message="This is the first step. These details are required so that exam numbers, reports, and other modules work correctly."
            />
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Left table -->
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        General Information
                    </h3>
                    <table class="w-full text-left text-xs">
                        <tbody>
                            <tr class="border-b border-gray-100">
                                <td class="w-1/3 px-2 py-2 font-medium text-gray-600">
                                    School Name
                                </td>
                                <td class="px-2 py-2">
                                    <div>
                                        <input
                                            v-model="form.name"
                                            @blur="saveField('name')"
                                            type="text"
                                            class="w-full rounded-md border px-2 py-1.5 text-xs focus:outline-none focus:ring-emerald-500"
                                            :class="errors.name ? 'border-red-400 focus:border-red-500' : 'border-gray-200 focus:border-emerald-500'"
                                            placeholder="e.g. Mlimani Secondary School"
                                        />
                                        <p v-if="errors.name" class="mt-1 text-[10px] text-red-600">
                                            {{ errors.name[0] }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            <tr class="border-b border-gray-100">
                                <td class="px-2 py-2 font-medium text-gray-600">
                                    Registration No.
                                </td>
                                <td class="px-2 py-2">
                                    <input
                                        v-model="form.registration_number"
                                        @blur="saveField('registration_number')"
                                        type="text"
                                        class="w-full rounded-md border border-gray-200 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                        placeholder="NECTA / EMIS number"
                                    />
                                </td>
                            </tr>
                            <tr class="border-b border-gray-100">
                                <td class="px-2 py-2 font-medium text-gray-600">
                                    School Code
                                </td>
                                <td class="px-2 py-2">
                                    <input
                                        v-model="form.school_code"
                                        @blur="saveField('school_code')"
                                        type="text"
                                        class="w-full rounded-md border border-gray-200 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                        placeholder="Used to build exam numbers"
                                    />
                                </td>
                            </tr>
                            <tr class="border-b border-gray-100">
                                <td class="px-2 py-2 font-medium text-gray-600">
                                    Level
                                </td>
                                <td class="px-2 py-2">
                                    <select
                                        v-model="form.level"
                                        @change="saveField('level')"
                                        class="w-full rounded-md border border-gray-200 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    >
                                        <option value="" disabled>Select level</option>
                                        <option value="Primary">Primary</option>
                                        <option value="O-Level">O-Level (Secondary)</option>
                                        <option value="A-Level">A-Level (Secondary)</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 font-medium text-gray-600">
                                    Ownership
                                </td>
                                <td class="px-2 py-2">
                                    <select
                                        v-model="form.ownership"
                                        @change="saveField('ownership')"
                                        class="w-full rounded-md border border-gray-200 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                    >
                                        <option value="" disabled>Select ownership</option>
                                        <option value="Government">Government</option>
                                        <option value="Private">Private</option>
                                        <option value="Faith-Based">Faith-Based</option>
                                        <option value="Community">Community</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Right table -->
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Contact & Location
                    </h3>
                    <table class="w-full text-left text-xs">
                        <tbody>
                            <tr class="border-b border-gray-100">
                                <td class="w-1/3 px-2 py-2 font-medium text-gray-600">
                                    Phone
                                </td>
                                <td class="px-2 py-2">
                                    <input
                                        v-model="form.phone"
                                        @blur="saveField('phone')"
                                        type="text"
                                        class="w-full rounded-md border border-gray-200 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                        placeholder="Main school contact number"
                                    />
                                </td>
                            </tr>
                            <tr class="border-b border-gray-100">
                                <td class="px-2 py-2 font-medium text-gray-600">
                                    Email
                                </td>
                                <td class="px-2 py-2">
                                    <input
                                        v-model="form.email"
                                        @blur="saveField('email')"
                                        type="email"
                                        class="w-full rounded-md border border-gray-200 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                        placeholder="Official school email"
                                    />
                                </td>
                            </tr>
                            <tr class="border-b border-gray-100">
                                <td class="px-2 py-2 font-medium text-gray-600">
                                    Address
                                </td>
                                <td class="px-2 py-2">
                                    <input
                                        v-model="form.address"
                                        @blur="saveField('address')"
                                        type="text"
                                        class="w-full rounded-md border border-gray-200 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                        placeholder="P.O. Box / Street"
                                    />
                                </td>
                            </tr>
                            <tr class="border-b border-gray-100">
                                <td class="px-2 py-2 font-medium text-gray-600">
                                    District
                                </td>
                                <td class="px-2 py-2">
                                    <input
                                        v-model="form.district"
                                        @blur="saveField('district')"
                                        type="text"
                                        class="w-full rounded-md border border-gray-200 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                        placeholder="e.g. Ilala"
                                    />
                                </td>
                            </tr>
                            <tr class="border-b border-gray-100">
                                <td class="px-2 py-2 font-medium text-gray-600">
                                    Region
                                </td>
                                <td class="px-2 py-2">
                                    <input
                                        v-model="form.region"
                                        @blur="saveField('region')"
                                        type="text"
                                        class="w-full rounded-md border border-gray-200 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                        placeholder="e.g. Dar es Salaam"
                                    />
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 font-medium text-gray-600">
                                    Head Teacher
                                </td>
                                <td class="px-2 py-2">
                                    <div class="grid grid-cols-2 gap-2">
                                        <input
                                            v-model="form.head_teacher_name"
                                            @blur="saveField('head_teacher_name')"
                                            type="text"
                                            class="w-full rounded-md border border-gray-200 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                            placeholder="Full name"
                                        />
                                        <input
                                            v-model="form.head_teacher_phone"
                                            @blur="saveField('head_teacher_phone')"
                                            type="text"
                                            class="w-full rounded-md border border-gray-200 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                            placeholder="Phone number"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Subjects Offered
                </h3>
                <p class="mb-2 text-[11px] text-gray-500">
                    These are the subjects configured for this school. You can remove a subject by clicking the small 
                    <span class="font-semibold">×</span> icon on the chip. To add more, use the Subjects settings pages.
                </p>
                <div v-if="subjects.length" class="flex flex-wrap gap-1">
                    <span
                        v-for="subject in subjects"
                        :key="subject.id"
                        class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-800 ring-1 ring-emerald-100"
                    >
                        <span>{{ subject.subject_code }}</span>
                        <button
                            type="button"
                            class="text-[11px] text-emerald-500 hover:text-red-600"
                            @click="deleteSubject(subject.id)"
                        >
                            ×
                        </button>
                    </span>
                </div>
                <div v-else class="text-[11px] text-gray-400">
                    No subjects configured yet. Start by adding subjects under the Subjects section.
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-[11px] text-gray-500">
                        Changes are saved inline when you leave a field (on blur). You can also click Save below to store everything at once.
                    </p>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="savingAll"
                        @click="saveAll"
                    >
                        <span v-if="savingAll">Saving...</span>
                        <span v-else>Save</span>
                    </button>
                </div>

                <div class="max-w-md">
                    <Alert
                        v-if="saveStatus === 'success'"
                        variant="success"
                        title="Saved successfully"
                        message="Your school information has been saved."
                    />
                    <Alert
                        v-else-if="saveStatus === 'error'"
                        variant="danger"
                        title="Save failed"
                        :message="saveError || 'Something went wrong while saving. Please check the fields and try again.'"
                    />
                </div>
            </div>

            <!-- Registration success overlay -->
            <div
                v-if="showRegistrationSuccess"
                class="pointer-events-auto fixed inset-0 z-40 flex items-center justify-center bg-black/60 px-4 backdrop-blur-sm"
            >
                <div class="flex w-full max-w-md flex-col items-center rounded-2xl bg-white p-6 text-center shadow-2xl ring-1 ring-emerald-100">
                    <div class="mb-4 h-32 w-32">
                        <!-- Lottie animation: ensure lottie-player script is loaded globally -->
                        <lottie-player
                            src="/success.json"
                            background="transparent"
                            speed="1"
                            style="width: 100%; height: 100%"
                            autoplay
                        ></lottie-player>
                    </div>
                    <h2 class="mb-1 text-lg font-semibold text-gray-900">
                        Account created successfully
                    </h2>
                    <p class="mb-4 text-xs text-gray-500">
                        Welcome to E-Matokeo. Next, please fill in your school details so that exams and reports work correctly.
                    </p>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                        @click="showRegistrationSuccess = false"
                    >
                        Continue
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
