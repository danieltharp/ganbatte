<?php

use Illuminate\Support\Facades\Route;
use App\Models\Lesson;

Route::get('/', function () {
    return view('welcome');
});

// Example route to demonstrate furigana functionality
Route::get('/lesson/{lesson}', function (Lesson $lesson) {
    $lesson->load(['vocabulary', 'grammarPoints', 'questions']);
    return view('example-lesson', compact('lesson'));
})->name('lesson.show');

// Route to show first lesson for quick testing
Route::get('/demo', function () {
    $lesson = Lesson::with(['vocabulary', 'grammarPoints', 'questions'])->first();
    
    if (!$lesson) {
        return 'No lessons found. Please run: php artisan db:seed';
    }
    
    return view('example-lesson', compact('lesson'));
})->name('demo');
