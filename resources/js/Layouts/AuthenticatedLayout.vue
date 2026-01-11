<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const isSidebarOpen = ref(true);
const mobileSidebarOpen = ref(false);
const page = usePage();

const isAdmin = computed(() => page.props.auth.user?.role === 'super_admin');
const isTeacher = computed(() => page.props.auth.user?.role === 'teacher');

const homeHref = computed(() => {
    if (isAdmin.value) return safeRoute('admin.dashboard');
    if (isTeacher.value) return safeRoute('teacher.dashboard');
    return safeRoute('dashboard');
});

const userSidebarSections = [
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
        items: ['Teacher Assignments', 'Add Teacher', 'View Teachers', 'Credentials', 'Roles & Permissions'],
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
        items: ['Class Timetable', 'Class Timetables', 'All Timetables', 'Create Timetable', 'Teacher Initials', 'Invigilation Timetable', 'Sitting Plan', 'Resources', 'Topics'],
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
        items: ['My Details', 'Change Password', 'Logout'],
    },
];

const adminSidebarSections = [
    {
        key: 'admin-dashboard',
        title: 'Admin Dashboard',
        items: ['Overview', 'System Statistics'],
    },
    {
        key: 'admin-schools',
        title: 'Schools Management',
        items: ['All Schools', 'Pending Schools', 'Active Schools', 'School Details'],
    },
    {
        key: 'admin-users',
        title: 'Users Management',
        items: ['All Users', 'Admins', 'School Administrators', 'Active Users'],
    },
    {
        key: 'admin-system',
        title: 'System Settings',
        items: ['General Settings', 'Email Configuration', 'SMS Configuration', 'Backup & Restore'],
    },
    {
        key: 'admin-reports',
        title: 'System Reports',
        items: ['Schools Report', 'Users Report', 'Activity Logs', 'System Health'],
    },
    {
        key: 'admin-blog',
        title: 'Blog Management',
        items: ['All Posts', 'Create Post', 'Categories', 'Comments'],
    },
    {
        key: 'admin-subscribers',
        title: 'Subscribers',
        items: ['All Subscribers', 'Mailing Lists', 'Email Campaigns'],
    },
    {
        key: 'admin-helpdesk',
        title: 'Help Desk',
        items: ['Tickets', 'Categories', 'FAQ', 'Support Team'],
    },
    {
        key: 'admin-notifications',
        title: 'Notifications',
        items: ['Send Notification', 'Notification History', 'Templates'],
    },
    {
        key: 'admin-announcements',
        title: 'Announcements',
        items: ['All Announcements', 'Create Announcement', 'Scheduled'],
    },
    {
        key: 'profile',
        title: 'Profile',
        items: ['My Details', 'Change Password', 'Logout'],
    },
];

const teacherSidebarSections = [
    {
        key: 'teacher-dashboard',
        title: 'Dashboard',
        items: ['Overview'],
    },
    {
        key: 'students',
        title: 'Students',
        items: ['View Students'],
    },
    {
        key: 'exams',
        title: 'Exams',
        items: ['My Exams', 'Enter Marks'],
    },
    {
        key: 'results',
        title: 'Results',
        items: ['All Results', 'Class Performance', 'Subject Performance'],
    },
    {
        key: 'timetables',
        title: 'Timetables',
        items: ['My Timetable'],
    },
    {
        key: 'notifications',
        title: 'Announcements',
        items: ['Announcements'],
    },
    {
        key: 'profile',
        title: 'Profile',
        items: ['My Account', 'Change Password', 'Logout'],
    },
];

const sidebarSections = computed(() => {
    if (isAdmin.value) return adminSidebarSections;
    if (isTeacher.value) return teacherSidebarSections;
    return userSidebarSections;
});

const openSections = ref([]);

const toggleSection = (key) => {
    if (openSections.value.includes(key)) {
        openSections.value = openSections.value.filter((k) => k !== key);
    } else {
        openSections.value.push(key);
    }
};

const safeRoute = (name, params = undefined) => {
    try {
        return params === undefined ? route(name) : route(name, params);
    } catch (e) {
        return '#';
    }
};

