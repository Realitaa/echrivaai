<?php

declare(strict_types=1);

use App\Models\User;
use App\Enums\Locales;

describe('Locale Switch', function () {

    beforeEach(function () {

        config()->set('app.locale', 'id');

        config()->set('locale.supported', Locales::toArray());
    });

    it('switches locale correctly for guest users', function () {
        $response = $this->patch(route('locale.update'), [
            'locale' => 'en',
        ]);

        $response
            ->assertRedirect()
            ->assertCookie('locale', 'en');

        $this
            ->withCookie('locale', 'en')
            ->get('/')
            ->assertOk();

        expect(app()->getLocale())
            ->toBe('en');
    });

    it('overrides guest cookie locale using authenticated user locale', function () {
        $user = User::factory()->create([
            'locale' => 'fr',
        ]);

        $response = $this
            ->withCookie('locale', 'en')
            ->actingAs($user)
            ->get('/');

        $response->assertOk();

        expect(app()->getLocale())
            ->toBe('fr');
    });

    it('does not overwrite database locale when guest locale exists', function () {
        $user = User::factory()->create([
            'locale' => 'id',
        ]);

        $this
            ->withCookie('locale', 'fr')
            ->actingAs($user)
            ->get('/');

        expect($user->fresh()->locale)
            ->toBe('id');
    });

    it('updates database and cookie when authenticated user switches locale', function () {
        $user = User::factory()->create([
            'locale' => 'id',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('locale.update'), [
                'locale' => 'en',
            ]);

        $response
            ->assertRedirect()
            ->assertCookie('locale', 'en');

        expect($user->fresh()->locale)
            ->toBe('en');

        expect(app()->getLocale())
            ->toBe('en');
    });

    it('keeps locale after logout using cookie persistence', function () {
        $user = User::factory()->create([
            'locale' => 'fr',
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

    it('uses locale from second authenticated account', function () {
        $userA = User::factory()->create([
            'locale' => 'en',
        ]);

        $userB = User::factory()->create([
            'locale' => 'id',
        ]);

        $this
            ->actingAs($userA)
            ->get('/')
            ->assertOk();

        expect(app()->getLocale())
            ->toBe('en');

        auth()->logout();

        $this
            ->actingAs($userB)
            ->get('/')
            ->assertOk();

        expect(app()->getLocale())
            ->toBe('id');
    });

    it('second authenticated account overrides previous cookie locale', function () {
        $userA = User::factory()->create([
            'locale' => 'fr',
        ]);

        $userB = User::factory()->create([
            'locale' => 'id',
        ]);

        $responseA = $this
            ->actingAs($userA)
            ->patch(route('locale.update'), [
                'locale' => 'fr',
            ]);

        $responseA->assertCookie('locale', 'fr');

        auth()->logout();

        $responseB = $this
            ->withCookie('locale', 'fr')
            ->actingAs($userB)
            ->get('/');

        $responseB->assertOk();

        expect(app()->getLocale())
            ->toBe('id');
    });

    it('uses browser locale before authentication', function () {
        $response = $this
            ->withHeader('Accept-Language', 'fr-FR,fr;q=0.9')
            ->get('/');

        $response->assertOk();

        expect(app()->getLocale())
            ->toBe('fr');
    });

    it('authenticated locale overrides browser locale after login', function () {
        $user = User::factory()->create([
            'locale' => 'en',
        ]);

        $response = $this
            ->withHeader('Accept-Language', 'fr-FR,fr;q=0.9')
            ->actingAs($user)
            ->get('/');

        $response->assertOk();

        expect(app()->getLocale())
            ->toBe('en');
    });

    it('changes runtime locale immediately after switching locale', function () {
        $user = User::factory()->create([
            'locale' => 'id',
        ]);

        $this
            ->actingAs($user)
            ->patch(route('locale.update'), [
                'locale' => 'fr',
            ]);

        expect(app()->getLocale())
            ->toBe('fr');
    });

    it('persists switched locale across multiple authenticated requests', function () {
        $user = User::factory()->create([
            'locale' => 'en',
        ]);

        $this
            ->actingAs($user)
            ->get('/')
            ->assertOk();

        expect(app()->getLocale())
            ->toBe('en');

        $this
            ->actingAs($user)
            ->get('/')
            ->assertOk();

        expect(app()->getLocale())
            ->toBe('en');
    });

    it('persists switched locale across multiple guest requests', function () {
        $this
            ->withCookie('locale', 'fr')
            ->get('/')
            ->assertOk();

        expect(app()->getLocale())
            ->toBe('fr');

        $this
            ->withCookie('locale', 'fr')
            ->get('/')
            ->assertOk();

        expect(app()->getLocale())
            ->toBe('fr');
    });
});