<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    timetables: {
        type: Array,
        default: () => [],
    },
});

const previewTimetable = (id) => {
    router.get(route('timetables.show', id));
};

const showPreviewModal = ref(false);
const previewItem = ref(null);

const openPreviewModal = (timetable) => {
    previewItem.value = timetable || null;
    showPreviewModal.value = true;
};

const deleteTimetable = (id) => {
    if (!confirm('Delete this timetable?')) return;

    router.delete(route('timetables.destroy', id));
};

const togglePublish = (id) => {
    router.post(route('timetables.publish', id));
};

const formatDateTime = (value) => {
    if (!value) return '-';
    const dt = new Date(value);
    if (Number.isNaN(dt.getTime())) return String(value);
    return dt.toLocaleString();
};

const hasSchedule = (t) => {
    const s = t?.schedule_json;
    if (!s) return false;
    if (Array.isArray(s)) return s.length > 0;
    if (typeof s === 'object') return Object.keys(s).length > 0;
    return false;
};

const days = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY'];
const slotLabels = ['P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7', 'P8', 'P9'];

const normalizeSchedule = (rawSchedule) => {
    try {
        let data = rawSchedule;
        if (!data) return {};

        if (typeof data === 'string') {
            const s = String(data).trim();
            if (!s) return {};
            data = JSON.parse(s);
        }

        if (!data || typeof data !== 'object') return {};

        const out = {};
        Object.keys(data).forEach((day) => {
            const rows = Array.isArray(data[day]) ? data[day] : [];
            out[day] = rows.map((row) => {
                const slots = Array.isArray(row?.slots) ? row.slots : [];
                const normalizedSlots = Array.from({ length: 9 }).map((_, i) => {
                    const slot = slots[i];
                    if (slot === null || slot === undefined) return null;

                    if (typeof slot === 'string') {
                        const val = String(slot || '').trim();
                        if (!val) return null;
                        return { subject: val, teacher_initials: '' };
                    }

                    if (typeof slot === 'object') {
                        const subject = String(slot?.subject ?? '').trim();
                        const teacherInitials = String(slot?.teacher_initials ?? slot?.teacher ?? '').trim();
                        if (!subject && !teacherInitials) return null;
                        return { subject, teacher_initials: teacherInitials };
                    }

                    return null;
                });

                return {
                    ...(row || {}),
                    slots: normalizedSlots,
                };
            });
        });

        return out;
    } catch {
        return {};
    }
};

const escapeHtml = (value) => {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
};

const colourForSubject = (subjectCode) => {
    const code = String(subjectCode || '').trim().toUpperCase();
    if (!code) return { bg: '#ffffff', fg: '#111827' };
    const palette = [
        { bg: '#ECFDF5', fg: '#065F46' },
        { bg: '#EFF6FF', fg: '#1D4ED8' },
        { bg: '#FEF3C7', fg: '#92400E' },
        { bg: '#FCE7F3', fg: '#9D174D' },
        { bg: '#EDE9FE', fg: '#5B21B6' },
        { bg: '#FFE4E6', fg: '#9F1239' },
        { bg: '#F1F5F9', fg: '#0F172A' },
        { bg: '#E0F2FE', fg: '#075985' },
    ];
    let hash = 0;
    for (let i = 0; i < code.length; i += 1) {
        hash = ((hash << 5) - hash) + code.charCodeAt(i);
        hash |= 0;
    }
    const idx = Math.abs(hash) % palette.length;
    return palette[idx];
};

