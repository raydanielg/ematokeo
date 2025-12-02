<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
	requirements: {
		type: Object,
		default: () => ({}),
	},
	canContinue: {
		type: Boolean,
		default: false,
	},
	envExampleExists: {
		type: Boolean,
		default: false,
	},
});
const page = usePage();

const step = ref(1);

const envForm = useForm({
	app_name: 'E-Matokeo',
	app_url: window.location.origin,
	db_host: '127.0.0.1',
	db_port: '3306',
	db_database: '',
	db_username: '',
	db_password: '',
});

const adminForm = useForm({
	name: '',
	username: '',
	email: '',
	phone: '',
	password: '',
	password_confirmation: '',
});

const runEnvSave = () => {
	envForm.post(route('install.environment'), {
		preserveScroll: true,
		onSuccess: () => {
			step = 3;
		},
	});
};

const runMigrate = () => {
	adminForm.clearErrors();
	adminForm.processing = true;
	window.axios
		.post(route('install.migrate'))
		.finally(() => {
			adminForm.processing = false;
			step = 4;
		});
};

const runCreateAdmin = () => {
	adminForm.post(route('install.admin'), {
		preserveScroll: true,
	});
};
</script>

<template>
	<Head title="E-Matokeo Installer" />

	<!-- Use bare layout to avoid auth requirement during install -->
	<div class="min-h-screen bg-slate-100">
		<div class="mx-auto max-w-4xl px-4 py-10">
			<div class="mb-8 text-center">
				<h1 class="text-2xl font-semibold text-gray-800">E-Matokeo Installer</h1>
				<p class="mt-1 text-sm text-gray-500">
					Step-by-step setup for your school results & hostel management system.
				</p>
			</div>

			<!-- Steps indicator -->
			<ol class="mb-6 flex items-center justify-between text-xs text-gray-600">
				<li class="flex-1">
					<div class="flex items-center gap-2">
						<div
							class="flex h-6 w-6 items-center justify-center rounded-full border text-[11px]"
							:class="step >= 1 ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-gray-300 bg-white'"
						>
							1
						</div>
						<span>Requirements</span>
					</div>
				</li>
				<li class="flex-1">
					<div class="flex items-center gap-2">
						<div
							class="flex h-6 w-6 items-center justify-center rounded-full border text-[11px]"
							:class="step >= 2 ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-gray-300 bg-white'"
						>
							2
						</div>
						<span>.env Configuration</span>
					</div>
				</li>
				<li class="flex-1">
					<div class="flex items-center gap-2">
						<div
							class="flex h-6 w-6 items-center justify-center rounded-full border text-[11px]"
							:class="step >= 3 ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-gray-300 bg-white'"
						>
							3
						</div>
						<span>Migrations</span>
					</div>
				</li>
				<li class="flex-1">
					<div class="flex items-center gap-2">
						<div
							class="flex h-6 w-6 items-center justify-center rounded-full border text-[11px]"
							:class="step >= 4 ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-gray-300 bg-white'"
						>
							4
						</div>
						<span>Admin Account</span>
					</div>
				</li>
			</ol>

			<!-- Step 1: Requirements -->
			<div v-if="step === 1" class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
				<h2 class="text-sm font-semibold text-gray-800">Server Requirements</h2>
				<p class="mt-1 text-xs text-gray-500">Check if your hosting environment meets the minimum requirements.</p>

				<div class="mt-4 space-y-4 text-xs text-gray-700">
					<div>
						<h3 class="mb-1 font-semibold">PHP Version</h3>
						<div class="flex items-center justify-between rounded-lg border border-gray-100 bg-slate-50 px-3 py-2">
							<span>Current: {{ requirements.php_version?.current }} (Required: {{ requirements.php_version?.required }})</span>
							<span
								class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
								:class="requirements.php_version?.ok ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
							>
								{{ requirements.php_version?.ok ? 'OK' : 'Upgrade PHP' }}
							</span>
						</div>
					</div>

					<div>
						<h3 class="mb-1 font-semibold">PHP Extensions</h3>
						<ul class="grid gap-2 md:grid-cols-2">
							<li
								v-for="(ok, key) in requirements.extensions || {}"
								:key="key"
								class="flex items-center justify-between rounded-lg border border-gray-100 bg-slate-50 px-3 py-1.5"
							>
								<span>{{ key }}</span>
								<span
									class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
									:class="ok ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
								>
									{{ ok ? 'Enabled' : 'Missing' }}
								</span>
							</li>
						</ul>
					</div>

					<div>
						<h3 class="mb-1 font-semibold">Permissions</h3>
						<ul class="space-y-2">
							<li class="flex items-center justify-between rounded-lg border border-gray-100 bg-slate-50 px-3 py-1.5">
								<span>storage/ writable</span>
								<span
									class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
									:class="requirements.permissions?.storage_writable ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
								>
									{{ requirements.permissions?.storage_writable ? 'OK' : 'Fix permissions' }}
								</span>
							</li>
							<li class="flex items-center justify-between rounded-lg border border-gray-100 bg-slate-50 px-3 py-1.5">
								<span>bootstrap/cache writable</span>
								<span
									class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
									:class="requirements.permissions?.bootstrap_cache_writable ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
								>
									{{ requirements.permissions?.bootstrap_cache_writable ? 'OK' : 'Fix permissions' }}
								</span>
							</li>
						</ul>
					</div>
				</div>

				<div class="mt-4 flex items-center justify-between text-xs">
					<p class="text-gray-500">
						Kama kuna requirement haijakaa sawa, rekebisha upande wa hosting kisha refresh ukurasa huu.
					</p>
					<button
						class="rounded-md px-3 py-1.5 text-xs font-semibold text-white shadow-sm"
						:class="canContinue ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-gray-300 cursor-not-allowed'"
						:disabled="!canContinue"
						@click="step = 2"
					>
						Continue
					</button>
				</div>
			</div>

			<!-- Step 2: .env configuration -->
			<div v-else-if="step === 2" class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
				<h2 class="text-sm font-semibold text-gray-800">.env Configuration</h2>
				<p class="mt-1 text-xs text-gray-500">
					Fill in your application and database details. These will be written into the <code>.env</code> file.
				</p>

				<div class="mt-4 grid gap-4 md:grid-cols-2 text-xs text-gray-700">
					<div class="space-y-2">
						<div>
							<label class="mb-1 block font-medium">App Name</label>
							<input
								v-model="envForm.app_name"
								type="text"
								class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
							/>
						</div>
						<div>
							<label class="mb-1 block font-medium">App URL</label>
							<input
								v-model="envForm.app_url"
								type="text"
								class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
							/>
						</div>
					</div>
					<div class="space-y-2">
						<div>
							<label class="mb-1 block font-medium">DB Host</label>
							<input
								v-model="envForm.db_host"
								type="text"
								class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
							/>
						</div>
						<div class="grid grid-cols-2 gap-2">
							<div>
								<label class="mb-1 block font-medium">DB Port</label>
								<input
									v-model="envForm.db_port"
									type="text"
									class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
								/>
							</div>
						</div>
					</div>
				</div>

				<div class="mt-4 grid gap-4 md:grid-cols-2 text-xs text-gray-700">
					<div class="space-y-2">
						<div>
							<label class="mb-1 block font-medium">DB Name</label>
							<input
								v-model="envForm.db_database"
								type="text"
								class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
							/>
						</div>
					</div>
					<div class="space-y-2">
						<div>
							<label class="mb-1 block font-medium">DB Username</label>
							<input
								v-model="envForm.db_username"
								type="text"
								class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
							/>
						</div>
						<div>
							<label class="mb-1 block font-medium">DB Password</label>
							<input
								v-model="envForm.db_password"
								type="password"
								class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
							/>
						</div>
					</div>
				</div>

				<div class="mt-4 flex items-center justify-between text-xs">
					<button
						class="rounded-md bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-200"
						@click="step = 1"
					>
						Back
					</button>
					<button
						class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700"
						@click="runEnvSave"
					>
						Save & Continue
					</button>
				</div>
			</div>

			<!-- Step 3: Migrations -->
			<div v-else-if="step === 3" class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
				<h2 class="text-sm font-semibold text-gray-800">Run Database Migrations</h2>
				<p class="mt-1 text-xs text-gray-500">
					The system will create the necessary tables in your configured database.
				</p>

				<div class="mt-4 rounded-lg bg-slate-50 px-3 py-2 text-[11px] text-gray-600">
					<p>
						Make sure your database exists and the credentials you provided are correct. This will run
						<code>php artisan migrate --force</code>.
					</p>
				</div>

				<div class="mt-4 flex items-center justify-between text-xs">
					<button
						class="rounded-md bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-200"
						@click="step = 2"
					>
						Back
					</button>
					<button
						class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700"
						@click="runMigrate"
					>
						Run Migrations
					</button>
				</div>
			</div>

			<!-- Step 4: Admin account -->
			<div v-else-if="step === 4" class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
				<h2 class="text-sm font-semibold text-gray-800">Create Admin Account</h2>
				<p class="mt-1 text-xs text-gray-500">
					This account will have full access to manage the school, results and hostels.
				</p>

				<div class="mt-4 grid gap-4 md:grid-cols-2 text-xs text-gray-700">
					<div class="space-y-2">
						<div>
							<label class="mb-1 block font-medium">Full Name</label>
							<input
								v-model="adminForm.name"
								type="text"
								class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
							/>
						</div>
						<div>
							<label class="mb-1 block font-medium">Username</label>
							<input
								v-model="adminForm.username"
								type="text"
								class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
							/>
						</div>
					</div>
					<div class="space-y-2">
						<div>
							<label class="mb-1 block font-medium">Email</label>
							<input
								v-model="adminForm.email"
								type="email"
								class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
							/>
						</div>
						<div>
							<label class="mb-1 block font-medium">Phone (optional)</label>
							<input
								v-model="adminForm.phone"
								type="text"
								class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
							/>
						</div>
					</div>
				</div>

				<div class="mt-4 grid gap-4 md:grid-cols-2 text-xs text-gray-700">
					<div class="space-y-2">
						<div>
							<label class="mb-1 block font-medium">Password</label>
							<input
								v-model="adminForm.password"
								type="password"
								class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
							/>
						</div>
					</div>
					<div class="space-y-2">
						<div>
							<label class="mb-1 block font-medium">Confirm Password</label>
							<input
								v-model="adminForm.password_confirmation"
								type="password"
								class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
							/>
						</div>
					</div>
				</div>

				<div class="mt-4 flex items-center justify-between text-xs">
					<button
						class="rounded-md bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-200"
						@click="step = 3"
					>
						Back
					</button>
					<button
						class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700"
						@click="runCreateAdmin"
					>
						Create Admin & Finish
					</button>
				</div>
			</div>
		</div>
	</div>
</template>
