<?php

declare(strict_types=1);

use App\Models\User;
use App\Enums\Locales;

describe('Locale Security', function () {
    beforeEach(function () {
        config()->set('app.locale', 'id');
        config()->set('locale.supported', Locales::toArray());
    });

    it('rejects unsupported locale values', function () {
        $response = $this->patch(route('locale.update'), [
            'locale' => 'jp',
        ]);

        $response->assertSessionHasErrors(['locale']);
    });

    it('rejects empty locale', function () {
        $response = $this->patch(route('locale.update'), [
            'locale' => '',
        ]);

        $response->assertSessionHasErrors(['locale']);
    });

    it('rejects null locale', function () {
        $response = $this->patch(route('locale.update'), [
            'locale' => null,
        ]);

        $response->assertSessionHasErrors(['locale']);
    });

    it('rejects array locale payload', function () {
        $response = $this->patch(route('locale.update'), [
            'locale' => ['en'],
        ]);

        $response->assertSessionHasErrors(['locale']);
    });

    it('rejects malformed locale payload', function () {
        $response = $this->patch(route('locale.update'), [
            'locale' => '<script>alert(1)</script>',
        ]);

        $response->assertSessionHasErrors(['locale']);
    });

    it('rejects uppercase unsupported locale', function () {
        $response = $this->patch(route('locale.update'), [
            'locale' => 'ENGLISH',
        ]);

        $response->assertSessionHasErrors(['locale']);
    });

    it('does not persist locale to database for guests', function () {
        $user = User::factory()->create([
            'locale' => 'id',
        ]);

        $this->patch(route('locale.update'), [
            'locale' => 'en',
        ]);

        expect($user->fresh()->locale)->toBe('id');
    });

    it('only updates authenticated users own locale', function () {
        $userA = User::factory()->create([
            'locale' => 'id',
        ]);

        $userB = User::factory()->create([
            'locale' => 'fr',
        ]);

        $this->actingAs($userA)->patch(route('locale.update'), [
            'locale' => 'en',
        ]);

        expect($userA->fresh()->locale)->toBe('en');

        expect($userB->fresh()->locale)->toBe('fr');
    });

    it(
        'does not overwrite authenticated locale using cookie locale',
        function () {
            $user = User::factory()->create([
                'locale' => 'id',
            ]);

            $this->actingAs($user)->withCookie('locale', 'fr')->get('/');

            expect($user->fresh()->locale)->toBe('id');
        },
    );

    it('ignores unsupported locale cookie', function () {
        $response = $this->withCookie('locale', 'jp')
            ->withHeader('Accept-Language', '')
            ->get('/');

        $response->assertOk();

        expect(app()->getLocale())->toBe('id');
    });

    it('ignores malformed locale cookie', function () {
        $response = $this->withCookie('locale', '<script>alert(1)</script>')
            ->withHeader('Accept-Language', '')
            ->get('/');

        $response->assertOk();

        expect(app()->getLocale())->toBe('id');
    });

    it('ignores empty locale cookie', function () {
        $response = $this->withCookie('locale', '')
            ->withHeader('Accept-Language', '')
            ->get('/');

        $response->assertOk();

        expect(app()->getLocale())->toBe('id');
    });

    it('ignores unsupported browser locale', function () {
        $response = $this->withHeader('Accept-Language', 'jp-JP,jp;q=0.9')->get(
            '/',
        );

        $response->assertOk();

        expect(app()->getLocale())->toBe('id');
    });

    it('ignores malformed browser locale header', function () {
        $response = $this->withHeader(
            'Accept-Language',
            '<script>alert(1)</script>',
        )->get('/');

        $response->assertOk();

        expect(app()->getLocale())->toBe('id');
    });

    it('handles locale cookie correctly', function () {
        $this->withCookie('locale', 'en')->get('/')->assertOk();

        expect(app()->getLocale())->toBe('en');
    });

    it('uses default locale when no cookie is provided', function () {
        $this->withHeader('Accept-Language', '')->get('/')->assertOk();

        expect(app()->getLocale())->toBe('id');
    });

    it('only allows locales defined in enums', function () {
        $response = $this->patch(route('locale.update'), [
            'locale' => 'jp',
        ]);

        $response->assertSessionHasErrors(['locale']);
    });

    it(
        'guest locale cannot override authenticated database locale',
        function () {
            $user = User::factory()->create([
                'locale' => 'fr',
            ]);

            $response = $this->actingAs($user)
                ->withCookie('locale', 'en')
                ->withHeader('Accept-Language', 'id-ID,id;q=0.9')
                ->get('/');

            $response->assertOk();

            expect(app()->getLocale())->toBe('fr');
        },
    );

    it('locale changes remain isolated per authenticated user', function () {
        $userA = User::factory()->create([
            'locale' => 'id',
        ]);

        $userB = User::factory()->create([
            'locale' => 'fr',
        ]);

        $this->actingAs($userA)->patch(route('locale.update'), [
            'locale' => 'en',
        ]);

        expect($userA->fresh()->locale)->toBe('en');

        expect($userB->fresh()->locale)->toBe('fr');
    });
});
