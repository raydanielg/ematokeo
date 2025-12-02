<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const isSidebarOpen = ref(true);

const sidebarSections = [
    {
        key: 'dashboard',
        title: 'Dashboard',
        items: ['Overview', 'Statistics', 'Recent Activities'],
    },
    {
        key: 'students',
        title: 'Students',
        items: ['Add Student', 'View Students', 'Import Students (Excel)', 'Promote Students'],
    },
    {
        key: 'classes',
        title: 'Classes',
        items: ['Manage Classes', 'Manage Streams', 'Class Teachers'],
    },
    {
        key: 'subjects',
        title: 'Subjects',
        items: ['Add Subject', 'View Subjects', 'Assign Teachers'],
    },
    {
        key: 'teachers',
        title: 'Teachers',
        items: ['Add Teacher', 'View Teachers', 'Roles & Permissions'],
    },
    {
        key: 'exams',
        title: 'Exams',
        items: ['Create Exam', 'Enter Marks', 'Moderation / Approvals'],
    },
    {
        key: 'results',
        title: 'Results',
        items: ['Process Results', 'Class Performance', 'Subject Performance', 'Ranking', 'Publish Results'],
    },
    {
        key: 'reports',
        title: 'Reports',
        items: ['Student Report Card', 'Class Report', 'School Report', 'Analytics (Charts)', 'PDF / Print Reports'],
    },
    {
        key: 'timetables',
        title: 'Timetables Management',
        items: ['Class Timetables', 'All Timetables', 'Create Timetable', 'Invigilation Timetable', 'Sitting Plan', 'Resources', 'Topics'],
    },
    {
        key: 'notifications',
        title: 'Notifications',
        items: ['Announcements', 'Exam Alerts', 'Results Alerts (SMS/Email)', 'Calendar'],
    },
    {
        key: 'settings',
        title: 'Settings',
        items: ['School Information', 'Academic Year', 'Grading System', 'Logo & Branding', 'SMS/Email Settings', 'User Management'],
    },
    {
        key: 'hostels',
        title: 'Hostel Management',
        items: [
            'All Hostel Students',
            'Parents & Guardians',
            'Payments',
            'Matron & Patron',
            'Rooms & Beds',
            'Allocation',
            'Hostel Reports',
        ],
    },
    {
        key: 'profile',
        title: 'Profile',
        items: ['My Account', 'Change Password', 'Logout'],
    },
];

const openSections = ref([]);

