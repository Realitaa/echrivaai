<?php

declare(strict_types=1);

use App\Models\User;
use App\Enums\Locales;

describe('Authenticated Locale', function () {

    beforeEach(function () {
        config()->set('app.locale', 'id');
        config()->set('locale.supported', Locales::toArray());
    });

    it('uses authenticated user locale', function () {
        $user = User::factory()->create([
            'locale' => 'en',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/');

        $response->assertOk();

        expect(app()->getLocale())
            ->toBe('en');
    });

    it('prioritizes authenticated locale over cookie locale', function () {
        $user = User::factory()->create([
            'locale' => 'fr',
        ]);

        $response = $this
            ->actingAs($user)
            ->withCookie('locale', 'en')
            ->get('/');

        $response->assertOk();

        expect(app()->getLocale())
            ->toBe('fr');
    });

    it('prioritizes authenticated locale over browser locale', function () {
        $user = User::factory()->create([
            'locale' => 'id',
        ]);

        $response = $this
            ->actingAs($user)
            ->withHeader('Accept-Language', 'en-US,en;q=0.9')
            ->get('/');

        $response->assertOk();

        expect(app()->getLocale())
            ->toBe('id');
    });

    it('allows authenticated user to update locale', function () {
        $user = User::factory()->create([
            'locale' => 'id',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('locale.update'), [
                'locale' => 'en',
            ]);

        $response->assertRedirect();

        expect($user->fresh()->locale)
            ->toBe('en');
    });

    it('updates locale cookie when authenticated user changes locale', function () {
        $user = User::factory()->create([
            'locale' => 'id',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('locale.update'), [
                'locale' => 'fr',
            ]);

        $response
            ->assertRedirect()
            ->assertCookie('locale', 'fr');
    });

    it('updates runtime locale immediately after changing locale', function () {
        $user = User::factory()->create([
            'locale' => 'id',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('locale.update'), [
                'locale' => 'en',
            ]);

        $response->assertRedirect();

        expect(app()->getLocale())
            ->toBe('en');
    });

    it('persists locale across authenticated requests', function () {
        $user = User::factory()->create([
            'locale' => 'fr',
        ]);

        $this
            ->actingAs($user)
            ->get('/')
            ->assertOk();

        expect(app()->getLocale())
            ->toBe('fr');

        $this
            ->actingAs($user)
            ->get('/')
            ->assertOk();

        expect(app()->getLocale())
            ->toBe('fr');
    });

    it('rejects unsupported locale', function () {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('locale.update'), [
                'locale' => 'jp',
            ]);

        $response->assertSessionHasErrors([
            'locale',
        ]);
    });

    it('rejects invalid locale format', function () {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('locale.update'), [
                'locale' => '<script>alert(1)</script>',
            ]);

        $response->assertSessionHasErrors([
            'locale',
        ]);
    });

    it('does not update other users locale', function () {
        $userA = User::factory()->create([
            'locale' => 'id',
        ]);

        $userB = User::factory()->create([
            'locale' => 'fr',
        ]);

        $this
            ->actingAs($userA)
            ->patch(route('locale.update'), [
                'locale' => 'en',
            ]);

        expect($userA->fresh()->locale)
            ->toBe('en');

        expect($userB->fresh()->locale)
            ->toBe('fr');
    });

    it('keeps locale in cookie after logout', function () {
        $user = User::factory()->create([
            'locale' => 'en',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('locale.update'), [
                'locale' => 'fr',
            ]);

        $response
            ->assertRedirect()
            ->assertCookie('locale', 'fr');

        auth()->logout();

        $guestResponse = $this
            ->withCookie('locale', 'fr')
            ->get('/');

        $guestResponse->assertOk();

        expect(app()->getLocale())
            ->toBe('fr');
    });

    it('uses locale from newly authenticated account', function () {
        $userA = User::factory()->create([
            'locale' => 'en',
        ]);

        $userB = User::factory()->create([
            'locale' => 'id',
        ]);

        $this
            ->actingAs($userA)
            ->withCookie('locale', 'fr')
            ->get('/');

        expect(app()->getLocale())
            ->toBe('en');

        auth()->logout();

        $this
            ->actingAs($userB)
            ->withCookie('locale', 'fr')
            ->get('/');

        expect(app()->getLocale())
            ->toBe('id');
    });

    it('does not overwrite database locale using cookie locale', function () {
        $user = User::factory()->create([
            'locale' => 'id',
        ]);

        $this
            ->actingAs($user)
            ->withCookie('locale', 'fr')
            ->get('/');

        expect($user->fresh()->locale)
            ->toBe('id');
    });
});