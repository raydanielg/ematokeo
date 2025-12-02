<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
	<Head title="Statistics" />

	<AuthenticatedLayout>
		<template #header>
			<div class="flex items-center justify-between">
				<div>
					<h2 class="text-2xl font-semibold leading-tight text-gray-800">
						Statistics
					</h2>
					<p class="mt-1 text-sm text-gray-500">
						Detailed performance analytics by form, division, and gender.
					</p>
				</div>
			</div>
		</template>

		<div class="space-y-6">
			<!-- Top cards: summary of forms/divisions -->
			<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
				<div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-100">
					<p class="text-xs font-medium text-gray-500">Form 1
						Overview</p>
					<p class="mt-1 text-2xl font-semibold text-gray-900">0%</p>
					<p class="text-xs text-gray-500">Pass rate</p>
				</div>
				<div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-100">
					<p class="text-xs font-medium text-gray-500">Form 2 Overview</p>
					<p class="mt-1 text-2xl font-semibold text-gray-900">0%</p>
					<p class="text-xs text-gray-500">Pass rate</p>
				</div>
				<div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-100">
					<p class="text-xs font-medium text-gray-500">Form 3 Overview</p>
					<p class="mt-1 text-2xl font-semibold text-gray-900">0%</p>
					<p class="text-xs text-gray-500">Pass rate</p>
				</div>
				<div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-100">
					<p class="text-xs font-medium text-gray-500">Form 4 Overview</p>
					<p class="mt-1 text-2xl font-semibold text-gray-900">0%</p>
					<p class="text-xs text-gray-500">Pass rate</p>
				</div>
			</div>

			<div class="grid gap-4 lg:grid-cols-3">
				<!-- Bar chart: Forms performance -->
				<div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100 lg:col-span-2">
					<div class="mb-3 flex items-center justify-between">
						<h3 class="text-sm font-semibold text-gray-800">
							Form Performance (Pass Rate)
						</h3>
						<p class="text-xs text-gray-500">Form 1 - Form 4</p>
					</div>
					<div class="mt-4 flex items-end gap-4 border-t border-dashed border-gray-200 pt-4">
						<div class="flex flex-1 flex-col items-center gap-1 text-xs text-gray-600">
							<div class="flex h-40 w-6 items-end rounded-full bg-emerald-50">
								<div class="w-full rounded-full bg-emerald-500" style="height: 0%"></div>
							</div>
							<span>Form 1</span>
						</div>
						<div class="flex flex-1 flex-col items-center gap-1 text-xs text-gray-600">
							<div class="flex h-40 w-6 items-end rounded-full bg-emerald-50">
								<div class="w-full rounded-full bg-emerald-500" style="height: 0%"></div>
							</div>
							<span>Form 2</span>
						</div>
						<div class="flex flex-1 flex-col items-center gap-1 text-xs text-gray-600">
							<div class="flex h-40 w-6 items-end rounded-full bg-emerald-50">
								<div class="w-full rounded-full bg-emerald-500" style="height: 0%"></div>
							</div>
							<span>Form 3</span>
						</div>
						<div class="flex flex-1 flex-col items-center gap-1 text-xs text-gray-600">
							<div class="flex h-40 w-6 items-end rounded-full bg-emerald-50">
								<div class="w-full rounded-full bg-emerald-500" style="height: 0%"></div>
							</div>
							<span>Form 4</span>
						</div>
					</div>
				</div>

				<!-- Pie style division breakdown -->
				<div class="space-y-4">
					<div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
						<h3 class="text-sm font-semibold text-gray-800">
							Division Distribution (Overall)
						</h3>
						<p class="mt-1 text-xs text-gray-500">Div 1 1 2 3 0</p>
						<div class="mt-3 flex items-center gap-4">
							<div class="flex h-24 w-24 items-center justify-center rounded-full bg-gray-100 text-[11px] text-gray-400">
								Pie chart
							</div>
							<div class="space-y-1 text-xs text-gray-600">
								<div class="flex items-center gap-2">
									<span class="h-2 w-2 rounded-full bg-emerald-500"></span>
									<span>Div 1: 0%</span>
								</div>
								<div class="flex items-center gap-2">
									<span class="h-2 w-2 rounded-full bg-blue-500"></span>
									<span>Div 2: 0%</span>
								</div>
								<div class="flex items-center gap-2">
									<span class="h-2 w-2 rounded-full bg-amber-500"></span>
									<span>Div 3: 0%</span>
								</div>
								<div class="flex items-center gap-2">
									<span class="h-2 w-2 rounded-full bg-rose-500"></span>
									<span>Div 0: 0%</span>
								</div>
							</div>
						</div>
					</div>

					<div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
						<h3 class="text-sm font-semibold text-gray-800">
							Gender Performance by Division
						</h3>
						<p class="mt-1 text-xs text-gray-500">Male vs Female per division.</p>
						<div class="mt-3 space-y-3 text-xs text-gray-600">
							<div>
								<p class="mb-1 font-medium">Div 1</p>
								<div class="flex items-center gap-2">
									<span class="w-6 text-gray-500">M</span>
									<div class="flex-1 rounded-full bg-gray-100">
										<div class="h-1.5 w-0 rounded-full bg-emerald-500"></div>
									</div>
									<span>0%</span>
								</div>
								<div class="mt-1 flex items-center gap-2">
									<span class="w-6 text-gray-500">F</span>
									<div class="flex-1 rounded-full bg-gray-100">
										<div class="h-1.5 w-0 rounded-full bg-rose-500"></div>
									</div>
									<span>0%</span>
								</div>
							</div>
							<div>
								<p class="mb-1 font-medium">Div 2</p>
								<div class="flex items-center gap-2">
									<span class="w-6 text-gray-500">M</span>
									<div class="flex-1 rounded-full bg-gray-100">
										<div class="h-1.5 w-0 rounded-full bg-emerald-500"></div>
									</div>
									<span>0%</span>
								</div>
								<div class="mt-1 flex items-center gap-2">
									<span class="w-6 text-gray-500">F</span>
									<div class="flex-1 rounded-full bg-gray-100">
										<div class="h-1.5 w-0 rounded-full bg-rose-500"></div>
									</div>
									<span>0%</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</AuthenticatedLayout>
</template>
