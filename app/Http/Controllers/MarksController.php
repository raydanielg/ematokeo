<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamStudentResult;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\GradingScheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Carbon;

class MarksController extends Controller
{
    protected function upsertStudentSummary(int $examId, int $studentId, ?int $schoolId, ?string $academicYear, $schemes): void
    {
        $gradeForMark = function ($mark) use ($schemes) {
            if ($mark === null) {
                return null;
            }

            $intMark = (int) round($mark);

            return $schemes->first(function (GradingScheme $s) use ($intMark) {
                return $intMark >= $s->min_mark && $intMark <= $s->max_mark;
            });
        };

        $divisionBucket = function (?string $division) {
            if (! $division) {
                return null;
            }

            $d = strtoupper(trim($division));
            if (in_array($d, ['I', 'II', 'III', 'IV', '0'], true)) {
                return $d;
            }

            if (str_contains($d, 'DIVISION I')) {
                return 'I';
            }
            if (str_contains($d, 'DIVISION II')) {
                return 'II';
            }
            if (str_contains($d, 'DIVISION III')) {
                return 'III';
            }
            if (str_contains($d, 'DIVISION IV')) {
                return 'IV';
            }
            if (str_contains($d, 'DIVISION 0') || $d === 'DIVISION 0') {
                return '0';
            }

            return null;
        };

        $results = ExamResult::query()
            ->where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->get(['marks']);

        $validMarks = $results->pluck('marks')->filter(fn ($m) => $m !== null);
        $count = $validMarks->count();

        $total = $count > 0 ? (int) $validMarks->sum() : null;
        $average = $count > 0 ? round(((float) $validMarks->sum()) / $count, 2) : null;

        $pointsArr = [];
        foreach ($validMarks as $m) {
            $scheme = $gradeForMark($m);
            if ($scheme && $scheme->points !== null) {
                $pointsArr[] = $scheme->points;
            }
        }
        $pointsTotal = ! empty($pointsArr) ? (int) array_sum($pointsArr) : null;

        $overallGrade = null;
        $divisionCode = null;
        if ($average !== null) {
            $avgScheme = $gradeForMark((int) round($average));
            $overallGrade = $avgScheme?->grade ? strtoupper((string) $avgScheme->grade) : null;

            if ($avgScheme && $avgScheme->division) {
                $divisionCode = $divisionBucket($avgScheme->division);
            } elseif ($overallGrade !== null) {
                $divisionCode = match ($overallGrade) {
                    'A' => 'I',
                    'B' => 'II',
                    'C' => 'III',
                    'D' => 'IV',
                    default => '0',
                };
            }
        }

        $summary = ExamStudentResult::firstOrNew([
            'exam_id' => $examId,
            'student_id' => $studentId,
            'school_id' => $schoolId,
        ]);

        $summary->academic_year = $academicYear;
        $summary->total = $total;
        $summary->average = $average;
        $summary->grade = $overallGrade;
        $summary->division = $divisionCode;
        $summary->points = $pointsTotal;
        $summary->save();
    }

