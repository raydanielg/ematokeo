<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Create Blog Post" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                Create New Blog Post
            </h2>
        </template>

        <div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-100 p-6">
            <form class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                    <input type="text" placeholder="Enter post title" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-emerald-500 focus:outline-none" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                    <textarea rows="8" placeholder="Write your post content here..." class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-emerald-500 focus:outline-none"></textarea>
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-emerald-500 focus:outline-none">
                            <option>Select category</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-emerald-500 focus:outline-none">
                            <option>Draft</option>
                            <option>Published</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="px-6 py-2 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition-colors">
                        Publish Post
                    </button>
                    <button type="button" class="px-6 py-2 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
