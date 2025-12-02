<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    folders: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    folder: '',
    title: '',
    description: '',
    file: null,
});

const showModal = ref(false); // upload modal
const showCreateFolderModal = ref(false); // create-folder helper modal

const selectedFolder = ref('');
const newFolderName = ref('');

const folderOptions = computed(() => (props.folders || []).map((f) => f.folder));

const submit = () => {
    // Prefer selected existing folder if present; otherwise use typed name
    if (selectedFolder.value) {
        form.folder = selectedFolder.value;
    }

    form.post(route('resources.books.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset('folder', 'title', 'description', 'file');
            selectedFolder.value = '';
            showModal.value = false;
        },
    });
};

const handleFileChange = (event) => {
    const [file] = event.target.files;
    form.file = file || null;
};

const openCreateFolderModal = () => {
    newFolderName.value = '';
    showCreateFolderModal.value = true;
};

const openUploadModal = () => {
    showModal.value = true;
};

const createFolderOnly = () => {
    const name = (newFolderName.value || '').trim();
    if (!name) return;

    selectedFolder.value = '';
    form.folder = name;
    form.title = name;
    showCreateFolderModal.value = false;
    submit();
    newFolderName.value = '';
};
</script>

<template>
    <Head title="Books & References" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Books & References
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Create resource folders and upload documents (PDF, Word, etc.) for teachers.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                        @click="openCreateFolderModal"
                    >
                        Create Folder
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md bg-emerald-50 px-3 py-1.5 text-[11px] font-semibold text-emerald-700 shadow-sm ring-1 ring-emerald-200 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                        @click="openUploadModal"
                    >
                        Upload Document
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <!-- Folders & files -->
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-700">
                        Folders ({{ folders.length }})
                    </p>
                    <p class="text-[11px] text-gray-500">
                        Each card represents a folder containing uploaded books/documents.
                    </p>
                </div>

                <div v-if="folders.length === 0" class="py-6 text-center text-[11px] text-gray-500">
                    No resources uploaded yet. Create a folder name and upload your first document above.
                </div>

                <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div
                        v-for="folder in folders"
                        :key="folder.folder"
                        class="flex flex-col rounded-lg border border-gray-200 bg-slate-50 p-4 shadow-sm"
                    >
                        <div class="mb-2 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800">
                                {{ folder.folder }}
                            </h3>
                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 ring-1 ring-emerald-100">
                                {{ folder.items.length }} files
                            </span>
                        </div>
                        <ul class="mt-1 space-y-1 text-[11px] text-gray-700">
                            <li
                                v-for="item in folder.items"
                                :key="item.id"
                                class="flex items-start justify-between gap-2 rounded-md border border-gray-200 bg-white px-2 py-1"
                            >
                                <div>
                                    <div class="font-semibold text-gray-800">
                                        {{ item.title }}
                                    </div>
                                    <div v-if="item.description" class="text-[10px] text-gray-500">
                                        {{ item.description }}
                                    </div>
                                </div>
                                <a
                                    :href="`/storage/${item.file_path}`"
                                    target="_blank"
                                    class="shrink-0 rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100"
                                >
                                    Open
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Create Folder helper modal (no upload yet) -->
            <div
                v-if="showCreateFolderModal"
                class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6 sm:px-0"
            >
                <div
                    class="w-full max-w-md rounded-xl bg-white p-5 text-xs text-gray-700 shadow-lg ring-1 ring-gray-200"
                >
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">
                                Create Folder
                            </h3>
                            <p class="mt-0.5 text-[11px] text-gray-500">
                                Type the name of the folder you want to use, then continue to upload documents into it.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="text-[11px] text-gray-500 hover:text-gray-700"
                            @click="showCreateFolderModal = false"
                        >
                            Close
                        </button>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Folder name</label>
                            <input
                                v-model="newFolderName"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="e.g. Form II Physics, Staff Library"
                            />
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-md bg-gray-100 px-3 py-1.5 text-[11px] font-medium text-gray-700 hover:bg-gray-200"
                            @click="showCreateFolderModal = false"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                            @click="createFolderOnly"
                        >
                            Create Folder
                        </button>
                    </div>
                </div>
            </div>

            <!-- Create / Upload modal -->
            <div
                v-if="showModal"
                class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4 py-6 sm:px-0"
            >
                <div
                    class="w-full max-w-3xl rounded-xl bg-white p-5 text-xs text-gray-700 shadow-lg ring-1 ring-gray-200"
                >
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">
                                Create Folder & Upload Document
                            </h3>
                            <p class="mt-0.5 text-[11px] text-gray-500">
                                Choose an existing folder or type a new one, then set document details and upload the file.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="text-[11px] text-gray-500 hover:text-gray-700"
                            @click="showModal = false"
                        >
                            Close
                        </button>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2 text-xs">
                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Select existing folder</label>
                            <select
                                v-model="selectedFolder"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                            >
                                <option value="">
                                    -- Choose folder (optional) --
                                </option>
                                <option
                                    v-for="name in folderOptions"
                                    :key="name"
                                    :value="name"
                                >
                                    {{ name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Or new folder name</label>
                            <input
                                v-model="form.folder"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="e.g. Form II Physics, Staff Library"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Title</label>
                            <input
                                v-model="form.title"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="e.g. Physics Book 2 - NECTA"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-medium">Description (optional)</label>
                            <input
                                v-model="form.description"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                                placeholder="Short notes for teachers / subject"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-medium">File</label>
                            <input
                                type="file"
                                class="w-full text-[11px]"
                                @change="handleFileChange"
                            />
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-md bg-gray-100 px-3 py-1.5 text-[11px] font-medium text-gray-700 hover:bg-gray-200"
                            @click="showModal = false"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                            @click="submit"
                        >
                            Upload Document
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
