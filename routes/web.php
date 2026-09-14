<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AcademyController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnglishBasicsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MockTestController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SpeakingController;
use App\Livewire\Admin\CollectionEditor;
use App\Livewire\Admin\CollectionIndex;
use App\Livewire\Admin\MediaLibrary;
use App\Livewire\Admin\Overview;
use App\Livewire\Admin\SiteEditor;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/academy', [AcademyController::class, 'index'])->name('academy');
Route::get('/academy/plan', [AcademyController::class, 'plan'])->name('academy.plan');

Route::get('/english-basics', [EnglishBasicsController::class, 'index'])->name('english-basics.index');
Route::get('/english-basics/{topic}', [EnglishBasicsController::class, 'show'])->name('english-basics.show');
Route::post('/english-basics/{topic}/complete', [EnglishBasicsController::class, 'complete'])
    ->middleware('student')
    ->name('english-basics.complete');

Route::get('/dashboard', DashboardController::class)->name('dashboard');

Route::get('/mock-tests', [MockTestController::class, 'index'])->name('mock-tests.index');
Route::get('/mock-tests/{mock:slug}', [MockTestController::class, 'show'])->name('mock-tests.show');

Route::get('/practice/{skill}/{task}', [PracticeController::class, 'task'])->name('practice.task');
Route::get('/practice/{skill}/{task}/{question}', [PracticeController::class, 'question'])
    ->middleware('student')
    ->name('practice.question');
Route::post('/practice/{skill}/{task}/{question}', [PracticeController::class, 'submit'])
    ->middleware('student')
    ->name('practice.submit');

Route::get('/speaking/one-to-one', [SpeakingController::class, 'show'])
    ->middleware('student')
    ->name('speaking.show');
Route::post('/speaking/one-to-one', [SpeakingController::class, 'store'])
    ->middleware('student')
    ->name('speaking.store');

Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
Route::get('/certificates/{certificate:slug}', [CertificateController::class, 'show'])->name('certificates.show');

Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'store'])->name('admin.login.store');
Route::post('/admin/logout', [AdminAuthController::class, 'destroy'])->name('admin.logout');

Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Overview::class)->name('overview');
    Route::get('/site', SiteEditor::class)->name('site');
    Route::get('/media', MediaLibrary::class)->name('media');
    Route::get('/{collection}/new', CollectionEditor::class)->name('collection.create');
    Route::get('/{collection}/{id}', CollectionEditor::class)->name('collection.edit');
    Route::get('/{collection}', CollectionIndex::class)->name('collection');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
