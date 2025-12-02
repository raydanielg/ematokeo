<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    payments: {
        type: Array,
        default: () => [],
    },
});

const statusFilter = ref('All');

const filteredPayments = computed(() => {
    if (statusFilter.value === 'All') {
        return props.payments;
    }
    return props.payments.filter((p) => p.status === statusFilter.value);
});

const formatAmount = (value) => {
    if (value === null || value === undefined) return '-';
    try {
        return Number(value).toLocaleString();
    } catch (e) {
        return value;
    }
};

const showPaymentModal = ref(false);
const activeAllocation = ref(null);

const paymentForm = useForm({
    allocation_id: '',
    amount: '',
    paid_on: '',
    method: 'Cash',
    reference: '',
    notes: '',
});

const openPaymentModal = (payment) => {
    activeAllocation.value = payment;
    paymentForm.reset();
    paymentForm.allocation_id = payment.id;
    // default amount to remaining balance if any
    paymentForm.amount = payment.balance || '';
    // default date to today
    const today = new Date().toISOString().slice(0, 10);
    paymentForm.paid_on = today;
    showPaymentModal.value = true;
};

const closePaymentModal = () => {
    showPaymentModal.value = false;
    activeAllocation.value = null;
};

const submitPayment = () => {
    paymentForm.post(route('hostel-payments.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showPaymentModal.value = false;
            activeAllocation.value = null;
        },
    });
};

const projectedBalance = computed(() => {
    if (!activeAllocation.value) return null;
    const baseBalance = Number(activeAllocation.value.balance || 0);
    const add = Number(paymentForm.amount || 0);
    const next = baseBalance - add;
    return isNaN(next) ? null : Math.max(next, 0);
});
</script>

