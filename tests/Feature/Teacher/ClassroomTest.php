<?php

use App\Models\User;
use App\Models\Classroom;
use App\Models\Task;
use App\Models\Enrollment;
use Inertia\Testing\AssertableInertia as Assert;

// === INDEX ===

test('teacher can view classroom list card', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    Classroom::factory()
        ->count(3)
        ->create([
            'teacher_id' => $teacher->id,
        ]);

    $this->actingAs($teacher)
        ->get(route('teacher.classroom.index'))
        ->assertSuccessful()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('teacher/classroom/Index')
                ->has('classrooms.data', 3),
        );
});

test('teacher only sees their own classrooms', function () {
    $teacher1 = User::factory()->create(['role' => 'teacher']);
    $teacher2 = User::factory()->create(['role' => 'teacher']);

    Classroom::factory()->create([
        'name' => 'OWN_CLASS',
        'teacher_id' => $teacher1->id,
    ]);
    Classroom::factory()->create([
        'name' => 'OTHER_CLASS',
        'teacher_id' => $teacher2->id,
    ]);

    $this->actingAs($teacher1)
        ->get(route('teacher.classroom.index'))
        ->assertInertia(
            fn(Assert $page) => $page->where('classrooms.data', function (
                $classrooms,
            ) {
                $names = collect($classrooms)->pluck('name');

                return $names->contains('OWN_CLASS') &&
                    !$names->contains('OTHER_CLASS');
            }),
        );
});

// === STORE ===

test('teacher can create classroom with name and description', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)->post(
        route('teacher.classroom.store'),
        [
            'name' => 'New Class',
            'description' => 'Description here',
        ],
    );

    $response->assertRedirect(route('teacher.classroom.index'));

    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Classroom created successfully!',
    ]);

    $this->assertDatabaseHas('classrooms', [
        'name' => 'New Class',
        'teacher_id' => $teacher->id,
    ]);
});

test('teacher can create classroom with only name', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)->post(
        route('teacher.classroom.store'),
        [
            'name' => 'Only Name Class',
        ],
    );

    $response->assertRedirect(route('teacher.classroom.index'));

    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Classroom created successfully!',
    ]);

    $this->assertDatabaseHas('classrooms', [
        'name' => 'Only Name Class',
    ]);
});

test('teacher cannot create classroom without name', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($teacher)
        ->post(route('teacher.classroom.store'), [])
        ->assertSessionHasErrors('name');
});

test('teacher cannot create classroom with invalid name format', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($teacher)
        ->post(route('teacher.classroom.store'), [
            'name' => '   ',
        ])
        ->assertSessionHasErrors('name');
});

test('classroom code is generated automatically', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($teacher)->post(route('teacher.classroom.store'), [
        'name' => 'Auto Code Class',
    ]);

    $this->assertDatabaseHas('classrooms', [
        'name' => 'Auto Code Class',
    ]);

    expect(Classroom::first()->code)->not->toBeNull();
});

test('classroom code is unique globally', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    Classroom::factory()->create(['code' => 'ABC123']);

    $this->actingAs($teacher)->post(route('teacher.classroom.store'), [
        'name' => 'New Class',
    ]);

    $codes = Classroom::pluck('code');

    expect($codes->unique()->count())->toBe($codes->count());
});

test('classroom code regenerates if collision occurs', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    Classroom::factory()->create(['code' => 'FIXEDCODE']);

    // asumsi generator pertama menghasilkan FIXEDCODE lagi → harus regenerate
    $this->actingAs($teacher)->post(route('teacher.classroom.store'), [
        'name' => 'Collision Class',
    ]);

    $this->assertDatabaseCount('classrooms', 2);

    $codes = Classroom::pluck('code');

    expect($codes->unique()->count())->toBe(2);
});

test(
    'teacher cannot assign classroom to another teacher on create',
    function () {
        $teacher1 = User::factory()->create(['role' => 'teacher']);
        $teacher2 = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher1)->post(route('teacher.classroom.store'), [
            'name' => 'Hack Class',
            'teacher_id' => $teacher2->id,
        ]);

        $this->assertDatabaseHas('classrooms', [
            'name' => 'Hack Class',
            'teacher_id' => $teacher1->id,
        ]);
    },
);

test('non-teacher cannot create classroom', function () {
    $user = User::factory()->create(['role' => 'student']);

    $this->actingAs($user)
        ->post(route('teacher.classroom.store'), [])
        ->assertForbidden();
});

// === SHOW ===

test('teacher can view classroom details', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create([
        'teacher_id' => $teacher->id,
    ]);

    $this->actingAs($teacher)
        ->get(route('teacher.classroom.show', $classroom))
        ->assertSuccessful()
        ->assertInertia(
            fn(Assert $page) => $page->where('classroom.id', $classroom->id),
        );
});

test('teacher can view enrolled students', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create([
        'teacher_id' => $teacher->id,
    ]);

    $student = User::factory()->create(['role' => 'student']);
    Enrollment::factory()->create([
        'classroom_id' => $classroom->id,
        'user_id' => $student->id,
    ]);

    $this->actingAs($teacher)
        ->get(route('teacher.classroom.show', $classroom))
        ->assertSuccessful()
        ->assertInertia(
            fn(Assert $page) => $page->has(
                'classroom.students',
                1,
                fn(Assert $page) => $page->where('id', $student->id)->etc(),
            ),
        );
});

