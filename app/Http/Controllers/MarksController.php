<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\GradingScheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MarksController extends Controller
{
    public function index(Request $request)
    {
        $examId = $request->query('exam');
        $classLevel = $request->query('class');
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

        if ($examId) {
            $exam = Exam::with('classes')->find($examId);

            if ($exam) {
                // Distinct class levels (e.g. Form I, Form II) attached to this exam
                $classes = $exam->classes
                    ->pluck('level')
                    ->filter()
                    ->unique()
                    ->values()
                    ->map(function ($level) {
                        return [
                            'id' => $level,
                            'name' => $level,
                            'level' => $level,
                        ];
                    });

                if ($classLevel) {
                    // All classes (streams) for this level that sit for this exam
                    $classesForLevel = $exam->classes
                        ->where('level', $classLevel)
                        ->values();

                    if ($classesForLevel->isNotEmpty()) {
                        // Subjects: union of all subjects across streams for this level, sorted by code
                        $subjectIds = SchoolClass::with('subjects:id,subject_code')
                            ->whereIn('id', $classesForLevel->pluck('id'))
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

                        // Students: all students in this level across all streams,
                        // limited to the current school. We avoid over-filtering by academic_year
                        // here because some schools may not have set it consistently on students.
                        $studentsQuery = Student::query()
                            ->where('class_level', $classLevel)
                            ->orderBy('exam_number');

                        if ($user && $user->school_id) {
                            $studentsQuery->where('school_id', $user->school_id);
                        }

                        $students = $studentsQuery->get([
                            'id',
                            'exam_number',
                            'full_name',
                            'gender',
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
            'gradingSchemes' => $gradingSchemes,
            'filters' => [
                'exam' => $examId,
                'class' => $classLevel,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'exam_id' => ['required', 'integer', 'exists:exams,id'],
            'class_id' => ['required', 'string', 'max:100'],
            'rows' => ['required', 'array'],
            'rows.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'rows.*.marks' => ['required', 'array'],
            'rows.*.marks.*.subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'rows.*.marks.*.marks' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $user = $request->user();
        $exam = Exam::find($data['exam_id']);
        $schoolId = $user?->school_id;

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

            // Persist a per-student snapshot file under a stable folder structure
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

        return redirect()
            ->route('exams.marks', [
                'exam' => $data['exam_id'],
                'class' => $data['class_id'],
            ])
            ->with('success', 'Marks saved successfully.');
    }
}
