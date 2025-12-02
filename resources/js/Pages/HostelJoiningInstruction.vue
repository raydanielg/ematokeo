<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  allocation: {
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
  requirements: {
    type: Array,
    default: () => [],
  },
});

const onPrint = () => {
  window.print();
};
</script>

<template>
  <Head title="Hostel Joining Instructions" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h2 class="text-2xl font-semibold leading-tight text-gray-800">Hostel Joining Instructions</h2>
          <p class="mt-1 text-sm text-gray-500">
            Personalized joining instructions for {{ allocation.student_name || 'student' }}.
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs">
          <Link
            :href="route('hostel-students.index')"
            class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-[11px] font-semibold text-gray-700 shadow-sm ring-1 ring-gray-200 hover:bg-gray-50"
          >
            ← Back to hostel students
          </Link>
          <button
            type="button"
            class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700"
            @click="onPrint"
          >
            Print joining instructions
          </button>
        </div>
      </div>
    </template>

    <div
      class="mx-auto max-w-3xl rounded-xl bg-white p-6 text-xs text-gray-800 shadow-sm ring-1 ring-gray-100 print:shadow-none print:ring-0"
    >
      <!-- Header -->
      <section class="border-b border-gray-200 pb-4 text-center">
        <img src="/images/emblem.png" alt="Emblem" class="mx-auto h-10 w-auto object-contain" />
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
        <p class="mt-1 text-[11px] font-semibold text-gray-800">HOSTEL JOINING INSTRUCTIONS</p>
        <p class="text-[10px] text-gray-600">Academic Year {{ academicYear || '________' }}</p>
      </section>

      <!-- Login / identification details -->
      <section class="mt-4 rounded-md border border-dashed border-gray-200 bg-gray-50 px-3 py-2 text-[11px] text-gray-800">
        <p class="text-[11px] font-semibold text-gray-700">Student Identification / Login Details</p>
        <p class="mt-1">
          <span class="font-semibold">Username:</span>
          <span class="ml-1">{{ allocation.exam_number || '________________' }}</span>
        </p>
        <p class="mt-1">
          <span class="font-semibold">Password:</span>
          <span class="ml-1">________________________</span>
        </p>
      </section>

      <!-- Addressed to student/guardian -->
      <section class="mt-3 space-y-1 text-[11px]">
        <p>
          <span class="font-semibold">To:</span>
          {{ allocation.student_name || '________________' }}
          <span v-if="allocation.class_name" class="ml-1">({{ allocation.class_name }})</span>
        </p>
        <p>
          <span class="font-semibold">Guardian:</span>
          {{ allocation.guardian_name || '________________' }}
          <span v-if="allocation.guardian_phone" class="ml-1">({{ allocation.guardian_phone }})</span>
        </p>
      </section>

      <!-- Intro paragraph -->
      <section class="mt-4 space-y-2 text-[11px]">
        <p>
          We are pleased to inform you that the above-named student has been allocated a place in the
          <span class="font-semibold">{{ allocation.hostel_name || '__________' }}</span>
          hostel for the academic year {{ academicYear || '________' }}. The purpose of this letter is to provide joining
          instructions, required items and key hostel regulations that must be observed throughout the stay.
        </p>
      </section>

      <!-- Requirements -->
      <section class="mt-5 space-y-2 text-[11px]">
        <h3 class="text-[12px] font-semibold text-gray-800">1. Items / Requirements to Bring</h3>
        <p v-if="!requirements.length" class="text-[11px] text-gray-500">
          Specific hostel requirements have not been configured yet. Please consult the school for detailed information.
        </p>
        <div v-else class="space-y-2">
          <div
            v-for="req in requirements"
            :key="req.id"
            class="rounded-md border border-gray-200 bg-gray-50 px-3 py-2"
          >
            <p v-if="req.title" class="text-[11px] font-semibold text-gray-800">
              {{ req.title }}
            </p>
            <ul v-if="req.items" class="ml-4 list-disc space-y-0.5 text-[11px] text-gray-700">
              <li v-for="(line, idx) in req.items.split('\n')" :key="`ji-i-${req.id}-${idx}`" v-if="line.trim()">
                {{ line.trim() }}
              </li>
            </ul>
          </div>
        </div>
      </section>

      <!-- Hostel fees -->
      <section class="mt-5 space-y-1 text-[11px]">
        <h3 class="text-[12px] font-semibold text-gray-800">2. Hostel Fees</h3>
        <p>
          The approved hostel fee for this student is
          <span class="font-semibold">TSh {{ allocation.amount?.toLocaleString?.() ?? allocation.amount }}</span> for the
          current academic year. At the time of issuing these instructions, a total of
          <span class="font-semibold">TSh {{ allocation.paid?.toLocaleString?.() ?? allocation.paid }}</span> has been
          received, leaving an outstanding balance of
          <span class="font-semibold">TSh {{ allocation.balance?.toLocaleString?.() ?? allocation.balance }}</span>.
        </p>
        <p class="text-[10px] text-gray-600">
          All payments must be made through the official school payment channels. Receipts should be kept safely and
          produced upon request.
        </p>
      </section>

      <!-- Rules -->
      <section class="mt-5 space-y-2 text-[11px]">
        <h3 class="text-[12px] font-semibold text-gray-800">3. Key Hostel Rules and Regulations</h3>
        <p v-if="!requirements.length" class="text-[11px] text-gray-500">
          A detailed list of hostel rules will be provided upon arrival. The student is expected to observe all school and
          hostel regulations.
        </p>
        <div v-else class="space-y-2">
          <div
            v-for="req in requirements"
            :key="`rules-${req.id}`"
            class="rounded-md border border-gray-200 bg-gray-50 px-3 py-2"
          >
            <p v-if="req.title" class="text-[11px] font-semibold text-gray-800">
              {{ req.title }}
            </p>
            <ul v-if="req.rules" class="ml-4 list-disc space-y-0.5 text-[11px] text-gray-700">
              <li v-for="(line, idx) in req.rules.split('\n')" :key="`ji-r-${req.id}-${idx}`" v-if="line.trim()">
                {{ line.trim() }}
              </li>
            </ul>
          </div>
        </div>
      </section>

      <!-- Closing remarks -->
      <section class="mt-5 space-y-2 text-[11px]">
        <h3 class="text-[12px] font-semibold text-gray-800">4. General Conduct</h3>
        <p>
          The student is expected to maintain high standards of discipline, cleanliness and respect towards staff and
          fellow students while in the hostel. Failure to comply with hostel rules may lead to disciplinary measures,
          including suspension or withdrawal from hostel services.
        </p>
        <p>
          We kindly request the parent/guardian to read these instructions carefully, ensure that all required items are
          provided, and support the school in enforcing the hostel regulations.
        </p>
      </section>

      <!-- Signatures -->
      <section class="mt-8 grid gap-6 text-[10px] text-gray-700 md:grid-cols-2">
        <div>
          <p class="font-semibold">For the Parent/Guardian</p>
          <p class="mt-4">Name: __________________________</p>
          <p class="mt-4">Signature: ______________________</p>
          <p class="mt-2">Date: ____ / ____ / {{ academicYear || '____' }}</p>
        </div>
        <div>
          <p class="font-semibold">For the Head of School</p>
          <p class="mt-4">Name: __________________________</p>
          <p class="mt-4">Signature: ______________________</p>
          <p class="mt-2">Date: ____ / ____ / {{ academicYear || '____' }}</p>
        </div>
      </section>

      <section class="mt-6 flex justify-between text-[10px] text-gray-500">
        <p>Generated on: {{ new Date().toLocaleDateString() }}</p>
        <p>Reference: HOSTEL-JOINING-{{ allocation.id || '____' }}</p>
      </section>
    </div>
  </AuthenticatedLayout>
</template>
