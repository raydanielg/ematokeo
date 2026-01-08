<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
	stats: {
		type: Object,
		default: () => ({}),
	},
	availableYears: {
		type: Array,
		default: () => [],
	},
	upcomingEvents: {
		type: Array,
		default: () => [],
	},
	recentAnnouncements: {
		type: Array,
		default: () => [],
	},
	resultsSummary: {
		type: Object,
		default: () => ({}),
	},
	topStudents: {
		type: Array,
		default: () => [],
	},
	bottomStudents: {
		type: Array,
		default: () => [],
	},
});

const page = usePage();
const currentYear = computed(() => {
	const y = page?.props?.ziggy?.query?.year;
	const n = y ? Number(y) : null;
	return Number.isFinite(n) && n > 0 ? n : (props.resultsSummary?.year || null);
});

const onChangeYear = (e) => {
	const y = Number(e.target.value);
	router.get(route('dashboard'), { year: Number.isFinite(y) && y > 0 ? y : undefined }, { preserveScroll: true });
};
</script>

<template>
	<Head title="E-Matokeo Dashboard" />

	<AuthenticatedLayout>
		<template #header>
			<div class="flex items-center justify-between">
				<div>
					<h2 class="text-2xl font-semibold leading-tight text-gray-800">
						Overview
					</h2>
					<p class="mt-1 text-sm text-gray-500">
						Summary of key metrics for the current academic year.
					</p>
				</div>
				<div class="flex items-center gap-2">
					<label class="text-xs font-medium uppercase tracking-wide text-gray-500">
						Year
					</label>
					<select
						class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
						:disabled="!props.availableYears || !props.availableYears.length"
						:value="currentYear || ''"
						@change="onChangeYear"
					>
						<option value="" v-if="!props.availableYears || !props.availableYears.length">No results</option>
						<option v-for="y in props.availableYears" :key="y" :value="y">{{ y }}</option>
					</select>
				</div>
			</div>
		</template>

		<div class="space-y-6">
			<!-- Top summary cards -->
			<div class="grid gap-4 md:grid-cols-3 lg:grid-cols-5">
				<div class="flex items-center justify-between rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-100">
					<div>
						<p class="text-xs font-medium text-gray-500">Total Students</p>
						<p class="mt-1 text-2xl font-semibold text-gray-900">{{ props.stats.students ?? 0 }}</p>
					</div>
					<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
						<i class="material-icons text-2xl">school</i>
					</div>
				</div>

				<div class="flex items-center justify-between rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-100">
					<div>
						<p class="text-xs font-medium text-gray-500">Total Teachers</p>
						<p class="mt-1 text-2xl font-semibold text-gray-900">{{ props.stats.teachers ?? 0 }}</p>
					</div>
					<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
						<i class="material-icons text-2xl">person_outline</i>
					</div>
				</div>

				<div class="flex items-center justify-between rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-100">
					<div>
						<p class="text-xs font-medium text-gray-500">Total Subjects</p>
						<p class="mt-1 text-2xl font-semibold text-gray-900">{{ props.stats.subjects ?? 0 }}</p>
					</div>
					<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
						<i class="material-icons text-2xl">library_books</i>
					</div>
				</div>

				<div class="flex items-center justify-between rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-100">
					<div>
						<p class="text-xs font-medium text-gray-500">Total Classes</p>
						<p class="mt-1 text-2xl font-semibold text-gray-900">{{ props.stats.classes ?? 0 }}</p>
					</div>
					<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
						<i class="material-icons text-2xl">domain</i>
					</div>
				</div>

				<div class="flex items-center justify-between rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-100">
					<div>
						<p class="text-xs font-medium text-gray-500">Total Parents</p>
						<p class="mt-1 text-2xl font-semibold text-gray-900">{{ props.stats.parents ?? 0 }}</p>
					</div>
					<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
						<i class="material-icons text-2xl">family_restroom</i>
					</div>
				</div>
			</div>

			<!-- Analytics area -->
			<div class="grid gap-4 lg:grid-cols-3">
				<!-- Main results analytics card -->
				<div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100 lg:col-span-2">
					<div class="flex items-center justify-between">
						<div>
							<h3 class="text-sm font-semibold text-gray-800">Results Analytics</h3>
							<p class="text-xs text-gray-500">
								Year
								<span v-if="props.resultsSummary && props.resultsSummary.year" class="font-semibold text-gray-700">
									{{ props.resultsSummary.year }}
								</span>
								<span v-else class="italic">No results yet</span>
							</p>
						</div>
						<div class="flex items-center gap-3 text-xs" v-if="props.resultsSummary && props.resultsSummary.total">
							<div class="flex items-center gap-1 text-emerald-600">
								<span class="h-2 w-2 rounded-full bg-emerald-500"></span>
								<span>Pass: {{ props.resultsSummary.passRate }}%</span>
							</div>
							<div class="flex items-center gap-1 text-rose-600">
								<span class="h-2 w-2 rounded-full bg-rose-500"></span>
								<span>Fail: {{ props.resultsSummary.failRate }}%</span>
							</div>
							<div class="text-[11px] text-gray-500">
								Total candidates: <span class="font-semibold text-gray-700">{{ props.resultsSummary.total }}</span>
							</div>
						</div>
					</div>

					<div class="mt-4 grid gap-4 md:grid-cols-3">
						<!-- Pass / Fail bar -->
						<div class="flex flex-col justify-between rounded-lg bg-slate-50 p-4 md:col-span-1">
							<p class="text-xs font-semibold text-gray-700">Pass / Fail</p>
							<div v-if="props.resultsSummary && props.resultsSummary.total" class="mt-3 space-y-2 text-[11px] text-gray-700">
								<div class="flex items-center justify-between">
									<span class="text-emerald-600 font-medium">Pass</span>
									<span>{{ props.resultsSummary.pass }} ({{ props.resultsSummary.passRate }}%)</span>
								</div>
								<div class="h-2 w-full overflow-hidden rounded-full bg-emerald-100">
									<div
										class="h-2 rounded-full bg-emerald-500 transition-all"
										:style="{ width: `${props.resultsSummary.passRate}%` }"
									></div>
								</div>
								<div class="mt-3 flex items-center justify-between">
									<span class="text-rose-600 font-medium">Fail</span>
									<span>{{ props.resultsSummary.fail }} ({{ props.resultsSummary.failRate }}%)</span>
								</div>
								<div class="h-2 w-full overflow-hidden rounded-full bg-rose-100">
									<div
										class="h-2 rounded-full bg-rose-500 transition-all"
										:style="{ width: `${props.resultsSummary.failRate}%` }"
									></div>
								</div>
							</div>
							<p v-else class="mt-3 text-xs text-gray-500">No results data yet for this year.</p>
						</div>

						<!-- Grade distribution -->
						<div class="space-y-4 md:col-span-2">
							<div class="rounded-lg border border-dashed border-gray-200 p-3">
								<h4 class="text-xs font-semibold text-gray-700">Grade distribution (A–F)</h4>
								<div v-if="props.resultsSummary && props.resultsSummary.total" class="mt-2 space-y-1 text-[11px] text-gray-700">
									<div v-for="grade in ['A', 'B', 'C', 'D', 'F']" :key="grade" class="flex items-center justify-between gap-2">
										<span class="w-4 font-semibold text-gray-700">{{ grade }}</span>
										<div class="flex-1 rounded-full bg-gray-100">
											<div
												class="h-1.5 rounded-full bg-emerald-500 transition-all"
												:style="{
													width: props.resultsSummary.grades && props.resultsSummary.total
														? `${Math.round(((props.resultsSummary.grades[grade] || 0) / props.resultsSummary.total) * 100)}%`
														: '0%',
												}"
											></div>
										</div>
										<span class="w-16 text-right text-[10px] text-gray-600">
											{{ props.resultsSummary.grades && props.resultsSummary.grades[grade] ? props.resultsSummary.grades[grade] : 0 }}
										</span>
									</div>
								</div>
								<p v-else class="mt-1 text-xs text-gray-500">No grades data available yet.</p>
							</div>
						</div>
					</div>
				</div>

				<!-- Side analytics cards -->
				<div class="space-y-4">
					<div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
						<div class="flex items-center justify-between text-xs">
							<h3 class="font-semibold text-gray-800">Teacher Pass Rate</h3>
							<span class="text-gray-400">Top 0</span>
						</div>
						<p class="mt-3 text-xs text-gray-500">No data.</p>
					</div>

					<div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
						<div class="flex items-center justify-between text-xs">
							<h3 class="font-semibold text-gray-800">Subject Average</h3>
							<span class="text-gray-400">Top 0</span>
						</div>
						<p class="mt-3 text-xs text-gray-500">No data.</p>
					</div>

					<div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
						<div class="flex items-center justify-between text-xs">
							<h3 class="font-semibold text-gray-800">Gender Pass Rate</h3>
						</div>
						<div class="mt-3 space-y-2 text-xs text-gray-600">
							<div class="flex items-center justify-between gap-2">
								<span class="w-4 text-gray-500">M</span>
								<div class="flex-1 rounded-full bg-gray-100">
									<div class="h-1.5 w-0 rounded-full bg-emerald-500"></div>
								</div>
								<span>0%</span>
							</div>
							<div class="flex items-center justify-between gap-2">
								<span class="w-4 text-gray-500">F</span>
								<div class="flex-1 rounded-full bg-gray-100">
									<div class="h-1.5 w-0 rounded-full bg-rose-500"></div>
								</div>
								<span>0%</span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Calendar & events + latest announcements -->
			<div class="grid gap-4 lg:grid-cols-3">
				<div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100 lg:col-span-2">
					<div class="mb-3 flex items-center justify-between">
						<div>
							<h3 class="text-sm font-semibold text-gray-800">Calendar & Events</h3>
							<p class="text-xs text-gray-500">Upcoming school events from the calendar.</p>
						</div>
						<Link
							:href="route('calendar')"
							class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-100"
						>
							Open Calendar
						</Link>
					</div>
					<div v-if="!upcomingEvents || !upcomingEvents.length" class="text-[11px] text-gray-500">
						No upcoming events have been added yet.
					</div>
					<ul v-else class="space-y-2 text-[11px]">
						<li
							v-for="event in upcomingEvents"
							:key="event.id"
							class="flex items-start justify-between gap-2 rounded-lg border border-gray-100 bg-slate-50 px-3 py-2"
						>
							<div>
								<div class="text-[10px] font-semibold text-emerald-700">
									{{ new Date(event.date).toLocaleDateString() }}
								</div>
								<div class="text-[11px] font-semibold text-gray-900">
									{{ event.title }}
								</div>
								<div v-if="event.description" class="text-[10px] text-gray-600">
									{{ event.description }}
								</div>
							</div>
						</li>
					</ul>
				</div>
				<div class="rounded-xl bg-white p-5 text-xs text-gray-700 shadow-sm ring-1 ring-gray-100">
					<div class="mb-3 flex items-center justify-between">
						<h3 class="text-sm font-semibold text-gray-800">Latest Announcements</h3>
						<Link
							:href="route('announcements.index')"
							class="text-[10px] font-medium text-emerald-700 hover:text-emerald-800"
						>
							View all
						</Link>
					</div>
					<div v-if="!recentAnnouncements || !recentAnnouncements.length" class="text-[11px] text-gray-500">
						No announcements yet.
					</div>
					<ul v-else class="space-y-2 text-[11px]">
						<li
							v-for="item in recentAnnouncements"
							:key="item.id"
							class="rounded-lg border border-gray-100 bg-slate-50 px-3 py-2"
						>
							<div class="text-[11px] font-semibold text-gray-900">
								{{ item.title }}
							</div>
							<div class="mt-0.5 line-clamp-2 text-[10px] text-gray-600">
								{{ item.body }}
							</div>
							<div class="mt-0.5 text-[10px] text-gray-400">
								{{ new Date(item.created_at).toLocaleString() }}
							</div>
						</li>
					</ul>
				</div>
			</div>

			<!-- Top performers & support needed - NOW AT BOTTOM -->
			<div class="grid gap-4 lg:grid-cols-3">
				<!-- Top 10 best students -->
				<div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100 lg:col-span-1">
					<div class="mb-3 flex items-center justify-between">
						<div>
							<h3 class="text-sm font-semibold text-gray-800">Top 10 Best Students</h3>
							<p class="text-xs text-gray-500">Based on average marks (current year).</p>
						</div>
						<span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-medium text-emerald-700">
							Top {{ props.topStudents?.length || 0 }}
						</span>
					</div>
					<div v-if="props.topStudents && props.topStudents.length" class="space-y-1.5 text-[11px] text-gray-700">
						<div
							v-for="(row, index) in props.topStudents"
							:key="row.student_id || index"
							class="flex items-center justify-between gap-2 rounded-lg border border-gray-100 bg-slate-50 px-3 py-1.5"
						>
							<div class="flex items-center gap-2">
								<span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-[10px] font-semibold text-emerald-700">
									{{ index + 1 }}
								</span>
								<div>
									<div class="font-semibold text-gray-900 truncate max-w-[140px]">
										{{ row.full_name || row.exam_number || 'Student' }}
									</div>
									<div class="text-[10px] text-gray-500">
										{{ row.exam_number }}
										<span v-if="row.class_level" class="ml-1">• {{ row.class_level }} {{ row.stream }}</span>
									</div>
								</div>
							</div>
							<div class="text-right">
								<div class="text-xs font-semibold text-emerald-700">
									{{ row.avg_marks ? row.avg_marks.toFixed ? row.avg_marks.toFixed(1) : Number(row.avg_marks).toFixed(1) : '0.0' }}%
								</div>
								<div class="mt-0.5 h-1.5 w-16 overflow-hidden rounded-full bg-emerald-100">
									<div
										class="h-1.5 rounded-full bg-emerald-500"
										:style="{ width: `${Math.min(100, Math.max(0, Number(row.avg_marks || 0)))}%` }"
									></div>
								</div>
							</div>
						</div>
					</div>
					<p v-else class="text-[11px] text-gray-500">No student ranking data available yet.</p>
				</div>

				<!-- Top 10 lowest students -->
				<div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100 lg:col-span-1">
					<div class="mb-3 flex items-center justify-between">
						<div>
							<h3 class="text-sm font-semibold text-gray-800">Top 10 Students Needing Support</h3>
							<p class="text-xs text-gray-500">Lowest averages – for follow-up and interventions.</p>
						</div>
						<span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-0.5 text-[10px] font-medium text-rose-700">
							Top {{ props.bottomStudents?.length || 0 }}
						</span>
					</div>
					<div v-if="props.bottomStudents && props.bottomStudents.length" class="space-y-1.5 text-[11px] text-gray-700">
						<div
							v-for="(row, index) in props.bottomStudents"
							:key="row.student_id || index"
							class="flex items-center justify-between gap-2 rounded-lg border border-gray-100 bg-slate-50 px-3 py-1.5"
						>
							<div class="flex items-center gap-2">
								<span class="flex h-5 w-5 items-center justify-center rounded-full bg-rose-50 text-[10px] font-semibold text-rose-700">
									{{ index + 1 }}
								</span>
								<div>
									<div class="font-semibold text-gray-900 truncate max-w-[140px]">
										{{ row.full_name || row.exam_number || 'Student' }}
									</div>
									<div class="text-[10px] text-gray-500">
										{{ row.exam_number }}
										<span v-if="row.class_level" class="ml-1">• {{ row.class_level }} {{ row.stream }}</span>
									</div>
								</div>
							</div>
							<div class="text-right">
								<div class="text-xs font-semibold text-rose-700">
									{{ row.avg_marks ? row.avg_marks.toFixed ? row.avg_marks.toFixed(1) : Number(row.avg_marks).toFixed(1) : '0.0' }}%
								</div>
								<div class="mt-0.5 h-1.5 w-16 overflow-hidden rounded-full bg-rose-100">
									<div
										class="h-1.5 rounded-full bg-rose-500"
										:style="{ width: `${Math.min(100, Math.max(0, Number(row.avg_marks || 0)))}%` }"
									></div>
								</div>
							</div>
						</div>
					</div>
					<p v-else class="text-[11px] text-gray-500">No support ranking data available yet.</p>
				</div>

				<!-- Ward ranking coming soon -->
				<div class="flex flex-col justify-between rounded-xl bg-gradient-to-br from-slate-900 via-emerald-900 to-emerald-600 p-5 text-slate-50 shadow-sm lg:col-span-1">
					<div>
						<p class="text-[11px] font-semibold uppercase tracking-[0.15em] text-emerald-200">
							Ward Ranking
						</p>
						<h3 class="mt-1 text-lg font-semibold">Coming soon</h3>
						<p class="mt-1 text-[11px] text-emerald-100">
							Compare your school performance against other schools in the same ward with live league tables and trends.
						</p>
					</div>
					<div class="mt-4 flex items-end justify-between text-[11px]">
						<div class="space-y-1">
							<p class="text-emerald-100">Features on the way:</p>
							<ul class="space-y-0.5 text-emerald-50">
								<li class="flex items-center gap-1">
									<span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
									<span>Ward ranking table</span>
								</li>
								<li class="flex items-center gap-1">
									<span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
									<span>Trend graph over years</span>
								</li>
								<li class="flex items-center gap-1">
									<span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
									<span>Highlight of top improving schools</span>
								</li>
							</ul>
						</div>
						<div class="flex flex-col items-end text-right text-[10px] text-emerald-200">
							<span class="rounded-full bg-white/5 px-2 py-0.5">Ward league</span>
							<span class="mt-1 text-[9px] uppercase tracking-[0.16em]">Coming in a future update</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</AuthenticatedLayout>
</template>
