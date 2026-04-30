<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('user can upload file to temporary folder', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('materi-tambahan.pdf', 1000);

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

});

test('user can remove file from temporary folder', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('materi-tambahan.pdf', 1000);

    $response = $this->actingAs($user)
        ->postJson(route('file.upload'), [
            'file' => $file,
        ])->assertSuccessful();

    $file = $response->json('file')['id'];

    $this->actingAs($user)
        ->deleteJson(route('file.remove', $file))->assertSuccessful();

    $this->assertDatabaseMissing('temporary_files', [
        'id' => $file,
    ]);
});

test('user trying to upload file exceeding upload size limit should failed', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('materi-tambahan.pdf', 25000);

    $response = $this->actingAs($user)
        ->postJson(route('file.upload'), [
            'file' => $file,
        ]);

    $response->assertJsonValidationErrors(['file']);
});

test('guest trying to upload file should failed', function () {
    $file = UploadedFile::fake()->create('materi-tambahan.pdf', 1000);

    $response = $this->postJson(route('file.upload'), [
        'file' => $file,
    ]);

    $response->assertUnauthorized();
});