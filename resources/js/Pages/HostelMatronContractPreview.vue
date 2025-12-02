<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    staff: {
        type: Object,
        default: () => ({}),
    },
    school: {
        type: Object,
        default: () => null,
    },
    academicYear: {
        type: String,
        default: '',
    },
});

const onPrint = () => {
    window.print();
};
</script>

<template>
    <Head title="Hostel Matron/Patron Contract" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Hostel Matron / Patron Contract
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Contract details for the hostel {{ staff.role }} for academic year {{ academicYear || '...' }}.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <Link
                        :href="route('hostel-matron-patron.index')"
                        class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-[11px] font-semibold text-gray-700 shadow-sm ring-1 ring-gray-200 hover:bg-gray-50"
                    >
                        ← Back to list
                    </Link>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700"
                        @click="onPrint"
                    >
                        Print
                    </button>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-3xl rounded-xl bg-white p-6 text-xs text-gray-800 shadow-sm ring-1 ring-gray-100 print:shadow-none print:ring-0">
            <!-- School header / contract title -->
            <section class="border-b border-gray-200 pb-4">
                <div class="flex flex-col gap-1 text-center">
                    <div class="mb-2 flex flex-col items-center justify-center gap-1">
                        <img
                            src="/images/emblem.png"
                            alt="Emblem"
                            class="h-10 w-auto object-contain"
                        />
                        <p class="mt-1 text-[10px] font-semibold uppercase tracking-wide text-gray-700">
                            HALMASHAURI YA
                            <span class="ml-1">{{ school?.district || 'MANISPAA' }}</span>
                        </p>
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-900">
                            {{ school?.name || 'School Name' }}
                        </p>
                        <p class="text-[10px] text-gray-600">
                            TEL: {{ school?.phone || 'N/A' }}
                            <span class="mx-1">|</span>
                            EMAIL: {{ school?.email || 'N/A' }}
                        </p>
                    </div>
                    <div class="mt-2 flex items-center justify-center">
                        <div class="h-px w-16 bg-gray-300"></div>
                        <span class="mx-2 text-[10px] font-medium uppercase tracking-[0.2em] text-gray-500">A</span>
                        <div class="h-px w-16 bg-gray-300"></div>
                    </div>
                    <p class="mt-2 text-[12px] font-semibold text-gray-800">
                        HOSTEL MATRON / PATRON CONTRACT
                    </p>
                    <p class="mt-0.5 text-[11px] text-gray-600">
                        Academic Year {{ academicYear || '________' }}
                    </p>
                </div>
            </section>

            <!-- Staff details + photo -->
            <section class="mt-5 space-y-2">
                <h3 class="text-[12px] font-semibold text-gray-800">
                    I. Matron / Patron Details
                </h3>
                <div class="grid grid-cols-3 gap-4 text-[11px]">
                    <!-- Maelezo ya msingi -->
                    <div class="col-span-2 space-y-2">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="font-semibold text-gray-600">Full name</p>
                                <p class="mt-0.5 text-gray-900">
                                    {{ staff.name || '_____________' }}
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-600">Role</p>
                                <p class="mt-0.5 capitalize text-gray-900">
                                    {{ staff.role || 'matron/patron' }}
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-600">Hostel</p>
                                <p class="mt-0.5 text-gray-900">
                                    {{ staff.hostel_name || '_____________' }}
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-600">Phone</p>
                                <p class="mt-0.5 text-gray-900">
                                    {{ staff.phone || '_____________' }}
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-600">Email</p>
                                <p class="mt-0.5 break-all text-gray-900">
                                    {{ staff.email || '_____________' }}
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-600">System account (username)</p>
                                <p class="mt-0.5 break-all text-gray-900">
                                    {{ staff.username || '_____________' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Picha ya mhusika -->
                    <div class="flex flex-col items-center justify-start">
                        <div class="flex h-24 w-20 items-center justify-center rounded border border-dashed border-gray-300 bg-gray-50 text-[10px] text-gray-400">
                            <span>PHOTO<br />3x4</span>
                        </div>
                        <p class="mt-1 text-[10px] text-gray-500">To be affixed after printing</p>
                    </div>
                </div>
                <p class="mt-3 text-[11px] text-gray-600">
                    Hereafter the staff member is referred to as the <span class="font-semibold">"Matron/Patron"</span> and the
                    institution as the <span class="font-semibold">"Employer"</span>. Both parties agree to abide by all terms and
                    conditions set out in this contract.
                </p>
            </section>

            <!-- Contract period -->
            <section class="mt-5 space-y-2">
                <h3 class="text-[12px] font-semibold text-gray-800">
                    II. Contract Period and Nature of Engagement
                </h3>
                <p class="text-[11px] text-gray-700">
                    1. This contract shall commence on
                    <span class="font-semibold underline decoration-dotted" contenteditable="true">__________</span> and end on
                    <span class="font-semibold underline decoration-dotted" contenteditable="true">__________</span> within the
                    academic year
                    <span class="font-semibold">{{ academicYear || '________' }}</span>, unless terminated earlier in accordance with
                    the provisions set out below.
                </p>
                <p class="text-[11px] text-gray-700">
                    2. The nature of engagement is a <span class="font-semibold">hostel supervision contract</span> for the purposes of
                    overseeing students residing in the hostel, ensuring discipline, safety and welfare outside normal
                    classroom hours.
                </p>
            </section>

            <section class="mt-5 space-y-2">
                <h3 class="text-[12px] font-semibold text-gray-800">
                    III. Duties and Responsibilities of the Matron / Patron
                </h3>
                <ul class="ml-4 list-disc space-y-1 text-[11px] text-gray-700">
                    <li>
                        Supervising the discipline of all students residing in the hostel, including monitoring check-in and
                        check-out times, bedtime, and evening/night study schedules.
                    </li>
                    <li>
                        Ensuring that the hostel environment is clean and safe, including rooms, corridors, toilets and washing
                        areas.
                    </li>
                    <li>
                        Monitoring the health and wellbeing of students in the hostel, and promptly reporting to school
                        leadership or health personnel where any physical or psychological concern is observed.
                    </li>
                    <li>
                        Recording and keeping a log of important incidents in the hostel, including breaches of regulations,
                        minor accidents, and relevant communication with parents/guardians.
                    </li>
                    <li>
                        Collaborating with school leadership to prepare and supervise hostel schedules (meals, study times,
                        rest periods, etc.).
                    </li>
                    <li>
                        Ensuring that students observe appropriate dress code and hostel rules as set by the school.
                    </li>
                    <li>
                        Providing guidance on character and conduct to students and serving as a role model in behaviour and
                        ethics.
                    </li>
                    <li>
                        Immediately reporting to school leadership any serious incident (e.g., fire, theft, major conflict,
                        attempted escape from the hostel, etc.).
                    </li>
                    <li>
                        Performing any other reasonable duties related to hostel management as may be assigned by the school
                        administration.
                    </li>
                </ul>
            </section>

            <section class="mt-5 space-y-2">
                <h3 class="text-[12px] font-semibold text-gray-800">
                    IV. Remuneration and Financial Terms
                </h3>
                <p class="text-[11px] text-gray-700">
                    1. The Employer shall pay the Matron/Patron a monthly allowance of
                    <span class="font-semibold underline decoration-dotted" contenteditable="true">TSh 300,000 (three hundred thousand shillings only)</span>
                    as hostel supervision allowance. This amount may be reviewed from time to time based on mutual agreement
                    and official communication by the school.
                </p>
                <p class="text-[11px] text-gray-700">
                    2. Payments shall be made through the schools official payment system (e.g., bank or mobile money)
                    within
                    <span class="font-semibold underline decoration-dotted" contenteditable="true">__________</span>
                    days from the end of the respective month.
                </p>
                <p class="text-[11px] text-gray-700">
                    3. The Matron/Patron may be entitled to additional payments where they perform extra duties or overtime,
                    in line with separately written agreements signed by both parties.
                </p>
            </section>

            <section class="mt-5 space-y-2">
                <h3 class="text-[12px] font-semibold text-gray-800">
                    V. Code of Conduct and Public Service Principles
                </h3>
                <p class="text-[11px] text-gray-700">
                    1. The Matron/Patron shall observe the principles of
                    <span class="font-semibold">good public service</span>, including integrity, honesty, confidentiality of
                    student and school information, separation of personal interests from official duties, and refraining from
                    corruption or unfair favoritism.
                </p>
                <p class="text-[11px] text-gray-700">
                    2. The Matron/Patron shall not undertake any external activity that may negatively affect their
                    performance without prior written approval from the Employer.
                </p>
                <p class="text-[11px] text-gray-700">
                    3. The Matron/Patron shall respect the fundamental rights of students, avoid cruel or degrading
                    punishment, and apply <span class="font-semibold">fair and proportionate disciplinary measures</span> in
                    accordance with school regulations and relevant Ministry guidelines.
                </p>
            </section>

            <section class="mt-5 space-y-2">
                <h3 class="text-[12px] font-semibold text-gray-800">
                    VI. Sanctions and Disciplinary Measures
                </h3>
                <p class="text-[11px] text-gray-700">
                    In case of breach of this contract or misconduct, the Employer may apply one or more of the following
                    measures, depending on the severity of the offence:
                </p>
                <ul class="ml-4 list-disc space-y-1 text-[11px] text-gray-700">
                    <li><span class="font-semibold">Verbal warning</span> for minor and infrequent offences.</li>
                    <li><span class="font-semibold">Written warning</span> to be kept on the staff member's file.</li>
                    <li>
                        <span class="font-semibold">Suspension</span> where the misconduct has a significant impact on student safety
                        or welfare.
                    </li>
                    <li>
                        <span class="font-semibold">Termination of contract</span> where serious misconduct, abuse of authority or
                        criminal behaviour is established.
                    </li>
                </ul>
                <p class="text-[11px] text-gray-700">
                    Either party may terminate this contract by giving at least
                    <span class="font-semibold underline decoration-dotted" contenteditable="true">30</span>
                    days' written notice, except in cases of gross misconduct or emergency situations as defined by school
                    policy.
                </p>
            </section>

            <!-- Sahihi -->
            <section class="mt-8 space-y-6">
                <h3 class="text-[12px] font-semibold text-gray-800">
                    VII. Signatures and Confirmation
                </h3>
                <div class="grid gap-8 md:grid-cols-2">
                    <div class="space-y-3">
                        <p class="text-[11px] font-semibold text-gray-700">For and on behalf of the School</p>
                        <p class="mt-4 text-[11px] text-gray-700">Name: __________________________</p>
                        <p class="text-[11px] text-gray-700">Title: Head of School</p>
                        <p class="mt-6 text-[11px] text-gray-700">Signature: ______________________</p>
                        <p class="text-[11px] text-gray-700">Date: ____ / ____ / {{ academicYear || '____' }}</p>
                    </div>
                    <div class="space-y-3">
                        <p class="text-[11px] font-semibold text-gray-700">For the Matron/Patron</p>
                        <p class="mt-4 text-[11px] text-gray-700">Name: {{ staff.name || '__________________________' }}</p>
                        <p class="text-[11px] text-gray-700">Hostel: {{ staff.hostel_name || '__________________' }}</p>
                        <p class="mt-6 text-[11px] text-gray-700">Signature: ______________________</p>
                        <p class="text-[11px] text-gray-700">Date: ____ / ____ / {{ academicYear || '____' }}</p>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
