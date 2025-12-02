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
  exams: {
    type: Array,
    default: () => [],
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
  <Head title="Hostel Student Report" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h2 class="text-2xl font-semibold leading-tight text-gray-800">Hostel Student Report</h2>
          <p class="mt-1 text-sm text-gray-500">
            Summary of hostel allocation, guardian details and payments for {{ allocation.student_name || 'student' }}.
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs">
          <Link
            :href="route('hostel-students.index')"
            class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-[11px] font-semibold text-gray-700 shadow-sm ring-1 ring-gray-200 hover:bg-gray-50"
          >
            ← Back to hostel students
          </Link>
          <Link
            v-if="allocation.id"
            :href="route('hostel-payments.receipt', allocation.id)"
            class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-[11px] font-semibold text-indigo-600 shadow-sm ring-1 ring-indigo-200 hover:bg-indigo-50"
          >
            View receipt
          </Link>
          <button
            type="button"
            class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700"
            @click="onPrint"
          >
            Print report
          </button>
        </div>
      </div>
    </template>

    <div
      class="mx-auto max-w-3xl rounded-xl bg-white p-6 text-xs text-gray-800 shadow-sm ring-1 ring-gray-100 print:shadow-none print:ring-0"
    >
      <!-- Header similar to other hostel docs -->
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
        <p class="mt-1 text-[11px] font-semibold text-gray-800">HOSTEL STUDENT SUMMARY REPORT</p>
        <p class="text-[10px] text-gray-600">Academic Year {{ academicYear || '________' }}</p>
      </section>

      <!-- Student & guardian details -->
      <section class="mt-4 grid gap-3 md:grid-cols-2 text-[11px]">
        <div class="space-y-1">
          <p><span class="font-semibold">Student:</span> {{ allocation.student_name || '-' }}</p>
          <p><span class="font-semibold">Exam No.:</span> {{ allocation.exam_number || '-' }}</p>
          <p><span class="font-semibold">Class:</span> {{ allocation.class_name || '-' }}</p>
          <p><span class="font-semibold">Gender:</span> {{ allocation.gender || '-' }}</p>
        </div>
        <div class="space-y-1">
          <p><span class="font-semibold">Hostel:</span> {{ allocation.hostel_name || '-' }}</p>
          <p><span class="font-semibold">Room:</span> {{ allocation.room_name || '-' }}</p>
          <p><span class="font-semibold">Status:</span> {{ allocation.status || 'Unpaid' }}</p>
        </div>
      </section>

      <section class="mt-4 grid gap-3 md:grid-cols-2 text-[11px]">
        <div class="space-y-1">
          <p><span class="font-semibold">Guardian name:</span> {{ allocation.guardian_name || '-' }}</p>
          <p><span class="font-semibold">Guardian phone:</span> {{ allocation.guardian_phone || '-' }}</p>
        </div>
        <div class="space-y-1">
          <p><span class="font-semibold">Relationship:</span> {{ allocation.guardian_relationship || '-' }}</p>
          <p><span class="font-semibold">Notes:</span> {{ allocation.notes || '-' }}</p>
        </div>
      </section>

      <!-- Fee summary -->
      <section class="mt-5 space-y-1 text-[11px]">
        <h3 class="text-[12px] font-semibold text-gray-800">Hostel Fee Summary</h3>
        <p>
          <span class="font-semibold">Total hostel fee:</span>
          <span class="ml-1">TSh {{ allocation.amount?.toLocaleString?.() ?? allocation.amount }}</span>
        </p>
        <p>
          <span class="font-semibold">Total paid:</span>
          <span class="ml-1">TSh {{ allocation.paid?.toLocaleString?.() ?? allocation.paid }}</span>
        </p>
        <p>
          <span class="font-semibold">Outstanding balance:</span>
          <span class="ml-1">TSh {{ allocation.balance?.toLocaleString?.() ?? allocation.balance }}</span>
        </p>
      </section>

      <!-- Exam results summary -->
      <section class="mt-6 space-y-2 text-[11px]">
        <h3 class="text-[12px] font-semibold text-gray-800">Exam Results (Current Academic Year)</h3>
        <p v-if="!exams.length" class="text-[11px] text-gray-500">
          No exam results have been recorded for this student in the current academic year.
        </p>
        <div v-else class="overflow-x-auto rounded-md border border-gray-200">
          <table class="min-w-full border-collapse text-left text-[11px]">
            <thead>
              <tr class="border-b border-gray-200 bg-gray-50 text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                <th class="px-3 py-1.5">Exam</th>
                <th class="px-3 py-1.5">Subject</th>
                <th class="px-3 py-1.5 text-right">Marks</th>
                <th class="px-3 py-1.5">Grade</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(row, index) in exams"
                :key="index"
                class="border-b border-gray-100 text-[11px] text-gray-700"
              >
                <td class="px-3 py-1.5 align-top">
                  <div class="font-semibold text-gray-800">{{ row.exam_name || '-' }}</div>
                  <div class="text-[10px] text-gray-500">{{ row.exam_type || '' }}</div>
                </td>
                <td class="px-3 py-1.5 align-top">
                  {{ row.subject_name || row.subject_code || '-' }}
                </td>
                <td class="px-3 py-1.5 align-top text-right">
                  {{ row.marks === null || row.marks === undefined ? '-' : row.marks }}
                </td>
                <td class="px-3 py-1.5 align-top">
                  {{ row.grade || '-' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Simple rules / notes placeholder -->
      <section class="mt-5 space-y-1 text-[11px]">
        <h3 class="text-[12px] font-semibold text-gray-800">Hostel Notes & Regulations (Summary)</h3>
        <p v-if="!requirements.length" class="text-gray-500">
          No specific hostel requirements or rules have been configured yet for this student. You can define templates in
          the hostel requirements table.
        </p>
        <div v-else class="space-y-3">
          <div
            v-for="req in requirements"
            :key="req.id"
            class="rounded-md border border-gray-200 bg-gray-50 px-3 py-2"
          >
            <p v-if="req.title" class="text-[11px] font-semibold text-gray-800">
              {{ req.title }}
            </p>
            <div v-if="req.items" class="mt-1">
              <p class="text-[11px] font-semibold text-gray-700">Items / Requirements:</p>
              <ul class="ml-4 list-disc space-y-0.5 text-[11px] text-gray-700">
                <li v-for="(line, idx) in req.items.split('\n')" :key="`i-${req.id}-${idx}`" v-if="line.trim()">
                  {{ line.trim() }}
                </li>
              </ul>
            </div>
            <div v-if="req.rules" class="mt-1">
              <p class="text-[11px] font-semibold text-gray-700">Rules / Notes:</p>
              <ul class="ml-4 list-disc space-y-0.5 text-[11px] text-gray-700">
                <li v-for="(line, idx) in req.rules.split('\n')" :key="`r-${req.id}-${idx}`" v-if="line.trim()">
                  {{ line.trim() }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      <!-- Comments -->
      <section class="mt-6 space-y-1 text-[11px]">
        <h3 class="text-[12px] font-semibold text-gray-800">Comments</h3>
        <p class="text-[10px] text-gray-500">
          This area can be used by the Matron/Patron or Head of School to write overall comments about the student's
          conduct and performance in the hostel.
        </p>
        <div class="mt-1 min-h-[60px] rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-left text-[11px] text-gray-700">
          <!-- Left intentionally blank for handwritten comments after printing -->
        </div>
      </section>

      <section class="mt-8 grid gap-6 text-[10px] text-gray-700 md:grid-cols-2">
        <div>
          <p class="font-semibold">Matron/Patron</p>
          <p class="mt-4">Name: __________________________</p>
          <p class="mt-4">Signature: ______________________</p>
          <p class="mt-2">Date: ____ / ____ / {{ academicYear || '____' }}</p>
        </div>
        <div>
          <p class="font-semibold">Head of School</p>
          <p class="mt-4">Name: __________________________</p>
          <p class="mt-4">Signature: ______________________</p>
          <p class="mt-2">Date: ____ / ____ / {{ academicYear || '____' }}</p>
        </div>
      </section>
    </div>
  </AuthenticatedLayout>
</template>
