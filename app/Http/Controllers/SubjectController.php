<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubjectController extends Controller
{
    public function index()
    {
        $schoolId = request()->user()?->school_id;

        $query = Subject::query();

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        $subjects = $query
            ->orderBy('subject_code')
            ->get([
            'id',
            'subject_code',
            'name',
            'class_levels',
        ]);

        $classes = SchoolClass::orderBy('id')->get([
            'id',
            'name',
        ]);

        return Inertia::render('SubjectsIndex', [
            'subjects' => $subjects,
            'classes' => $classes,
        ]);
    }

    public function create()
    {
        $schoolId = request()->user()?->school_id;

        $query = Subject::query();

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        $subjects = $query
            ->orderBy('subject_code')
            ->get([
            'id',
            'subject_code',
            'name',
        ]);

        $classes = SchoolClass::orderBy('name')->get([
            'id',
            'name',
        ]);

        return Inertia::render('AddSubject', [
            'subjects' => $subjects,
            'classes' => $classes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_code' => ['required', 'string', 'max:10'],
            'name' => ['required', 'string', 'max:255'],
            'class_levels' => ['nullable', 'string', 'max:255'],
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'class_ids' => ['sometimes', 'array'],
            'class_ids.*' => ['integer', 'exists:school_classes,id'],
            'assign_to_current_user' => ['sometimes', 'boolean'],
        ]);

        $classIds = [];
        if (! empty($validated['class_ids']) && is_array($validated['class_ids'])) {
            $classIds = $validated['class_ids'];
        } elseif (! empty($validated['class_id'])) {
            $classIds = [$validated['class_id']];
        }

        $classLevels = $validated['class_levels'] ?? null;
        if (! empty($classIds)) {
            $classLevels = SchoolClass::whereIn('id', $classIds)
                ->orderBy('id')
                ->pluck('name')
                ->implode(', ');
        }

        $subject = Subject::create([
            'school_id' => $request->user()?->school_id,
            'subject_code' => $validated['subject_code'],
            'name' => $validated['name'],
            'class_levels' => $classLevels,
        ]);

        // Optionally assign this subject to selected classes immediately
        if (! empty($classIds)) {
            SchoolClass::whereIn('id', $classIds)->each(function (SchoolClass $class) use ($subject) {
                $class->subjects()->syncWithoutDetaching([$subject->id]);
            });
        }

        // Optionally assign this subject to the authenticated user (teacher)
        if (! empty($validated['assign_to_current_user']) && $request->user()) {
            $request->user()->subjects()->syncWithoutDetaching([$subject->id]);
        }

        return redirect()->route('subjects.index')->with('success', 'Subject created successfully.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()->route('subjects.index')->with('success', 'Subject deleted successfully.');
    }

    public function import(Request $request)
    {
        $data = $request->validate([
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'subject_ids' => ['required', 'array'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
            'assign_to_current_user' => ['sometimes', 'boolean'],
        ]);

        $class = SchoolClass::find($data['class_id']);

        if ($class) {
            $class->subjects()->syncWithoutDetaching($data['subject_ids']);
        }

        if (! empty($data['assign_to_current_user']) && $request->user()) {
            $request->user()->subjects()->syncWithoutDetaching($data['subject_ids']);
        }

        return redirect()->route('subjects.create')->with('success', 'Subjects imported successfully.');
    }
}
