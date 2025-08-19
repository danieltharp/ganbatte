<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\VocabularyController;
use App\Http\Controllers\VocabularyQuizController;
use App\Http\Controllers\GrammarController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\WorksheetController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ProgressController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Read-only routes for content display
Route::get('/lessons', [LessonController::class, 'index'])->name('lessons.index');
Route::get('/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');

Route::get('/vocabulary', [VocabularyController::class, 'index'])->name('vocabulary.index');
Route::get('/vocabulary/{vocabulary}', [VocabularyController::class, 'show'])->name('vocabulary.show');

// Vocabulary Quiz Routes (no authentication required)
Route::get('/vocabulary-quiz', [VocabularyQuizController::class, 'index'])->name('vocabulary.quiz.index');
Route::post('/vocabulary-quiz/start', [VocabularyQuizController::class, 'start'])->name('vocabulary.quiz.start');
Route::get('/vocabulary-quiz/take', [VocabularyQuizController::class, 'take'])->name('vocabulary.quiz.take');
Route::post('/vocabulary-quiz/submit', [VocabularyQuizController::class, 'submit'])->name('vocabulary.quiz.submit');

Route::get('/grammar', [GrammarController::class, 'index'])->name('grammar.index');
Route::get('/grammar/{grammar}', [GrammarController::class, 'show'])->name('grammar.show');

Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('articles.show');

Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');
Route::get('/questions/{question}', [QuestionController::class, 'show'])->name('questions.show');

Route::get('/tests', [TestController::class, 'index'])->name('tests.index');
Route::get('/tests/{test}', [TestController::class, 'show'])->name('tests.show');

Route::get('/worksheets', [WorksheetController::class, 'index'])->name('worksheets.index');
Route::get('/worksheets/create', [WorksheetController::class, 'create'])->name('worksheets.create');
Route::post('/worksheets', [WorksheetController::class, 'store'])->name('worksheets.store');
Route::get('/worksheets/{worksheet}', [WorksheetController::class, 'show'])->name('worksheets.show');
Route::get('/worksheets/{worksheet}/edit', [WorksheetController::class, 'edit'])->name('worksheets.edit');
Route::put('/worksheets/{worksheet}', [WorksheetController::class, 'update'])->name('worksheets.update');
Route::delete('/worksheets/{worksheet}', [WorksheetController::class, 'destroy'])->name('worksheets.destroy');
Route::get('/worksheets/{worksheet}/generate', [WorksheetController::class, 'generate'])->name('worksheets.generate');
Route::post('/worksheets/{worksheet}/kanji-pdf', [WorksheetController::class, 'generateKanjiPdf'])->name('worksheets.kanji-pdf');

Route::get('/sections', [SectionController::class, 'index'])->name('sections.index');
Route::get('/sections/{section}', [SectionController::class, 'show'])->name('sections.show');

Route::get('/exercises', [ExerciseController::class, 'index'])->name('exercises.index');
Route::get('/exercises/{exercise}', [ExerciseController::class, 'show'])->name('exercises.show');

// Custom routes
Route::get('/vocabulary/kanji-worksheet', [VocabularyController::class, 'kanjiWorksheet'])->name('vocabulary.kanji-worksheet');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Progress tracking endpoints
    Route::post('/sections/{section}/complete', [SectionController::class, 'markComplete'])->name('sections.complete');
    Route::delete('/sections/{section}/complete', [SectionController::class, 'resetCompletion'])->name('sections.reset');
    
    // Exercise submission endpoints
    Route::post('/exercises/{exercise}/submit', [ExerciseController::class, 'submit'])->name('exercises.submit');
    Route::get('/exercises/{exercise}/stats', [ExerciseController::class, 'getStats'])->name('exercises.stats');
    
    // Exercise manual correction endpoints
    Route::get('/exercise-attempts/{attempt}/results', [ExerciseController::class, 'showResults'])->name('exercises.results');
    Route::post('/exercise-attempts/{attempt}/accept-answer', [ExerciseController::class, 'acceptAnswer'])->name('exercises.accept-answer');
    Route::get('/exercise-attempts/{attempt}/results-json', [ExerciseController::class, 'getResults'])->name('exercises.results-json');
    
    // Test taking endpoints
    Route::post('/tests/{test}/start', [TestController::class, 'start'])->name('tests.start');
    Route::post('/test-attempts/{attempt}/submit', [TestController::class, 'submit'])->name('tests.submit');
    Route::get('/test-attempts/{attempt}/results', [TestController::class, 'results'])->name('tests.results');
    
    // Progress and grading endpoints
    Route::get('/progress/dashboard', [ProgressController::class, 'dashboard'])->name('progress.dashboard');
    Route::get('/progress/lesson/{lesson}', [ProgressController::class, 'getLessonGrade'])->name('progress.lesson');
    Route::get('/progress/semester/{semesterNumber}', [ProgressController::class, 'getSemesterGrade'])->name('progress.semester');
    Route::get('/progress/all', [ProgressController::class, 'getAllGrades'])->name('progress.all');
});

require __DIR__.'/auth.php';