const toggleSection = (key) => {
    if (openSections.value.includes(key)) {
        openSections.value = openSections.value.filter((k) => k !== key);
    } else {
        openSections.value.push(key);
    }
};
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="border-b border-emerald-900/40 bg-emerald-900 text-emerald-50 print:hidden">
                <!-- Primary Navigation Menu -->
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex items-center gap-3">
                            <!-- Sidebar toggle -->
                            <button
                                class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-emerald-800 bg-emerald-800 text-emerald-100 shadow-sm transition hover:bg-emerald-700 focus:outline-none"
                                @click="isSidebarOpen = !isSidebarOpen"
                                type="button"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h10M4 18h7"
                                    />
                                </svg>
                            </button>

                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden sm:-my-px sm:ms-10 sm:flex sm:items-center">
                                <span class="text-sm font-medium text-emerald-100/90">
                                    Dashboard
                                </span>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex items-center gap-3 rounded-md">
                                            <!-- Notifications -->
                                            <button
                                                type="button"
                                                class="relative inline-flex h-9 w-9 items-center justify-center rounded-full border border-emerald-700 bg-emerald-800 text-emerald-100 shadow-sm transition hover:bg-emerald-700"
                                            >
                                                <span
                                                    v-if="$page.props.announcementsHeader && $page.props.announcementsHeader.length"
                                                    class="absolute -right-0.5 -top-0.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
                                                >
                                                    {{ $page.props.announcementsHeader.length }}
                                                </span>
                                                <svg
                                                    class="h-4 w-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                                                    />
                                                </svg>
                                            </button>

                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-emerald-800 bg-emerald-800 px-3 py-2 text-sm font-medium leading-4 text-emerald-50 transition duration-150 ease-in-out hover:bg-emerald-700 hover:text-white focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="border-b border-gray-100 px-3 py-2 text-[11px] text-gray-700">
                                            <div class="mb-1 flex items-center justify-between">
                                                <span class="font-semibold">Announcements</span>
                                                <span class="text-[10px] text-gray-400">
                                                    {{ ($page.props.announcementsHeader || []).length }} new
                                                </span>
                                            </div>
                                            <div v-if="!$page.props.announcementsHeader || !$page.props.announcementsHeader.length" class="text-[10px] text-gray-400">
                                                No recent announcements.
                                            </div>
                                            <ul
                                                v-else
                                                class="max-h-40 space-y-1 overflow-auto pr-1 text-[10px]"
                                            >
                                                <li
                                                    v-for="item in $page.props.announcementsHeader"
                                                    :key="item.id"
                                                    class="rounded bg-slate-50 px-2 py-1"
                                                >
                                                    <Link
                                                        :href="route('announcements.index')"
                                                        class="block text-left font-semibold text-gray-800 hover:text-emerald-700"
                                                    >
                                                        {{ item.title }}
                                                    </Link>
                                                    <div class="mt-0.5 line-clamp-1 text-[10px] text-gray-500">
                                                        {{ item.body }}
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="border-t border-gray-100 px-3 py-2 text-[11px] text-gray-700">
                                            <DropdownLink :href="route('announcements.index')">
                                                View all announcements
                                            </DropdownLink>
                                            <DropdownLink
                                                :href="route('profile.edit')"
                                            >
                                                Profile
                                            </DropdownLink>
                                            <DropdownLink
                                                :href="route('logout')"
                                                method="post"
                                                as="button"
                                            >
                                                Log Out
                                            </DropdownLink>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 pb-1 pt-4"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            			<div class="flex min-h-[calc(100vh-4rem)]">
				<!-- Sidebar -->
				<aside
					v-show="isSidebarOpen"
					class="w-72 border-r border-emerald-900/30 bg-emerald-900 px-3 py-4 text-emerald-50 print:hidden"
				>
                    <nav class="max-h-[calc(100vh-4rem)] space-y-1 overflow-y-auto pr-1 text-sm">
                        <div class="mb-3 px-2 text-xs font-semibold uppercase tracking-wide text-emerald-200">
                            Navigation
                        </div>
                        <div
                            v-for="section in sidebarSections"
                            :key="section.key"
                            class="rounded-md"
                        >
                            <button
                                class="flex w-full items-center justify-between rounded-md px-3 py-2 text-left text-[13px] font-semibold text-emerald-50 transition hover:bg-emerald-700 hover:text-white"
                                @click="toggleSection(section.key)"
                            >
                                <span>{{ section.title }}</span>
                                <span
                                    class="text-xs text-emerald-200"
                                >
                                    {{ openSections.includes(section.key) ? '−' : '+' }}
                                </span>
                            </button>
                            <ul
                                v-if="openSections.includes(section.key)"
                                class="mt-1 space-y-0.5 border-l border-emerald-800/60 pl-3 text-[12px] text-emerald-100/90"
                            >
                                <li
                                    v-for="item in section.items"
                                    :key="item"
                                >
                                    <Link
                                        :href="
                                                section.key === 'dashboard' && item === 'Overview'
                                                    ? route('dashboard')
                                            : section.key === 'dashboard' && item === 'Statistics'
                                                ? route('statistics')
                                            : section.key === 'dashboard' && item === 'Recent Activities'
                                                ? route('recent-activities')
                                            : section.key === 'teachers' && item === 'View Teachers'
                                                ? route('teachers.index')
                                            : section.key === 'teachers' && item === 'Add Teacher'
                                                ? route('teachers.create')
                                            : section.key === 'students' && (item === 'View Students' || item === 'Add Student')
                                                ? route('students.index')
                                            : section.key === 'students' && item === 'Promote Students'
                                                ? route('students.promote')
                                            : section.key === 'classes' && item === 'Manage Classes'
                                                ? route('classes.index')
                                            : section.key === 'classes' && item === 'Manage Streams'
                                                ? route('streams.index')
                                            : section.key === 'classes' && item === 'Class Teachers'
                                                ? route('classes.teachers.index')
                                            : section.key === 'subjects' && item === 'View Subjects'
                                                ? route('subjects.index')
                                            : section.key === 'subjects' && item === 'Add Subject'
                                                ? route('subjects.create')
                                            : section.key === 'subjects' && item === 'Assign Teachers'
                                                ? route('subjects.assign-teachers')
                                            : section.key === 'exams' && item === 'Create Exam'
                                                ? route('exams.create')
                                            : section.key === 'exams' && item === 'Enter Marks'
                                                ? route('exams.marks')
                                            : section.key === 'results' && item === 'Process Results'
                                                ? route('results.process')
                                            : section.key === 'results' && item === 'Class Performance'
                                                ? route('results.class')
                                            : section.key === 'results' && item === 'Subject Performance'
                                                ? route('results.subject')
                                            : section.key === 'results' && item === 'Ranking'
                                                ? route('results.ranking')
                                            : section.key === 'results' && item === 'Publish Results'
                                                ? route('results.publish')
                                            : section.key === 'reports' && item === 'Student Report Card'
                                                ? route('reports.students.index')
                                            : section.key === 'reports' && item === 'Class Report'
                                                ? route('reports.classes.index')
                                            : section.key === 'reports' && item === 'School Report'
                                                ? route('reports.school')
                                            : section.key === 'timetables' && (item === 'Class Timetables' || item === 'All Timetables')
                                                ? route('timetables.index')
                                            : section.key === 'timetables' && item === 'Create Timetable'
                                                ? route('timetables.create')
                                            : section.key === 'timetables' && item === 'Invigilation Timetable'
                                                ? route('timetables.invigilation')
                                            : section.key === 'timetables' && item === 'Sitting Plan'
                                                ? route('sitting-plans.index')
                                            : section.key === 'timetables' && item === 'Resources'
                                                ? route('resources.index')
                                            : section.key === 'timetables' && item === 'Topics'
                                                ? route('topics.index')
                                            : section.key === 'notifications' && item === 'Announcements'
                                                ? route('announcements.index')
                                            : section.key === 'notifications' && item === 'Results Alerts (SMS/Email)'
                                                ? route('notifications.sms')
                                            : section.key === 'notifications' && item === 'Calendar'
                                                ? route('calendar')
                                            : section.key === 'settings' && item === 'School Information'
                                                ? route('settings.school-information')
                                            : section.key === 'settings' && item === 'Academic Year'
                                                ? route('settings.academic-year')
                                            : section.key === 'settings' && item === 'Grading System'
                                                ? route('settings.grading-system')
                                            : section.key === 'settings' && item === 'Logo & Branding'
                                                ? route('settings.logo-branding')
                                            : section.key === 'settings' && item === 'SMS/Email Settings'
                                                ? route('settings.email-sms')
                                            : section.key === 'settings' && item === 'User Management'
                                                ? route('settings.user-management')
                                            : section.key === 'hostels' && item === 'All Hostel Students'
                                                ? route('hostel-students.index')
                                            : section.key === 'hostels' && item === 'Parents & Guardians'
                                                ? route('hostel-guardians.index')
                                            : section.key === 'hostels' && item === 'Payments'
                                                ? route('hostel-payments.index')
                                            : section.key === 'hostels' && item === 'Matron & Patron'
                                                ? route('hostel-matron-patron.index')
                                            : section.key === 'hostels' && item === 'Rooms & Beds'
                                                ? route('hostel-rooms-beds.index')
                                            : section.key === 'hostels' && item === 'Allocation'
                                                ? route('hostel-allocations.index')
                                            : section.key === 'hostels' && item === 'Hostel Reports'
                                                ? route('hostel-reports.index')
                                                : '#'
                                        "
                                        class="flex items-center justify-between rounded px-2 py-1 text-emerald-50 transition hover:bg-emerald-700 hover:text-white"
                                        :class="{
                                            'bg-emerald-800 text-white':
                                                (section.key === 'dashboard' && item === 'Overview' && route().current('dashboard')) ||
                                                (section.key === 'dashboard' && item === 'Statistics' && route().current('statistics')) ||
                                                (section.key === 'dashboard' && item === 'Recent Activities' && route().current('recent-activities')) ||
                                                (section.key === 'students' && (item === 'View Students' || item === 'Add Student') && route().current('students.index')) ||
                                                (section.key === 'students' && item === 'Promote Students' && route().current('students.promote')) ||
                                                (section.key === 'classes' && item === 'Manage Classes' && route().current('classes.index')) ||
                                                (section.key === 'classes' && item === 'Manage Streams' && route().current('streams.index')) ||
                                                (section.key === 'classes' && item === 'Class Teachers' && route().current('classes.teachers.index')) ||
                                                (section.key === 'teachers' && item === 'View Teachers' && route().current('teachers.index')) ||
                                                (section.key === 'teachers' && item === 'Add Teacher' && route().current('teachers.create')) ||
                                                (section.key === 'subjects' && item === 'View Subjects' && route().current('subjects.index')) ||
                                                (section.key === 'subjects' && item === 'Add Subject' && route().current('subjects.create')) ||
                                                (section.key === 'subjects' && item === 'Assign Teachers' && route().current('subjects.assign-teachers')) ||
                                                (section.key === 'exams' && item === 'Create Exam' && route().current('exams.create')) ||
                                                (section.key === 'exams' && item === 'Enter Marks' && route().current('exams.marks')) ||
                                                (section.key === 'results' && item === 'Process Results' && route().current('results.process')) ||
                                                (section.key === 'results' && item === 'Class Performance' && route().current('results.class')) ||
                                                (section.key === 'results' && item === 'Subject Performance' && route().current('results.subject')) ||
                                                (section.key === 'results' && item === 'Ranking' && route().current('results.ranking')) ||
                                                (section.key === 'results' && item === 'Publish Results' && route().current('results.publish')) ||
                                                (section.key === 'reports' && item === 'Student Report Card' && (route().current('reports.students.index') || route().current('reports.students.show'))) ||
                                                (section.key === 'reports' && item === 'Class Report' && (route().current('reports.classes.index') || route().current('reports.classes.show'))) ||
                                                (section.key === 'reports' && item === 'School Report' && route().current('reports.school')) ||
                                                (section.key === 'timetables' && (item === 'Class Timetables' || item === 'All Timetables') && route().current('timetables.index')) ||
                                                (section.key === 'timetables' && item === 'Create Timetable' && route().current('timetables.create')) ||
                                                (section.key === 'timetables' && item === 'Invigilation Timetable' && route().current('timetables.invigilation')) ||
                                                (section.key === 'timetables' && item === 'Sitting Plan' && route().current('sitting-plans.index')) ||
                                                (section.key === 'timetables' && item === 'Resources' && route().current('resources.index')) ||
                                                (section.key === 'timetables' && item === 'Topics' && route().current('topics.index')) ||
                                                (section.key === 'notifications' && item === 'Announcements' && route().current('announcements.index')) ||
                                                (section.key === 'notifications' && item === 'Results Alerts (SMS/Email)' && route().current('notifications.sms')) ||
                                                (section.key === 'notifications' && item === 'Calendar' && route().current('calendar')) ||
                                                (section.key === 'settings' && item === 'School Information' && route().current('settings.school-information')) ||
                                                (section.key === 'settings' && item === 'Academic Year' && route().current('settings.academic-year')) ||
                                                (section.key === 'settings' && item === 'Grading System' && route().current('settings.grading-system')) ||
                                                (section.key === 'settings' && item === 'Logo & Branding' && route().current('settings.logo-branding')) ||
                                                (section.key === 'settings' && item === 'SMS/Email Settings' && route().current('settings.email-sms')) ||
                                                (section.key === 'settings' && item === 'User Management' && route().current('settings.user-management')) ||
                                                (section.key === 'hostels' && item === 'All Hostel Students' && route().current('hostel-students.index')) ||
                                                (section.key === 'hostels' && item === 'Parents & Guardians' && route().current('hostel-guardians.index')) ||
                                                (section.key === 'hostels' && item === 'Payments' && route().current('hostel-payments.index')) ||
                                                (section.key === 'hostels' && item === 'Matron & Patron' && route().current('hostel-matron-patron.index')) ||
                                                (section.key === 'hostels' && item === 'Rooms & Beds' && route().current('hostel-rooms-beds.index')) ||
                                                (section.key === 'hostels' && item === 'Allocation' && route().current('hostel-allocations.index')) ||
                                                (section.key === 'hostels' && item === 'Hostel Reports' && route().current('hostel-reports.index')),
                                        }"
                                    >
                                        <span>{{ item }}</span>
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </nav>
                </aside>

                <!-- Main content area -->
                <div class="relative flex-1">
                    <!-- Dimmed page content (header + main) -->
                    <div
                        :class="{
                            'pointer-events-none opacity-40':
                                ($page.props.schoolIncomplete && !route().current('settings.school-information')) ||
                                (!$page.props.schoolIncomplete && $page.props.classesIncomplete && !route().current('classes.index') && !route().current('settings.school-information')),
                        }"
                    >
                        <!-- Global alert (top-right) -->
                        <div
                            v-if="$page.props.flash && $page.props.flash.success"
                            class="fixed right-4 top-20 z-40 max-w-sm rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs text-emerald-900 shadow-lg"
                        >
                            <div class="flex items-start gap-2">
                                <span class="mt-0.5 text-emerald-600">✔</span>
                                <div>
                                    <p class="font-semibold">Saved</p>
                                    <p class="mt-0.5 text-[11px]">{{ $page.props.flash.success }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Page Heading -->
                        <header
                            class="bg-white shadow"
                            v-if="$slots.header"
                        >
                            <div class="px-4 py-6 sm:px-6 lg:px-8">
                                <slot name="header" />
                            </div>
                        </header>

                        <!-- Page Content -->
                        <main class="px-4 py-6 sm:px-6 lg:px-8">
                            <slot />
                        </main>
                    </div>

                    <!-- School completion required overlay (not dimmed) -->
                    <div
                        v-if="$page.props.schoolIncomplete && !route().current('settings.school-information')"
                        class="pointer-events-auto fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4 backdrop-blur-sm"
                    >
                        <div class="w-full max-w-2xl rounded-2xl bg-white p-9 shadow-[0_24px_55px_rgba(15,23,42,0.45)] ring-1 ring-emerald-100">
                            <div class="flex flex-col items-start gap-4 md:flex-row md:items-start md:gap-5">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                    <svg
                                        class="h-5 w-5"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"
                                        />
                                    </svg>
                                </div>
                                <div class="text-left md:text-center">
                                    <h2 class="mb-3 text-2xl font-semibold text-gray-900">
                                        Complete your school information to continue
                                    </h2>
                                    <p class="mb-2 text-sm text-gray-600">
                                        Before you can use the dashboard, classes, exams, results and other features, you need to fill in your school details.
                                    </p>
                                    <p class="mb-5 text-xs text-gray-500">
                                        This is a one-time setup step. Once you save your school information, all menus and pages will be fully available.
                                    </p>
                                    <Link
                                        :href="route('settings.school-information')"
                                        class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                                    >
                                        Continue
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Classes configuration required overlay (not dimmed) -->
                    <div
                        v-else-if="!$page.props.schoolIncomplete && $page.props.classesIncomplete && !route().current('classes.index') && !route().current('settings.school-information')"
                        class="pointer-events-auto fixed inset-0 z-40 flex items-center justify-center bg-black/60 px-4 backdrop-blur-sm"
                    >
                        <div class="w-full max-w-2xl rounded-2xl bg-white p-9 shadow-[0_24px_55px_rgba(15,23,42,0.45)] ring-1 ring-emerald-100">
                            <div class="flex flex-col items-start gap-4 md:flex-row md:items-start md:gap-5">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                    <svg
                                        class="h-5 w-5"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 7h16M4 12h8m-8 5h12"
                                        />
                                    </svg>
                                </div>
                                <div class="text-left md:text-center">
                                    <h2 class="mb-3 text-2xl font-semibold text-gray-900">
                                        Set up your classes before continuing
                                    </h2>
                                    <p class="mb-2 text-sm text-gray-600">
                                        You need to create the standard classes (Form I–Form IV) and assign subjects to them before using other modules.
                                    </p>
                                    <p class="mb-5 text-xs text-gray-500">
                                        Once all four forms exist and have subjects assigned, you will be able to access exams, results and other features.
                                    </p>
                                    <Link
                                        :href="route('classes.index')"
                                        class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                                    >
                                        Go to Classes
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
