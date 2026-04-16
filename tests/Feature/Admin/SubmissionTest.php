<?php

use App\Models\User;
use App\Models\Submission;
use App\Models\AiFeedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

// === Admin/SubmissionController.index ===

test('admin can view submission list table', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Submission::factory()->count(5)->create();

    $this->actingAs($admin)
        ->get(route('admin.submission.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) =>
            $page->component('admin/submission/Index')
                 ->has('submissions.data', 5)
        );
});

test('non-admin cannot access submission list table', function () {
    $user = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($user)
        ->get(route('admin.submission.index'))
        ->assertForbidden();
});


// === Admin/SubmissionController.show ===

test('admin can view submission detail with ai feedback', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $submission = Submission::factory()->create();

    AiFeedback::factory()->count(2)->create([
        'submission_id' => $submission->id
    ]);

    $this->actingAs($admin)
        ->get(route('admin.submission.show', $submission))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) =>
            $page->component('admin/submission/Detail')
                 ->where('submission.id', $submission->id)
                 ->has('submission.ai_feedbacks', 2)
        );
});

test('non-admin cannot view submission detail', function () {
    $user = User::factory()->create(['role' => 'teacher']);

    $submission = Submission::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.submission.show', $submission))
        ->assertForbidden();
});

test('admin gets 404 when submission not found', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.submission.show', 999))
        ->assertNotFound();
});

test('submission detail works without ai feedback', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $submission = Submission::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.submission.show', $submission))
        ->assertInertia(fn (Assert $page) =>
            $page->has('submission.ai_feedbacks', 0)
        );
});

test('admin cannot view soft deleted submission', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $submission = Submission::factory()->create();
    $submission->delete();

    $this->actingAs($admin)
        ->get(route('admin.submission.show', $submission))
        ->assertNotFound();
});