const buildTimetableExcelHtml = (timetable) => {
    const schedule = normalizeSchedule(timetable?.schedule_json);
    const title = String(timetable?.title || 'Timetable').trim() || 'Timetable';
    const className = String(timetable?.class_name || '').trim();
    const academicYear = String(timetable?.academic_year || '').trim();
    const term = String(timetable?.term || '').trim();

    let html = `<!DOCTYPE html><html><head><meta charset="utf-8" />`;
    html += `<style>`;
    html += `table{border-collapse:collapse;font-family:Calibri,Arial,sans-serif;font-size:11px;}`;
    html += `th,td{border:1px solid #CBD5E1;padding:4px 6px;vertical-align:middle;}`;
    html += `th{background:#F1F5F9;font-weight:700;text-align:center;}`;
    html += `.meta{border:0;padding:2px 0;}`;
    html += `.meta strong{font-weight:700;}`;
    html += `</style></head><body>`;
    html += `<div class="meta"><strong>${escapeHtml(title)}</strong></div>`;
    html += `<div class="meta">${escapeHtml([className, academicYear, term].filter(Boolean).join(' · '))}</div>`;
    html += `<br/>`;
    html += `<table>`;
    html += `<thead><tr>`;
    html += `<th>DAY</th><th>CLASS</th><th>STREAM</th>`;
    slotLabels.forEach((l) => {
        html += `<th>${escapeHtml(l)}</th>`;
    });
    html += `</tr></thead><tbody>`;

    days.forEach((d) => {
        const rows = Array.isArray(schedule?.[d]) ? schedule[d] : [];
        if (!rows.length) {
            html += `<tr><td>${escapeHtml(d)}</td><td></td><td></td>`;
            slotLabels.forEach(() => { html += `<td></td>`; });
            html += `</tr>`;
            return;
        }

        rows.forEach((row) => {
            const classLabel = String(row?.class_label || row?.form || row?.class_name || '').trim();
            const stream = String(row?.stream || '').trim();
            const slots = Array.isArray(row?.slots) ? row.slots : [];

            html += `<tr>`;
            html += `<td>${escapeHtml(d)}</td>`;
            html += `<td>${escapeHtml(classLabel)}</td>`;
            html += `<td>${escapeHtml(stream)}</td>`;

            for (let i = 0; i < 9; i += 1) {
                const cell = slots[i];
                const subject = String(cell?.subject || '').trim();
                const teacher = String(cell?.teacher_initials || '').trim();
                const label = [subject, teacher].filter(Boolean).join(' ');
                const c = colourForSubject(subject);
                html += `<td style="background:${c.bg};color:${c.fg};text-align:center;">${escapeHtml(label)}</td>`;
            }
            html += `</tr>`;
        });
    });

    html += `</tbody></table></body></html>`;
    return html;
};

