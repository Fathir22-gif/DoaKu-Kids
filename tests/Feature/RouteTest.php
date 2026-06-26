<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RouteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function route_home_exists_and_is_accessible(): void
    {
        $this->assertRouteExists('home');
    }

    /** @test */
    public function route_login_exists(): void
    {
        $this->assertRouteExists('login');
    }

    /** @test */
    public function route_register_exists(): void
    {
        $this->assertRouteExists('register');
    }

    /** @test */
    public function route_logout_exists(): void
    {
        $this->assertRouteExists('logout');
    }

    /** @test */
    public function route_doa_detail_follows_correct_url_pattern(): void
    {
        $this->assertEquals('/doa/detail/5', route('doa.detail', '5', false));
    }

    /** @test */
    public function route_doa_search_exists(): void
    {
        $this->assertRouteExists('doa.search');
    }

    /** @test */
    public function route_favorites_index_exists(): void
    {
        $this->assertRouteExists('favorites.index');
    }

    /** @test */
    public function route_favorites_toggle_exists(): void
    {
        $this->assertRouteExists('favorites.toggle');
    }

    /** @test */
    public function route_memorization_index_exists(): void
    {
        $this->assertRouteExists('memorization.index');
    }

    /** @test */
    public function route_memorization_add_exists(): void
    {
        $this->assertRouteExists('memorization.add');
    }

    /** @test */
    public function route_memorization_update_exists(): void
    {
        $this->assertRouteExists('memorization.update');
    }

    /** @test */
    public function route_memorization_remove_exists(): void
    {
        $this->assertRouteExists('memorization.remove');
    }

    /**
     * Helper untuk mengecek apakah route dengan nama tertentu terdaftar.
     */
    protected function assertRouteExists(string $routeName): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has($routeName),
            "Route [{$routeName}] seharusnya terdaftar tetapi tidak ditemukan."
        );
    }
}
