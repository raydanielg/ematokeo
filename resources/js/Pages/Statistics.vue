<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
	formStats: {
		type: Object,
		default: () => ({
			form1: { pass: 0, fail: 0, students: 0 },
			form2: { pass: 0, fail: 0, students: 0 },
			form3: { pass: 0, fail: 0, students: 0 },
			form4: { pass: 0, fail: 0, students: 0 },
		}),
	},
	genderStats: {
		type: Object,
		default: () => ({
			male: { pass: 0, fail: 0 },
			female: { pass: 0, fail: 0 },
		}),
	},
	divisionStats: {
		type: Object,
		default: () => ({
			div1: 0,
			div2: 0,
			div3: 0,
			div4: 0,
		}),
	},
	totalStudents: {
		type: Number,
		default: 0,
	},
});
</script>

<template>
	<Head title="Statistics" />

	<AuthenticatedLayout>
		<template #header>
			<div class="flex items-center justify-between">
				<div>
					<h2 class="text-2xl font-semibold leading-tight text-gray-800">
						Statistics & Analytics
					</h2>
					<p class="mt-1 text-sm text-gray-500">
						Comprehensive performance analytics by form, division, and gender.
					</p>
				</div>
			</div>
		</template>

		<div class="space-y-6">
			<!-- Top KPI Cards -->
			<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
				<div class="rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 border border-emerald-200 p-5 shadow-sm">
					<div class="flex items-center justify-between">
						<div>
							<p class="text-xs font-medium text-emerald-700 uppercase tracking-wide">Form 1 Pass Rate</p>
							<p class="mt-2 text-3xl font-bold text-emerald-900">{{ formStats.form1.pass }}%</p>
							<p class="mt-1 text-xs text-emerald-700">{{ formStats.form1.students }} students</p>
						</div>
						<i class="material-icons text-5xl text-emerald-300">trending_up</i>
					</div>
				</div>

				<div class="rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 p-5 shadow-sm">
					<div class="flex items-center justify-between">
						<div>
							<p class="text-xs font-medium text-blue-700 uppercase tracking-wide">Form 2 Pass Rate</p>
							<p class="mt-2 text-3xl font-bold text-blue-900">{{ formStats.form2.pass }}%</p>
							<p class="mt-1 text-xs text-blue-700">{{ formStats.form2.students }} students</p>
						</div>
						<i class="material-icons text-5xl text-blue-300">trending_up</i>
					</div>
				</div>

				<div class="rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 border border-amber-200 p-5 shadow-sm">
					<div class="flex items-center justify-between">
						<div>
							<p class="text-xs font-medium text-amber-700 uppercase tracking-wide">Form 3 Pass Rate</p>
							<p class="mt-2 text-3xl font-bold text-amber-900">{{ formStats.form3.pass }}%</p>
							<p class="mt-1 text-xs text-amber-700">{{ formStats.form3.students }} students</p>
						</div>
						<i class="material-icons text-5xl text-amber-300">trending_up</i>
					</div>
				</div>

				<div class="rounded-xl bg-gradient-to-br from-violet-50 to-violet-100 border border-violet-200 p-5 shadow-sm">
					<div class="flex items-center justify-between">
						<div>
							<p class="text-xs font-medium text-violet-700 uppercase tracking-wide">Form 4 Pass Rate</p>
							<p class="mt-2 text-3xl font-bold text-violet-900">{{ formStats.form4.pass }}%</p>
							<p class="mt-1 text-xs text-violet-700">{{ formStats.form4.students }} students</p>
						</div>
						<i class="material-icons text-5xl text-violet-300">trending_up</i>
					</div>
				</div>
			</div>

			<!-- Charts Section -->
			<div class="grid gap-4 lg:grid-cols-3">
				<!-- Form Performance Bar Chart -->
				<div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100 lg:col-span-2">
					<div class="mb-6 flex items-center justify-between">
						<div>
							<h3 class="text-lg font-semibold text-gray-900">Form Performance Comparison</h3>
							<p class="mt-1 text-sm text-gray-500">Pass rate by form level</p>
						</div>
						<i class="material-icons text-gray-400">bar_chart</i>
					</div>

					<div class="space-y-4">
						<!-- Form 1 -->
						<div>
							<div class="mb-2 flex items-center justify-between text-sm">
								<span class="font-medium text-gray-700">Form 1</span>
								<span class="font-bold text-emerald-600">{{ formStats.form1.pass }}%</span>
							</div>
							<div class="h-3 w-full overflow-hidden rounded-full bg-gray-100">
								<div class="h-3 rounded-full bg-gradient-to-r from-emerald-400 to-emerald-600 transition-all" :style="{ width: `${formStats.form1.pass}%` }"></div>
							</div>
						</div>

						<!-- Form 2 -->
						<div>
							<div class="mb-2 flex items-center justify-between text-sm">
								<span class="font-medium text-gray-700">Form 2</span>
								<span class="font-bold text-blue-600">{{ formStats.form2.pass }}%</span>
							</div>
							<div class="h-3 w-full overflow-hidden rounded-full bg-gray-100">
								<div class="h-3 rounded-full bg-gradient-to-r from-blue-400 to-blue-600 transition-all" :style="{ width: `${formStats.form2.pass}%` }"></div>
							</div>
						</div>

						<!-- Form 3 -->
						<div>
							<div class="mb-2 flex items-center justify-between text-sm">
								<span class="font-medium text-gray-700">Form 3</span>
								<span class="font-bold text-amber-600">{{ formStats.form3.pass }}%</span>
							</div>
							<div class="h-3 w-full overflow-hidden rounded-full bg-gray-100">
								<div class="h-3 rounded-full bg-gradient-to-r from-amber-400 to-amber-600 transition-all" :style="{ width: `${formStats.form3.pass}%` }"></div>
							</div>
						</div>

						<!-- Form 4 -->
						<div>
							<div class="mb-2 flex items-center justify-between text-sm">
								<span class="font-medium text-gray-700">Form 4</span>
								<span class="font-bold text-violet-600">{{ formStats.form4.pass }}%</span>
							</div>
							<div class="h-3 w-full overflow-hidden rounded-full bg-gray-100">
								<div class="h-3 rounded-full bg-gradient-to-r from-violet-400 to-violet-600 transition-all" :style="{ width: `${formStats.form4.pass}%` }"></div>
							</div>
						</div>
					</div>
				</div>

				<!-- Gender Distribution Pie Chart -->
				<div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
					<div class="mb-6 flex items-center justify-between">
						<div>
							<h3 class="text-lg font-semibold text-gray-900">Gender Pass Rate</h3>
							<p class="mt-1 text-sm text-gray-500">Performance by gender</p>
						</div>
						<i class="material-icons text-gray-400">pie_chart</i>
					</div>

					<div class="space-y-4">
						<!-- Male -->
						<div>
							<div class="mb-2 flex items-center justify-between text-sm">
								<div class="flex items-center gap-2">
									<span class="h-3 w-3 rounded-full bg-blue-500"></span>
									<span class="font-medium text-gray-700">Male</span>
								</div>
								<span class="font-bold text-blue-600">{{ genderStats.male.pass }}%</span>
							</div>
							<div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
								<div class="h-2 rounded-full bg-blue-500" :style="{ width: `${genderStats.male.pass}%` }"></div>
							</div>
						</div>

						<!-- Female -->
						<div>
							<div class="mb-2 flex items-center justify-between text-sm">
								<div class="flex items-center gap-2">
									<span class="h-3 w-3 rounded-full bg-rose-500"></span>
									<span class="font-medium text-gray-700">Female</span>
								</div>
								<span class="font-bold text-rose-600">{{ genderStats.female.pass }}%</span>
							</div>
							<div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
								<div class="h-2 rounded-full bg-rose-500" :style="{ width: `${genderStats.female.pass}%` }"></div>
							</div>
						</div>
					</div>

					<!-- Legend -->
					<div class="mt-6 border-t border-gray-200 pt-4">
						<p class="mb-3 text-xs font-semibold text-gray-600 uppercase">Summary</p>
						<div class="space-y-2 text-sm text-gray-600">
							<div class="flex items-center justify-between">
								<span>Overall Pass Rate:</span>
								<span class="font-bold text-emerald-600">{{ Math.round((formStats.form1.pass + formStats.form2.pass + formStats.form3.pass + formStats.form4.pass) / 4) }}%</span>
							</div>
							<div class="flex items-center justify-between">
								<span>Total Students:</span>
								<span class="font-bold text-gray-900">{{ totalStudents }}</span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Division Distribution -->
			<div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
				<div class="mb-6 flex items-center justify-between">
					<div>
						<h3 class="text-lg font-semibold text-gray-900">Division Distribution</h3>
						<p class="mt-1 text-sm text-gray-500">Student count by division</p>
					</div>
					<i class="material-icons text-gray-400">donut_large</i>
				</div>

				<div class="grid gap-6 md:grid-cols-4">
					<!-- Division 1 -->
					<div class="text-center">
						<div class="mx-auto mb-3 flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-emerald-100 to-emerald-200">
							<span class="text-2xl font-bold text-emerald-900">{{ divisionStats.div1 }}%</span>
						</div>
						<p class="font-semibold text-gray-900">Division 1</p>
						<p class="text-sm text-gray-500">{{ Math.round(totalStudents * divisionStats.div1 / 100) }} students</p>
					</div>

					<!-- Division 2 -->
					<div class="text-center">
						<div class="mx-auto mb-3 flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-blue-100 to-blue-200">
							<span class="text-2xl font-bold text-blue-900">{{ divisionStats.div2 }}%</span>
						</div>
						<p class="font-semibold text-gray-900">Division 2</p>
						<p class="text-sm text-gray-500">{{ Math.round(totalStudents * divisionStats.div2 / 100) }} students</p>
					</div>

					<!-- Division 3 -->
					<div class="text-center">
						<div class="mx-auto mb-3 flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-amber-100 to-amber-200">
							<span class="text-2xl font-bold text-amber-900">{{ divisionStats.div3 }}%</span>
						</div>
						<p class="font-semibold text-gray-900">Division 3</p>
						<p class="text-sm text-gray-500">{{ Math.round(totalStudents * divisionStats.div3 / 100) }} students</p>
					</div>

					<!-- Division 4 -->
					<div class="text-center">
						<div class="mx-auto mb-3 flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-violet-100 to-violet-200">
							<span class="text-2xl font-bold text-violet-900">{{ divisionStats.div4 }}%</span>
						</div>
						<p class="font-semibold text-gray-900">Division 4</p>
						<p class="text-sm text-gray-500">{{ Math.round(totalStudents * divisionStats.div4 / 100) }} students</p>
					</div>
				</div>
			</div>

			<!-- Detailed Statistics Table -->
			<div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
				<div class="mb-6 flex items-center justify-between">
					<div>
						<h3 class="text-lg font-semibold text-gray-900">Detailed Performance Metrics</h3>
						<p class="mt-1 text-sm text-gray-500">Complete breakdown by form</p>
					</div>
					<i class="material-icons text-gray-400">table_chart</i>
				</div>

				<div class="overflow-x-auto">
					<table class="w-full text-sm">
						<thead>
							<tr class="border-b border-gray-200">
								<th class="px-4 py-3 text-left font-semibold text-gray-700">Form</th>
								<th class="px-4 py-3 text-center font-semibold text-gray-700">Total Students</th>
								<th class="px-4 py-3 text-center font-semibold text-gray-700">Pass Rate</th>
								<th class="px-4 py-3 text-center font-semibold text-gray-700">Fail Rate</th>
								<th class="px-4 py-3 text-center font-semibold text-gray-700">Status</th>
							</tr>
						</thead>
						<tbody>
							<tr class="border-b border-gray-100 hover:bg-gray-50">
								<td class="px-4 py-3 font-medium text-gray-900">Form 1</td>
								<td class="px-4 py-3 text-center text-gray-600">{{ formStats.form1.students }}</td>
								<td class="px-4 py-3 text-center">
									<span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700">
										{{ formStats.form1.pass }}%
									</span>
								</td>
								<td class="px-4 py-3 text-center text-gray-600">{{ formStats.form1.fail }}%</td>
								<td class="px-4 py-3 text-center">
									<i class="material-icons text-emerald-600">check_circle</i>
								</td>
							</tr>
							<tr class="border-b border-gray-100 hover:bg-gray-50">
								<td class="px-4 py-3 font-medium text-gray-900">Form 2</td>
								<td class="px-4 py-3 text-center text-gray-600">{{ formStats.form2.students }}</td>
								<td class="px-4 py-3 text-center">
									<span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700">
										{{ formStats.form2.pass }}%
									</span>
								</td>
								<td class="px-4 py-3 text-center text-gray-600">{{ formStats.form2.fail }}%</td>
								<td class="px-4 py-3 text-center">
									<i class="material-icons text-emerald-600">check_circle</i>
								</td>
							</tr>
							<tr class="border-b border-gray-100 hover:bg-gray-50">
								<td class="px-4 py-3 font-medium text-gray-900">Form 3</td>
								<td class="px-4 py-3 text-center text-gray-600">{{ formStats.form3.students }}</td>
								<td class="px-4 py-3 text-center">
									<span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-700">
										{{ formStats.form3.pass }}%
									</span>
								</td>
								<td class="px-4 py-3 text-center text-gray-600">{{ formStats.form3.fail }}%</td>
								<td class="px-4 py-3 text-center">
									<i class="material-icons text-emerald-600">check_circle</i>
								</td>
							</tr>
							<tr class="hover:bg-gray-50">
								<td class="px-4 py-3 font-medium text-gray-900">Form 4</td>
								<td class="px-4 py-3 text-center text-gray-600">{{ formStats.form4.students }}</td>
								<td class="px-4 py-3 text-center">
									<span class="inline-flex items-center rounded-full bg-violet-100 px-3 py-1 text-sm font-semibold text-violet-700">
										{{ formStats.form4.pass }}%
									</span>
								</td>
								<td class="px-4 py-3 text-center text-gray-600">{{ formStats.form4.fail }}%</td>
								<td class="px-4 py-3 text-center">
									<i class="material-icons text-emerald-600">check_circle</i>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</AuthenticatedLayout>
</template>