test('teacher cannot view other teacher classroom', function () {
    $teacher1 = User::factory()->create(['role' => 'teacher']);
    $teacher2 = User::factory()->create(['role' => 'teacher']);

    $classroom = Classroom::factory()->create([
        'teacher_id' => $teacher2->id,
    ]);

    $this->actingAs($teacher1)
        ->get(route('teacher.classroom.show', $classroom))
        ->assertForbidden();
});

// === UPDATE ===

test('teacher can update classroom name and description', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create([
        'teacher_id' => $teacher->id,
    ]);

    $response = $this->actingAs($teacher)->put(
        route('teacher.classroom.update', $classroom),
        [
            'name' => 'Updated Name',
            'description' => 'Updated Desc',
        ],
    );

    $response->assertRedirect(route('teacher.classroom.index'));

    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Classroom updated successfully!',
    ]);

    $this->assertDatabaseHas('classrooms', [
        'id' => $classroom->id,
        'name' => 'Updated Name',
    ]);
});

test('teacher cannot update classroom with empty name', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create([
        'teacher_id' => $teacher->id,
    ]);

    $this->actingAs($teacher)
        ->put(route('teacher.classroom.update', $classroom), [
            'name' => '',
        ])
        ->assertSessionHasErrors('name');
});

test('teacher cannot update classroom with invalid name format', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create([
        'teacher_id' => $teacher->id,
    ]);

    $this->actingAs($teacher)
        ->put(route('teacher.classroom.update', $classroom), [
            'name' => '   ',
        ])
        ->assertSessionHasErrors('name');
});

test('teacher cannot update other teacher classroom', function () {
    $teacher1 = User::factory()->create(['role' => 'teacher']);
    $teacher2 = User::factory()->create(['role' => 'teacher']);

    $classroom = Classroom::factory()->create([
        'teacher_id' => $teacher2->id,
    ]);

    $this->actingAs($teacher1)
        ->put(route('teacher.classroom.update', $classroom), [
            'name' => 'Hack',
        ])
        ->assertForbidden();
});

test('teacher cannot change classroom ownership', function () {
    $teacher1 = User::factory()->create(['role' => 'teacher']);
    $teacher2 = User::factory()->create(['role' => 'teacher']);

    $classroom = Classroom::factory()->create([
        'teacher_id' => $teacher1->id,
    ]);

    $this->actingAs($teacher1)->put(
        route('teacher.classroom.update', $classroom),
        [
            'name' => 'Updated',
            'teacher_id' => $teacher2->id,
        ],
    );

    $this->assertDatabaseHas('classrooms', [
        'id' => $classroom->id,
        'teacher_id' => $teacher1->id,
    ]);
});

test('non-teacher cannot update classroom', function () {
    $user = User::factory()->create(['role' => 'student']);
    $classroom = Classroom::factory()->create();

    $this->actingAs($user)
        ->put(route('teacher.classroom.update', $classroom), [])
        ->assertForbidden();
});

// === DESTROY ===

test('teacher can delete classroom', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create([
        'teacher_id' => $teacher->id,
    ]);

    $response = $this->actingAs($teacher)->delete(
        route('teacher.classroom.destroy', $classroom),
    );

    $response->assertRedirect(route('teacher.classroom.index'));

    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Classroom deleted successfully!',
    ]);

    $this->assertDatabaseMissing('classrooms', [
        'id' => $classroom->id,
    ]);
});

test('teacher cannot delete other teacher classroom', function () {
    $teacher1 = User::factory()->create(['role' => 'teacher']);
    $teacher2 = User::factory()->create(['role' => 'teacher']);

    $classroom = Classroom::factory()->create([
        'teacher_id' => $teacher2->id,
    ]);

    $this->actingAs($teacher1)
        ->delete(route('teacher.classroom.destroy', $classroom))
        ->assertForbidden();
});

test('teacher cannot delete classroom with active tasks', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $classroom = Classroom::factory()->create([
        'teacher_id' => $teacher->id,
    ]);

    Task::factory()->create([
        'classroom_id' => $classroom->id,
        'is_published' => true,
    ]);

    $this->actingAs($teacher)->delete(
        route('teacher.classroom.destroy', $classroom),
    );

    $this->assertDatabaseHas('classrooms', [
        'id' => $classroom->id,
    ]);
});

test('non-teacher cannot delete classroom', function () {
    $user = User::factory()->create(['role' => 'student']);
    $classroom = Classroom::factory()->create();

    $this->actingAs($user)
        ->delete(route('teacher.classroom.destroy', $classroom))
        ->assertForbidden();
});

// === ROUTE ACCESS ===

test('non-teacher cannot access teacher classroom routes', function () {
    $user = User::factory()->create(['role' => 'student']);

    $this->actingAs($user)
        ->get(route('teacher.classroom.index'))
        ->assertForbidden();
});

test('guest cannot access teacher classroom routes', function () {
    $this->get(route('teacher.classroom.index'))->assertRedirect('/login');
});