    public function index(Request $request)
    {
        $examId = $request->query('exam');
        $classId = $request->query('class');
        $user = $request->user();

        $exams = Exam::orderByDesc('created_at')->get(['id', 'name', 'academic_year', 'type']);
        $gradingSchemes = GradingScheme::orderBy('min_mark')->get([
            'min_mark',
            'max_mark',
            'grade',
            'points',
        ]);

        $classes = collect();
        $students = collect();
        $subjects = collect();
        $existingMarks = collect();
        $streamSubjectsByStream = [];

        if ($examId) {
            $exam = Exam::with('classes:id,name')->find($examId);

            if ($exam) {
                // Classes attached to this exam (base classes)
                $classes = $exam->classes
                    ->map(function (SchoolClass $class) {
                        return [
                            'id' => $class->id,
                            'name' => $class->name,
                        ];
                    })
                    ->values();

                if ($classId) {
                    $selectedBase = $exam->classes->firstWhere('id', (int) $classId);

                    if ($selectedBase) {
                        $classIdsForExam = collect([$selectedBase->id]);

                        // Include streams (children) of this base class
                        $streamIds = SchoolClass::query()
                            ->where('parent_class_id', $selectedBase->id)
                            ->pluck('id');
                        $classIdsForExam = $classIdsForExam->merge($streamIds)->unique()->values();

                        // Stream -> subject_ids mapping (used to disable inputs for non-studied subjects)
                        $streamSubjectsByStream = SchoolClass::with('subjects:id')
                            ->where('parent_class_id', $selectedBase->id)
                            ->get()
                            ->filter(fn (SchoolClass $c) => $c->stream !== null && trim((string) $c->stream) !== '')
                            ->mapWithKeys(function (SchoolClass $c) {
                                return [
                                    (string) $c->stream => $c->subjects->pluck('id')->all(),
                                ];
                            })
                            ->all();

                        // Subjects: union of subjects across base class and its streams
                        $subjectIds = SchoolClass::with('subjects:id,subject_code')
                            ->whereIn('id', $classIdsForExam)
                            ->get()
                            ->flatMap(function (SchoolClass $class) {
                                return $class->subjects;
                            })
                            ->unique('id')
                            ->sortBy('subject_code')
                            ->values();

                        $subjects = $subjectIds->map(function (Subject $subject) {
                            return [
                                'id' => $subject->id,
                                'code' => $subject->subject_code,
                            ];
                        });

                        // Students in the selected base class (across streams)
                        $studentsQuery = Student::query()
                            ->where('class_level', $selectedBase->name)
                            ->orderBy('exam_number');

                        if ($user && $user->school_id) {
                            $studentsQuery->where('school_id', $user->school_id);
                        }

                        $students = $studentsQuery->get([
                            'id',
                            'exam_number',
                            'full_name',
                            'gender',
                            'stream',
                        ]);

                        if ($students->isNotEmpty() && $subjects->isNotEmpty()) {
                            $existingMarksQuery = ExamResult::query()
                                ->where('exam_id', $exam->id)
                                ->whereIn('student_id', $students->pluck('id'))
                                ->whereIn('subject_id', $subjects->pluck('id'));

                            if ($user && $user->school_id) {
                                $existingMarksQuery->where('school_id', $user->school_id);
                            }

                            $existingMarks = $existingMarksQuery->get([
                                'exam_id',
                                'student_id',
                                'subject_id',
                                'marks',
                            ]);
                        }
                    }
                }
            }
        }

        return Inertia::render('DataEntry', [
            'exams' => $exams,
            'classes' => $classes,
            'students' => $students,
            'subjects' => $subjects,
            'existingMarks' => $existingMarks,
            'streamSubjectsByStream' => $streamSubjectsByStream,
            'gradingSchemes' => $gradingSchemes,
            'filters' => [
                'exam' => $examId,
                'class' => $classId,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'exam_id' => ['required', 'integer', 'exists:exams,id'],
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'rows' => ['required', 'array'],
            'rows.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'rows.*.marks' => ['array'],
            'rows.*.marks.*.subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'rows.*.marks.*.marks' => ['nullable', 'integer', 'min:0', 'max:100'],
            'save_snapshot' => ['sometimes', 'boolean'],
        ]);

        $user = $request->user();
        $exam = Exam::find($data['exam_id']);
        $schoolId = $user?->school_id;
        $saveSnapshot = (bool) ($data['save_snapshot'] ?? false);

        // Preload grading schemes once for efficient grade/points lookup
        $schemes = GradingScheme::all();

        $gradeForMark = function ($mark) use ($schemes) {
            if ($mark === null) {
                return null;
            }

            $intMark = (int) round($mark);

            return $schemes->first(function (GradingScheme $s) use ($intMark) {
                return $intMark >= $s->min_mark && $intMark <= $s->max_mark;
            });
        };

        foreach ($data['rows'] as $row) {
            if (empty($row['marks']) || !is_array($row['marks'])) {
                continue;
            }
            foreach ($row['marks'] as $entry) {
                $marksValue = $entry['marks'];
                $scheme = $gradeForMark($marksValue);

                // Preserve original marks once in raw_marks, and treat subsequent saves as standardized/current
                // Include school_id in the lookup key so we match the composite unique index
                $result = ExamResult::firstOrNew([
                    'exam_id' => $data['exam_id'],
                    'student_id' => $row['student_id'],
                    'subject_id' => $entry['subject_id'],
                    'school_id' => $schoolId,
                ]);

                // If this is a brand new record, set raw_marks from the first entered value
                if (! $result->exists) {
                    $result->raw_marks = $marksValue;
                } elseif ($result->exists && $result->raw_marks === null) {
                    // If record exists but raw_marks was never set, backfill it from existing marks
                    $result->raw_marks = $result->marks;
                }

                // Current marks reflect the latest (possibly standardized) value
                $result->marks = $marksValue;
                $result->standardized_marks = $marksValue;
                $result->grade = $scheme?->grade;
                $result->points = $scheme?->points;
                $result->school_id = $user?->school_id;
                $result->academic_year = $exam?->academic_year;

                $result->save();
            }

            $this->upsertStudentSummary(
                (int) $data['exam_id'],
                (int) $row['student_id'],
                $schoolId ? (int) $schoolId : null,
                $exam?->academic_year,
                $schemes
            );

            // Persist a per-student snapshot file under a stable folder structure
            if ($saveSnapshot) {
                $student = Student::find($row['student_id']);

                if ($student) {
                    $school = $user && $user->school_id ? School::find($user->school_id) : null;
                    $schoolCode = $school && $school->school_code ? $school->school_code : 'SCH';

                    $folder = "schools/{$schoolCode}/students/{$student->exam_number}";

                    $snapshot = [
                        'student' => [
                            'id' => $student->id,
                            'exam_number' => $student->exam_number,
                            'full_name' => $student->full_name,
                        ],
                        'exam' => [
                            'id' => $exam?->id,
                            'name' => $exam?->name,
                            'academic_year' => $exam?->academic_year,
                        ],
                        'marks' => $row['marks'],
                    ];

                    Storage::put("{$folder}/marks_{$exam->id}.json", json_encode($snapshot, JSON_PRETTY_PRINT));
                }
            }
        }

        if ($request->wantsJson() || $request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
            ]);
        }

