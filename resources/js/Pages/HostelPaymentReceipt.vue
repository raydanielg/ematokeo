<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  allocation: {
    type: Object,
    default: () => ({}),
  },
  payments: {
    type: Array,
    default: () => [],
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
  <Head title="Hostel Payment Receipt" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h2 class="text-2xl font-semibold leading-tight text-gray-800">Hostel Payment Receipt</h2>
          <p class="mt-1 text-sm text-gray-500">
            Receipt for hostel fee payments for {{ allocation.student_name || 'Student' }}.
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs">
          <Link
            :href="route('hostel-payments.index')"
            class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-[11px] font-semibold text-gray-700 shadow-sm ring-1 ring-gray-200 hover:bg-gray-50"
          >
            ← Back to payments
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

    <div
      class="mx-auto max-w-3xl rounded-xl bg-white p-6 text-xs text-gray-800 shadow-sm ring-1 ring-gray-100 print:shadow-none print:ring-0"
    >
      <!-- Header similar to contract -->
      <section class="border-b border-gray-200 pb-3 text-center">
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
        <p class="mt-1 text-[11px] font-semibold text-gray-800">HOSTEL FEE PAYMENT RECEIPT</p>
        <p class="text-[10px] text-gray-600">Academic Year {{ academicYear || '________' }}</p>
      </section>

      <!-- Student & hostel details -->
      <section class="mt-4 grid gap-3 md:grid-cols-2 text-[11px]">
        <div class="space-y-1">
          <p><span class="font-semibold">Student:</span> {{ allocation.student_name || '-' }}</p>
          <p><span class="font-semibold">Class:</span> {{ allocation.class_name || '-' }}</p>
          <p><span class="font-semibold">Hostel:</span> {{ allocation.hostel_name || '-' }}</p>
        </div>
        <div class="space-y-1 text-right md:text-left">
          <p>
            <span class="font-semibold">Total Fee:</span>
            <span class="ml-1">TSh {{ allocation.amount?.toLocaleString?.() ?? allocation.amount }}</span>
          </p>
          <p>
            <span class="font-semibold">Total Paid:</span>
            <span class="ml-1">TSh {{ allocation.paid?.toLocaleString?.() ?? allocation.paid }}</span>
          </p>
          <p>
            <span class="font-semibold">Balance:</span>
            <span class="ml-1">TSh {{ allocation.balance?.toLocaleString?.() ?? allocation.balance }}</span>
          </p>
          <p>
            <span class="font-semibold">Status:</span>
            <span class="ml-1">{{ allocation.status || 'Unpaid' }}</span>
          </p>
        </div>
      </section>

      <!-- Payments table -->
      <section class="mt-4">
        <table class="min-w-full border-collapse text-left text-[11px]">
          <thead>
            <tr class="border-b border-gray-200 bg-gray-50 text-[11px] font-semibold uppercase tracking-wide text-gray-500">
              <th class="px-3 py-2">#</th>
              <th class="px-3 py-2">Date</th>
              <th class="px-3 py-2">Method</th>
              <th class="px-3 py-2">Reference</th>
              <th class="px-3 py-2 text-right">Amount (TSh)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!payments.length">
              <td colspan="5" class="px-3 py-5 text-center text-[11px] text-gray-400">
                No payments have been recorded for this allocation yet.
              </td>
            </tr>
            <tr
              v-for="(p, index) in payments"
              :key="p.id || index"
              class="border-b border-gray-100 text-[11px] text-gray-700"
            >
              <td class="px-3 py-1.5 align-top">{{ index + 1 }}</td>
              <td class="px-3 py-1.5 align-top">{{ p.paid_on || '-' }}</td>
              <td class="px-3 py-1.5 align-top">{{ p.method || '-' }}</td>
              <td class="px-3 py-1.5 align-top">{{ p.reference || '-' }}</td>
              <td class="px-3 py-1.5 align-top text-right">
                {{ p.amount?.toLocaleString?.() ?? p.amount }}
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <!-- Footer note -->
      <section class="mt-6 flex justify-between text-[10px] text-gray-500">
        <p>Generated on: {{ new Date().toLocaleDateString() }}</p>
        <p>Signature (Bursar/Matron): ___________________________</p>
      </section>
    </div>
  </AuthenticatedLayout>
</template>