const getRouteForMenuItem = (sectionKey, itemName) => {
    // Admin routes
    if (sectionKey === 'admin-dashboard' && itemName === 'Overview') return route('admin.dashboard');
    if (sectionKey === 'admin-dashboard' && itemName === 'System Statistics') return route('admin.statistics');
    if (sectionKey === 'teacher-dashboard' && itemName === 'Overview') return route('teacher.dashboard');
    
    // Schools Management
    if (sectionKey === 'admin-schools' && itemName === 'All Schools') return route('admin.schools.index');
    if (sectionKey === 'admin-schools' && itemName === 'Pending Schools') return route('admin.schools.pending');
    if (sectionKey === 'admin-schools' && itemName === 'Active Schools') return route('admin.schools.active');
    if (sectionKey === 'admin-schools' && itemName === 'School Details') return route('admin.schools.details');
    
    // Users Management
    if (sectionKey === 'admin-users' && itemName === 'All Users') return route('admin.users.index');
    if (sectionKey === 'admin-users' && itemName === 'Admins') return route('admin.users.admins');
    if (sectionKey === 'admin-users' && itemName === 'School Administrators') return route('admin.users.school-admins');
    if (sectionKey === 'admin-users' && itemName === 'Active Users') return route('admin.users.active');
    
    // System Settings
    if (sectionKey === 'admin-system' && itemName === 'General Settings') return route('admin.settings.general');
    if (sectionKey === 'admin-system' && itemName === 'Email Configuration') return route('admin.settings.email');
    if (sectionKey === 'admin-system' && itemName === 'SMS Configuration') return route('admin.settings.sms');
    if (sectionKey === 'admin-system' && itemName === 'Backup & Restore') return route('admin.settings.backup');
    
    // System Reports
    if (sectionKey === 'admin-reports' && itemName === 'Schools Report') return route('admin.reports.schools');
    if (sectionKey === 'admin-reports' && itemName === 'Users Report') return route('admin.reports.users');
    if (sectionKey === 'admin-reports' && itemName === 'Activity Logs') return route('admin.reports.logs');
    if (sectionKey === 'admin-reports' && itemName === 'System Health') return route('admin.reports.health');
    
    // Blog Management
    if (sectionKey === 'admin-blog' && itemName === 'All Posts') return route('admin.blog.index');
    if (sectionKey === 'admin-blog' && itemName === 'Create Post') return route('admin.blog.create');
    if (sectionKey === 'admin-blog' && itemName === 'Categories') return route('admin.blog.categories');
    if (sectionKey === 'admin-blog' && itemName === 'Comments') return route('admin.blog.comments');
    
    // Subscribers
    if (sectionKey === 'admin-subscribers' && itemName === 'All Subscribers') return route('admin.subscribers.index');
    if (sectionKey === 'admin-subscribers' && itemName === 'Mailing Lists') return route('admin.subscribers.lists');
    if (sectionKey === 'admin-subscribers' && itemName === 'Email Campaigns') return route('admin.subscribers.campaigns');
    
    // Help Desk
    if (sectionKey === 'admin-helpdesk' && itemName === 'Tickets') return route('admin.helpdesk.tickets');
    if (sectionKey === 'admin-helpdesk' && itemName === 'Categories') return route('admin.helpdesk.categories');
    if (sectionKey === 'admin-helpdesk' && itemName === 'FAQ') return route('admin.helpdesk.faq');
    if (sectionKey === 'admin-helpdesk' && itemName === 'Support Team') return route('admin.helpdesk.team');
    
    // Notifications
    if (sectionKey === 'admin-notifications' && itemName === 'Send Notification') return route('admin.notifications.send');
    if (sectionKey === 'admin-notifications' && itemName === 'Notification History') return route('admin.notifications.history');
    if (sectionKey === 'admin-notifications' && itemName === 'Templates') return route('admin.notifications.templates');
    
    // Announcements
    if (sectionKey === 'admin-announcements' && itemName === 'All Announcements') return route('admin.announcements.index');
    if (sectionKey === 'admin-announcements' && itemName === 'Create Announcement') return route('admin.announcements.create');
    if (sectionKey === 'admin-announcements' && itemName === 'Scheduled') return route('admin.announcements.scheduled');
    
    // User routes (existing)
    if (sectionKey === 'dashboard' && itemName === 'Overview') return route('dashboard');
    if (sectionKey === 'dashboard' && itemName === 'Statistics') return route('statistics');
    if (sectionKey === 'dashboard' && itemName === 'Recent Activities') return route('recent-activities');
    if (sectionKey === 'teachers' && itemName === 'Teacher Assignments') return '/teachers/assignments';
    if (sectionKey === 'teachers' && itemName === 'View Teachers') return route('teachers.index');
    if (sectionKey === 'teachers' && itemName === 'Add Teacher') return route('teachers.create');
    if (sectionKey === 'teachers' && itemName === 'Credentials') return route('teachers.credentials');
    if (sectionKey === 'teachers' && itemName === 'Roles & Permissions') return route('roles.index');
    if (sectionKey === 'students' && (itemName === 'View Students' || itemName === 'Add Student')) {
        return isTeacher.value ? route('teacher.students.index') : route('students.index');
    }
    if (sectionKey === 'students' && itemName === 'Promote Students') return route('students.promote');
    if (sectionKey === 'classes' && itemName === 'Manage Classes') return route('classes.index');
    if (sectionKey === 'classes' && itemName === 'Manage Streams') return route('streams.index');
    if (sectionKey === 'classes' && itemName === 'Class Teachers') return route('classes.teachers.index');
    if (sectionKey === 'subjects' && itemName === 'View Subjects') return route('subjects.index');
    if (sectionKey === 'subjects' && itemName === 'Add Subject') return route('subjects.create');
    if (sectionKey === 'subjects' && itemName === 'Assign Teachers') return route('subjects.assign-teachers');
    if (sectionKey === 'exams' && itemName === 'Create Exam') return route('exams.create');
    if (sectionKey === 'exams' && itemName === 'My Exams') return isTeacher.value ? route('teacher.exams.index') : route('exams.create');
    if (sectionKey === 'exams' && itemName === 'Enter Marks') return isTeacher.value ? route('teacher.exams.marks') : route('exams.marks');
    if (sectionKey === 'results' && itemName === 'Process Results') return route('results.process');
    if (sectionKey === 'results' && itemName === 'All Results') return isTeacher.value ? route('teacher.results.index') : route('results.process');
    if (sectionKey === 'results' && itemName === 'Class Performance') return isTeacher.value ? route('teacher.results.class') : route('results.class');
    if (sectionKey === 'results' && itemName === 'Subject Performance') return isTeacher.value ? route('teacher.results.subject') : route('results.subject');
    if (sectionKey === 'results' && itemName === 'Ranking') return route('results.ranking');
    if (sectionKey === 'results' && itemName === 'Publish Results') return route('results.publish');
    if (sectionKey === 'reports' && itemName === 'Student Report Card') return route('reports.students.index');
    if (sectionKey === 'reports' && itemName === 'Class Report') return route('reports.classes.index');
    if (sectionKey === 'reports' && itemName === 'School Report') return route('reports.school');
    if (sectionKey === 'timetables' && itemName === 'My Timetable') return route('teacher.timetables.my');
    if (sectionKey === 'timetables' && itemName === 'Class Timetable') return route('timetables.class');
    if (sectionKey === 'timetables' && (itemName === 'Class Timetables' || itemName === 'All Timetables')) {
        return isTeacher.value ? route('teacher.timetables.my') : route('timetables.index');
    }
    if (sectionKey === 'timetables' && itemName === 'Create Timetable') return route('timetables.create');
    if (sectionKey === 'timetables' && itemName === 'Teacher Initials') return route('timetables.initials');
    if (sectionKey === 'timetables' && itemName === 'Invigilation Timetable') return route('timetables.invigilation');
    if (sectionKey === 'timetables' && itemName === 'Sitting Plan') return route('sitting-plans.index');
    if (sectionKey === 'timetables' && itemName === 'Resources') return route('resources.index');
    if (sectionKey === 'timetables' && itemName === 'Topics') return route('topics.index');
    if (sectionKey === 'notifications' && itemName === 'Announcements') {
        return isTeacher.value ? route('teacher.announcements.index') : route('announcements.index');
    }
    if (sectionKey === 'notifications' && itemName === 'Results Alerts (SMS/Email)') return route('notifications.sms');
    if (sectionKey === 'notifications' && itemName === 'Calendar') return route('calendar');
    if (sectionKey === 'settings' && itemName === 'School Information') return route('settings.school-information');
    if (sectionKey === 'settings' && itemName === 'Academic Year') return route('settings.academic-year');
    if (sectionKey === 'settings' && itemName === 'Grading System') return route('settings.grading-system');
    if (sectionKey === 'settings' && itemName === 'Logo & Branding') return route('settings.logo-branding');
    if (sectionKey === 'settings' && itemName === 'SMS/Email Settings') return route('settings.email-sms');
    if (sectionKey === 'settings' && itemName === 'User Management') return route('settings.user-management');
    if (sectionKey === 'hostels' && itemName === 'All Hostel Students') return route('hostel-students.index');
    if (sectionKey === 'hostels' && itemName === 'Parents & Guardians') return route('hostel-guardians.index');
    if (sectionKey === 'hostels' && itemName === 'Payments') return route('hostel-payments.index');
    if (sectionKey === 'hostels' && itemName === 'Matron & Patron') return route('hostel-matron-patron.index');
    if (sectionKey === 'hostels' && itemName === 'Rooms & Beds') return route('hostel-rooms-beds.index');
    if (sectionKey === 'hostels' && itemName === 'Allocation') return route('hostel-allocations.index');
    if (sectionKey === 'hostels' && itemName === 'Hostel Reports') return route('hostel-reports.index');
    if (sectionKey === 'profile' && itemName === 'My Account') {
        return isTeacher.value ? route('teacher.details') : route('profile.edit');
    }
    if (sectionKey === 'profile' && itemName === 'My Details') return route('teacher.details');
    if (sectionKey === 'profile' && itemName === 'Change Password') {
        return isTeacher.value ? route('teacher.change-password') : route('profile.edit');
    }
    if (sectionKey === 'profile' && itemName === 'Logout') return route('logout');
    
    return '#';
};
</script>

