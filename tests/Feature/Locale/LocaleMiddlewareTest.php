<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

describe('Locale Middleware', function () {

    beforeEach(function () {

        config()->set('app.locale', 'id');

        config()->set('locale.supported', [
            'id',
            'en',
            'fr',
        ]);

        Route::middleware(['web'])
            ->group(function () {

                Route::get('/locale-test', function () {

                    return response()->json([
                        'locale' => app()->getLocale(),
                    ]);
                });

                Route::get('/locale-test/inertia', function () {

                    return Inertia::render('Dashboard', [
                        'locale' => app()->getLocale(),
                    ]);
                });

                Route::get('/locale-test/translation', function () {

                    return response()->json([
                        'message' => __('validation.required', [
                            'attribute' => 'email',
                        ]),
                    ]);
                });
            });
    });

    it('uses application default locale when no locale source exists', function () {

        $response = $this
            ->withHeader('Accept-Language', '')
            ->get('/locale-test');

        $response
            ->assertOk()
            ->assertJson([
                'locale' => 'id',
            ]);

        expect(app()->getLocale())
            ->toBe('id');
    });

    it('uses browser locale when available', function () {

        $response = $this
            ->withHeader('Accept-Language', 'en-US,en;q=0.9')
            ->get('/locale-test');

        $response
            ->assertOk()
            ->assertJson([
                'locale' => 'en',
            ]);

        expect(app()->getLocale())
            ->toBe('en');
    });

    it('falls back to default locale for unsupported browser locale', function () {

        $response = $this
            ->withHeader('Accept-Language', 'jp-JP,jp;q=0.9')
            ->get('/locale-test');

        $response
            ->assertOk()
            ->assertJson([
                'locale' => 'id',
            ]);

        expect(app()->getLocale())
            ->toBe('id');
    });

    it('uses locale from cookie', function () {

        $response = $this
            ->withCookie('locale', 'fr')
            ->get('/locale-test');

        $response
            ->assertOk()
            ->assertJson([
                'locale' => 'fr',
            ]);

        expect(app()->getLocale())
            ->toBe('fr');
    });

    it('prioritizes cookie locale over browser locale', function () {

        $response = $this
            ->withCookie('locale', 'fr')
            ->withHeader('Accept-Language', 'en-US,en;q=0.9')
            ->get('/locale-test');

        $response
            ->assertOk()
            ->assertJson([
                'locale' => 'fr',
            ]);

        expect(app()->getLocale())
            ->toBe('fr');
    });

    it('ignores unsupported cookie locale', function () {

        $response = $this
            ->withCookie('locale', 'jp')
            ->get('/locale-test');

        $response
            ->assertOk()
            ->assertJson([
                'locale' => 'id',
            ]);

        expect(app()->getLocale())
            ->toBe('id');
    });

    it('uses authenticated user locale', function () {

        $user = User::factory()->create([
            'locale' => 'en',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/locale-test');

        $response
            ->assertOk()
            ->assertJson([
                'locale' => 'en',
            ]);

        expect(app()->getLocale())
            ->toBe('en');
    });

    it('prioritizes authenticated locale over cookie locale', function () {

        $user = User::factory()->create([
            'locale' => 'id',
        ]);

        $response = $this
            ->actingAs($user)
            ->withCookie('locale', 'fr')
            ->get('/locale-test');

        $response
            ->assertOk()
            ->assertJson([
                'locale' => 'id',
            ]);

        expect(app()->getLocale())
            ->toBe('id');
    });

    it('prioritizes authenticated locale over browser locale', function () {

        $user = User::factory()->create([
            'locale' => 'fr',
        ]);

        $response = $this
            ->actingAs($user)
            ->withHeader('Accept-Language', 'en-US,en;q=0.9')
            ->get('/locale-test');

        $response
            ->assertOk()
            ->assertJson([
                'locale' => 'fr',
            ]);

        expect(app()->getLocale())
            ->toBe('fr');
    });

    it('uses correct locale priority order', function () {
        $user = User::factory()->create([
            'locale' => 'id',
        ]);

        $response = $this
            ->actingAs($user)
            ->withCookie('locale', 'fr')
            ->withHeader('Accept-Language', 'en-US,en;q=0.9')
            ->get('/locale-test');

        $response
            ->assertOk()
            ->assertJson([
                'locale' => 'id',
            ]);

        expect(app()->getLocale())
            ->toBe('id');
    });

    it('shares locale correctly through inertia props', function () {

        $response = $this
            ->withCookie('locale', 'fr')
            ->get('/locale-test/inertia');

        $response->assertOk();

        $page = $response->viewData('page');

        expect($page['props']['locale'])
            ->toBe('fr');
    });

    it('uses correct translation locale during request lifecycle', function () {

        $response = $this
            ->withCookie('locale', 'en')
            ->get('/locale-test/translation');

        $response->assertOk();

        expect(app()->getLocale())
            ->toBe('en');
    });

    it('maintains locale across sequential requests', function () {

        $this
            ->withCookie('locale', 'fr')
            ->get('/locale-test')
            ->assertJson([
                'locale' => 'fr',
            ]);

        expect(app()->getLocale())
            ->toBe('fr');

        $this
            ->withCookie('locale', 'fr')
            ->get('/locale-test')
            ->assertJson([
                'locale' => 'fr',
            ]);

        expect(app()->getLocale())
            ->toBe('fr');
    });

    it('handles empty accept language header gracefully', function () {

        $response = $this
            ->withHeader('Accept-Language', '')
            ->get('/locale-test');

        $response
            ->assertOk()
            ->assertJson([
                'locale' => 'id',
            ]);

        expect(app()->getLocale())
            ->toBe('id');
    });

    it('handles malformed locale cookie gracefully', function () {

        $response = $this
            ->withCookie('locale', '<script>alert(1)</script>')
            ->get('/locale-test');

        $response
            ->assertOk()
            ->assertJson([
                'locale' => 'id',
            ]);

        expect(app()->getLocale())
            ->toBe('id');
    });
});