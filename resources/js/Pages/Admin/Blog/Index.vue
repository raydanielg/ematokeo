<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Blog Posts" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Blog Posts
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage all blog posts and content
                    </p>
                </div>
                <Link :href="route('admin.blog.create')" class="px-6 py-2 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition-colors">
                    + Create Post
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats -->
            <div class="grid gap-6 md:grid-cols-3">
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Total Posts</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">0</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Published</p>
                    <p class="mt-2 text-3xl font-bold text-blue-600">0</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-600">Drafts</p>
                    <p class="mt-2 text-3xl font-bold text-orange-600">0</p>
                </div>
            </div>

            <!-- Posts Table -->
            <div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        All Posts
                    </h3>
                </div>
                <div class="p-6 text-center text-gray-500">
                    <p>No blog posts yet. <Link :href="route('admin.blog.create')" class="text-emerald-600 hover:text-emerald-700 font-semibold">Create your first post</Link></p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
