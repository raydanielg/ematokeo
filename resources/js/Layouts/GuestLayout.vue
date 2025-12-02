<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
	<div
		class="relative flex min-h-screen flex-col items-center pt-6 sm:justify-center sm:pt-0"
		style="
			background-image: url('/images/PXL_20250502_075432413.RAW-01.COVER.jpg');
			background-size: cover;
			background-position: center;
		"
	>
		<!-- Green blur overlay -->
		<div
			class="pointer-events-none absolute inset-0 bg-emerald-900/70 backdrop-blur-sm"
		></div>

		<div class="relative z-10 flex flex-col items-center">
			<div>
				<Link href="/">
					<ApplicationLogo class="h-20 w-20" />
				</Link>
			</div>

			<div
				class="mt-6 w-full overflow-hidden bg-white/95 px-6 py-4 shadow-xl sm:max-w-md sm:rounded-xl"
			>
				<slot />
			</div>
		</div>
	</div>
</template>
