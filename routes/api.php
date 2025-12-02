<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Visitor;
use App\Models\ContactFormSubmission;

Route::get('/visitors-count', function () {
    $count = Visitor::whereDate('created_at', today())->count();
    return response()->json(['count' => $count]);
});

Route::post('/contact-form-submission', function (Request $request) {
    $validatedData = $request->validate([
        'name' => 'required|string',
        'email' => 'required|email',
        'message' => 'required|string',
    ]);

    $submission = new ContactFormSubmission();
    $submission->name = $validatedData['name'];
    $submission->email = $validatedData['email'];
    $submission->message = $validatedData['message'];
    $submission->save();

    return response()->json(['message' => 'Submission received successfully']);
});