        return redirect()
            ->route('exams.marks', [
                'exam' => $data['exam_id'],
                'class' => $data['class_id'],
            ])
            ->with('success', 'Marks saved successfully.');
    }

    public function saveCell(Request $request)
    {
        $data = $request->validate([
            'exam_id' => ['required', 'integer', 'exists:exams,id'],
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'marks' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $user = $request->user();
        $schoolId = $user?->school_id;

        $exam = Exam::with('classes:id,name')->find($data['exam_id']);
        if (! $exam) {
            abort(404);
        }

        // Ensure the selected base class belongs to the exam assignment
        $selectedBase = $exam->classes->firstWhere('id', (int) $data['class_id']);
        if (! $selectedBase) {
            abort(403);
        }

        // Ensure student belongs to this base class (by class_level name)
        $student = Student::find($data['student_id']);
        if (! $student) {
            abort(404);
        }

        if ($schoolId && $student->school_id && (int) $student->school_id !== (int) $schoolId) {
            abort(403);
        }

        if ($student->class_level !== $selectedBase->name) {
            abort(403);
        }

        // Preload grading schemes once for efficient grade/points lookup
        $schemes = GradingScheme::all();
        $gradeForMark = function ($mark) use ($schemes) {
            if ($mark === null) {
                return null;
            }

            $intMark = (int) round($mark);

            return $schemes->first(function (GradingScheme $s) use ($intMark) {
                return $intMark >= $s->min_mark && $intMark <= $s->max_mark;
            });
        };

        $scheme = $gradeForMark($data['marks']);

        $result = ExamResult::firstOrNew([
            'exam_id' => $data['exam_id'],
            'student_id' => $data['student_id'],
            'subject_id' => $data['subject_id'],
            'school_id' => $schoolId,
        ]);

        if (! $result->exists) {
            $result->raw_marks = $data['marks'];
        } elseif ($result->exists && $result->raw_marks === null) {
            $result->raw_marks = $result->marks;
        }

        $result->marks = $data['marks'];
        $result->standardized_marks = $data['marks'];
        $result->grade = $scheme?->grade;
        $result->points = $scheme?->points;
        $result->school_id = $schoolId;
        $result->academic_year = $exam->academic_year;
        $result->save();

        $this->upsertStudentSummary(
            (int) $data['exam_id'],
            (int) $data['student_id'],
            $schoolId ? (int) $schoolId : null,
            $exam?->academic_year,
            $schemes
        );

        return response()->json([
            'ok' => true,
            'student_id' => $result->student_id,
            'subject_id' => $result->subject_id,
            'marks' => $result->marks,
            'updated_at' => $result->updated_at?->toISOString(),
        ]);
    }

    public function live(Request $request)
    {
        $data = $request->validate([
            'exam' => ['required', 'integer', 'exists:exams,id'],
            'class' => ['required', 'integer', 'exists:school_classes,id'],
            'since' => ['nullable', 'date'],
        ]);

        $user = $request->user();
        $schoolId = $user?->school_id;

        $exam = Exam::with('classes:id,name')->find($data['exam']);
        if (! $exam) {
            abort(404);
        }

        $selectedBase = $exam->classes->firstWhere('id', (int) $data['class']);
        if (! $selectedBase) {
            abort(403);
        }

        $studentsQuery = Student::query()
            ->where('class_level', $selectedBase->name)
            ->orderBy('exam_number');

        if ($schoolId) {
            $studentsQuery->where('school_id', $schoolId);
        }

        $studentIds = $studentsQuery->pluck('id');

        $classIdsForExam = collect([$selectedBase->id]);
        $streamIds = SchoolClass::query()
            ->where('parent_class_id', $selectedBase->id)
            ->pluck('id');
        $classIdsForExam = $classIdsForExam->merge($streamIds)->unique()->values();

        $subjectIds = SchoolClass::with('subjects:id')
            ->whereIn('id', $classIdsForExam)
            ->get()
            ->flatMap(fn (SchoolClass $c) => $c->subjects)
            ->unique('id')
            ->pluck('id')
            ->values();

        $since = null;
        if (! empty($data['since'])) {
            try {
                $since = Carbon::parse($data['since']);
            } catch (\Throwable $e) {
                $since = null;
            }
        }

        $query = ExamResult::query()
            ->where('exam_id', $exam->id)
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($studentIds->isNotEmpty(), fn ($q) => $q->whereIn('student_id', $studentIds))
            ->when($subjectIds->isNotEmpty(), fn ($q) => $q->whereIn('subject_id', $subjectIds));

        if ($since) {
            $query->where('updated_at', '>', $since);
        }

        $rows = $query
            ->orderBy('updated_at')
            ->limit(500)
            ->get([
                'student_id',
                'subject_id',
                'marks',
                'updated_at',
            ])
            ->map(function (ExamResult $r) {
                return [
                    'student_id' => $r->student_id,
                    'subject_id' => $r->subject_id,
                    'marks' => $r->marks,
                    'updated_at' => $r->updated_at?->toISOString(),
                ];
            })
            ->values();

        $maxUpdatedAt = $rows->isNotEmpty()
            ? $rows->max('updated_at')
            : now()->toISOString();

        return response()->json([
            'ok' => true,
            'rows' => $rows,
            'server_time' => now()->toISOString(),
            'max_updated_at' => $maxUpdatedAt,
        ]);
    }
}