<template>
    <Head title="Hostel Payments" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Hostel Payments
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Track hostel fee payments and balances for boarding students.
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <div
                v-if="$page.props.flash && $page.props.flash.success"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[11px] text-emerald-900"
            >
                <div class="flex items-start gap-2">
                    <span class="mt-0.5 text-emerald-600">✔</span>
                    <div>
                        <p class="font-semibold">Saved</p>
                        <p class="text-[11px] text-emerald-800">{{ $page.props.flash.success }}</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-600">
                <div class="flex items-center gap-2">
                    <label class="text-[11px] font-medium text-gray-600">Filter by status</label>
                    <select
                        v-model="statusFilter"
                        class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-[11px] text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                    >
                        <option value="All">All</option>
                        <option value="Paid">Paid</option>
                        <option value="Partial">Partial</option>
                        <option value="Unpaid">Unpaid</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <table class="min-w-full border-collapse text-left text-[11px]">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-2">#</th>
                            <th class="px-3 py-2">Student</th>
                            <th class="px-3 py-2">Class</th>
                            <th class="px-3 py-2">Hostel</th>
                            <th class="px-3 py-2 text-right">Amount</th>
                            <th class="px-3 py-2 text-right">Paid</th>
                            <th class="px-3 py-2 text-right">Balance</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredPayments.length">
                            <td colspan="9" class="px-3 py-6 text-center text-[11px] text-gray-400">
                                No hostel payments recorded yet.
                            </td>
                        </tr>
                        <tr
                            v-for="(payment, index) in filteredPayments"
                            :key="payment.id || index"
                            class="border-b border-gray-100 text-[11px] text-gray-700 hover:bg-gray-50"
                        >
                            <td class="px-3 py-2 align-top">{{ index + 1 }}</td>
                            <td class="px-3 py-2 align-top">{{ payment.student_name || '-' }}</td>
                            <td class="px-3 py-2 align-top">{{ payment.class_name || '-' }}</td>
                            <td class="px-3 py-2 align-top">{{ payment.hostel_name || '-' }}</td>
                            <td class="px-3 py-2 align-top text-right">TSh {{ formatAmount(payment.amount) }}</td>
                            <td class="px-3 py-2 align-top text-right">TSh {{ formatAmount(payment.paid) }}</td>
                            <td class="px-3 py-2 align-top text-right">TSh {{ formatAmount(payment.balance) }}</td>
                            <td class="px-3 py-2 align-top">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="{
                                    'bg-emerald-50 text-emerald-700': payment.status === 'Paid',
                                    'bg-amber-50 text-amber-700': payment.status === 'Partial',
                                    'bg-rose-50 text-rose-700': !payment.status || payment.status === 'Unpaid',
                                }">
                                    {{ payment.status || 'Unpaid' }}
                                </span>
                            </td>
                            <td class="px-3 py-2 align-top text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md bg-white px-2 py-1 text-[10px] font-semibold text-emerald-700 ring-1 ring-emerald-200 hover:bg-emerald-50"
                                        @click="openPaymentModal(payment)"
                                    >
                                        Add payment
                                    </button>
                                    <Link
                                        :href="route('hostel-payments.receipt', payment.id)"
                                        class="inline-flex items-center rounded-md bg-white px-2 py-1 text-[10px] font-semibold text-indigo-600 ring-1 ring-indigo-200 hover:bg-indigo-50"
                                    >
                                        Receipt
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add payment modal -->
        <div
            v-if="showPaymentModal && activeAllocation"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6 backdrop-blur-sm"
        >
            <div class="w-full max-w-md rounded-2xl bg-white p-5 text-xs text-gray-700 shadow-2xl">
                <div class="mb-3 flex items-start justify-between gap-4 border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-emerald-800">
                            Record Hostel Payment
                        </h3>
                        <p class="mt-1 text-[11px] text-gray-500">
                            Enter payment details for this student. Status and balances will update automatically.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-[11px] font-bold text-gray-600 hover:bg-gray-200"
                        @click="closePaymentModal"
                    >
                        ×
                    </button>
                </div>

                <div class="mb-3 rounded-md bg-gray-50 px-3 py-2 text-[11px] text-gray-700">
                    <p class="font-semibold text-gray-800">{{ activeAllocation.student_name || '-' }}</p>
                    <p class="text-[10px] text-gray-500">
                        Hostel: {{ activeAllocation.hostel_name || '-' }}
                    </p>
                    <p class="mt-1 text-[10px] text-gray-600">
                        Total: <span class="font-semibold">TSh {{ formatAmount(activeAllocation.amount) }}</span>
                        &nbsp;|&nbsp; Paid:
                        <span class="font-semibold">TSh {{ formatAmount(activeAllocation.paid) }}</span>
                        &nbsp;|&nbsp; Balance:
                        <span class="font-semibold">TSh {{ formatAmount(activeAllocation.balance) }}</span>
                    </p>
                    <p v-if="projectedBalance !== null" class="mt-1 text-[10px] text-emerald-700">
                        After this payment, new balance will be:
                        <span class="font-semibold">TSh {{ formatAmount(projectedBalance) }}</span>
                    </p>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <label class="text-[11px] font-medium text-gray-600">Amount (TSh)</label>
                        <input
                            v-model.number="paymentForm.amount"
                            type="number"
                            min="1"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        />
                    </div>
                    <div>
                        <label class="text-[11px] font-medium text-gray-600">Date paid</label>
                        <input
                            v-model="paymentForm.paid_on"
                            type="date"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        />
                    </div>
                </div>

                <div class="mt-3 grid gap-3 md:grid-cols-2">
                    <div>
                        <label class="text-[11px] font-medium text-gray-600">Method</label>
                        <select
                            v-model="paymentForm.method"
                            class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        >
                            <option value="Cash">Cash</option>
                            <option value="Bank">Bank</option>
                            <option value="Mobile Money">Mobile Money</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[11px] font-medium text-gray-600">Reference (optional)</label>
                        <input
                            v-model="paymentForm.reference"
                            type="text"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            placeholder="Receipt no. / Tx ID"
                        />
                    </div>
                </div>

                <div class="mt-3">
                    <label class="text-[11px] font-medium text-gray-600">Notes (optional)</label>
                    <textarea
                        v-model="paymentForm.notes"
                        rows="2"
                        class="mt-1 w-full resize-none rounded-md border border-gray-300 px-3 py-1.5 text-[11px] text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                        placeholder="Any additional details about this payment."
                    ></textarea>
                </div>

                <div class="mt-4 flex justify-end gap-2 text-[11px]">
                    <button
                        type="button"
                        class="rounded-md bg-gray-50 px-3 py-1.5 font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100"
                        @click="closePaymentModal"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-emerald-600 px-3 py-1.5 font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="
                            paymentForm.processing ||
                            !paymentForm.amount ||
                            !paymentForm.paid_on ||
                            !paymentForm.method
                        "
                        @click="submitPayment"
                    >
                        Save payment
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
