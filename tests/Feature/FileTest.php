<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('user can upload file to temporary folder', function () {
    $user = User::factory()->create();
    $fileOriginalName = "materi-tambahan.pdf";
    $file = UploadedFile::fake()->create($fileOriginalName, 1000);

    $response = $this->actingAs($user)
        ->postJson(route('file.upload'), [
            'file' => $file,
        ]);

    $response->assertSuccessful();

    $response->assertJsonStructure([
        'success',
        'file' => [
            'id',
            'filename',
            'original_name',
        ],
    ]);

    $uploadedFilename = $response->json('file.filename');
    Storage::disk('public')->assertExists("tmp/$uploadedFilename");

    $this->assertDatabaseHas('temporary_files', [
        'uploaded_by' => $user->id,
        'original_name' => $fileOriginalName,
    ]);
});

test('user can remove file from temporary folder', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('materi-tambahan.pdf', 1000);

    $response = $this->actingAs($user)
        ->postJson(route('file.upload'), [
            'file' => $file,
        ])->assertSuccessful();

    $uploadedFilename = $response->json('file.filename');
    Storage::disk('public')->assertExists("tmp/$uploadedFilename");

    $file = $response->json('file')['id'];

    $this->actingAs($user)
        ->deleteJson(route('file.remove', $file))->assertSuccessful();

    $this->assertDatabaseMissing('temporary_files', [
        'id' => $file,
    ]);

    Storage::disk('public')->assertMissing("tmp/$uploadedFilename");
});

test('user trying to upload file exceeding upload size limit should failed', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('materi-tambahan.pdf', 25000);

    $response = $this->actingAs($user)
        ->postJson(route('file.upload'), [
            'file' => $file,
        ]);

    $response->assertJsonValidationErrors(['file']);

    $uploadedFilename = $response->json('file.filename');
    Storage::disk('public')->assertMissing("tmp/$uploadedFilename");
});

test('user cannot remove another user file from temporary folder', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $file = UploadedFile::fake()->create('materi-tambahan.pdf', 1000);

    $response = $this->actingAs($user1)
        ->postJson(route('file.upload'), [
            'file' => $file,
        ])->assertSuccessful();

    $uploadedFilename = $response->json('file.filename');
    Storage::disk('public')->assertExists("tmp/$uploadedFilename");

    $file = $response->json('file')['id'];

    $this->actingAs($user2)
        ->deleteJson(route('file.remove', $file))->assertForbidden();

    $this->assertDatabaseHas('temporary_files', [
        'id' => $file,
    ]);

    Storage::disk('public')->assertExists("tmp/$uploadedFilename");
});

test('guest trying to upload file should failed', function () {
    $file = UploadedFile::fake()->create('materi-tambahan.pdf', 1000);

    $response = $this->postJson(route('file.upload'), [
        'file' => $file,
    ]);

    $response->assertUnauthorized();

    $uploadedFilename = $response->json('file.filename');
    Storage::disk('public')->assertMissing("tmp/$uploadedFilename");
});