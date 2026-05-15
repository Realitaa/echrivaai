<?php

declare(strict_types=1);

use App\Models\User;
use App\Enums\Locales;

describe('Guest Locale', function () {
    beforeEach(function () {
        config()->set('app.locale', 'id');
        config()->set('locale.supported', Locales::toArray());
    });

    it('uses app default locale for first visit', function () {
        $response = $this->withHeader('Accept-Language', '')->get('/');
        $response->assertOk();

        expect(app()->getLocale())->toBe('id');
    });

    it('uses browser locale when no cookie exists', function () {
        $response = $this->withHeader('Accept-Language', 'en-US,en;q=0.9')->get(
            '/',
        );
        $response->assertOk();

        expect(app()->getLocale())->toBe('en');
    });

    it('falls back to app locale for unsupported browser locale', function () {
        $response = $this->withHeader('Accept-Language', 'jp-JP,jp;q=0.9')->get(
            '/',
        );
        $response->assertOk();

        expect(app()->getLocale())->toBe('id');
    });

    it('allows guest to change locale', function () {
        $response = $this->patch(route('locale.update'), [
            'locale' => 'en',
        ]);
        $response->assertRedirect();
        $response->assertCookie('locale', 'en');
    });

    it('persists guest locale using cookie', function () {
        $response = $this->withCookie('locale', 'fr')->get('/');
        $response->assertOk();

        expect(app()->getLocale())->toBe('fr');
    });

    it('prioritizes cookie locale over browser locale', function () {
        $response = $this->withCookie('locale', 'fr')
            ->withHeader('Accept-Language', 'en-US,en;q=0.9')
            ->get('/');
        $response->assertOk();

        expect(app()->getLocale())->toBe('fr');
    });

    it('rejects unsupported locale', function () {
        $response = $this->patch(route('locale.update'), [
            'locale' => 'jp',
        ]);
        $response->assertSessionHasErrors(['locale']);
    });

    it('rejects invalid locale format', function () {
        $response = $this->patch(route('locale.update'), [
            'locale' => '<script>alert(1)</script>',
        ]);
        $response->assertSessionHasErrors(['locale']);
    });

    it('does not save locale to database for guest', function () {
        $user = User::factory()->create([
            'locale' => 'fr',
        ]);

        $this->patch(route('locale.update'), [
            'locale' => 'en',
        ]);

        expect($user->fresh()->locale)->toBe('fr');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'locale' => 'fr',
        ]);
    });

    it('keeps locale across multiple guest requests', function () {
        $this->withCookie('locale', 'en')->get('/')->assertOk();

        expect(app()->getLocale())->toBe('en');

        $this->withCookie('locale', 'en')->get('/')->assertOk();

        expect(app()->getLocale())->toBe('en');
    });

    it('authenticated user locale overrides cookie locale', function () {
        $user = User::factory()->create([
            'locale' => 'id',
        ]);

        $response = $this->actingAs($user)
            ->withCookie('locale', 'en')
            ->get('/');

        $response->assertOk();

        expect(app()->getLocale())->toBe('id');
    });
});
