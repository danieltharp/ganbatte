<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\VocabularyController;
use App\Http\Controllers\GrammarController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\WorksheetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Resource routes for our main models
Route::resource('lessons', LessonController::class);
Route::resource('vocabulary', VocabularyController::class);
Route::resource('grammar', GrammarController::class);
Route::resource('questions', QuestionController::class);
Route::resource('tests', TestController::class);
Route::resource('worksheets', WorksheetController::class);

// Custom routes
Route::get('/vocabulary/kanji-worksheet', [VocabularyController::class, 'kanjiWorksheet'])->name('vocabulary.kanji-worksheet');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
