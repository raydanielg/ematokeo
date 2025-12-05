<?php

use App\Http\Controllers\ProfileController;
use App\Models\User;
use App\Http\Controllers\MarksController;
use App\Models\NotificationSetting;
use App\Models\Timetable;
use App\Models\Subject;
use Illuminate\Support\Str;
use App\Models\SchoolClass;
use App\Models\SittingPlan;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\AcademicYear;
use App\Models\GradingScheme;
use App\Models\RecentActivity;
use App\Models\School;
use App\Models\Student;
use App\Models\BookResource;
use App\Models\Hostel;
use App\Models\HostelRoom;
use App\Models\HostelAllocation;
use App\Models\HostelPayment;
use App\Models\HostelRequirement;
use App\Models\Topic;
use App\Models\Event;
use App\Models\SmsNotification;
use App\Models\Announcement;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/about', function () {
    return Inertia::render('About', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/blog', function () {
    return Inertia::render('Blog', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/blog/{id}', function () {
    return Inertia::render('BlogDetail', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/publications', function () {
    return Inertia::render('Publications', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/contact', function () {
    return Inertia::render('Contact', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/success', function () {
    return Inertia::render('Auth/Success');
});

// Allow GET /logout to avoid MethodNotAllowed when URL is opened directly
Route::get('/logout', function (\Illuminate\Http\Request $request) {
    if (\Illuminate\Support\Facades\Auth::check()) {
        \Illuminate\Support\Facades\Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    return redirect('/');
});

Route::get('/dashboard', function () {
    $today = now()->toDateString();
    $user = request()->user();
    $schoolId = $user?->school_id;

    $studentQuery = Student::query();
    $subjectQuery = Subject::query();
    $classQuery = SchoolClass::query();

    if ($schoolId) {
        $studentQuery->where('school_id', $schoolId);
        $subjectQuery->where('school_id', $schoolId);
        $classQuery->where('school_id', $schoolId);
    }

    $stats = [
        'students' => $studentQuery->count(),
        'subjects' => $subjectQuery->count(),
        'classes' => $classQuery->count(),
    ];

    $upcomingEventsQuery = Event::whereDate('date', '>=', $today)
        ->orderBy('date')
        ->limit(6);

    if ($schoolId) {
        $upcomingEventsQuery->where('school_id', $schoolId);
    }

    $upcomingEvents = $upcomingEventsQuery->get([
        'id',
        'date',
        'title',
        'description',
    ]);

    $recentAnnouncementsQuery = Announcement::orderByDesc('created_at')
        ->limit(4);

    if ($schoolId) {
        $recentAnnouncementsQuery->where('school_id', $schoolId);
    }

    $recentAnnouncements = $recentAnnouncementsQuery->get([
        'id',
        'title',
        'body',
        'created_at',
    ]);

    // Results summary (basic dashboard analytics)
    $currentResultsYear = ExamResult::max('academic_year');

    $resultsSummary = [
        'year' => $currentResultsYear,
        'total' => 0,
        'pass' => 0,
        'fail' => 0,
        'passRate' => 0,
        'failRate' => 0,
        'grades' => [
            'A' => 0,
            'B' => 0,
            'C' => 0,
            'D' => 0,
            'F' => 0,
        ],
    ];

    $topStudents = collect();
    $bottomStudents = collect();

    if ($currentResultsYear) {
        $baseQuery = ExamResult::where('academic_year', $currentResultsYear)
            ->whereHas('student', function($q) use ($schoolId) {
                if ($schoolId) {
                    $q->where('school_id', $schoolId);
                }
            });

        $totalResults = (clone $baseQuery)->count();

        if ($totalResults > 0) {
            $passCount = (clone $baseQuery)->where('grade', '!=', 'F')->count();
            $failCount = (clone $baseQuery)->where('grade', 'F')->count();

            $gradeCounts = [
                'A' => (clone $baseQuery)->where('grade', 'A')->count(),
                'B' => (clone $baseQuery)->where('grade', 'B')->count(),
                'C' => (clone $baseQuery)->where('grade', 'C')->count(),
                'D' => (clone $baseQuery)->where('grade', 'D')->count(),
                'F' => (clone $baseQuery)->where('grade', 'F')->count(),
            ];

            $passRate = $totalResults > 0 ? round(($passCount / $totalResults) * 100) : 0;
            $failRate = 100 - $passRate;

            $resultsSummary = [
                'year' => $currentResultsYear,
                'total' => $totalResults,
                'pass' => $passCount,
                'fail' => $failCount,
                'passRate' => $passRate,
                'failRate' => $failRate,
                'grades' => $gradeCounts,
            ];

            // Top and bottom 10 students (by average marks) for this school and year
            $studentBaseQuery = DB::table('exam_results')
                ->join('students', 'exam_results.student_id', '=', 'students.id')
                ->where('exam_results.academic_year', $currentResultsYear);

            if ($schoolId) {
                $studentBaseQuery->where('students.school_id', $schoolId);
            }

            $rankingSelect = [
                'students.id as student_id',
                'students.full_name',
                'students.exam_number',
                'students.class_level',
                'students.stream',
                DB::raw('AVG(exam_results.marks) as avg_marks'),
            ];

            $topStudents = (clone $studentBaseQuery)
                ->select($rankingSelect)
                ->groupBy('students.id', 'students.full_name', 'students.exam_number', 'students.class_level', 'students.stream')
                ->orderByDesc('avg_marks')
                ->limit(10)
                ->get();

            $bottomStudents = (clone $studentBaseQuery)
                ->select($rankingSelect)
                ->groupBy('students.id', 'students.full_name', 'students.exam_number', 'students.class_level', 'students.stream')
                ->orderBy('avg_marks')
                ->limit(10)
                ->get();
        }
    }

    return Inertia::render('Dashboard', [
        'stats' => $stats,
        'upcomingEvents' => $upcomingEvents,
        'recentAnnouncements' => $recentAnnouncements,
        'resultsSummary' => $resultsSummary,
        'topStudents' => $topStudents,
        'bottomStudents' => $bottomStudents,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin Routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        $totalSchools = School::count();
        $activeSchools = School::whereNotNull('name')->count();
        $pendingSchools = $totalSchools - $activeSchools;

        $totalUsers = User::count();
        $adminUsers = User::where('role', 'admin')->count();

        $totalStudents = Student::count();
        $schoolsWithStudents = Student::distinct('school_id')->count('school_id');

        // Guard teachers count in case the table does not exist on this installation
        $totalTeachers = \Illuminate\Support\Facades\Schema::hasTable('teachers')
            ? \Illuminate\Support\Facades\DB::table('teachers')->count()
            : 0;

        $schoolsByLevel = School::select('level', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('level')
            ->orderBy('level')
            ->get();

        $recentSchools = School::latest('created_at')->limit(10)->get();

        return Inertia::render('Admin/Dashboard', [
            'totalSchools' => $totalSchools,
            'activeSchools' => $activeSchools,
            'pendingSchools' => $pendingSchools,
            'totalUsers' => $totalUsers,
            'adminUsers' => $adminUsers,
            'totalStudents' => $totalStudents,
            'totalTeachers' => $totalTeachers,
            'schoolsWithStudents' => $schoolsWithStudents,
            'schoolsByLevel' => $schoolsByLevel,
            'recentSchools' => $recentSchools,
        ]);
    })->name('dashboard');

    Route::get('/statistics', function () {
        $laravelVersion = \Illuminate\Foundation\Application::VERSION;
        $phpVersion = PHP_VERSION;
        $dbDriver = config('database.default');
        $dbConnection = config("database.connections.{$dbDriver}");

        // Get database info
        $dbName = $dbConnection['database'] ?? 'N/A';
        $dbHost = $dbConnection['host'] ?? 'N/A';

        // Count tables and records
        $tables = [];
        try {
            $tableNames = \Illuminate\Support\Facades\DB::select("SELECT name FROM sqlite_master WHERE type='table'");
            foreach ($tableNames as $table) {
                $tableName = $table->name;
                $count = \Illuminate\Support\Facades\DB::table($tableName)->count();
                $tables[] = [
                    'name' => $tableName,
                    'count' => $count,
                ];
            }
        } catch (\Exception $e) {
            // Fallback for non-sqlite databases
            $tables = [];
        }

        // Cache info
        $cacheDriver = config('cache.default');
        $cacheEnabled = $cacheDriver !== 'null';

        // Get file size of database
        $dbSize = 0;
        if (file_exists($dbName)) {
            $dbSize = filesize($dbName);
        }

        return Inertia::render('Admin/Statistics', [
            'laravelVersion' => $laravelVersion,
            'phpVersion' => $phpVersion,
            'dbDriver' => $dbDriver,
            'dbName' => $dbName,
            'dbHost' => $dbHost,
            'dbSize' => $dbSize,
            'tables' => $tables,
            'cacheDriver' => $cacheDriver,
            'cacheEnabled' => $cacheEnabled,
        ]);
    })->name('statistics');

    Route::post('/cache-clear', function () {
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');

        return back()->with('success', 'Cache cleared successfully!');
    })->name('cache-clear');

    // Schools Management
    Route::get('/schools', function () {
        $schools = School::with('users')->paginate(20);
        return Inertia::render('Admin/Schools/Index', [
            'schools' => $schools,
        ]);
    })->name('schools.index');

    Route::get('/schools/pending', function () {
        $schools = School::whereNull('name')->orWhere('name', '')->paginate(20);
        return Inertia::render('Admin/Schools/Pending', [
            'schools' => $schools,
        ]);
    })->name('schools.pending');

    Route::get('/schools/active', function () {
        $schools = School::whereNotNull('name')->where('name', '!=', '')->paginate(20);
        return Inertia::render('Admin/Schools/Active', [
            'schools' => $schools,
        ]);
    })->name('schools.active');

    Route::get('/schools/details', function () {
        return Inertia::render('Admin/Schools/Details');
    })->name('schools.details');

    // Users Management
    Route::get('/users', function () {
        $users = User::paginate(20);
        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    })->name('users.index');

    Route::get('/users/admins', function () {
        $users = User::where('role', 'admin')->paginate(20);
        return Inertia::render('Admin/Users/Admins', [
            'users' => $users,
        ]);
    })->name('users.admins');

    Route::get('/users/school-admins', function () {
        $users = User::where('role', '!=', 'admin')->paginate(20);
        return Inertia::render('Admin/Users/SchoolAdmins', [
            'users' => $users,
        ]);
    })->name('users.school-admins');

    Route::get('/users/active', function () {
        $users = User::where('is_active', true)->paginate(20);
        return Inertia::render('Admin/Users/Active', [
            'users' => $users,
        ]);
    })->name('users.active');

    // System Settings
    Route::get('/settings/general', function () {
        return Inertia::render('Admin/Settings/General');
    })->name('settings.general');

    Route::get('/settings/email', function () {
        return Inertia::render('Admin/Settings/Email');
    })->name('settings.email');

    Route::get('/settings/sms', function () {
        return Inertia::render('Admin/Settings/SMS');
    })->name('settings.sms');

    Route::get('/settings/backup', function () {
        return Inertia::render('Admin/Settings/Backup');
    })->name('settings.backup');

    // System Reports
    Route::get('/reports/schools', function () {
        return Inertia::render('Admin/Reports/Schools');
    })->name('reports.schools');

    Route::get('/reports/users', function () {
        return Inertia::render('Admin/Reports/Users');
    })->name('reports.users');

    Route::get('/reports/logs', function () {
        $logs = RecentActivity::latest('occurred_at')->paginate(50);
        return Inertia::render('Admin/Reports/Logs', [
            'logs' => $logs,
        ]);
    })->name('reports.logs');

    Route::get('/reports/health', function () {
        return Inertia::render('Admin/Reports/Health');
    })->name('reports.health');

    // Blog Management
    Route::get('/blog', function () {
        return Inertia::render('Admin/Blog/Index');
    })->name('blog.index');
    Route::get('/blog/create', function () {
        return Inertia::render('Admin/Blog/Create');
    })->name('blog.create');
    Route::get('/blog/categories', function () {
        return Inertia::render('Admin/Blog/Categories');
    })->name('blog.categories');
    Route::get('/blog/comments', function () {
        return Inertia::render('Admin/Blog/Comments');
    })->name('blog.comments');

    // Subscribers
    Route::get('/subscribers', function () {
        return Inertia::render('Admin/Subscribers/Index');
    })->name('subscribers.index');
    Route::get('/subscribers/lists', function () {
        return Inertia::render('Admin/Subscribers/Lists');
    })->name('subscribers.lists');
    Route::get('/subscribers/campaigns', function () {
        return Inertia::render('Admin/Subscribers/Campaigns');
    })->name('subscribers.campaigns');

    // Help Desk
    Route::get('/helpdesk/tickets', function () {
        return Inertia::render('Admin/HelpDesk/Tickets');
    })->name('helpdesk.tickets');
    Route::get('/helpdesk/categories', function () {
        return Inertia::render('Admin/HelpDesk/Categories');
    })->name('helpdesk.categories');
    Route::get('/helpdesk/faq', function () {
        return Inertia::render('Admin/HelpDesk/FAQ');
    })->name('helpdesk.faq');
    Route::get('/helpdesk/team', function () {
        return Inertia::render('Admin/HelpDesk/Team');
    })->name('helpdesk.team');

    // Notifications
    Route::get('/notifications/send', function () {
        return Inertia::render('Admin/Notifications/Send');
    })->name('notifications.send');
    Route::get('/notifications/history', function () {
        return Inertia::render('Admin/Notifications/History');
    })->name('notifications.history');
    Route::get('/notifications/templates', function () {
        return Inertia::render('Admin/Notifications/Templates');
    })->name('notifications.templates');

    // Announcements
    Route::get('/announcements', function () {
        return Inertia::render('Admin/Announcements/Index');
    })->name('announcements.index');
    Route::get('/announcements/create', function () {
        return Inertia::render('Admin/Announcements/Create');
    })->name('announcements.create');
    Route::get('/announcements/scheduled', function () {
        return Inertia::render('Admin/Announcements/Scheduled');
    })->name('announcements.scheduled');
});

Route::middleware('auth')->group(function () {
    Route::get('/statistics', function () {
        $user = request()->user();
        $schoolId = $user?->school_id;

        // Get form-wise statistics
        $formStats = [];
        for ($form = 1; $form <= 4; $form++) {
            $query = \App\Models\Student::where('class_level', "Form $form");
            if ($schoolId) {
                $query->where('school_id', $schoolId);
            }
            
            $totalStudents = $query->count();
            
            // Get students with passing marks (>= 40)
            $passStudents = 0;
            if ($totalStudents > 0) {
                $passStudents = \App\Models\ExamResult::whereHas('student', function($q) use ($schoolId) {
                    $q->where('class_level', "Form $form");
                    if ($schoolId) {
                        $q->where('school_id', $schoolId);
                    }
                })->where('marks', '>=', 40)->distinct('student_id')->count();
            }
            
            $formStats["form$form"] = [
                'students' => $totalStudents,
                'pass' => $totalStudents > 0 ? round(($passStudents / $totalStudents) * 100) : 0,
                'fail' => $totalStudents > 0 ? round((($totalStudents - $passStudents) / $totalStudents) * 100) : 0,
            ];
        }

        // Get gender statistics
        $totalStudents = \App\Models\Student::when($schoolId, function ($q) use ($schoolId) {
            return $q->where('school_id', $schoolId);
        })->count();

        $maleTotal = \App\Models\Student::where('gender', 'M')
            ->when($schoolId, function ($q) use ($schoolId) {
                return $q->where('school_id', $schoolId);
            })->count();

        $malePass = 0;
        if ($maleTotal > 0) {
            $malePass = \App\Models\ExamResult::whereHas('student', function($q) use ($schoolId) {
                $q->where('gender', 'M');
                if ($schoolId) {
                    $q->where('school_id', $schoolId);
                }
            })->where('marks', '>=', 40)->distinct('student_id')->count();
        }

        $femaleTotal = \App\Models\Student::where('gender', 'F')
            ->when($schoolId, function ($q) use ($schoolId) {
                return $q->where('school_id', $schoolId);
            })->count();

        $femalePass = 0;
        if ($femaleTotal > 0) {
            $femalePass = \App\Models\ExamResult::whereHas('student', function($q) use ($schoolId) {
                $q->where('gender', 'F');
                if ($schoolId) {
                    $q->where('school_id', $schoolId);
                }
            })->where('marks', '>=', 40)->distinct('student_id')->count();
        }

        $genderStats = [
            'male' => [
                'pass' => $maleTotal > 0 ? round(($malePass / $maleTotal) * 100) : 0,
                'fail' => $maleTotal > 0 ? round((($maleTotal - $malePass) / $maleTotal) * 100) : 0,
            ],
            'female' => [
                'pass' => $femaleTotal > 0 ? round(($femalePass / $femaleTotal) * 100) : 0,
                'fail' => $femaleTotal > 0 ? round((($femaleTotal - $femalePass) / $femaleTotal) * 100) : 0,
            ],
        ];

        // Get division statistics
        $divisionStats = [];
        $divisions = ['I', 'II', 'III', 'IV'];
        foreach ($divisions as $index => $div) {
            $divCount = \App\Models\Student::where('division', $div)
                ->when($schoolId, function ($q) use ($schoolId) {
                    return $q->where('school_id', $schoolId);
                })->count();
            
            $divisionStats["div" . ($index + 1)] = $totalStudents > 0 ? round(($divCount / $totalStudents) * 100) : 0;
        }

        return Inertia::render('Statistics', [
            'formStats' => $formStats,
            'genderStats' => $genderStats,
            'divisionStats' => $divisionStats,
            'totalStudents' => $totalStudents,
        ]);
    })->name('statistics');

    Route::get('/hostel-students', function () {
        $user = request()->user();
        $currentYear = AcademicYear::where('is_current', true)->value('year');

        $query = HostelAllocation::with([
            'student:id,full_name,exam_number,class_level,stream,gender',
            'hostel:id,name',
            'room:id,hostel_id,name',
        ])->withSum('payments as paid_sum', 'amount')
            ->orderByDesc('created_at');

        if ($user && $user->school_id) {
            $query->where('school_id', $user->school_id);
        }
        if ($currentYear) {
            $query->where('academic_year', $currentYear);
        }

        $hostelStudents = $query->get()->map(function (HostelAllocation $allocation) {
            $student = $allocation->student;
            $className = $student
                ? trim(($student->class_level ?? '').' '.($student->stream ?? '')) ?: null
                : null;

            $amount = (int) $allocation->fee_amount;
            $paid = (int) ($allocation->paid_sum ?? 0);
            $balance = max($amount - $paid, 0);

            if ($amount <= 0 && $paid <= 0) {
                $status = 'Unpaid';
            } elseif ($paid >= $amount && $amount > 0) {
                $status = 'Paid';
            } elseif ($paid > 0 && $paid < $amount) {
                $status = 'Partial';
            } else {
                $status = 'Unpaid';
            }

            return [
                'id' => $allocation->id,
                'student_name' => optional($student)->full_name,
                'exam_number' => optional($student)->exam_number,
                'class_name' => $className,
                'gender' => optional($student)->gender,
                'hostel_name' => optional($allocation->hostel)->name,
                'room_name' => optional($allocation->room)->name,
                'guardian_name' => $allocation->guardian_name,
                'guardian_phone' => $allocation->guardian_phone,
                'guardian_relationship' => $allocation->guardian_relationship,
                'amount' => $amount,
                'paid' => $paid,
                'balance' => $balance,
                'status' => $status,
            ];
        });

        return Inertia::render('HostelStudent', [
            'hostelStudents' => $hostelStudents,
        ]);
    })->name('hostel-students.index');

    Route::get('/hostel-students/{allocation}', function (HostelAllocation $allocation) {
        $allocation->load([
            'student:id,full_name,exam_number,class_level,stream,gender,school_id',
            'hostel:id,name',
            'room:id,hostel_id,name',
            'payments',
        ]);

        $student = $allocation->student;
        $className = $student
            ? trim(($student->class_level ?? '').' '.($student->stream ?? '')) ?: null
            : null;

        $amount = (int) $allocation->fee_amount;
        $paid = (int) $allocation->payments->sum('amount');
        $balance = max($amount - $paid, 0);

        if ($amount <= 0 && $paid <= 0) {
            $status = 'Unpaid';
        } elseif ($paid >= $amount && $amount > 0) {
            $status = 'Paid';
        } elseif ($paid > 0 && $paid < $amount) {
            $status = 'Partial';
        } else {
            $status = 'Unpaid';
        }

        $allocationPayload = [
            'id' => $allocation->id,
            'student_name' => optional($student)->full_name,
            'exam_number' => optional($student)->exam_number,
            'class_name' => $className,
            'gender' => optional($student)->gender,
            'hostel_name' => optional($allocation->hostel)->name,
            'room_name' => optional($allocation->room)->name,
            'guardian_name' => $allocation->guardian_name,
            'guardian_phone' => $allocation->guardian_phone,
            'guardian_relationship' => $allocation->guardian_relationship,
            'notes' => $allocation->notes,
            'amount' => $amount,
            'paid' => $paid,
            'balance' => $balance,
            'status' => $status,
        ];

        $school = null;
        if ($student && $student->school_id) {
            $schoolModel = School::find($student->school_id);
            if ($schoolModel) {
                $school = [
                    'name' => $schoolModel->name,
                    'phone' => $schoolModel->phone,
                    'email' => $schoolModel->email,
                    'district' => $schoolModel->district,
                    'region' => $schoolModel->region,
                ];
            }
        }

        $currentYear = AcademicYear::where('is_current', true)->value('year');

        // Load exam results for this student in the current academic year
        $examRows = collect();
        if ($student && $currentYear) {
            $examRows = ExamResult::with(['exam:id,name,type,academic_year', 'subject:id,subject_code,name'])
                ->where('student_id', $student->id)
                ->where('academic_year', $currentYear)
                ->orderBy('exam_id')
                ->orderBy('subject_id')
                ->get();
        }

        $exams = $examRows->map(function (ExamResult $res) {
            return [
                'exam_name' => optional($res->exam)->name,
                'exam_type' => optional($res->exam)->type,
                'academic_year' => $res->academic_year,
                'subject_code' => optional($res->subject)->subject_code,
                'subject_name' => optional($res->subject)->name ?: optional($res->subject)->subject_code,
                'marks' => $res->marks,
                'grade' => $res->grade,
            ];
        });

        // Load hostel requirements / rules template for this student (by school, hostel, class, year)
        $requirements = collect();
        if ($student && $currentYear) {
            $requirements = HostelRequirement::query()
                ->where(function ($q) use ($student) {
                    if ($student->school_id) {
                        $q->where('school_id', $student->school_id);
                    }
                })
                ->when($allocation->hostel_id, function ($q, $hostelId) {
                    $q->where(function ($inner) use ($hostelId) {
                        $inner->whereNull('hostel_id')->orWhere('hostel_id', $hostelId);
                    });
                })
                ->when($student->class_level, function ($q, $classLevel) {
                    $q->where(function ($inner) use ($classLevel) {
                        $inner->whereNull('class_level')->orWhere('class_level', $classLevel);
                    });
                })
                ->when($currentYear, function ($q, $year) {
                    $q->where(function ($inner) use ($year) {
                        $inner->whereNull('academic_year')->orWhere('academic_year', $year);
                    });
                })
                ->orderBy('hostel_id')
                ->orderBy('class_level')
                ->get([
                    'id',
                    'title',
                    'items',
                    'rules',
                ]);
        }

        return Inertia::render('HostelStudentReport', [
            'allocation' => $allocationPayload,
            'school' => $school,
            'academicYear' => $currentYear,
            'exams' => $exams,
            'requirements' => $requirements,
        ]);
    })->name('hostel-students.report');

    Route::get('/hostel-students/{allocation}/joining', function (HostelAllocation $allocation) {
        $allocation->load([
            'student:id,full_name,exam_number,class_level,stream,gender,school_id',
            'hostel:id,name',
        ]);

        $student = $allocation->student;

        $amount = (int) $allocation->fee_amount;
        $paid = (int) $allocation->payments()->sum('amount');
        $balance = max($amount - $paid, 0);

        $allocationPayload = [
            'id' => $allocation->id,
            'student_name' => optional($student)->full_name,
            'exam_number' => optional($student)->exam_number,
            'class_name' => $student ? trim(($student->class_level ?? '').' '.($student->stream ?? '')) ?: null : null,
            'hostel_name' => optional($allocation->hostel)->name,
            'guardian_name' => $allocation->guardian_name,
            'guardian_phone' => $allocation->guardian_phone,
            'guardian_relationship' => $allocation->guardian_relationship,
            'amount' => $amount,
            'paid' => $paid,
            'balance' => $balance,
        ];

        $school = null;
        if ($student && $student->school_id) {
            $schoolModel = School::find($student->school_id);
            if ($schoolModel) {
                $school = [
                    'name' => $schoolModel->name,
                    'phone' => $schoolModel->phone,
                    'email' => $schoolModel->email,
                    'district' => $schoolModel->district,
                    'region' => $schoolModel->region,
                ];
            }
        }

        $currentYear = AcademicYear::where('is_current', true)->value('year');

        // Reuse hostel requirements query logic
        $requirements = collect();
        if ($student && $currentYear) {
            $requirements = HostelRequirement::query()
                ->where(function ($q) use ($student) {
                    if ($student->school_id) {
                        $q->where('school_id', $student->school_id);
                    }
                })
                ->when($allocation->hostel_id, function ($q, $hostelId) {
                    $q->where(function ($inner) use ($hostelId) {
                        $inner->whereNull('hostel_id')->orWhere('hostel_id', $hostelId);
                    });
                })
                ->when($student->class_level, function ($q, $classLevel) {
                    $q->where(function ($inner) use ($classLevel) {
                        $inner->whereNull('class_level')->orWhere('class_level', $classLevel);
                    });
                })
                ->when($currentYear, function ($q, $year) {
                    $q->where(function ($inner) use ($year) {
                        $inner->whereNull('academic_year')->orWhere('academic_year', $year);
                    });
                })
                ->orderBy('hostel_id')
                ->orderBy('class_level')
                ->get([
                    'id',
                    'title',
                    'items',
                    'rules',
                ]);
        }

        return Inertia::render('HostelJoiningInstruction', [
            'allocation' => $allocationPayload,
            'school' => $school,
            'academicYear' => $currentYear,
            'requirements' => $requirements,
        ]);
    })->name('hostel-students.joining');

    Route::get('/hostel-students/search', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        $term = trim((string) $request->query('q', ''));

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $query = Student::query();
        if ($user && $user->school_id) {
            $query->where('school_id', $user->school_id);
        }

        $students = $query
            ->where(function ($q) use ($term) {
                $q->where('full_name', 'like', "%{$term}%")
                    ->orWhere('exam_number', 'like', "%{$term}%");
            })
            ->orderBy('full_name')
            ->limit(15)
            ->get([
                'id',
                'full_name',
                'exam_number',
                'class_level',
                'stream',
                'gender',
            ]);

        return response()->json($students);
    })->name('hostel-students.search');

    Route::get('/hostel-guardians', function () {
        $guardians = [];

        return Inertia::render('HostelParentsGuardians', [
            'guardians' => $guardians,
        ]);
    })->name('hostel-guardians.index');

    Route::get('/hostel-payments', function () {
        $user = request()->user();
        $currentYear = AcademicYear::where('is_current', true)->value('year');

        $query = HostelAllocation::with(['student:id,full_name,class_level,stream', 'hostel:id,name'])
            ->withSum('payments as paid_sum', 'amount')
            ->orderByDesc('created_at');

        if ($user && $user->school_id) {
            $query->where('school_id', $user->school_id);
        }
        if ($currentYear) {
            $query->where('academic_year', $currentYear);
        }

        $allocations = $query->get();

        $payments = $allocations->map(function (HostelAllocation $allocation) {
            $amount = (int) $allocation->fee_amount;
            $paid = (int) ($allocation->paid_sum ?? 0);
            $balance = max($amount - $paid, 0);

            if ($amount <= 0 && $paid <= 0) {
                $status = 'Unpaid';
            } elseif ($paid >= $amount && $amount > 0) {
                $status = 'Paid';
            } elseif ($paid > 0 && $paid < $amount) {
                $status = 'Partial';
            } else {
                $status = 'Unpaid';
            }

            $student = $allocation->student;
            $className = trim(($student->class_level ?? '').' '.($student->stream ?? '')) ?: null;

            return [
                'id' => $allocation->id,
                'student_name' => optional($student)->full_name,
                'class_name' => $className,
                'hostel_name' => optional($allocation->hostel)->name,
                'amount' => $amount,
                'paid' => $paid,
                'balance' => $balance,
                'status' => $status,
            ];
        });

        return Inertia::render('HostelPayments', [
            'payments' => $payments,
        ]);
    })->name('hostel-payments.index');

    Route::get('/hostel-payments/{allocation}/receipt', function (HostelAllocation $allocation) {
        $allocation->load([
            'student:id,full_name,class_level,stream,school_id',
            'hostel:id,name',
            'payments' => function ($q) {
                $q->orderBy('paid_on')->orderBy('created_at');
            },
        ]);

        $school = null;
        if ($allocation->student && $allocation->student->school_id) {
            $schoolModel = School::find($allocation->student->school_id);
            if ($schoolModel) {
                $school = [
                    'name' => $schoolModel->name,
                    'phone' => $schoolModel->phone,
                    'email' => $schoolModel->email,
                    'district' => $schoolModel->district,
                    'region' => $schoolModel->region,
                ];
            }
        }

        $amount = (int) $allocation->fee_amount;
        $paid = (int) $allocation->payments->sum('amount');
        $balance = max($amount - $paid, 0);

        if ($amount <= 0 && $paid <= 0) {
            $status = 'Unpaid';
        } elseif ($paid >= $amount && $amount > 0) {
            $status = 'Paid';
        } elseif ($paid > 0 && $paid < $amount) {
            $status = 'Partial';
        } else {
            $status = 'Unpaid';
        }

        $student = $allocation->student;
        $className = $student
            ? trim(($student->class_level ?? '').' '.($student->stream ?? '')) ?: null
            : null;

        $allocationPayload = [
            'id' => $allocation->id,
            'student_name' => optional($student)->full_name,
            'class_name' => $className,
            'hostel_name' => optional($allocation->hostel)->name,
            'amount' => $amount,
            'paid' => $paid,
            'balance' => $balance,
            'status' => $status,
        ];

        $payments = $allocation->payments->map(function (HostelPayment $payment) {
            return [
                'id' => $payment->id,
                'amount' => (int) $payment->amount,
                'paid_on' => optional($payment->paid_on)->format('Y-m-d'),
                'method' => $payment->method,
                'reference' => $payment->reference,
            ];
        });

        $currentYear = AcademicYear::where('is_current', true)->value('year');

        return Inertia::render('HostelPaymentReceipt', [
            'allocation' => $allocationPayload,
            'payments' => $payments,
            'school' => $school,
            'academicYear' => $currentYear,
        ]);
    })->name('hostel-payments.receipt');

    Route::post('/hostel-payments', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'allocation_id' => ['required', 'exists:hostel_allocations,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'paid_on' => ['required', 'date'],
            'method' => ['required', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        HostelPayment::create([
            'allocation_id' => $data['allocation_id'],
            'amount' => $data['amount'],
            'paid_on' => $data['paid_on'],
            'method' => $data['method'],
            'reference' => $data['reference'] ?? null,
            'received_by' => $user?->id,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('hostel-payments.index')
            ->with('success', 'Hostel payment recorded successfully.');
    })->name('hostel-payments.store');

    Route::get('/hostel-matron-patron', function () {
        $hostels = Hostel::orderBy('name')
            ->get(['id', 'name', 'type']);

        $user = request()->user();

        $staffQuery = \App\Models\User::with(['hostel:id,name'])
            ->whereIn('role', ['matron', 'patron'])
            ->orderBy('name');

        if ($user && $user->school_id) {
            $staffQuery->where('school_id', $user->school_id);
        }

        $staff = $staffQuery
            ->get([
                'id',
                'school_id',
                'hostel_id',
                'name',
                'email',
                'phone',
                'role',
            ])
            ->map(function (\App\Models\User $u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'phone' => $u->phone,
                    'role' => $u->role,
                    'hostel_name' => optional($u->hostel)->name,
                ];
            });

        return Inertia::render('HostelMatronPatron', [
            'staff' => $staff,
            'hostels' => $hostels,
        ]);
    })->name('hostel-matron-patron.index');

    Route::post('/hostel-matron-patron', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'hostel_id' => ['required', 'exists:hostels,id'],
            'role' => ['required', 'in:matron,patron'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:6'],
            'notes' => ['nullable', 'string'], // reserved for future use
        ]);

        $user = $request->user();

        // Generate username & default password based on name if not provided
        $name = trim($data['name']);
        $parts = preg_split('/\s+/', $name);
        $first = $parts[0] ?? '';
        $last = $parts[count($parts) - 1] ?? '';

        // Build base handle: first letter of first name + last name
        $baseHandle = strtolower(preg_replace('/[^a-z]/', '', mb_substr($first, 0, 1).$last));
        $baseHandle = $baseHandle ?: 'user';

        // Ensure username uniqueness by appending number if needed
        $username = $baseHandle;
        $counter = 1;
        while (\App\Models\User::where('username', $username)->exists()) {
            $username = $baseHandle.$counter;
            $counter++;
        }

        $rawPassword = $data['password'] ?? $baseHandle;

        \App\Models\User::create([
            'school_id' => $user?->school_id,
            'hostel_id' => $data['hostel_id'],
            'name' => $data['name'],
            'username' => $username,
            'email' => $data['email'],
            'role' => $data['role'],
            'is_active' => true,
            'phone' => $data['phone'],
            'password' => $rawPassword,
        ]);

        return redirect()
            ->route('hostel-matron-patron.index')
            ->with('success', 'Matron/Patron created successfully.');
    })->name('hostel-matron-patron.store');

    Route::get('/hostel-matron-patron/{user}/contract', function (\App\Models\User $user) {
        abort_unless(in_array($user->role, ['matron', 'patron']), 404);

        $user->load(['hostel:id,name', 'school:id,name,address']);

        $staff = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'hostel_name' => optional($user->hostel)->name,
        ];

        $school = null;
        if ($user->school) {
            $school = [
                'name' => $user->school->name,
                'address' => $user->school->address ?? null,
                'phone' => $user->school->phone ?? null,
                'email' => $user->school->email ?? null,
                'district' => $user->school->district ?? null,
                'region' => $user->school->region ?? null,
            ];
        }

        $currentYear = AcademicYear::where('is_current', true)->value('year');

        return Inertia::render('HostelMatronContractPreview', [
            'staff' => $staff,
            'school' => $school,
            'academicYear' => $currentYear,
        ]);
    })->name('hostel-matron-patron.contract');

    Route::get('/hostel-rooms-beds', function () {
        $currentYear = AcademicYear::where('is_current', true)->value('year');
        $user = request()->user();

        $hostelQuery = Hostel::orderBy('name');
        if ($user && $user->school_id) {
            $hostelQuery->where('school_id', $user->school_id);
        }
        if ($currentYear) {
            $hostelQuery->where(function ($q) use ($currentYear) {
                $q->whereNull('academic_year')->orWhere('academic_year', $currentYear);
            });
        }

        $hostels = $hostelQuery->get(['id', 'name', 'type', 'academic_year']);

        $roomQuery = HostelRoom::with('hostel:id,name')
            ->orderBy('hostel_id')
            ->orderBy('name');

        if ($user && $user->school_id) {
            $roomQuery->where('school_id', $user->school_id);
        }
        if ($currentYear) {
            $roomQuery->where(function ($q) use ($currentYear) {
                $q->whereNull('academic_year')->orWhere('academic_year', $currentYear);
            });
        }

        $rooms = $roomQuery
            ->get()
            ->map(function (HostelRoom $room) {
                return [
                    'id' => $room->id,
                    'hostel_id' => $room->hostel_id,
                    'hostel_name' => optional($room->hostel)->name,
                    'name' => $room->name,
                    'capacity' => $room->capacity,
                    'occupied' => $room->occupied,
                    'available' => $room->available,
                    'notes' => $room->notes,
                ];
            });

        return Inertia::render('HostelRoomsBeds', [
            'hostels' => $hostels,
            'rooms' => $rooms,
        ]);
    })->name('hostel-rooms-beds.index');

    Route::post('/hostel-rooms-beds', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'hostel_id' => ['required', 'exists:hostels,id'],
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:0'],
            'occupied' => ['nullable', 'integer', 'min:0'],
            'available' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $currentYear = AcademicYear::where('is_current', true)->value('year');
        $user = $request->user();

        $capacity = (int) $data['capacity'];
        $occupied = isset($data['occupied']) ? (int) $data['occupied'] : 0;
        $available = isset($data['available']) ? (int) $data['available'] : max($capacity - $occupied, 0);

        HostelRoom::create([
            'school_id' => $user?->school_id,
            'academic_year' => $currentYear,
            'hostel_id' => $data['hostel_id'],
            'name' => $data['name'],
            'capacity' => $capacity,
            'occupied' => $occupied,
            'available' => $available,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('hostel-rooms-beds.index')
            ->with('success', 'Hostel room added successfully.');
    })->name('hostel-rooms-beds.store');

    Route::put('/hostel-rooms-beds/{room}', function (\Illuminate\Http\Request $request, HostelRoom $room) {
        $data = $request->validate([
            'hostel_id' => ['required', 'exists:hostels,id'],
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:0'],
            'occupied' => ['nullable', 'integer', 'min:0'],
            'available' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $capacity = (int) $data['capacity'];
        $occupied = isset($data['occupied']) ? (int) $data['occupied'] : 0;
        $available = isset($data['available']) ? (int) $data['available'] : max($capacity - $occupied, 0);

        $room->update([
            'hostel_id' => $data['hostel_id'],
            'name' => $data['name'],
            'capacity' => $capacity,
            'occupied' => $occupied,
            'available' => $available,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('hostel-rooms-beds.index')
            ->with('success', 'Hostel room updated successfully.');
    })->name('hostel-rooms-beds.update');

    Route::delete('/hostel-rooms-beds/{room}', function (HostelRoom $room) {
        $room->delete();

        return redirect()
            ->route('hostel-rooms-beds.index')
            ->with('success', 'Hostel room deleted successfully.');
    })->name('hostel-rooms-beds.destroy');

    Route::post('/hostels', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:boys,girls,mixed'],
            'description' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        $currentYear = AcademicYear::where('is_current', true)->value('year');

        Hostel::create([
            'school_id' => $user?->school_id,
            'name' => $data['name'],
            'type' => $data['type'],
            'academic_year' => $currentYear,
            'description' => $data['description'] ?? null,
        ]);

        return redirect()
            ->route('hostel-rooms-beds.index')
            ->with('success', 'Hostel created successfully.');
    })->name('hostels.store');

    Route::get('/hostel-allocations', function () {
        $user = request()->user();
        $currentYear = AcademicYear::where('is_current', true)->value('year');

        $hostels = Hostel::orderBy('name')
            ->get(['id', 'name', 'type']);

        $rooms = HostelRoom::orderBy('hostel_id')
            ->orderBy('name')
            ->get(['id', 'hostel_id', 'name']);

        $query = HostelAllocation::with([
            'student:id,full_name,class_level,stream',
            'hostel:id,name',
            'room:id,hostel_id,name',
        ])->orderByDesc('created_at');

        if ($user && $user->school_id) {
            $query->where('school_id', $user->school_id);
        }
        if ($currentYear) {
            $query->where('academic_year', $currentYear);
        }

        $allocations = $query->get()->map(function (HostelAllocation $allocation) {
            $student = $allocation->student;
            $className = $student
                ? trim(($student->class_level ?? '').' '.($student->stream ?? '')) ?: null
                : null;

            return [
                'id' => $allocation->id,
                'student_name' => optional($student)->full_name,
                'class_name' => $className,
                'hostel_name' => optional($allocation->hostel)->name,
                'room_name' => optional($allocation->room)->name,
                'fee_amount' => (int) $allocation->fee_amount,
            ];
        });

        return Inertia::render('HostelAllocations', [
            'allocations' => $allocations,
            'hostels' => $hostels,
            'rooms' => $rooms,
        ]);
    })->name('hostel-allocations.index');

    Route::post('/hostel-allocations', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'hostel_id' => ['required', 'exists:hostels,id'],
            'hostel_room_id' => ['nullable', 'exists:hostel_rooms,id'],
            'fee_amount' => ['required', 'integer', 'min:0'],
            'guardian_name' => ['required', 'string', 'max:255'],
            'guardian_phone' => ['required', 'string', 'max:50'],
            'guardian_relationship' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $currentYear = AcademicYear::where('is_current', true)->value('year');

        HostelAllocation::create([
            'school_id' => $user?->school_id,
            'student_id' => $data['student_id'],
            'hostel_id' => $data['hostel_id'],
            'hostel_room_id' => $data['hostel_room_id'] ?? null,
            'academic_year' => $currentYear,
            'fee_amount' => $data['fee_amount'],
            'status' => 'active',
            'guardian_name' => $data['guardian_name'],
            'guardian_phone' => $data['guardian_phone'],
            'guardian_relationship' => $data['guardian_relationship'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('hostel-allocations.index')
            ->with('success', 'Hostel allocation created successfully.');
    })->name('hostel-allocations.store');

    Route::get('/hostel-reports', function () {
        $summary = [
            'students' => 0,
            'rooms' => 0,
            'occupancy_rate' => 0,
        ];

        return Inertia::render('HostelReports', [
            'summary' => $summary,
        ]);
    })->name('hostel-reports.index');

    Route::get('/results/process', function (\Illuminate\Http\Request $request) {
        $yearFilter = $request->query('year');
        $examFilter = $request->query('exam');

        $years = Exam::select('academic_year')
            ->whereNotNull('academic_year')
            ->distinct()
            ->orderBy('academic_year', 'desc')
            ->pluck('academic_year');

        $examQuery = Exam::with('classes:id,level')
            ->orderByDesc('created_at');

        if ($yearFilter) {
            $examQuery->where('academic_year', $yearFilter);
        }

        if ($examFilter) {
            $examQuery->where('id', $examFilter);
        }

        $exams = $examQuery
            ->get([
                'id',
                'name',
                'type',
                'academic_year',
            ])
            ->map(function (Exam $exam) {
                $levels = $exam->classes
                    ->pluck('level')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                return [
                    'id' => $exam->id,
                    'name' => $exam->name,
                    'type' => $exam->type,
                    'academic_year' => $exam->academic_year,
                    'levels' => $levels,
                ];
            });

        return Inertia::render('ProcessResults', [
            'years' => $years,
            'exams' => $exams,
            'filters' => [
                'year' => $yearFilter,
                'exam' => $examFilter,
            ],
        ]);
    })->name('results.process');

    Route::get('/results/class', function (\Illuminate\Http\Request $request) {
        $yearFilter = $request->query('year');
        $examFilter = $request->query('exam');

        $years = Exam::select('academic_year')
            ->whereNotNull('academic_year')
            ->distinct()
            ->orderBy('academic_year', 'desc')
            ->pluck('academic_year');

        $examListQuery = Exam::orderByDesc('created_at');
        if ($yearFilter) {
            $examListQuery->where('academic_year', $yearFilter);
        }

        $examList = $examListQuery->get([
            'id',
            'name',
            'type',
            'academic_year',
        ]);

        $performance = collect();
        $topStudents = collect();
        $bottomStudents = collect();

        if ($examFilter) {
            $exam = Exam::find($examFilter);

            if ($exam) {
                $allResults = ExamResult::where('exam_id', $exam->id)
                    ->with(['student:id,exam_number,full_name,gender,class_level'])
                    ->get();

                if ($allResults->isNotEmpty()) {
                    $schemes = GradingScheme::all();

                    $gradeForMark = function (?int $mark) use ($schemes) {
                        if ($mark === null) {
                            return null;
                        }

                        return $schemes->first(function (GradingScheme $s) use ($mark) {
                            return $mark >= $s->min_mark && $mark <= $s->max_mark;
                        });
                    };

                    // Group by class level and compute aggregates
                    $performance = $allResults
                        ->groupBy(function (ExamResult $res) {
                            return $res->student->class_level ?? 'Unknown';
                        })
                        ->map(function ($rows, $classLevel) use ($gradeForMark) {
                            $studentGroups = $rows->groupBy('student_id');
                            $candidates = $studentGroups->count();

                            // Average mark per class (over all marks)
                            $validMarks = $rows->pluck('marks')->filter(fn ($m) => $m !== null);
                            $averageMark = $validMarks->isNotEmpty() ? round($validMarks->avg(), 1) : null;

                            // Per-student division for distribution
                            $divisionCounts = [
                                'I' => 0,
                                'II' => 0,
                                'III' => 0,
                                'IV' => 0,
                                '0' => 0,
                            ];

                            foreach ($studentGroups as $studentId => $studentRows) {
                                $marks = $studentRows->pluck('marks')->filter(fn ($m) => $m !== null);
                                if ($marks->isEmpty()) {
                                    $divisionCounts['0']++;
                                    continue;
                                }

                                $total = $marks->sum();
                                $count = $marks->count();
                                $avg = $count > 0 ? round($total / $count) : null;

                                $divBucket = '0';
                                if ($avg !== null) {
                                    $scheme = $gradeForMark($avg);
                                    if ($scheme && $scheme->division) {
                                        $code = $scheme->division;
                                        if (str_contains($code, 'I')) {
                                            $divBucket = 'I';
                                        } elseif (str_contains($code, 'II')) {
                                            $divBucket = 'II';
                                        } elseif (str_contains($code, 'III')) {
                                            $divBucket = 'III';
                                        } elseif (str_contains($code, 'IV')) {
                                            $divBucket = 'IV';
                                        } else {
                                            $divBucket = '0';
                                        }
                                    } elseif ($scheme) {
                                        $grade = strtoupper($scheme->grade);
                                        $divBucket = match ($grade) {
                                            'A' => 'I',
                                            'B' => 'II',
                                            'C' => 'III',
                                            'D' => 'IV',
                                            default => '0',
                                        };
                                    }
                                }

                                $divisionCounts[$divBucket]++;
                            }

                            $passCount = $divisionCounts['I'] + $divisionCounts['II'] + $divisionCounts['III'] + $divisionCounts['IV'];
                            $passRate = $candidates > 0 ? round(($passCount / $candidates) * 100, 1) : 0.0;

                            return [
                                'class_level' => $classLevel,
                                'candidates' => $candidates,
                                'average_mark' => $averageMark,
                                'divisions' => $divisionCounts,
                                'pass_rate' => $passRate,
                            ];
                        })
                        ->values();

                    // Per-student ranking (for top/bottom lists)
                    $studentsPerf = $allResults
                        ->groupBy('student_id')
                        ->map(function ($rows) use ($gradeForMark) {
                            /** @var \App\Models\ExamResult $first */
                            $first = $rows->first();
                            $student = $first->student;

                            $marks = $rows->pluck('marks')->filter(fn ($m) => $m !== null);
                            $total = $marks->sum();
                            $count = $marks->count();
                            $average = $count > 0 ? round($total / $count, 1) : null;

                            // Division from average
                            $divisionCode = null;
                            if ($average !== null) {
                                $scheme = $gradeForMark((int) round($average));
                                if ($scheme && $scheme->division) {
                                    $divisionCode = $scheme->division;
                                } elseif ($scheme) {
                                    $grade = strtoupper($scheme->grade);
                                    $divisionCode = match ($grade) {
                                        'A' => 'Division I',
                                        'B' => 'Division II',
                                        'C' => 'Division III',
                                        'D' => 'Division IV',
                                        default => 'Division 0',
                                    };
                                }
                            }

                            return [
                                'exam_number' => $student->exam_number,
                                'full_name' => $student->full_name,
                                'class_level' => $student->class_level,
                                'average' => $average,
                                'division' => $divisionCode,
                            ];
                        })
                        ->values();

                    $topStudents = $studentsPerf
                        ->sortByDesc(function ($row) {
                            return $row['average'] ?? -INF;
                        })
                        ->take(10)
                        ->values();

                    $bottomStudents = $studentsPerf
                        ->sortBy(function ($row) {
                            return $row['average'] ?? INF;
                        })
                        ->take(10)
                        ->values();
                }
            }
        }

        return Inertia::render('ClassPerformance', [
            'years' => $years,
            'exams' => $examList,
            'performance' => $performance,
            'topStudents' => $topStudents,
            'bottomStudents' => $bottomStudents,
            'filters' => [
                'year' => $yearFilter,
                'exam' => $examFilter,
            ],
        ]);
    })->name('results.class');

    Route::get('/results/subject', function (\Illuminate\Http\Request $request) {
        $yearFilter = $request->query('year');
        $examFilter = $request->query('exam');

        $years = Exam::select('academic_year')
            ->whereNotNull('academic_year')
            ->distinct()
            ->orderBy('academic_year', 'desc')
            ->pluck('academic_year');

        $examListQuery = Exam::orderByDesc('created_at');
        if ($yearFilter) {
            $examListQuery->where('academic_year', $yearFilter);
        }

        $examList = $examListQuery->get([
            'id',
            'name',
            'type',
            'academic_year',
        ]);

        $performance = collect();

        if ($examFilter) {
            $exam = Exam::find($examFilter);

            if ($exam) {
                $allResults = ExamResult::where('exam_id', $exam->id)
                    ->with(['subject:id,subject_code,name'])
                    ->get();

                if ($allResults->isNotEmpty()) {
                    $schemes = GradingScheme::all();

                    $gradeForMark = function (?int $mark) use ($schemes) {
                        if ($mark === null) {
                            return null;
                        }

                        return $schemes->first(function (GradingScheme $s) use ($mark) {
                            return $mark >= $s->min_mark && $mark <= $s->max_mark;
                        });
                    };

                    // Subject summary metrics (similar to exams.results)
                    $performance = $allResults
                        ->groupBy('subject_id')
                        ->map(function ($rows, $subjectId) use ($gradeForMark) {
                            /** @var \App\Models\ExamResult $first */
                            $first = $rows->first();
                            $subject = $first->subject;

                            $sat = $rows->count();
                            $pass = 0;
                            $points = [];

                            foreach ($rows as $res) {
                                $scheme = $gradeForMark($res->marks);
                                if ($scheme) {
                                    if ($scheme->points !== null) {
                                        $points[] = $scheme->points;
                                    }
                                    if (strtoupper($scheme->grade) !== 'F') {
                                        $pass++;
                                    }
                                }
                            }

                            $fail = $sat - $pass;
                            $gpa = ! empty($points) ? round(array_sum($points) / count($points), 4) : null;

                            // Simple competency text based on GPA
                            $competency = null;
                            if ($gpa !== null) {
                                if ($gpa <= 2.0) {
                                    $competency = 'Grade B (Very Good)';
                                } elseif ($gpa <= 3.0) {
                                    $competency = 'Grade C (Good)';
                                } elseif ($gpa <= 4.0) {
                                    $competency = 'Grade D (Satisfactory)';
                                } else {
                                    $competency = 'Grade F (Fail)';
                                }
                            }

                            return [
                                'code' => $subject->subject_code,
                                'name' => $subject->name ?? $subject->subject_code,
                                'sat' => $sat,
                                'pass' => $pass,
                                'fail' => $fail,
                                'gpa' => $gpa,
                                'competency' => $competency,
                            ];
                        })
                        ->values();
                }
            }
        }

        return Inertia::render('SubjectPerformance', [
            'years' => $years,
            'exams' => $examList,
            'performance' => $performance,
            'filters' => [
                'year' => $yearFilter,
                'exam' => $examFilter,
            ],
        ]);
    })->name('results.subject');

    Route::get('/results/ranking', function (\Illuminate\Http\Request $request) {
        $yearFilter = $request->query('year');
        $examFilter = $request->query('exam');

        $years = Exam::select('academic_year')
            ->whereNotNull('academic_year')
            ->distinct()
            ->orderBy('academic_year', 'desc')
            ->pluck('academic_year');

        $examListQuery = Exam::orderByDesc('created_at');
        if ($yearFilter) {
            $examListQuery->where('academic_year', $yearFilter);
        }

        $examList = $examListQuery->get([
            'id',
            'name',
            'type',
            'academic_year',
        ]);

        $ranking = collect();

        if ($examFilter) {
            $exam = Exam::find($examFilter);

            if ($exam) {
                $allResults = ExamResult::where('exam_id', $exam->id)
                    ->with(['student:id,exam_number,full_name,gender,class_level'])
                    ->get();

                if ($allResults->isNotEmpty()) {
                    $schemes = GradingScheme::all();

                    $gradeForMark = function (?int $mark) use ($schemes) {
                        if ($mark === null) {
                            return null;
                        }

                        return $schemes->first(function (GradingScheme $s) use ($mark) {
                            return $mark >= $s->min_mark && $mark <= $s->max_mark;
                        });
                    };

                    $ranking = $allResults
                        ->groupBy('student_id')
                        ->map(function ($rows) use ($gradeForMark) {
                            /** @var \App\Models\ExamResult $first */
                            $first = $rows->first();
                            $student = $first->student;

                            $marks = $rows->pluck('marks')->filter(fn ($m) => $m !== null);
                            $total = $marks->sum();
                            $count = $marks->count();
                            $average = $count > 0 ? round($total / $count, 1) : null;

                            // Points total from grading scheme
                            $pointsArr = [];
                            foreach ($rows as $res) {
                                $scheme = $gradeForMark($res->marks);
                                if ($scheme && $scheme->points !== null) {
                                    $pointsArr[] = $scheme->points;
                                }
                            }
                            $pointsTotal = ! empty($pointsArr) ? array_sum($pointsArr) : null;

                            // Division based on average
                            $divisionCode = null;
                            if ($average !== null) {
                                $avgScheme = $gradeForMark((int) round($average));
                                if ($avgScheme && $avgScheme->division) {
                                    $divisionCode = $avgScheme->division;
                                } elseif ($avgScheme) {
                                    $grade = strtoupper($avgScheme->grade);
                                    $divisionCode = match ($grade) {
                                        'A' => 'Division I',
                                        'B' => 'Division II',
                                        'C' => 'Division III',
                                        'D' => 'Division IV',
                                        default => 'Division 0',
                                    };
                                }
                            }

                            return [
                                'exam_number' => $student->exam_number,
                                'full_name' => $student->full_name,
                                'class_level' => $student->class_level,
                                'total' => $count > 0 ? $total : null,
                                'average' => $average,
                                'division' => $divisionCode,
                                'points' => $pointsTotal,
                            ];
                        })
                        ->values()
                        ->sortByDesc(function ($row) {
                            // Sort by average desc, then points asc
                            $avg = $row['average'] ?? -INF;
                            $points = $row['points'] ?? INF;
                            return sprintf('%015.3f-%015.3f', $avg, -$points);
                        })
                        ->values()
                        ->map(function ($row, $index) {
                            $row['position'] = $index + 1;

                            return $row;
                        });
                }
            }
        }

        return Inertia::render('Ranking', [
            'years' => $years,
            'exams' => $examList,
            'ranking' => $ranking,
            'filters' => [
                'year' => $yearFilter,
                'exam' => $examFilter,
            ],
        ]);
    })->name('results.ranking');

    Route::get('/results/publish', function (\Illuminate\Http\Request $request) {
        $yearFilter = $request->query('year');

        $years = Exam::select('academic_year')
            ->whereNotNull('academic_year')
            ->distinct()
            ->orderBy('academic_year', 'desc')
            ->pluck('academic_year');

        $examQuery = Exam::with('classes:id,level')
            ->orderByDesc('created_at');

        if ($yearFilter) {
            $examQuery->where('academic_year', $yearFilter);
        }

        $exams = $examQuery
            ->get([
                'id',
                'name',
                'type',
                'academic_year',
                'is_published',
            ])
            ->map(function (Exam $exam) {
                $levels = $exam->classes
                    ->pluck('level')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                return [
                    'id' => $exam->id,
                    'name' => $exam->name,
                    'type' => $exam->type,
                    'academic_year' => $exam->academic_year,
                    'levels' => $levels,
                    'is_published' => (bool) ($exam->is_published ?? false),
                ];
            });

        return Inertia::render('PublishResults', [
            'years' => $years,
            'exams' => $exams,
            'filters' => [
                'year' => $yearFilter,
            ],
        ]);
    })->name('results.publish');

    Route::post('/results/publish/{exam}', function (\Illuminate\Http\Request $request, Exam $exam) {
        $data = $request->validate([
            'publish' => ['required', 'boolean'],
        ]);

        $exam->is_published = $data['publish'];
        $exam->save();

        return back()->with('success', $exam->is_published ? 'Exam results published.' : 'Exam results unpublished.');
    })->name('results.publish.toggle');

    Route::get('/reports/students', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        $schoolId = $user?->school_id;
        $yearFilter = $request->query('year');
        $examFilter = $request->query('exam');
        $testExamFilter = $request->query('test_exam');
        $classFilter = $request->query('class');

        $yearsQuery = Exam::select('academic_year')
            ->whereNotNull('academic_year')
            ->distinct()
            ->orderBy('academic_year', 'desc');

        if ($schoolId) {
            $yearsQuery->where('school_id', $schoolId);
        }

        $years = $yearsQuery->pluck('academic_year');

        $examListQuery = Exam::orderByDesc('created_at');
        if ($yearFilter) {
            $examListQuery->where('academic_year', $yearFilter);
        }
        if ($schoolId) {
            $examListQuery->where('school_id', $schoolId);
        }

        $examList = $examListQuery->get([
            'id',
            'name',
            'type',
            'academic_year',
        ]);

        $studentsQuery = Student::query();
        if ($classFilter) {
            $studentsQuery->where('class_level', $classFilter);
        }
        if ($schoolId) {
            $studentsQuery->where('school_id', $schoolId);
        }

        $students = $studentsQuery
            ->orderBy('class_level')
            ->orderBy('stream')
            ->orderBy('exam_number')
            ->get([
                'id',
                'exam_number',
                'full_name',
                'class_level',
                'stream',
                'gender',
            ]);

        $classesQuery = Student::select('class_level')
            ->distinct()
            ->orderBy('class_level');

        if ($schoolId) {
            $classesQuery->where('school_id', $schoolId);
        }

        $classes = $classesQuery->pluck('class_level');

        return Inertia::render('StudentReportsIndex', [
            'years' => $years,
            'exams' => $examList,
            'students' => $students,
            'classes' => $classes,
            'filters' => [
                'year' => $yearFilter,
                'exam' => $examFilter,
                'test_exam' => $testExamFilter,
                'class' => $classFilter,
            ],
        ]);
    })->name('reports.students.index');

    Route::get('/timetables/invigilation', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        $schoolId = $user?->school_id;

        $exams = Exam::orderByDesc('created_at')->get([
            'id',
            'name',
            'exam_type',
            'academic_year',
        ]);

        $school = School::query()->first(['id', 'name', 'school_code', 'region', 'logo_path']);

        $selectedExamId = $request->query('exam');
        $selectedExam = $selectedExamId
            ? $exams->firstWhere('id', (int) $selectedExamId)
            : null;

        $selectedClassId = $request->query('class');

        $classesQuery = SchoolClass::orderBy('name');
        if ($schoolId) {
            $classesQuery->where('school_id', $schoolId);
        }

        $classes = $classesQuery
            ->get(['id', 'name', 'level', 'stream'])
            ->map(function (SchoolClass $class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'level' => $class->level,
                    'stream' => $class->stream,
                ];
            });

        $teachersQuery = User::where('role', 'teacher')->orderBy('name');
        if ($schoolId) {
            $teachersQuery->where('school_id', $schoolId);
        }

        $teachers = $teachersQuery->get(['id', 'name', 'teaching_classes']);

        $classSubjects = collect();

        if ($selectedClassId) {
            $classModelQuery = SchoolClass::with('subjects:id,subject_code,name')
                ->where('id', $selectedClassId);

            if ($schoolId) {
                $classModelQuery->where('school_id', $schoolId);
            }

            $classModel = $classModelQuery->first();

            if ($classModel) {
                $classSubjects = $classModel->subjects
                    ->sortBy('subject_code')
                    ->values()
                    ->map(function (Subject $subject) {
                        return [
                            'id' => $subject->id,
                            'name' => $subject->name,
                            'subject_code' => $subject->subject_code,
                        ];
                    });
            }
        }

        $academicTeacher = User::whereIn('role', ['academic', 'academic_teacher'])
            ->when($schoolId, function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->orderBy('name')
            ->first(['id', 'name']);

        return Inertia::render('InvigilationTimetable', [
            'exams' => $exams,
            'selectedExamId' => $selectedExam?->id,
            'selectedExam' => $selectedExam,
            'school' => $school,
            'classes' => $classes,
            'teachers' => $teachers,
            'selectedClassId' => $selectedClassId ? (int) $selectedClassId : null,
            'classSubjects' => $classSubjects,
            'academicTeacher' => $academicTeacher,
        ]);
    })->name('timetables.invigilation');

    Route::get('/reports/students/{student}', function (\Illuminate\Http\Request $request, Student $student) {
        $examId = $request->query('exam');
        $testExamId = $request->query('test_exam');

        $school = School::query()->first();
        $currentYear = AcademicYear::where('is_current', true)->first();

        $exam = null;
        $subjects = collect();
        $summary = null;
        $behaviours = collect();

        if ($examId) {
            $exam = Exam::find($examId);

            if ($exam) {
                $results = ExamResult::where('exam_id', $exam->id)
                    ->where('student_id', $student->id)
                    ->with('subject:id,subject_code,name')
                    ->get();

                // Optional test exam for JARIBIO column
                $testResultsBySubject = collect();
                if ($testExamId) {
                    $testResults = ExamResult::where('exam_id', $testExamId)
                        ->where('student_id', $student->id)
                        ->get(['subject_id', 'marks']);

                    $testResultsBySubject = $testResults->keyBy('subject_id');
                }

                $schemes = GradingScheme::all();

                $gradeForMark = function (?int $mark) use ($schemes) {
                    if ($mark === null) {
                        return null;
                    }

                    return $schemes->first(function (GradingScheme $s) use ($mark) {
                        return $mark >= $s->min_mark && $mark <= $s->max_mark;
                    });
                };

                $subjects = $results->map(function (ExamResult $res) use ($gradeForMark, $testResultsBySubject) {
                    $testMark = null;
                    if ($testResultsBySubject->isNotEmpty()) {
                        $testMark = optional($testResultsBySubject->get($res->subject_id))->marks;
                    }

                    $examMark = $res->marks;
                    $total = ($testMark ?? 0) + ($examMark ?? 0);

                    // If both test and exam marks are missing, treat total/average as null so UI shows '-'
                    if ($testMark === null && $examMark === null) {
                        $total = null;
                        $average = null;
                    } elseif ($testMark !== null && $examMark !== null) {
                        $average = round($total / 2, 2);
                    } else {
                        // Only one of them exists, use that value as the average
                        $average = $examMark ?? $testMark;
                    }

                    $scheme = $gradeForMark($average !== null ? (int) round($average) : null);

                    return [
                        'code' => $res->subject->subject_code,
                        'name' => $res->subject->name ?? $res->subject->subject_code,
                        'test_mark' => $testMark,
                        'exam_mark' => $examMark,
                        'total' => $total,
                        'average' => $average,
                        'grade' => $scheme?->grade,
                    ];
                });

                $validMarks = $subjects->pluck('average')->filter(fn ($m) => $m !== null);
                $totalMarks = $validMarks->sum();
                $subjectCount = $validMarks->count();
                $average = $subjectCount > 0 ? round($totalMarks / $subjectCount, 2) : null;

                // Points total for this student based on best seven subjects
                $pointsArr = [];
                foreach ($subjects as $sub) {
                    $scheme = $gradeForMark($sub['average']);
                    if ($scheme && $scheme->points !== null) {
                        $pointsArr[] = $scheme->points;
                    }
                }

                // Take the best seven (lowest) points only
                if (! empty($pointsArr)) {
                    sort($pointsArr); // ascending: best performance first (lowest points)
                    $bestSeven = array_slice($pointsArr, 0, 7);
                    $pointsTotal = array_sum($bestSeven);
                } else {
                    $pointsTotal = null;
                }

                // Division derived from pointsTotal using NECTA-like thresholds
                if ($pointsTotal === null) {
                    $division = null;
                } elseif ($pointsTotal >= 34) {
                    $division = 'Division 0';
                } elseif ($pointsTotal >= 26) {
                    $division = 'Division IV';
                } elseif ($pointsTotal >= 22) {
                    $division = 'Division III';
                } elseif ($pointsTotal >= 18) {
                    $division = 'Division II';
                } else {
                    $division = 'Division I';
                }

                // Compute position within same class level for this exam
                $position = null;
                $outOf = null;

                $allResults = ExamResult::where('exam_id', $exam->id)
                    ->with(['student:id,class_level'])
                    ->get();

                if ($allResults->isNotEmpty()) {
                    $classLevel = $student->class_level;

                    $perStudent = $allResults
                        ->filter(function (ExamResult $res) use ($classLevel) {
                            return $res->student && $res->student->class_level === $classLevel;
                        })
                        ->groupBy('student_id')
                        ->map(function ($rows, $studId) use ($gradeForMark) {
                            $marks = $rows->pluck('marks')->filter(fn ($m) => $m !== null);
                            $total = $marks->sum();
                            $count = $marks->count();
                            $average = $count > 0 ? round($total / $count, 2) : null;

                            $pointsArr = [];
                            foreach ($rows as $res) {
                                $scheme = $gradeForMark($res->marks);
                                if ($scheme && $scheme->points !== null) {
                                    $pointsArr[] = $scheme->points;
                                }
                            }
                            $pointsTotal = ! empty($pointsArr) ? array_sum($pointsArr) : null;

                            return [
                                'student_id' => $studId,
                                'average' => $average,
                                'points' => $pointsTotal,
                            ];
                        })
                        ->values()
                        ->sortByDesc(function ($row) {
                            $avg = $row['average'] ?? -INF;
                            $points = $row['points'] ?? INF;

                            return sprintf('%015.3f-%015.3f', $avg, -$points);
                        })
                        ->values();

                    $outOf = $perStudent->count();

                    foreach ($perStudent as $index => $row) {
                        if ((int) $row['student_id'] === (int) $student->id) {
                            $position = $index + 1;
                            break;
                        }
                    }
                }

                $summary = [
                    'total' => $totalMarks,
                    'average' => $average,
                    'division' => $division,
                    'points' => $pointsTotal,
                    'position' => $position,
                    'out_of' => $outOf,
                ];

                // Behaviour grid pattern driven by division
                $baseBehaviours = [
                    ['code' => '901', 'label' => 'Bidii ya Kazi'],
                    ['code' => '902', 'label' => 'Mahudhurio Darasani'],
                    ['code' => '903', 'label' => 'Kutunza mali za shule'],
                    ['code' => '904', 'label' => 'Utii kazini/darasani'],
                    ['code' => '905', 'label' => 'Uelewano na ushirikiano'],
                    ['code' => '906', 'label' => 'Heshima kwa wote'],
                    ['code' => '907', 'label' => 'Kumudu uongozi'],
                    ['code' => '908', 'label' => 'Adabu'],
                    ['code' => '909', 'label' => 'Kujua usafi binafsi'],
                    ['code' => '910', 'label' => 'Kukubali ushauri'],
                    ['code' => '911', 'label' => 'Kuthamini kazi'],
                    ['code' => '912', 'label' => 'Kujituma'],
                    ['code' => '913', 'label' => 'Kushiriki michezo'],
                    ['code' => '914', 'label' => 'Sifa nzuri ya Ushawishi'],
                ];

                $divisionCode = strtoupper((string) $division);

                // Default: mixed C/D
                $gradesPattern = ['C', 'D', 'C', 'D', 'C', 'D', 'C', 'D', 'C', 'D', 'C', 'D', 'C', 'D'];

                if (str_contains($divisionCode, 'I')) {
                    // Division I: A nyingi, B chache
                    $gradesPattern = ['A', 'A', 'A', 'A', 'A', 'A', 'B', 'A', 'A', 'B', 'A', 'A', 'A', 'B'];
                } elseif (str_contains($divisionCode, 'II')) {
                    // Division II: A na B zinalingana idadi
                    $gradesPattern = ['A', 'B', 'A', 'B', 'A', 'B', 'A', 'B', 'A', 'B', 'A', 'B', 'A', 'B'];
                } elseif (str_contains($divisionCode, 'III')) {
                    // Division III: B nyingi, C chache
                    $gradesPattern = ['B', 'B', 'B', 'B', 'B', 'C', 'B', 'B', 'C', 'B', 'B', 'B', 'C', 'B'];
                } elseif (str_contains($divisionCode, 'IV')) {
                    // Division IV: C nyingi, B chache
                    $gradesPattern = ['C', 'C', 'C', 'B', 'C', 'C', 'C', 'B', 'C', 'C', 'C', 'B', 'C', 'C'];
                }

                $behaviours = collect($baseBehaviours)->map(function (array $beh, int $index) use ($gradesPattern) {
                    return [
                        'code' => $beh['code'],
                        'label' => $beh['label'],
                        'grade' => $gradesPattern[$index] ?? 'C',
                    ];
                });
            }
        }

        return Inertia::render('StudentReportCard', [
            'student' => $student,
            'school' => $school,
            'exam' => $exam,
            'subjects' => $subjects,
            'summary' => $summary,
            'behaviours' => $behaviours,
            'year' => $currentYear?->year ?? date('Y'),
        ]);
    })->name('reports.students.show');

    Route::get('/reports/classes', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        $schoolId = $user?->school_id;
        $yearFilter = $request->query('year');
        $examFilter = $request->query('exam');

        $yearsQuery = Exam::select('academic_year')
            ->whereNotNull('academic_year')
            ->distinct()
            ->orderBy('academic_year', 'desc');

        if ($schoolId) {
            $yearsQuery->where('school_id', $schoolId);
        }

        $years = $yearsQuery->pluck('academic_year');

        $examListQuery = Exam::orderByDesc('created_at');
        if ($yearFilter) {
            $examListQuery->where('academic_year', $yearFilter);
        }
        if ($schoolId) {
            $examListQuery->where('school_id', $schoolId);
        }

        $examList = $examListQuery->get([
            'id',
            'name',
            'type',
            'academic_year',
        ]);

        $classesQuery = SchoolClass::orderBy('level')
            ->orderBy('stream');

        if ($schoolId) {
            $classesQuery->where('school_id', $schoolId);
        }

        $classes = $classesQuery->get([
            'id',
            'level',
            'stream',
        ]);

        return Inertia::render('ClassReportsIndex', [
            'years' => $years,
            'exams' => $examList,
            'classes' => $classes,
            'filters' => [
                'year' => $yearFilter,
                'exam' => $examFilter,
            ],
        ]);
    })->name('reports.classes.index');

    Route::get('/reports/classes/{class}', function (\Illuminate\Http\Request $request, SchoolClass $class) {
        $examId = $request->query('exam');

        $school = School::query()->first();
        $currentYear = AcademicYear::where('is_current', true)->first();

        $exam = null;
        $students = collect();
        $summary = null;

        if ($examId) {
            $exam = Exam::find($examId);

            if ($exam) {
                // All results for this exam for students in this class (level + stream)
                $results = ExamResult::where('exam_id', $exam->id)
                    ->with(['student:id,exam_number,full_name,gender,class_level,stream'])
                    ->get();

                $classLevel = $class->level;
                $classStream = $class->stream;

                $results = $results->filter(function (ExamResult $res) use ($classLevel, $classStream) {
                    if (! $res->student) {
                        return false;
                    }

                    return $res->student->class_level === $classLevel && $res->student->stream === $classStream;
                });

                if ($results->isNotEmpty()) {
                    $schemes = GradingScheme::all();

                    $gradeForMark = function (?int $mark) use ($schemes) {
                        if ($mark === null) {
                            return null;
                        }

                        return $schemes->first(function (GradingScheme $s) use ($mark) {
                            return $mark >= $s->min_mark && $mark <= $s->max_mark;
                        });
                    };

                    $students = $results
                        ->groupBy('student_id')
                        ->map(function ($rows) use ($gradeForMark) {
                            /** @var \App\Models\ExamResult $first */
                            $first = $rows->first();
                            $student = $first->student;

                            $marks = $rows->pluck('marks')->filter(fn ($m) => $m !== null);
                            $total = $marks->sum();
                            $count = $marks->count();
                            $average = $count > 0 ? round($total / $count, 2) : null;

                            // Points total from grading scheme
                            $pointsArr = [];
                            foreach ($rows as $res) {
                                $scheme = $gradeForMark($res->marks);
                                if ($scheme && $scheme->points !== null) {
                                    $pointsArr[] = $scheme->points;
                                }
                            }
                            $pointsTotal = ! empty($pointsArr) ? array_sum($pointsArr) : null;

                            // Division based on average
                            $divisionCode = null;
                            if ($average !== null) {
                                $avgScheme = $gradeForMark((int) round($average));
                                if ($avgScheme && $avgScheme->division) {
                                    $divisionCode = $avgScheme->division;
                                } elseif ($avgScheme) {
                                    $grade = strtoupper($avgScheme->grade);
                                    $divisionCode = match ($grade) {
                                        'A' => 'Division I',
                                        'B' => 'Division II',
                                        'C' => 'Division III',
                                        'D' => 'Division IV',
                                        default => 'Division 0',
                                    };
                                }
                            }

                            return [
                                'exam_number' => $student->exam_number,
                                'full_name' => $student->full_name,
                                'gender' => $student->gender,
                                'total' => $count > 0 ? $total : null,
                                'average' => $average,
                                'division' => $divisionCode,
                                'points' => $pointsTotal,
                            ];
                        })
                        ->values()
                        ->sortByDesc(function ($row) {
                            $avg = $row['average'] ?? -INF;
                            $points = $row['points'] ?? INF;

                            return sprintf('%015.3f-%015.3f', $avg, -$points);
                        })
                        ->values()
                        ->map(function ($row, $index) {
                            $row['position'] = $index + 1;

                            return $row;
                        });

                    $candidateCount = $students->count();
                    $classAverage = $candidateCount > 0
                        ? round($students->pluck('average')->filter(fn ($v) => $v !== null)->avg(), 2)
                        : null;

                    $summary = [
                        'candidates' => $candidateCount,
                        'average_mark' => $classAverage,
                    ];
                }
            }
        }

        return Inertia::render('ClassReport', [
            'class' => $class,
            'school' => $school,
            'exam' => $exam,
            'students' => $students,
            'summary' => $summary,
            'year' => $currentYear?->year ?? date('Y'),
        ]);
    })->name('reports.classes.show');

    Route::get('/reports/school', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        $schoolId = $user?->school_id;
        $yearFilter = $request->query('year');
        $examFilter = $request->query('exam');

        $school = $schoolId ? School::find($schoolId) : School::query()->first();

        $yearsQuery = Exam::select('academic_year')
            ->whereNotNull('academic_year')
            ->distinct()
            ->orderBy('academic_year', 'desc');

        if ($schoolId) {
            $yearsQuery->where('school_id', $schoolId);
        }

        $years = $yearsQuery->pluck('academic_year');

        $examListQuery = Exam::orderByDesc('created_at');
        if ($yearFilter) {
            $examListQuery->where('academic_year', $yearFilter);
        }
        if ($schoolId) {
            $examListQuery->where('school_id', $schoolId);
        }

        $examList = $examListQuery->get([
            'id',
            'name',
            'type',
            'academic_year',
        ]);

        $classPerformance = collect();
        $schoolSummary = null;
        $examSummaries = collect();
        $subjectPerformance = collect();
        $topSubjectsBest = collect();
        $topSubjectsWorst = collect();
        $yearTopStudents = collect();
        $yearTrends = collect();

        if ($examFilter) {
            $exam = Exam::find($examFilter);

            if ($exam) {
                $allResultsQuery = ExamResult::where('exam_id', $exam->id)
                    ->with(['student:id,exam_number,full_name,gender,class_level,stream']);

                if ($schoolId) {
                    $allResultsQuery->whereHas('student', function($q) use ($schoolId) {
                        $q->where('school_id', $schoolId);
                    });
                }

                $allResults = $allResultsQuery->get();

                if ($allResults->isNotEmpty()) {
                    $schemes = GradingScheme::all();

                    $gradeForMark = function (?int $mark) use ($schemes) {
                        if ($mark === null) {
                            return null;
                        }

                        return $schemes->first(function (GradingScheme $s) use ($mark) {
                            return $mark >= $s->min_mark && $mark <= $s->max_mark;
                        });
                    };

                    // Per-class (level) performance across school
                    $classPerformance = $allResults
                        ->groupBy(function (ExamResult $res) {
                            return $res->student->class_level ?? 'Unknown';
                        })
                        ->map(function ($rows, $classLevel) use ($gradeForMark) {
                            $studentGroups = $rows->groupBy('student_id');
                            $candidates = $studentGroups->count();

                            $validMarks = $rows->pluck('marks')->filter(fn ($m) => $m !== null);
                            $averageMark = $validMarks->isNotEmpty() ? round($validMarks->avg(), 2) : null;

                            // Division distribution (I, II, III, IV, 0)
                            $divisionCounts = [
                                'I' => 0,
                                'II' => 0,
                                'III' => 0,
                                'IV' => 0,
                                '0' => 0,
                            ];

                            foreach ($studentGroups as $studentId => $studentRows) {
                                $marks = $studentRows->pluck('marks')->filter(fn ($m) => $m !== null);
                                if ($marks->isEmpty()) {
                                    $divisionCounts['0']++;
                                    continue;
                                }

                                $total = $marks->sum();
                                $count = $marks->count();
                                $avg = $count > 0 ? round($total / $count) : null;

                                $divBucket = '0';
                                if ($avg !== null) {
                                    $scheme = $gradeForMark($avg);
                                    if ($scheme && $scheme->division) {
                                        $code = $scheme->division;
                                        if (str_contains($code, 'I')) {
                                            $divBucket = 'I';
                                        } elseif (str_contains($code, 'II')) {
                                            $divBucket = 'II';
                                        } elseif (str_contains($code, 'III')) {
                                            $divBucket = 'III';
                                        } elseif (str_contains($code, 'IV')) {
                                            $divBucket = 'IV';
                                        }
                                    } elseif ($scheme) {
                                        $grade = strtoupper($scheme->grade);
                                        $divBucket = match ($grade) {
                                            'A' => 'I',
                                            'B' => 'II',
                                            'C' => 'III',
                                            'D' => 'IV',
                                            default => '0',
                                        };
                                    }
                                }

                                $divisionCounts[$divBucket]++;
                            }

                            $passCount = $divisionCounts['I'] + $divisionCounts['II'] + $divisionCounts['III'] + $divisionCounts['IV'];
                            $passRate = $candidates > 0 ? round(($passCount / $candidates) * 100, 1) : 0.0;

                            return [
                                'class_level' => $classLevel,
                                'candidates' => $candidates,
                                'average_mark' => $averageMark,
                                'divisions' => $divisionCounts,
                                'pass_rate' => $passRate,
                            ];
                        })
                        ->values();

                    // School-wide summary for this exam
                    $totalCandidates = $classPerformance->sum('candidates');
                    $overallAverage = $classPerformance->pluck('average_mark')->filter(fn ($v) => $v !== null)->avg();
                    $overallAverage = $overallAverage !== null ? round($overallAverage, 2) : null;

                    $totalDivisions = [
                        'I' => 0,
                        'II' => 0,
                        'III' => 0,
                        'IV' => 0,
                        '0' => 0,
                    ];

                    foreach ($classPerformance as $row) {
                        foreach ($totalDivisions as $key => $val) {
                            $totalDivisions[$key] += $row['divisions'][$key] ?? 0;
                        }
                    }

                    $schoolPassCount = $totalDivisions['I'] + $totalDivisions['II'] + $totalDivisions['III'] + $totalDivisions['IV'];
                    $schoolPassRate = $totalCandidates > 0 ? round(($schoolPassCount / $totalCandidates) * 100, 1) : 0.0;

                    $schoolSummary = [
                        'candidates' => $totalCandidates,
                        'average_mark' => $overallAverage,
                        'divisions' => $totalDivisions,
                        'pass_rate' => $schoolPassRate,
                    ];
                }
            }
        }

        // Per-exam summary for selected year (multi-exam overview)
        if ($yearFilter) {
            $examSummaries = $examList->map(function (Exam $exam) {
                $results = ExamResult::where('exam_id', $exam->id)->get();

                if ($results->isEmpty()) {
                    return [
                        'id' => $exam->id,
                        'name' => $exam->name,
                        'type' => $exam->type,
                        'academic_year' => $exam->academic_year,
                        'candidates' => 0,
                        'average_mark' => null,
                    ];
                }

                $candidates = $results->groupBy('student_id')->count();
                $validMarks = $results->pluck('marks')->filter(fn ($m) => $m !== null);
                $avg = $validMarks->isNotEmpty() ? round($validMarks->avg(), 2) : null;

                return [
                    'id' => $exam->id,
                    'name' => $exam->name,
                    'type' => $exam->type,
                    'academic_year' => $exam->academic_year,
                    'candidates' => $candidates,
                    'average_mark' => $avg,
                ];
            });

            // Subject performance across the whole school for selected year (all exams of that year)
            $examIdsForYear = $examList->pluck('id');

            if ($examIdsForYear->isNotEmpty()) {
                $yearResults = ExamResult::whereIn('exam_id', $examIdsForYear)
                    ->with(['subject:id,subject_code,name', 'student:id,exam_number,full_name,class_level,stream,gender'])
                    ->get();

                if ($yearResults->isNotEmpty()) {
                    // Subject performance for the year
                    $subjectPerformance = $yearResults
                        ->groupBy('subject_id')
                        ->map(function ($rows) {
                            /** @var \App\Models\ExamResult $first */
                            $first = $rows->first();
                            $subject = $first->subject;

                            $sat = $rows->groupBy('student_id')->count();
                            $validMarks = $rows->pluck('marks')->filter(fn ($m) => $m !== null);
                            $avg = $validMarks->isNotEmpty() ? round($validMarks->avg(), 2) : null;

                            return [
                                'code' => $subject->subject_code,
                                'name' => $subject->name ?? $subject->subject_code,
                                'sat' => $sat,
                                'average_mark' => $avg,
                            ];
                        })
                        ->values();

                    $topSubjectsBest = $subjectPerformance
                        ->sortByDesc('average_mark')
                        ->take(5)
                        ->values();

                    $topSubjectsWorst = $subjectPerformance
                        ->sortBy(function (array $sub) {
                            return $sub['average_mark'] ?? INF;
                        })
                        ->take(5)
                        ->values();

                    // Top 10 students across all exams in the year
                    $schemes = GradingScheme::all();

                    $gradeForMarkYear = function (?int $mark) use ($schemes) {
                        if ($mark === null) {
                            return null;
                        }

                        return $schemes->first(function (GradingScheme $s) use ($mark) {
                            return $mark >= $s->min_mark && $mark <= $s->max_mark;
                        });
                    };

                    $yearTopStudents = $yearResults
                        ->groupBy('student_id')
                        ->map(function ($rows) use ($gradeForMarkYear) {
                            /** @var \App\Models\ExamResult $first */
                            $first = $rows->first();
                            $student = $first->student;

                            $marks = $rows->pluck('marks')->filter(fn ($m) => $m !== null);
                            $total = $marks->sum();
                            $count = $marks->count();
                            $average = $count > 0 ? round($total / $count, 2) : null;

                            // Points total
                            $pointsArr = [];
                            foreach ($rows as $res) {
                                $scheme = $gradeForMarkYear($res->marks);
                                if ($scheme && $scheme->points !== null) {
                                    $pointsArr[] = $scheme->points;
                                }
                            }
                            $pointsTotal = ! empty($pointsArr) ? array_sum($pointsArr) : null;

                            // Division from average
                            $divisionCode = null;
                            if ($average !== null) {
                                $avgScheme = $gradeForMarkYear((int) round($average));
                                if ($avgScheme && $avgScheme->division) {
                                    $divisionCode = $avgScheme->division;
                                } elseif ($avgScheme) {
                                    $grade = strtoupper($avgScheme->grade);
                                    $divisionCode = match ($grade) {
                                        'A' => 'Division I',
                                        'B' => 'Division II',
                                        'C' => 'Division III',
                                        'D' => 'Division IV',
                                        default => 'Division 0',
                                    };
                                }
                            }

                            return [
                                'exam_number' => $student->exam_number,
                                'full_name' => $student->full_name,
                                'class_level' => $student->class_level,
                                'stream' => $student->stream,
                                'average' => $average,
                                'division' => $divisionCode,
                                'points' => $pointsTotal,
                            ];
                        })
                        ->values()
                        ->sortByDesc(function ($row) {
                            $avg = $row['average'] ?? -INF;
                            $points = $row['points'] ?? INF;

                            return sprintf('%015.3f-%015.3f', $avg, -$points);
                        })
                        ->take(10)
                        ->values();
                }
            }
        }

        // Multi-year trend: average mark and candidates per academic year
        $allYears = Exam::select('academic_year')
            ->whereNotNull('academic_year')
            ->distinct()
            ->orderBy('academic_year')
            ->pluck('academic_year');

        $yearTrends = $allYears->map(function ($year) {
            $examIds = Exam::where('academic_year', $year)->pluck('id');

            if ($examIds->isEmpty()) {
                return [
                    'year' => $year,
                    'candidates' => 0,
                    'average_mark' => null,
                ];
            }

            $results = ExamResult::whereIn('exam_id', $examIds)->get();
            if ($results->isEmpty()) {
                return [
                    'year' => $year,
                    'candidates' => 0,
                    'average_mark' => null,
                ];
            }

            $candidates = $results->groupBy('student_id')->count();
            $validMarks = $results->pluck('marks')->filter(fn ($m) => $m !== null);
            $avg = $validMarks->isNotEmpty() ? round($validMarks->avg(), 2) : null;

            return [
                'year' => $year,
                'candidates' => $candidates,
                'average_mark' => $avg,
            ];
        });

        return Inertia::render('SchoolReport', [
            'school' => $school,
            'years' => $years,
            'exams' => $examList,
            'classPerformance' => $classPerformance,
            'schoolSummary' => $schoolSummary,
            'examSummaries' => $examSummaries,
            'subjectPerformance' => $subjectPerformance,
            'topSubjectsBest' => $topSubjectsBest,
            'topSubjectsWorst' => $topSubjectsWorst,
            'yearTopStudents' => $yearTopStudents,
            'yearTrends' => $yearTrends,
            'filters' => [
                'year' => $yearFilter,
                'exam' => $examFilter,
            ],
        ]);
    })->name('reports.school');

    Route::get('/students', function (\Illuminate\Http\Request $request) {
        $classFilter = $request->query('class');

        $user = $request->user();

        $query = Student::query();

        if ($user && $user->school_id) {
            $query->where('school_id', $user->school_id);
        }

        if ($classFilter) {
            $query->where('class_level', $classFilter);
        }

        $students = $query
            ->orderBy('class_level')
            ->orderBy('stream')
            ->orderBy('full_name')
            ->get();

        // Map class_level -> subject details (code + name) from SchoolClass
        $classSubjectsQuery = SchoolClass::with('subjects:id,subject_code,name');
        
        if ($user && $user->school_id) {
            $classSubjectsQuery->where('school_id', $user->school_id);
        }

        $classSubjects = $classSubjectsQuery->get()
            ->mapWithKeys(function (SchoolClass $class) {
                $key = trim($class->level ?? '');

                return [
                    $key => $class->subjects
                        ->map(function (Subject $subject) {
                            return [
                                'code' => $subject->subject_code,
                                'name' => $subject->name ?? $subject->subject_code,
                            ];
                        })
                        ->all(),
                ];
            });

        $students = $students->map(function (Student $student) use ($classSubjects) {
            $key = trim($student->class_level ?? '');

            return array_merge($student->toArray(), [
                'subjects' => $classSubjects->get($key, []),
            ]);
        });

        // Available class levels for the current school (for Add Student form/filter)
        $classLevelsQuery = SchoolClass::select('level')
            ->distinct()
            ->whereNotNull('level')
            ->orderBy('level');

        if ($user && $user->school_id) {
            $classLevelsQuery->where('school_id', $user->school_id);
        }

        $classes = $classLevelsQuery->pluck('level');

        $examsQuery = Exam::orderByDesc('created_at')
            ->limit(10);

        if ($user && $user->school_id) {
            $examsQuery->where('school_id', $user->school_id);
        }

        $exams = $examsQuery->get([
            'id',
            'name',
            'type',
            'academic_year',
        ]);

        return Inertia::render('Students', [
            'students' => $students,
            'classes' => $classes,
            'exams' => $exams,
            'filters' => [
                'class' => $classFilter,
            ],
        ]);
    })->name('students.index');

    Route::post('/students', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'class_level' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'in:M,F'],
            'date_of_birth' => ['nullable', 'date'],
        ]);

        $user = $request->user();
        $school = $user && $user->school_id ? \App\Models\School::find($user->school_id) : \App\Models\School::query()->first();

        $prefix = $school && $school->school_code ? $school->school_code : 'SCH01';

        $count = \App\Models\Student::where('exam_number', 'like', $prefix.'-%')->count() + 1;
        $number = str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        $examNumber = $prefix.'-'.$number;

        $currentYear = \App\Models\AcademicYear::where('is_current', true)->first();
        $academicYear = $currentYear?->year ?? date('Y');

        \App\Models\Student::create([
            'exam_number' => $examNumber,
            'full_name' => $data['full_name'],
            'class_level' => $data['class_level'],
            'gender' => $data['gender'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'school_id' => $school?->id,
            'academic_year' => $academicYear,
        ]);

        return redirect()->route('students.index')->with('success', 'Student added successfully.');
    })->name('students.store');

    Route::post('/students/bulk-delete', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:students,id'],
        ]);

        $user = $request->user();

        $query = Student::whereIn('id', $data['ids']);

        if ($user && $user->school_id) {
            $query->where('school_id', $user->school_id);
        }

        $query->delete();

        return redirect()->route('students.index')->with('success', 'Selected students deleted successfully.');
    })->name('students.bulk-delete');

    Route::post('/students/import-samples', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'class_level' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $school = $user && $user->school_id ? \App\Models\School::find($user->school_id) : \App\Models\School::query()->first();

        $prefix = $school && $school->school_code ? $school->school_code : 'SCH01';

        $currentYear = \App\Models\AcademicYear::where('is_current', true)->first();
        $academicYear = $currentYear?->year ?? date('Y');

        $names = [
            'Asha Juma','John Peter','Neema Joseph','Paul Daniel','Rehema Said','Michael James','Fatma Ali','Joseph Mark','Sara Victor','Peter Lucas',
            'Anna Joseph','David Thomas','Maria Paulo','James Leo','Stella Musa','Victor Jonas','Grace Peter','Daniel Simon','Rose Agnes','Lucas Peter',
            'Esther John','George Simon','Mariam Musa','Adam Joseph','Halima Said','Issa Omary','Zainab Khalid','Patrick Noel','Agnes Maria','Thomas Jacob',
            'Joyce Daniel','Felix Robert','Ruth Samuel','Emmanuel Lucas','Janeth Paulo','Abel Frank','Lucy Peter','Jonas Martin','Flora David','Kelvin Joseph',
            'Sarah Lucas','Abigail James','Brian Peter','Gloria Paul','Henry Daniel','Mercy Thomas','Nicholas John','Naomi Lucas','Oscar Victor','Patricia Joel',
            'Raymond Joseph','Salome Peter','Titus Paul','Vivian George','Winfred Lucas','Yohana James','Zawadi Musa','Catherine David','Denis Joseph','Edith Lucas',
            'Frank Peter','Grace Daniel','Hussein Ali','Irene Joseph','Jackson Paul','Kristina John','Lilian Lucas','Martin James','Nancy Peter','Omar Said',
            'Prisca Musa','Richard David','Susan Joseph','Tobias Lucas','Ursula Peter','Valence Paul','Walter John','Xavier Lucas','Yasinta Joseph','Zuberi Said',
            'Bahati Peter','Christopher John','Diana Lucas','Emanuel Joseph','Fatuma Paulo','Gilbert David','Helena James','Isack Peter','Janet Lucas','Kelvin Paul',
            'Lazaro Joseph','Martha David','Neema Lucas','Onesmo Peter','Penina James','Raphael Paul','Scola Joseph','Tuma Lucas','Victor Peter','Witness David',
        ];

        $existingCount = \App\Models\Student::where('exam_number', 'like', $prefix.'-%')->count();

        $counter = 1;
        foreach ($names as $fullName) {
            $seq = $existingCount + $counter;
            $number = str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
            $examNumber = $prefix.'-'.$number;

            $gender = $counter % 2 === 0 ? 'M' : 'F';
            $stream = $counter % 3 === 0 ? 'B' : 'A';

            \App\Models\Student::updateOrCreate(
                ['exam_number' => $examNumber],
                [
                    'full_name' => $fullName,
                    'class_level' => $data['class_level'],
                    'stream' => $stream,
                    'gender' => $gender,
                    'date_of_birth' => now()->subYears(15)->subDays($counter),
                    'school_id' => $school?->id,
                    'academic_year' => $academicYear,
                ],
            );

            $counter++;
        }

        return redirect()->route('students.index')->with('success', 'Sample students imported successfully.');
    })->name('students.import-samples');

    Route::get('/students/promote', function (\Illuminate\Http\Request $request) {
        $school = School::query()->first();

        $classFilter = $request->query('class');

        // Latest exam by created_at
        $latestExam = Exam::orderByDesc('created_at')->first();

        $topStudents = collect();

        if ($latestExam) {
            // Load all results for latest exam, optionally filtered by class level
            $resultsQuery = ExamResult::where('exam_id', $latestExam->id)
                ->with(['student:id,exam_number,full_name,gender,class_level,stream']);

            if ($classFilter) {
                $resultsQuery->whereHas('student', function ($q) use ($classFilter) {
                    $q->where('class_level', $classFilter);
                });
            }

            $allResults = $resultsQuery->get();

            if ($allResults->isNotEmpty()) {
                $schemes = GradingScheme::all();

                $gradeForMark = function (?int $mark) use ($schemes) {
                    if ($mark === null) {
                        return null;
                    }

                    return $schemes->first(function (GradingScheme $s) use ($mark) {
                        return $mark >= $s->min_mark && $mark <= $s->max_mark;
                    });
                };

                $topStudents = $allResults
                    ->groupBy('student_id')
                    ->map(function ($rows) use ($gradeForMark) {
                        /** @var \App\Models\ExamResult $first */
                        $first = $rows->first();
                        $student = $first->student;

                        $validMarks = $rows->pluck('marks')->filter(fn ($m) => $m !== null);
                        $total = $validMarks->sum();
                        $count = $validMarks->count();
                        $average = $count > 0 ? round($total / $count, 1) : null;

                        // Points total from grading scheme
                        $pointsArr = [];
                        foreach ($rows as $res) {
                            $scheme = $gradeForMark($res->marks);
                            if ($scheme && $scheme->points !== null) {
                                $pointsArr[] = $scheme->points;
                            }
                        }
                        $pointsTotal = ! empty($pointsArr) ? array_sum($pointsArr) : null;

                        // Division based on average
                        $divisionCode = null;
                        if ($average !== null) {
                            $avgScheme = $gradeForMark((int) round($average));
                            if ($avgScheme && $avgScheme->division) {
                                $divisionCode = $avgScheme->division;
                            } elseif ($avgScheme) {
                                $grade = strtoupper($avgScheme->grade);
                                $divisionCode = match ($grade) {
                                    'A' => 'Division I',
                                    'B' => 'Division II',
                                    'C' => 'Division III',
                                    'D' => 'Division IV',
                                    default => 'Division 0',
                                };
                            }
                        }

                        return [
                            'exam_number' => $student->exam_number,
                            'full_name' => $student->full_name,
                            'class_level' => $student->class_level,
                            'stream' => $student->stream,
                            'gender' => $student->gender,
                            'average' => $average,
                            'division' => $divisionCode,
                            'points' => $pointsTotal,
                        ];
                    })
                    // Sort by average (desc), then by total points (asc if available)
                    ->sortByDesc(function ($row) {
                        return $row['average'] ?? -INF;
                    })
                    ->take(10)
                    ->values();
            }
        }

        // Fallback: if no exam/results, just show top 10 students by name as before
        if ($topStudents->isEmpty()) {
            $studentQuery = Student::query();
            if ($classFilter) {
                $studentQuery->where('class_level', $classFilter);
            }

            $topStudents = $studentQuery
                ->orderBy('class_level')
                ->orderBy('stream')
                ->orderBy('full_name')
                ->limit(10)
                ->get([
                    'exam_number',
                    'full_name',
                    'class_level',
                    'stream',
                    'gender',
                ]);
        }

        $classLevels = Student::select('class_level')
            ->distinct()
            ->orderBy('class_level')
            ->pluck('class_level');

        $currentYear = AcademicYear::where('is_current', true)->first();

        return Inertia::render('PromoteStudents', [
            'school' => $school,
            'students' => $topStudents,
            'year' => $currentYear?->year ?? date('Y'),
            'classes' => $classLevels,
            'filters' => [
                'class' => $classFilter,
            ],
        ]);
    })->name('students.promote');

    Route::get('/timetables', function () {
        $timetables = Timetable::orderByDesc('created_at')->get([
            'id',
            'title',
            'table_no',
            'name',
            'class_name',
            'academic_year',
            'term',
            'file_path',
        ]);

        return Inertia::render('Timetables', [
            'timetables' => $timetables,
        ]);
    })->name('timetables.index');

    Route::get('/resources', function () {
        return Inertia::render('Resources');
    })->name('resources.index');

    Route::get('/resources/books', function () {
        $resources = BookResource::orderBy('folder')
            ->orderBy('title')
            ->get([
                'id',
                'folder',
                'title',
                'description',
                'file_path',
                'created_at',
            ]);

        $grouped = $resources
            ->groupBy('folder')
            ->map(function ($items, $folder) {
                return [
                    'folder' => $folder,
                    // Only treat rows with a real file_path as downloadable items
                    'items' => $items->whereNotNull('file_path')->values(),
                ];
            })
            ->values();

        return Inertia::render('ResourceBooks', [
            'folders' => $grouped,
        ]);
    })->name('resources.books.index');

    Route::post('/resources/books', function (Illuminate\Http\Request $request) {
        $data = $request->validate([
            'folder' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:51200'], // up to 50MB
        ]);

        $path = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $folderSlug = Str::slug($data['folder']);
            $path = $file->storeAs(
                'resources/books/'.$folderSlug,
                $file->getClientOriginalName(),
                'public'
            );
        }

        BookResource::create([
            'folder' => $data['folder'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'file_path' => $path,
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()->route('resources.books.index');
    })->name('resources.books.store');

    Route::get('/resources/lesson-plans', function () {
        $school = School::query()->first();

        $subjects = Subject::orderBy('subject_code')
            ->get([
                'id',
                'subject_code',
                'name',
            ]);

        return Inertia::render('LessonPlans', [
            'school' => $school,
            'subjects' => $subjects,
        ]);
    })->name('resources.lesson-plans.index');

    Route::get('/resources/lesson-plans/preview', function (Illuminate\Http\Request $request) {
        $school = School::query()->first();

        $subjectId = $request->query('subject_id');
        $subject = null;
        if ($subjectId) {
            $subject = Subject::find($subjectId, ['id', 'subject_code', 'name']);
        }

        $meta = [
            'teacher_name' => $request->query('teacher_name'),
            'class_label' => $request->query('class_label'),
            'year' => $request->query('year'),
            'school_name' => $request->query('school_name') ?: optional($school)->name,
        ];

        return Inertia::render('LessonPlanPreview', [
            'school' => $school,
            'subject' => $subject,
            'meta' => $meta,
        ]);
    })->name('resources.lesson-plans.preview');

    Route::get('/topics', function () {
        $subjects = Subject::orderBy('subject_code')
            ->get([
                'id',
                'subject_code',
                'name',
            ]);

        $classLevels = SchoolClass::select('level')
            ->distinct()
            ->orderBy('level')
            ->pluck('level');

        $topics = Topic::with('subject:id,subject_code,name')
            ->orderBy('class_level')
            ->orderBy('title')
            ->get([
                'id',
                'subject_id',
                'class_level',
                'title',
                'description',
                'created_at',
            ]);

        return Inertia::render('Topics', [
            'subjects' => $subjects,
            'classLevels' => $classLevels,
            'topics' => $topics,
            'bulkPreview' => session('topics_bulk_preview'),
        ]);
    })->name('topics.index');

    Route::post('/topics', function (Illuminate\Http\Request $request) {
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_level' => ['nullable', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        Topic::create($data);

        return redirect()->route('topics.index')->with('success', 'Topic added successfully.');
    })->name('topics.store');

    Route::get('/topics/template', function () {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="topics_template.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            // Header row
            fputcsv($handle, ['subject_code', 'class_level', 'title', 'description']);
            // Example row
            fputcsv($handle, ['PHY', 'Form I', 'Introduction to Physics', 'Overview of basic physics concepts']);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    })->name('topics.template');

    Route::post('/topics/bulk-preview', function (Illuminate\Http\Request $request) {
        $request->validate([
            'file' => ['required', 'file'],
        ]);

        $file = $request->file('file');
        $rows = [];

        if ($file->isValid()) {
            if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                $header = fgetcsv($handle, 0, ',');
                while (($data = fgetcsv($handle, 0, ',')) !== false) {
                    if (count($data) < 3) {
                        continue;
                    }

                    $rows[] = [
                        'subject_code' => $data[0] ?? null,
                        'class_level' => $data[1] ?? null,
                        'title' => $data[2] ?? null,
                        'description' => $data[3] ?? null,
                    ];
                }
                fclose($handle);
            }
        }

        // Resolve subject IDs
        $subjectsByCode = Subject::pluck('id', 'subject_code');

        $preview = collect($rows)->map(function ($row) use ($subjectsByCode) {
            $code = $row['subject_code'];
            $subjectId = $code && isset($subjectsByCode[$code]) ? $subjectsByCode[$code] : null;

            return [
                'subject_code' => $code,
                'subject_id' => $subjectId,
                'class_level' => $row['class_level'],
                'title' => $row['title'],
                'description' => $row['description'],
                'has_error' => ! $subjectId || ! $row['title'],
            ];
        })->all();

        return redirect()
            ->route('topics.index')
            ->with('topics_bulk_preview', $preview);
    })->name('topics.bulk-preview');

    Route::post('/topics/bulk-store', function (Illuminate\Http\Request $request) {
        $data = $request->validate([
            'rows' => ['required', 'array'],
            'rows.*.subject_id' => ['nullable', 'exists:subjects,id'],
            'rows.*.class_level' => ['nullable', 'string', 'max:50'],
            'rows.*.title' => ['nullable', 'string', 'max:255'],
            'rows.*.description' => ['nullable', 'string'],
        ]);

        $rows = collect($data['rows'])
            ->filter(fn ($row) => ! empty($row['subject_id']) && ! empty($row['title']))
            ->values();

        foreach ($rows as $row) {
            Topic::create([
                'subject_id' => $row['subject_id'],
                'class_level' => $row['class_level'] ?? null,
                'title' => $row['title'],
                'description' => $row['description'] ?? null,
            ]);
        }

        return redirect()->route('topics.index')->with('success', 'Bulk topics uploaded successfully.');
    })->name('topics.bulk-store');

    Route::get('/calendar', function () {
        $events = Event::orderBy('date')
            ->get([
                'id',
                'date',
                'title',
                'description',
            ]);

        return Inertia::render('Calendar', [
            'events' => $events,
        ]);
    })->name('calendar');

    Route::get('/calendar/preview', function () {
        $school = School::query()->first();

        $events = Event::orderBy('date')
            ->get([
                'id',
                'date',
                'title',
                'description',
            ]);

        return Inertia::render('CalendarPreview', [
            'school' => $school,
            'events' => $events,
        ]);
    })->name('calendar.preview');

    Route::post('/calendar/events', function (Illuminate\Http\Request $request) {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        Event::create($data);

        return redirect()->route('calendar')->with('success', 'Event added successfully.');
    })->name('calendar.events.store');

    Route::get('/notifications/sms', function () {
        $exams = Exam::orderByDesc('created_at')
            ->limit(15)
            ->get([
                'id',
                'name',
                'type',
                'academic_year',
            ]);

        $classLevels = Student::select('class_level')
            ->whereNotNull('class_level')
            ->distinct()
            ->orderBy('class_level')
            ->pluck('class_level');

        $recent = SmsNotification::orderByDesc('created_at')
            ->limit(15)
            ->get([
                'id',
                'type',
                'audience',
                'exam_id',
                'class_level',
                'message',
                'status',
                'created_at',
            ]);

        return Inertia::render('NotificationsSms', [
            'exams' => $exams,
            'classLevels' => $classLevels,
            'recent' => $recent,
        ]);
    })->name('notifications.sms');

    Route::post('/notifications/sms', function (Illuminate\Http\Request $request) {
        $data = $request->validate([
            'type' => ['required', 'string', 'max:50'],
            'audience' => ['required', 'string', 'max:50'],
            'exam_id' => ['nullable', 'integer', 'exists:exams,id'],
            'class_level' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string'],
        ]);

        $scope = [
            'audience' => $data['audience'],
            'class_level' => $data['class_level'] ?? null,
        ];

        SmsNotification::create([
            'type' => $data['type'],
            'audience' => $data['audience'],
            'channel' => 'sms',
            'exam_id' => $data['exam_id'] ?? null,
            'class_level' => $data['class_level'] ?? null,
            'message' => $data['message'],
            'status' => 'queued',
            'recipient_scope' => $scope,
            'created_by' => auth()->id(),
        ]);

        // NOTE: Here is where actual SMS sending would be integrated with an external provider.

        return redirect()->route('notifications.sms')->with('success', 'SMS notification saved (sending integration pending).');
    })->name('notifications.sms.store');

    Route::get('/announcements', function () {
        $announcements = Announcement::orderByDesc('created_at')
            ->get([
                'id',
                'title',
                'body',
                'audience',
                'published_at',
                'created_at',
            ]);

        return Inertia::render('Announcements', [
            'announcements' => $announcements,
        ]);
    })->name('announcements.index');

    Route::post('/announcements', function (Illuminate\Http\Request $request) {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'audience' => ['required', 'string', 'max:50'],
        ]);

        Announcement::create([
            'title' => $data['title'],
            'body' => $data['body'],
            'audience' => $data['audience'],
            'published_at' => now(),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('announcements.index')->with('success', 'Announcement created.');
    })->name('announcements.store');

    Route::delete('/announcements/{announcement}', function (Announcement $announcement) {
        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', 'Announcement deleted.');
    })->name('announcements.destroy');

    Route::get('/resources/lesson-plans', function () {
        $school = School::query()->first();

        $subjects = Subject::orderBy('subject_code')
            ->get([
                'id',
                'subject_code',
                'name',
            ]);

        return Inertia::render('LessonPlans', [
            'school' => $school,
            'subjects' => $subjects,
        ]);
    })->name('resources.lesson-plans.index');

    Route::get('/resources/lesson-plans/preview', function (Illuminate\Http\Request $request) {
        $school = School::query()->first();

        $subjectId = $request->query('subject_id');
        $subject = null;
        if ($subjectId) {
            $subject = Subject::find($subjectId, ['id', 'subject_code', 'name']);
        }

        $meta = [
            'teacher_name' => $request->query('teacher_name'),
            'class_label' => $request->query('class_label'),
            'year' => $request->query('year'),
            'school_name' => $request->query('school_name') ?: optional($school)->name,
        ];

        return Inertia::render('LessonPlanPreview', [
            'school' => $school,
            'subject' => $subject,
            'meta' => $meta,
        ]);
    })->name('resources.lesson-plans.preview');

    Route::get('/timetables/create', function () {
        $school = School::query()->first();

        if (! $school) {
            $school = School::create([]);
        }

        $subjects = Subject::with('teachers')
            ->orderBy('subject_code')
            ->get()
            ->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'subject_code' => $subject->subject_code,
                    'name' => $subject->name,
                    'class_levels' => $subject->class_levels,
                    'teachers' => $subject->teachers->map(function ($teacher) {
                        $parts = preg_split('/\s+/', trim($teacher->name));
                        $initials = collect($parts)
                            ->filter()
                            ->map(fn ($p) => Str::upper(mb_substr($p, 0, 1)))
                            ->implode('');

                        return [
                            'id' => $teacher->id,
                            'name' => $teacher->name,
                            'initials' => $initials,
                        ];
                    }),
                ];
            });

        $classes = SchoolClass::orderBy('level')
            ->orderBy('stream')
            ->get([
                'id',
                'name',
                'level',
                'stream',
            ])
            ->groupBy('level')
            ->map(function ($group) {
                return $group->first();
            })
            ->values();

        return Inertia::render('CreateTimetable', [
            'school' => $school,
            'timetable' => null,
            'subjects' => $subjects,
            'classes' => $classes,
        ]);
    })->name('timetables.create');

    Route::post('/timetables', function (Illuminate\Http\Request $request) {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'class_name' => ['nullable', 'string', 'max:255'],
            'academic_year' => ['nullable', 'string', 'max:50'],
            'term' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'max:50'],
        ]);

        $timetable = Timetable::create([
            'title' => $data['title'],
            'class_name' => $data['class_name'] ?? null,
            'academic_year' => $data['academic_year'] ?? null,
            'term' => $data['term'] ?? null,
            'table_no' => $request->input('table_no'),
            'name' => $request->input('name') ?? ($data['type'] ?? null),
        ]);

        // Generate a simple PDF version of the timetable header for archiving
        try {
            $school = School::query()->first();

            $pdf = Pdf::loadView('timetables.pdf', [
                'school' => $school,
                'timetable' => $timetable,
            ])->setPaper('a4', 'landscape');

            $relativePath = 'timetables/timetable_'.$timetable->id.'.pdf';

            Storage::disk('public')->put($relativePath, $pdf->output());

            $timetable->file_path = $relativePath;
            $timetable->save();
        } catch (\Throwable $e) {
            // If PDF generation fails, continue without blocking save
        }

        return redirect()->route('timetables.index');
    })->name('timetables.store');

    Route::get('/timetables/{timetable}/download', function (Timetable $timetable) {
        if (! $timetable->file_path) {
            abort(404);
        }

        if (! Storage::disk('public')->exists($timetable->file_path)) {
            abort(404);
        }

        $downloadName = ($timetable->title ?: 'timetable').'_'.$timetable->id.'.pdf';

        return Storage::disk('public')->download($timetable->file_path, $downloadName);
    })->name('timetables.download');

    // Sitting plans (exam sitting arrangements)
    Route::get('/sitting-plans', function () {
        $plans = SittingPlan::with(['exam:id,name,academic_year', 'class:id,name,level,stream'])
            ->orderByDesc('created_at')
            ->get([
                'id',
                'exam_id',
                'class_id',
                'title',
                'rooms_count',
                'file_path',
                'created_at',
            ]);

        $exams = Exam::orderByDesc('created_at')->get([
            'id',
            'name',
            'academic_year',
            'type',
        ]);

        $classes = SchoolClass::orderBy('level')
            ->orderBy('stream')
            ->get([
                'id',
                'name',
                'level',
                'stream',
            ])
            ->groupBy('level')
            ->map(function ($group) {
                return $group->first();
            })
            ->values();

        return Inertia::render('SittingPlans', [
            'plans' => $plans,
            'exams' => $exams,
            'classes' => $classes,
        ]);
    })->name('sitting-plans.index');

    Route::post('/sitting-plans', function (Illuminate\Http\Request $request) {
        $data = $request->validate([
            'exam_id' => ['required', 'exists:exams,id'],
            'class_id' => ['required', 'exists:school_classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'rooms_count' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        SittingPlan::create([
            'exam_id' => $data['exam_id'],
            'class_id' => $data['class_id'],
            'title' => $data['title'],
            'rooms_count' => $data['rooms_count'] ?? 1,
        ]);

        return redirect()->route('sitting-plans.index');
    })->name('sitting-plans.store');

    Route::get('/sitting-plans/{sittingPlan}', function (SittingPlan $sittingPlan) {
        $plan = $sittingPlan->load(['exam:id,name,academic_year,type', 'class:id,name,level,stream']);

        $school = School::query()->first();

        $class = $plan->class;

        $students = collect();
        if ($class) {
            $students = Student::query()
                ->where('class_level', $class->level)
                ->where('stream', $class->stream)
                ->orderBy('exam_number')
                ->get([
                    'id',
                    'exam_number',
                    'full_name',
                    'gender',
                ]);
        }

        return Inertia::render('SittingPlanPreview', [
            'plan' => $plan,
            'students' => $students,
            'school' => $school,
        ]);
    })->name('sitting-plans.show');

    Route::delete('/sitting-plans/{sittingPlan}', function (SittingPlan $sittingPlan) {
        $sittingPlan->delete();

        return redirect()->route('sitting-plans.index');
    })->name('sitting-plans.destroy');

    Route::get('/timetables/{timetable}', function (Timetable $timetable) {
        $school = School::query()->first();

        if (! $school) {
            $school = School::create([]);
        }

        $subjects = Subject::with('teachers')
            ->orderBy('subject_code')
            ->get()
            ->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'subject_code' => $subject->subject_code,
                    'name' => $subject->name,
                    'class_levels' => $subject->class_levels,
                    'teachers' => $subject->teachers->map(function ($teacher) {
                        $parts = preg_split('/\s+/', trim($teacher->name));
                        $initials = collect($parts)
                            ->filter()
                            ->map(fn ($p) => Str::upper(mb_substr($p, 0, 1)))
                            ->implode('');

                        return [
                            'id' => $teacher->id,
                            'name' => $teacher->name,
                            'initials' => $initials,
                        ];
                    }),
                ];
            });

        $classes = SchoolClass::orderBy('level')
            ->orderBy('stream')
            ->get([
                'id',
                'name',
                'level',
                'stream',
            ]);

        return Inertia::render('CreateTimetable', [
            'school' => $school,
            'timetable' => $timetable,
            'subjects' => $subjects,
            'classes' => $classes,
        ]);
    })->name('timetables.show');

    Route::delete('/timetables/{timetable}', function (Timetable $timetable) {
        $timetable->delete();

        return redirect()->route('timetables.index');
    })->name('timetables.destroy');

    Route::get('/subjects', [\App\Http\Controllers\SubjectController::class, 'index'])
        ->name('subjects.index');

    Route::get('/subjects/create', [\App\Http\Controllers\SubjectController::class, 'create'])
        ->name('subjects.create');

    Route::post('/subjects', [\App\Http\Controllers\SubjectController::class, 'store'])
        ->name('subjects.store');

    Route::post('/subjects/import', [\App\Http\Controllers\SubjectController::class, 'import'])
        ->name('subjects.import');

    Route::delete('/subjects/{subject}', [\App\Http\Controllers\SubjectController::class, 'destroy'])
        ->name('subjects.destroy');

    Route::get('/subjects/assign-teachers', function () {
        $teachers = User::query()
            ->where('role', 'teacher')
            ->with('subjects:id,subject_code')
            ->orderBy('name')
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'subject_ids' => $user->subjects->pluck('id')->all(),
                ];
            });

        $schoolId = request()->user()?->school_id;

        $subjectsQuery = Subject::query();

        if ($schoolId) {
            $subjectsQuery->where('school_id', $schoolId);
        }

        $subjects = $subjectsQuery
            ->orderBy('subject_code')
            ->get([
                'id',
                'subject_code',
                'name',
            ]);

        return Inertia::render('AssignTeachers', [
            'teachers' => $teachers,
            'subjects' => $subjects,
        ]);
    })->name('subjects.assign-teachers');

    Route::post('/subjects/assign-teachers', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'assignments' => ['required', 'array'],
            'assignments.*.teacher_id' => ['required', 'integer', 'exists:users,id'],
            'assignments.*.subject_ids' => ['required', 'array'],
            'assignments.*.subject_ids.*' => ['integer', 'exists:subjects,id'],
        ]);

        foreach ($data['assignments'] as $row) {
            $user = User::find($row['teacher_id']);
            $user->subjects()->sync($row['subject_ids']);
        }

        return back()->with('success', 'Teacher subject assignments updated.');
    })->name('subjects.assign-teachers.save');

    Route::delete('/subjects/{subject}', function (Subject $subject) {
        $subject->delete();

        return back()->with('success', 'Subject deleted.');
    })->name('subjects.destroy');

    Route::get('/teachers', function () {
        $schoolId = request()->user()?->school_id;

        $teachersQuery = User::where('role', 'teacher')
            ->orderBy('name');

        if ($schoolId) {
            $teachersQuery->where('school_id', $schoolId);
        }

        $teachers = $teachersQuery->get([
            'id',
            'name',
            'email',
            'phone',
            'check_number',
            'teaching_classes',
        ]);

        $classesQuery = SchoolClass::orderBy('name');

        if ($schoolId) {
            $classesQuery->where('school_id', $schoolId);
        }

        $classes = $classesQuery
            ->get(['id', 'name', 'level', 'stream'])
            ->map(function (SchoolClass $class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'level' => $class->level,
                    'stream' => $class->stream,
                ];
            });

        return Inertia::render('TeachersIndex', [
            'teachers' => $teachers,
            'classes' => $classes,
        ]);
    })->name('teachers.index');

    Route::get('/teachers/create', function () {
        return Inertia::render('AddTeacher');
    })->name('teachers.create');

    Route::post('/teachers', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:50'],
            'check_number' => ['required', 'string', 'max:100'],
            'teaching_classes' => ['required', 'string', 'max:255'],
        ]);

        $teacher = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'check_number' => $validated['check_number'],
            'teaching_classes' => $validated['teaching_classes'],
            'role' => 'teacher',
            'is_active' => true,
            // Default password; in real app you'd send reset link
            'password' => bcrypt('12345678'),
        ]);

        return redirect()->route('teachers.index')->with('success', 'Teacher registered successfully.');
    })->name('teachers.store');

    Route::put('/teachers/{teacher}', function (\Illuminate\Http\Request $request, User $teacher) {
        abort_unless($teacher->role === 'teacher', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'check_number' => ['required', 'string', 'max:100'],
            'teaching_classes' => ['required', 'string', 'max:255'],
        ]);

        $teacher->update($validated);

        return redirect()->route('teachers.index')->with('success', 'Teacher updated successfully.');
    })->name('teachers.update');

    Route::delete('/teachers/{teacher}', function (User $teacher) {
        abort_unless($teacher->role === 'teacher', 404);

        $teacher->delete();

        return redirect()->route('teachers.index')->with('success', 'Teacher deleted successfully.');
    })->name('teachers.destroy');

    Route::post('/teachers/import-samples', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
        ]);

        $user = $request->user();
        $schoolId = $user?->school_id;

        /** @var SchoolClass $class */
        $class = SchoolClass::with('subjects:id,subject_code,name')->findOrFail($data['class_id']);

        $subjects = $class->subjects;

        // Fallback simple subject list if none attached yet
        if ($subjects->isEmpty()) {
            $subjects = Subject::orderBy('subject_code')->take(8)->get(['id', 'subject_code', 'name']);
        }

        $count = 38;

        for ($i = 1; $i <= $count; $i++) {
            $subject = $subjects[($i - 1) % max($subjects->count(), 1)];

            $name = sprintf('Sample %s %s Teacher %02d', $class->level ?? 'Class', $subject->subject_code ?? 'SUB', $i);
            $email = sprintf('sample%s_%02d@school.test', $class->id, $i);

            if (User::where('email', $email)->exists()) {
                continue; // skip if already imported previously
            }

            User::create([
                'name' => $name,
                'email' => $email,
                'phone' => '0700000000',
                'check_number' => 'CHK-' . $class->id . '-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'teaching_classes' => trim(($class->name ?? 'Class') . ' - ' . ($subject->subject_code ?? $subject->name)),
                'role' => 'teacher',
                'is_active' => true,
                'school_id' => $schoolId,
                'password' => bcrypt('12345678'),
            ]);
        }

        return redirect()->route('teachers.index')->with('success', 'Sample teachers imported successfully.');
    })->name('teachers.import-samples');

    Route::get('/classes', function () {
        $user = request()->user();
        $schoolId = $user?->school_id;
        $school = $user?->school;

        $classesQuery = SchoolClass::with('subjects:id')
            ->orderBy('name');

        if ($schoolId) {
            $classesQuery->where('school_id', $schoolId);
        }

        $classes = $classesQuery
            ->get()
            ->map(function (SchoolClass $class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'level' => $class->level,
                    'description' => $class->description,
                    'subject_ids' => $class->subjects->pluck('id')->all(),
                ];
            });

        $subjects = Subject::orderBy('subject_code')->get([
            'id',
            'subject_code',
            'name',
        ]);

        return Inertia::render('Classes', [
            'classes' => $classes,
            'subjects' => $subjects,
            'schoolLevel' => $school?->level ?? '',
        ]);
    })->name('classes.index');

    Route::post('/classes', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'subject_ids' => ['sometimes', 'array'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
        ]);

        $subjectIds = $request->input('subject_ids', []);

        $class = SchoolClass::create([
            'school_id' => $request->user()?->school_id,
            'name' => $validated['name'],
            // Always use the school's level instead of the request payload
            'level' => optional($request->user()?->school)->level,
            'description' => $validated['description'] ?? null,
        ]);

        if (! empty($subjectIds)) {
            $class->subjects()->sync($subjectIds);
        }

        return redirect()->route('classes.index')->with('success', 'Class created successfully.');
    })->name('classes.store');

    Route::post('/classes/{class}', function (\Illuminate\Http\Request $request, SchoolClass $class) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'subject_ids' => ['sometimes', 'array'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
        ]);

        $subjectIds = $request->input('subject_ids', []);

        $class->update([
            'name' => $validated['name'],
            // Keep level in sync with the school's level
            'level' => optional($request->user()?->school)->level,
            'description' => $validated['description'] ?? null,
        ]);

        if (! empty($subjectIds)) {
            $class->subjects()->sync($subjectIds);
        }

        return redirect()->route('classes.index')->with('success', 'Class updated successfully.');
    })->name('classes.update');

    Route::delete('/classes/{class}', function (SchoolClass $class) {
        $class->delete();

        return redirect()->route('classes.index')->with('success', 'Class deleted successfully.');
    })->name('classes.destroy');

    Route::post('/classes/bulk-delete', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:school_classes,id'],
        ]);

        SchoolClass::whereIn('id', $data['ids'])->delete();

        return redirect()->route('classes.index')->with('success', 'Selected classes deleted successfully.');
    })->name('classes.bulk-delete');

    Route::post('/classes/bulk-create-standard', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        $schoolId = $user?->school_id;
        
        // Get level from request, or use school level as fallback
        $selectedLevel = $request->input('level');
        $schoolLevel = $selectedLevel ?: $user?->school?->level;

        // Define classes based on selected level
        $classesToCreate = [];
        
        if ($schoolLevel === 'Primary') {
            $classesToCreate = ['Class I', 'Class II', 'Class III', 'Class IV', 'Class V', 'Class VI', 'Class VII'];
        } elseif ($schoolLevel === 'O-Level') {
            $classesToCreate = ['Form I', 'Form II', 'Form III', 'Form IV'];
        } elseif ($schoolLevel === 'A-Level') {
            $classesToCreate = ['Form V', 'Form VI'];
        }

        if (empty($classesToCreate)) {
            return redirect()->route('classes.index')->with('error', 'Invalid school level. Please select a valid level.');
        }

        $createdCount = 0;
        foreach ($classesToCreate as $className) {
            $created = SchoolClass::firstOrCreate(
                ['school_id' => $schoolId, 'name' => $className],
                [
                    'level' => $schoolLevel,
                    'description' => null,
                ],
            );
            if ($created->wasRecentlyCreated) {
                $createdCount++;
            }
        }

        $message = $createdCount > 0 
            ? "Successfully created $createdCount classes for $schoolLevel."
            : 'All classes already exist.';

        return redirect()->route('classes.index')->with('success', $message);
    })->name('classes.bulk-create-standard');

    Route::post('/classes/assign-subjects', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'assignments' => ['required', 'array'],
            'assignments.*.class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'assignments.*.subject_ids' => ['required', 'array'],
            'assignments.*.subject_ids.*' => ['integer', 'exists:subjects,id'],
        ]);

        foreach ($data['assignments'] as $row) {
            $class = SchoolClass::find($row['class_id']);
            $class->subjects()->sync($row['subject_ids']);
        }

        return back()->with('success', 'Class subject assignments updated.');
    })->name('classes.assign-subjects.save');

    Route::get('/classes/teachers', function () {
        $classes = SchoolClass::with('teacher')
            ->orderBy('name')
            ->get()
            ->map(function (SchoolClass $class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'level' => $class->level,
                    'stream' => $class->stream,
                    'teacher_id' => $class->teacher_id,
                    'teacher_name' => optional($class->teacher)->name,
                ];
            });

        $teachers = User::where('role', 'teacher')
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        return Inertia::render('ClassTeachers', [
            'classes' => $classes,
            'teachers' => $teachers,
        ]);
    })->name('classes.teachers.index');

    Route::post('/classes/teachers', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'assignments' => ['required', 'array'],
            'assignments.*.class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'assignments.*.teacher_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        foreach ($data['assignments'] as $row) {
            $class = SchoolClass::find($row['class_id']);
            $class->teacher_id = $row['teacher_id'] ?? null;
            $class->save();
        }

        return back()->with('success', 'Class teacher assignments updated.');
    })->name('classes.assign-teachers.save');

    Route::get('/streams', function () {
        $classes = SchoolClass::with('subjects:id,subject_code')
            ->orderBy('parent_class_id')
            ->orderBy('name')
            ->get()
            ->map(function (SchoolClass $class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'level' => $class->level,
                    'stream' => $class->stream,
                    'parent_class_id' => $class->parent_class_id,
                    'description' => $class->description,
                    'subject_codes' => $class->subjects->pluck('subject_code')->all(),
                    'subject_ids' => $class->subjects->pluck('id')->all(),
                ];
            });

        $subjects = Subject::orderBy('subject_code')
            ->get([
                'id',
                'subject_code',
                'name',
            ]);

        return Inertia::render('Streams', [
            'classes' => $classes,
            'subjects' => $subjects,
        ]);
    })->name('streams.index');

    Route::post('/streams', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'stream' => ['required', 'string', 'max:10'],
            'description' => ['nullable', 'string', 'max:255'],
            'subject_ids' => ['required', 'array', 'min:7'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
        ]);

        $baseClass = SchoolClass::findOrFail($data['class_id']);

        $name = trim(($baseClass->name ?: '') . ' ' . strtoupper($data['stream']));

        $class = SchoolClass::create([
            'school_id' => $baseClass->school_id,
            'parent_class_id' => $baseClass->id,
            'name' => $name,
            'level' => $baseClass->level ?? optional($baseClass->school)->level,
            'stream' => strtoupper($data['stream']),
            'description' => $data['description'] ?? null,
        ]);

        $class->subjects()->sync($data['subject_ids']);

        return redirect()->route('streams.index');
    })->name('streams.store');

    Route::post('/streams/{class}', function (\Illuminate\Http\Request $request, SchoolClass $class) {
        $data = $request->validate([
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'stream' => ['required', 'string', 'max:10'],
            'description' => ['nullable', 'string', 'max:255'],
            'subject_ids' => ['required', 'array', 'min:7'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
        ]);

        $baseClass = SchoolClass::findOrFail($data['class_id']);

        $name = trim(($baseClass->name ?: '') . ' ' . strtoupper($data['stream']));

        $class->update([
            'school_id' => $baseClass->school_id,
            'parent_class_id' => $baseClass->id,
            'name' => $name,
            'level' => $baseClass->level ?? optional($baseClass->school)->level,
            'stream' => strtoupper($data['stream']),
            'description' => $data['description'] ?? null,
        ]);

        $class->subjects()->sync($data['subject_ids']);

        return redirect()->route('streams.index');
    })->name('streams.update');

    Route::delete('/streams/{class}', function (SchoolClass $class) {
        $class->subjects()->detach();
        $class->delete();

        return back()->with('success', 'Stream deleted.');
    })->name('streams.destroy');

    Route::get('/exams/create', function () {
        $classes = SchoolClass::orderBy('level')->orderBy('name')->get([
            'id',
            'name',
            'level',
            'stream',
        ]);

        $currentYear = AcademicYear::where('is_current', true)->first();

        $classLevels = SchoolClass::select('level')
            ->distinct()
            ->whereNotNull('level')
            ->orderBy('level')
            ->pluck('level');

        $exams = Exam::with('classes:id,level')
            ->orderByDesc('created_at')
            ->get([
                'id',
                'name',
                'type',
                'academic_year',
                'term',
                'start_date',
                'end_date',
            ])
            ->map(function (Exam $exam) {
                $levels = $exam->classes
                    ->pluck('level')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                return [
                    'id' => $exam->id,
                    'name' => $exam->name,
                    'type' => $exam->type,
                    'academic_year' => $exam->academic_year,
                    'term' => $exam->term,
                    'start_date' => $exam->start_date,
                    'end_date' => $exam->end_date,
                    'levels' => $levels,
                ];
            });

        return Inertia::render('CreateExam', [
            'classes' => $classes,
            'classLevels' => $classLevels,
            'year' => $currentYear?->year ?? date('Y'),
            'exams' => $exams,
        ]);
    })->name('exams.create');

    Route::post('/exams', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'term' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'levels' => ['nullable', 'array'],
            'levels.*' => ['string', 'max:100'],
        ]);

        $exam = Exam::create([
            'name' => $data['name'],
            'type' => $data['type'] ?? null,
            'academic_year' => $data['academic_year'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'term' => $data['term'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        if (! empty($data['levels'])) {
            $classIds = SchoolClass::whereIn('level', $data['levels'])
                ->pluck('id')
                ->all();

            $exam->classes()->sync($classIds);
        }

        return redirect()->route('exams.create')->with('success', 'Exam created successfully.');
    })->name('exams.store');

    Route::post('/exams/{exam}', function (\Illuminate\Http\Request $request, Exam $exam) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'term' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'levels' => ['nullable', 'array'],
            'levels.*' => ['string', 'max:100'],
        ]);

        $exam->update([
            'name' => $data['name'],
            'type' => $data['type'] ?? null,
            'academic_year' => $data['academic_year'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'term' => $data['term'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        if (array_key_exists('levels', $data)) {
            $levels = $data['levels'] ?? [];

            if (! empty($levels)) {
                $classIds = SchoolClass::whereIn('level', $levels)
                    ->pluck('id')
                    ->all();

                $exam->classes()->sync($classIds);
            } else {
                // If levels key is present but empty, detach all classes
                $exam->classes()->detach();
            }
        }

        return redirect()->route('exams.create')->with('success', 'Exam updated successfully.');
    })->name('exams.update');

    Route::delete('/exams/{exam}', function (Exam $exam) {
        $exam->delete();

        return back()->with('success', 'Exam deleted.');
    })->name('exams.destroy');

    Route::get('/exams/{exam}/results', function (Exam $exam) {
        $user = request()->user();
        $schoolId = $user?->school_id;

        $allResultsQuery = ExamResult::where('exam_id', $exam->id)
            ->with(['student:id,exam_number,full_name,gender', 'subject:id,subject_code']);

        if ($schoolId) {
            $allResultsQuery->whereHas('student', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        }

        $allResults = $allResultsQuery->get();

        if ($allResults->isEmpty()) {
            return Inertia::render('ExamResultsPreview', [
                'exam' => $exam,
                'students' => [],
                'centreSummary' => null,
                'subjectSummary' => [],
            ]);
        }

        $schemes = GradingScheme::all();

        $gradeForMark = function (?int $mark) use ($schemes) {
            if ($mark === null) {
                return null;
            }

            return $schemes->first(function (GradingScheme $s) use ($mark) {
                return $mark >= $s->min_mark && $mark <= $s->max_mark;
            });
        };

        // Per-student rows (for detailed table) + per-student division
        $students = $allResults
            ->groupBy('student_id')
            ->map(function ($rows) use ($gradeForMark) {
                /** @var \App\Models\ExamResult $first */
                $first = $rows->first();
                $student = $first->student;
                $subjects = $rows->map(function (ExamResult $res) {
                    return [
                        'code' => $res->subject->subject_code,
                        'marks' => $res->marks,
                    ];
                })->values();

                $validMarks = $rows->pluck('marks')->filter(fn ($m) => $m !== null);
                $total = $validMarks->sum();
                $count = $validMarks->count();
                $average = $count > 0 ? round($total / $count, 1) : null;

                // Simple division & points: sum of scheme points, division from average mark
                $pointsArr = [];
                foreach ($rows as $res) {
                    $scheme = $gradeForMark($res->marks);
                    if ($scheme && $scheme->points !== null) {
                        $pointsArr[] = $scheme->points;
                    }
                }
                $pointsTotal = ! empty($pointsArr) ? array_sum($pointsArr) : null;

                $divisionCode = null;
                if ($average !== null) {
                    $avgScheme = $gradeForMark((int) round($average));
                    if ($avgScheme && $avgScheme->division) {
                        $divisionCode = $avgScheme->division;
                    } elseif ($avgScheme) {
                        // Fallback mapping by grade
                        $grade = strtoupper($avgScheme->grade);
                        $divisionCode = match ($grade) {
                            'A' => 'Division I',
                            'B' => 'Division II',
                            'C' => 'Division III',
                            'D' => 'Division IV',
                            default => 'Division 0',
                        };
                    }
                }

                return [
                    'exam_number' => $student->exam_number,
                    'full_name' => $student->full_name,
                    'gender' => $student->gender,
                    'subjects' => $subjects,
                    'total' => $count > 0 ? $total : null,
                    'average' => $average,
                    'division' => $divisionCode,
                    'points' => $pointsTotal,
                ];
            })
            ->values();

        // Centre summary metrics
        $distinctStudents = $allResults->pluck('student_id')->unique();
        $totalCandidates = $distinctStudents->count();

        $validMarksAll = $allResults->pluck('marks')->filter(fn ($m) => $m !== null);
        $centreAverage = $validMarksAll->isNotEmpty() ? round($validMarksAll->avg(), 2) : null;

        $allPoints = [];
        $perStudentHasFail = [];
        $divisionSummary = [
            'F' => ['I' => 0, 'II' => 0, 'III' => 0, 'IV' => 0, '0' => 0],
            'M' => ['I' => 0, 'II' => 0, 'III' => 0, 'IV' => 0, '0' => 0],
            'T' => ['I' => 0, 'II' => 0, 'III' => 0, 'IV' => 0, '0' => 0],
        ];

        foreach ($students as $idx => $row) {
            $gender = strtoupper($row['gender'] ?? '');
            $sexKey = in_array($gender, ['F', 'M'], true) ? $gender : 'T';

            // Use student's points for centre GPA
            if ($row['points'] !== null) {
                $allPoints[] = $row['points'];
            }

            // Determine division bucket for summary
            $divLabel = $row['division'] ?? null;
            $bucket = null;
            if ($divLabel) {
                if (str_contains($divLabel, 'Division I')) {
                    $bucket = 'I';
                } elseif (str_contains($divLabel, 'Division II')) {
                    $bucket = 'II';
                } elseif (str_contains($divLabel, 'Division III')) {
                    $bucket = 'III';
                } elseif (str_contains($divLabel, 'Division IV')) {
                    $bucket = 'IV';
                } else {
                    $bucket = '0';
                }
            }

            if ($bucket) {
                $divisionSummary[$sexKey][$bucket]++;
                $divisionSummary['T'][$bucket]++;
            }

            if ($bucket === '0') {
                $perStudentHasFail[$idx] = true;
            }
        }

        $centreGpa = ! empty($allPoints) ? round(array_sum($allPoints) / count($allPoints), 3) : null;

        $failedStudents = collect($perStudentHasFail)->keys();
        $failedCount = $failedStudents->count();
        $passedCount = $totalCandidates - $failedCount;

        $centreSummary = [
            'total_candidates' => $totalCandidates,
            'passed_candidates' => $passedCount,
            'failed_candidates' => $failedCount,
            'centre_average' => $centreAverage,
            'centre_gpa' => $centreGpa,
            'division_summary' => $divisionSummary,
        ];

        // Subject summary metrics
        $subjectSummary = $allResults
            ->groupBy('subject_id')
            ->map(function ($rows, $subjectId) use ($gradeForMark) {
                /** @var \App\Models\ExamResult $first */
                $first = $rows->first();
                $subject = $first->subject;

                $sat = $rows->count();
                $pass = 0;
                $points = [];

                foreach ($rows as $res) {
                    $scheme = $gradeForMark($res->marks);
                    if ($scheme) {
                        if ($scheme->points !== null) {
                            $points[] = $scheme->points;
                        }
                        if (strtoupper($scheme->grade) !== 'F') {
                            $pass++;
                        }
                    }
                }

                $fail = $sat - $pass;
                $gpa = ! empty($points) ? round(array_sum($points) / count($points), 4) : null;

                // Simple competency text based on GPA
                $competency = null;
                if ($gpa !== null) {
                    if ($gpa <= 2.0) {
                        $competency = 'Grade B (Very Good)';
                    } elseif ($gpa <= 3.0) {
                        $competency = 'Grade C (Good)';
                    } elseif ($gpa <= 4.0) {
                        $competency = 'Grade D (Satisfactory)';
                    } else {
                        $competency = 'Grade F (Fail)';
                    }
                }

                return [
                    'code' => $subject->subject_code,
                    'name' => $subject->name ?? $subject->subject_code,
                    'sat' => $sat,
                    'pass' => $pass,
                    'fail' => $fail,
                    'gpa' => $gpa,
                    'competency' => $competency,
                ];
            })
            ->values();

        return Inertia::render('ExamResultsPreview', [
            'exam' => $exam,
            'students' => $students,
            'centreSummary' => $centreSummary,
            'subjectSummary' => $subjectSummary,
        ]);
    })->name('exams.results');

    Route::get('/exams/marks', [MarksController::class, 'index'])->name('exams.marks');

    Route::post('/exams/marks/save', [MarksController::class, 'store'])->name('exams.marks.save');

    Route::get('/students/{student}/marks', function (\Illuminate\Http\Request $request, Student $student) {
        $user = $request->user();

        // Ensure the authenticated user only accesses marks for their own school when applicable
        if ($user && $user->school_id && $student->school_id && $student->school_id !== $user->school_id) {
            abort(403);
        }

        $results = ExamResult::with(['subject:id,subject_code,name', 'student:id,exam_number,full_name', 'exam:id,name,academic_year'])
            ->where('student_id', $student->id)
            ->when($user && $user->school_id, fn ($q) => $q->where('school_id', $user->school_id))
            ->orderByDesc('academic_year')
            ->orderByDesc('exam_id')
            ->get()
            ->map(function (ExamResult $res) {
                return [
                    'exam' => [
                        'id' => $res->exam_id,
                        'name' => $res->exam?->name,
                        'academic_year' => $res->academic_year ?? $res->exam?->academic_year,
                    ],
                    'subject' => [
                        'id' => $res->subject_id,
                        'code' => $res->subject?->subject_code,
                        'name' => $res->subject?->name,
                    ],
                    'marks' => $res->marks,
                    'grade' => $res->grade,
                    'points' => $res->points,
                ];
            });

        return response()->json([
            'student' => [
                'id' => $student->id,
                'exam_number' => $student->exam_number,
                'full_name' => $student->full_name,
            ],
            'results' => $results,
        ]);
    })->name('students.marks.show');

    Route::get('/settings/school-information', function () {
        $user = request()->user();
        $school = $user->school;

        if (! $school) {
            $school = School::create([]);
            $user->update(['school_id' => $school->id]);
        }

        return Inertia::render('SchoolInformation', [
            'school' => $school,
        ]);
    })->name('settings.school-information');

    Route::put('/settings/school-information', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        $school = $user->school;

        if (! $school) {
            $school = School::create([]);
            $user->update(['school_id' => $school->id]);
        }

        // Validate school name uniqueness (excluding current school)
        $request->validate([
            'name' => 'nullable|string|max:255|unique:schools,name,' . $school->id,
            'school_code' => 'nullable|string|max:50',
        ]);

        $data = $request->only([
            'name',
            'registration_number',
            'school_code',
            'level',
            'ownership',
            'phone',
            'email',
            'address',
            'district',
            'region',
            'head_teacher_name',
            'head_teacher_phone',
        ]);

        $school->fill(array_filter($data, fn ($value) => ! is_null($value)));
        $school->save();

        return back()->with('success', 'School information updated.');
    })->name('settings.school-information.update');

    Route::get('/settings/user-management', function () {
        $user = request()->user();
        $schoolId = $user->school_id;

        $users = User::query()
            ->where('school_id', $schoolId)
            ->orderBy('name')
            ->get(['id', 'school_id', 'name', 'email', 'username', 'role', 'is_active']);

        return Inertia::render('UserManagement', [
            'users' => $users,
            'schoolId' => $schoolId,
        ]);
    })->name('settings.user-management');

    Route::post('/settings/user-management', function (Illuminate\Http\Request $request) {
        $currentUser = $request->user();
        $schoolId = $currentUser->school_id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['nullable', 'string', 'max:50'],
        ]);

        $password = 'changeme123';

        User::create([
            'school_id' => $schoolId,
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'] ?? null,
            'is_active' => true,
            'password' => $password,
        ]);

        return redirect()->route('settings.user-management')->with('success', 'User created. Default password: changeme123');
    })->name('settings.user-management.store');

    Route::post('/settings/user-management/{user}/toggle', function (User $user) {
        $user->is_active = ! (bool) $user->is_active;
        $user->save();

        return redirect()->route('settings.user-management')->with('success', 'User status updated.');
    })->name('settings.user-management.toggle');

    Route::get('/settings/email-sms', function () {
        $settings = NotificationSetting::query()->first();

        if (! $settings) {
            $settings = NotificationSetting::create([]);
        }

        return Inertia::render('EmailSmsSettings', [
            'settings' => $settings,
        ]);
    })->name('settings.email-sms');

    Route::get('/settings/academic-year', function () {
        $years = AcademicYear::orderByDesc('year')
            ->get()
            ->map(function (AcademicYear $year) {
                return [
                    'id' => $year->id,
                    'year' => $year->year,
                    'is_current' => $year->is_current,
                    'students_count' => 0, // placeholder until wired to real data
                    'results_count' => 0, // placeholder until wired to real data
                ];
            });

        return Inertia::render('AcademicYear', [
            'years' => $years,
        ]);
    })->name('settings.academic-year');

    Route::post('/settings/academic-year', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'year' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ]);

        AcademicYear::create($validated);

        return back()->with('success', 'Academic year added.');
    })->name('settings.academic-year.store');

    Route::put('/settings/academic-year/{academicYear}/set-current', function (AcademicYear $academicYear) {
        AcademicYear::query()->update(['is_current' => false]);
        $academicYear->is_current = true;
        $academicYear->save();

        return back()->with('success', 'Current academic year updated.');
    })->name('settings.academic-year.set-current');

    Route::get('/settings/grading-system', function () {
        $schemes = GradingScheme::orderByDesc('min_mark')->get();

        if ($schemes->isEmpty()) {
            $defaults = [
                ['grade' => 'A', 'min_mark' => 75, 'max_mark' => 100, 'division' => 'Division I', 'points' => 1],
                ['grade' => 'B', 'min_mark' => 65, 'max_mark' => 74, 'division' => 'Division II', 'points' => 2],
                ['grade' => 'C', 'min_mark' => 45, 'max_mark' => 64, 'division' => 'Division III', 'points' => 3],
                ['grade' => 'D', 'min_mark' => 35, 'max_mark' => 44, 'division' => 'Division IV', 'points' => 4],
                ['grade' => 'F', 'min_mark' => 0, 'max_mark' => 34, 'division' => 'Div 0 (Fail)', 'points' => 5],
            ];

            foreach ($defaults as $row) {
                GradingScheme::create($row);
            }

            $schemes = GradingScheme::orderByDesc('min_mark')->get();
        }

        return Inertia::render('GradingSystem', [
            'schemes' => $schemes,
        ]);
    })->name('settings.grading-system');

    Route::post('/settings/grading-system', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'schemes' => ['required', 'array'],
            'schemes.*.id' => ['nullable', 'integer', 'exists:grading_schemes,id'],
            'schemes.*.grade' => ['required', 'string', 'max:2'],
            'schemes.*.min_mark' => ['required', 'integer', 'min:0', 'max:100'],
            'schemes.*.max_mark' => ['required', 'integer', 'min:0', 'max:100'],
            'schemes.*.division' => ['nullable', 'string', 'max:50'],
            'schemes.*.points' => ['nullable', 'integer', 'min:0'],
        ]);

        $idsToKeep = [];

        foreach ($data['schemes'] as $row) {
            if (! empty($row['id'])) {
                $scheme = GradingScheme::find($row['id']);
                $scheme->update($row);
            } else {
                $scheme = GradingScheme::create($row);
            }

            $idsToKeep[] = $scheme->id;
        }

        GradingScheme::whereNotIn('id', $idsToKeep)->delete();

        return back()->with('success', 'Grading scheme saved.');
    })->name('settings.grading-system.save');

    Route::get('/settings/logo-branding', function () {
        $school = School::query()->first();

        if (! $school) {
            $school = School::create([]);
        }

        return Inertia::render('LogoBranding', [
            'school' => $school,
        ]);
    })->name('settings.logo-branding');

    Route::post('/settings/logo-branding', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'logo' => ['required', 'image', 'max:2048'],
        ]);

        $school = School::query()->first();

        if (! $school) {
            $school = School::create([]);
        }

        $path = $request->file('logo')->store('logos', 'public');

        $school->logo_path = $path;
        $school->save();

        return back()->with('success', 'Logo updated.');
    })->name('settings.logo-branding.update');

    Route::get('/recent-activities', function () {
        return Inertia::render('RecentActivities', [
            'activities' => RecentActivity::with('user')
                ->where('user_id', auth()->id())
                ->latest('occurred_at')
                ->take(100)
                ->get()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'action' => $activity->action,
                        'ip_address' => $activity->ip_address,
                        'user_agent' => $activity->user_agent,
                        'occurred_at' => $activity->occurred_at?->toDateTimeString(),
                    ];
                }),
        ]);
    })->name('recent-activities');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