const downloadPreviewExcel = (timetable) => {
    const tt = timetable || null;
    if (!tt) return;
    if (!tt.schedule_json || !hasSchedule(tt)) return;

    const html = buildTimetableExcelHtml(tt);
    const blob = new Blob([html], { type: 'application/vnd.ms-excel;charset=utf-8' });
    const url = URL.createObjectURL(blob);

    const base = String(tt?.title || 'timetable').trim() || 'timetable';
    const safe = base.replace(/[\\/:*?"<>|]+/g, '-');
    const fileName = `${safe}_${tt?.id || ''}.xls`;

    const a = document.createElement('a');
    a.href = url;
    a.download = fileName;
    document.body.appendChild(a);
    a.click();
    a.remove();

    setTimeout(() => URL.revokeObjectURL(url), 5000);
};
</script>

<template>
    <Head title="Timetables" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Timetables
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        View and manage class timetables. You can preview, download or delete a timetable from the list below.
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                    @click="router.get(route('timetables.create'))"
                >
                    Create Timetable
                </button>
            </div>
        </template>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <div class="mb-3 flex items-center justify-between gap-3">
                <p class="text-sm font-medium text-gray-700">
                    Timetables ({{ timetables.length }})
                </p>
                <p class="text-[11px] text-gray-500">
                    Hizi ni ratiba ulizohifadhi (kwa madarasa au shule). Unaweza ku-preview, ku-print au kufuta.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-2">Title</th>
                            <th class="px-3 py-2">Class</th>
                            <th class="px-3 py-2">Year / Term</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2">Created</th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="timetables.length === 0">
                            <td colspan="6" class="px-3 py-4 text-center text-xs text-gray-500">
                                No timetables created yet.
                            </td>
                        </tr>
                        <tr
                            v-for="timetable in timetables"
                            :key="timetable.id"
                            class="border-b border-gray-100 text-xs text-gray-700 hover:bg-gray-50"
                        >
                            <td class="px-3 py-2 align-top">
                                {{ timetable.title }}
                            </td>
                            <td class="px-3 py-2 align-top">
                                {{ timetable.class_name || '-' }}
                            </td>
                            <td class="px-3 py-2 align-top text-xs text-gray-600">
                                <span v-if="timetable.academic_year">
                                    {{ timetable.academic_year }}
                                </span>
                                <span v-if="timetable.term">
                                    • {{ timetable.term }}
                                </span>
                                <span v-if="!timetable.academic_year && !timetable.term">-</span>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex flex-wrap items-center gap-1">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1"
                                        :class="hasSchedule(timetable)
                                            ? 'bg-emerald-50 text-emerald-700 ring-emerald-100'
                                            : 'bg-amber-50 text-amber-800 ring-amber-100'"
                                    >
                                        {{ hasSchedule(timetable) ? 'Saved' : 'No schedule' }}
                                    </span>
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1"
                                        :class="timetable.is_published
                                            ? 'bg-violet-50 text-violet-700 ring-violet-100'
                                            : 'bg-gray-50 text-gray-600 ring-gray-100'"
                                    >
                                        {{ timetable.is_published ? 'Published' : 'Draft' }}
                                    </span>
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1"
                                        :class="timetable.file_path
                                            ? 'bg-blue-50 text-blue-700 ring-blue-100'
                                            : 'bg-gray-50 text-gray-500 ring-gray-100'"
                                    >
                                        {{ timetable.file_path ? 'PDF' : 'No PDF' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-3 py-2 align-top text-xs text-gray-600">
                                {{ formatDateTime(timetable.created_at) }}
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        class="rounded-md bg-blue-50 px-2 py-1 text-[11px] font-medium text-blue-700 ring-1 ring-blue-100 hover:bg-blue-100"
                                        @click="openPreviewModal(timetable)"
                                    >
                                        Preview
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-md bg-violet-50 px-2 py-1 text-[11px] font-medium text-violet-700 ring-1 ring-violet-100 hover:bg-violet-100"
                                        @click="togglePublish(timetable.id)"
                                    >
                                        {{ timetable.is_published ? 'Unpublish' : 'Publish' }}
                                    </button>
                                    <button
                                        type="button"
                                        :disabled="!timetable.file_path"
                                        :class="[
                                            'rounded-md px-2 py-1 text-[11px] font-medium ring-1',
                                            timetable.file_path
                                                ? 'bg-emerald-50 text-emerald-700 ring-emerald-100 hover:bg-emerald-100'
                                                : 'bg-gray-50 text-gray-400 ring-gray-100 cursor-not-allowed',
                                        ]"
                                        @click="timetable.file_path && window.open(route('timetables.download', timetable.id), '_blank')"
                                    >
                                        Download
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-md bg-red-50 px-2 py-1 text-[11px] font-medium text-red-700 ring-1 ring-red-100 hover:bg-red-100"
                                        @click="deleteTimetable(timetable.id)"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div
                v-if="showPreviewModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6"
                @click.self="showPreviewModal = false"
            >
                <div class="flex h-[85vh] w-full max-w-5xl flex-col overflow-hidden rounded-xl bg-white shadow-xl">
                    <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold text-gray-800">
                                {{ previewItem?.title || 'Timetable Preview' }}
                            </div>
                            <div class="mt-0.5 text-[11px] text-gray-500">
                                {{ previewItem?.class_name || '-' }}
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                :disabled="!hasSchedule(previewItem)"
                                :class="[
                                    'rounded-md px-2 py-1 text-[11px] font-medium ring-1',
                                    hasSchedule(previewItem)
                                        ? 'bg-blue-50 text-blue-700 ring-blue-100 hover:bg-blue-100'
                                        : 'bg-gray-50 text-gray-400 ring-gray-100 cursor-not-allowed',
                                ]"
                                @click="hasSchedule(previewItem) && downloadPreviewExcel(previewItem)"
                            >
                                Download Excel
                            </button>
                            <button
                                type="button"
                                class="rounded-md bg-emerald-50 px-2 py-1 text-[11px] font-medium text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100"
                                @click="previewItem?.id && router.get(route('timetables.show', previewItem.id))"
                            >
                                Edit
                            </button>
                            <button
                                type="button"
                                class="rounded-md bg-gray-50 px-2 py-1 text-[11px] font-medium text-gray-700 ring-1 ring-gray-100 hover:bg-gray-100"
                                @click="showPreviewModal = false"
                            >
                                Close
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 bg-gray-50">
                        <div v-if="!previewItem" class="p-6 text-center text-xs text-gray-500">
                            No timetable selected.
                        </div>
                        <div v-else-if="!previewItem.file_path" class="p-6 text-center text-xs text-gray-600">
                            This timetable has no PDF yet. Use Export to generate a PDF.
                        </div>
                        <iframe
                            v-else
                            :src="route('timetables.preview', previewItem.id)"
                            class="h-full w-full"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