<template>
    <div>
        <div class="min-h-screen bg-slate-50">
            <nav class="border-b border-emerald-900/40 bg-emerald-900 text-emerald-50 print:hidden">
                <!-- Primary Navigation Menu -->
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex items-center gap-3">
                            <!-- Sidebar toggle -->
                            <button
                                class="hidden sm:inline-flex h-9 w-9 items-center justify-center rounded-md border border-emerald-800 bg-emerald-800 text-emerald-100 shadow-sm transition hover:bg-emerald-700 focus:outline-none"
                                @click="isSidebarOpen = !isSidebarOpen"
                                type="button"
                            >
                                <span class="material-icons text-[18px] leading-none">menu</span>
                            </button>

                            <button
                                class="inline-flex sm:hidden h-9 w-9 items-center justify-center rounded-md border border-emerald-800 bg-emerald-800 text-emerald-100 shadow-sm transition hover:bg-emerald-700 focus:outline-none"
                                type="button"
                                @click="mobileSidebarOpen = true"
                            >
                                <span class="material-icons text-[18px] leading-none">menu</span>
                            </button>

                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="homeHref">
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
                                                <span class="material-icons text-[18px] leading-none">notifications</span>
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
                                class="hidden inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
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
                            :href="homeHref"
                            :active="route().current(isTeacher ? 'teacher.dashboard' : isAdmin ? 'admin.dashboard' : 'dashboard')"
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

            <div
                v-if="mobileSidebarOpen"
                class="fixed inset-0 z-40 bg-black/40 sm:hidden"
                @click="mobileSidebarOpen = false"
            ></div>

            <aside
                class="fixed inset-y-0 right-0 z-50 w-72 transform border-l border-emerald-900/30 bg-emerald-900 px-3 py-4 text-emerald-50 transition-transform duration-200 sm:hidden print:hidden"
                :class="mobileSidebarOpen ? 'translate-x-0' : 'translate-x-full'"
            >
                <div class="mb-3 flex items-center justify-between px-2">
                    <div class="text-xs font-semibold uppercase tracking-wide text-emerald-200">
                        Menu
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-emerald-800 bg-emerald-800 text-emerald-100 shadow-sm transition hover:bg-emerald-700 focus:outline-none"
                        @click="mobileSidebarOpen = false"
                    >
                        <span class="material-icons text-[18px] leading-none">close</span>
                    </button>
                </div>
                <nav class="max-h-[calc(100vh-6rem)] space-y-1 overflow-y-auto pr-1 text-sm">
                    <div
                        v-for="section in sidebarSections"
                        :key="`m-${section.key}`"
                        class="rounded-md"
                    >
                        <button
                            class="flex w-full items-center justify-between rounded-md px-3 py-2 text-left text-[13px] font-semibold text-emerald-50 transition hover:bg-emerald-700 hover:text-white"
                            @click="toggleSection(section.key)"
                        >
                            <span>{{ section.title }}</span>
                            <span class="text-xs text-emerald-200">
                                {{ openSections.includes(section.key) ? '−' : '+' }}
                            </span>
                        </button>
                        <ul
                            v-if="openSections.includes(section.key)"
                            class="mt-1 space-y-0.5 border-l border-emerald-800/60 pl-3 text-[12px] text-emerald-100/90"
                        >
                            <li v-for="item in section.items" :key="`m-${section.key}-${item}`">
                                <Link
                                    v-if="!(section.key === 'profile' && item === 'Logout')"
                                    :href="getRouteForMenuItem(section.key, item)"
                                    class="flex items-center justify-between rounded px-2 py-1 text-emerald-50 transition hover:bg-emerald-700 hover:text-white"
                                    @click="mobileSidebarOpen = false"
                                >
                                    <span>{{ item }}</span>
                                </Link>

                                <Link
                                    v-else
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="flex w-full items-center justify-between rounded px-2 py-1 text-left text-emerald-50 transition hover:bg-emerald-700 hover:text-white"
                                    @click="mobileSidebarOpen = false"
                                >
                                    <span>Logout</span>
                                </Link>
                            </li>
                        </ul>
                    </div>
                </nav>
            </aside>

            <div class="flex min-h-[calc(100vh-4rem)]">
                <!-- Sidebar -->
                <aside
                    v-show="isSidebarOpen"
                    class="hidden w-72 border-r border-emerald-900/30 bg-emerald-900 px-3 py-4 text-emerald-50 sm:block print:hidden"
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
                                <span class="text-xs text-emerald-200">
                                    {{ openSections.includes(section.key) ? '−' : '+' }}
                                </span>
                            </button>
                            <ul
                                v-if="openSections.includes(section.key)"
                                class="mt-1 space-y-0.5 border-l border-emerald-800/60 pl-3 text-[12px] text-emerald-100/90"
                            >
                                <li v-for="item in section.items" :key="`${section.key}-${item}`">
                                    <Link
                                        v-if="!(section.key === 'profile' && item === 'Logout')"
                                        :href="getRouteForMenuItem(section.key, item)"
                                        class="flex items-center justify-between rounded px-2 py-1 text-emerald-50 transition hover:bg-emerald-700 hover:text-white"
                                    >
                                        <span>{{ item }}</span>
                                    </Link>

                                    <Link
                                        v-else
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                        class="flex w-full items-center justify-between rounded px-2 py-1 text-left text-emerald-50 transition hover:bg-emerald-700 hover:text-white"
                                    >
                                        <span>Logout</span>
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
                            class="border-b border-slate-200 bg-slate-50/80 backdrop-blur"
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
