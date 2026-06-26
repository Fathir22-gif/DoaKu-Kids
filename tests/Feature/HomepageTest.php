<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Contracts\DoaServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock DoaService karena bergantung pada external API
        $this->mock(DoaServiceInterface::class, function ($mock) {
            $mock->shouldReceive('getAllPrayers')->andReturn([
                ['id' => '1', 'doa' => 'Doa Sebelum Makan', 'ayat' => 'بِسْمِ اللّٰه', 'latin' => 'Bismillah', 'artinya' => 'Dengan nama Allah'],
                ['id' => '2', 'doa' => 'Doa Sebelum Tidur', 'ayat' => 'اللَّهُمَّ', 'latin' => 'Allahumma', 'artinya' => 'Ya Allah'],
            ]);
            $mock->shouldReceive('getRandomPrayer')->andReturn(
                ['id' => '1', 'doa' => 'Doa Sebelum Makan', 'ayat' => 'بِسْمِ اللّٰه', 'latin' => 'Bismillah', 'artinya' => 'Dengan nama Allah']
            );
        });
    }

    /** @test */
    public function homepage_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /** @test */
    public function homepage_shows_prayer_list(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Doa Sebelum Makan');
        $response->assertSee('Doa Sebelum Tidur');
    }

    /** @test */
    public function homepage_shows_hero_section(): void
    {
        $response = $this->get('/');

        $response->assertSee('AYO BELAJAR!', false);
        $response->assertSee('DOA HARI INI', false);
    }

    /** @test */
    public function homepage_shows_login_link_for_guests(): void
    {
        $response = $this->get('/');

        $response->assertSee(route('login'));
    }

    /** @test */
    public function homepage_shows_favorit_and_hafalan_buttons_when_logged_in(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        // Ketika login, form favorit dan hafalan harus tampil
        $response->assertSee(route('favorites.toggle'));
        $response->assertSee(route('memorization.add'));
    }

    /** @test */
    public function homepage_search_returns_results(): void
    {
        $this->mock(DoaServiceInterface::class, function ($mock) {
            $mock->shouldReceive('getAllPrayers')->andReturn([]);
            $mock->shouldReceive('getRandomPrayer')->andReturn(null);
            $mock->shouldReceive('searchPrayers')->with('makan')->andReturn([
                ['id' => '1', 'doa' => 'Doa Sebelum Makan', 'ayat' => 'بِسْمِ اللّٰه', 'latin' => 'Bismillah', 'artinya' => 'Dengan nama Allah'],
            ]);
        });

        $response = $this->get('/doa/search?query=makan');

        $response->assertStatus(200);
        $response->assertSee('Doa Sebelum Makan');
    }

    /** @test */
    public function homepage_search_shows_empty_when_no_results(): void
    {
        $this->mock(DoaServiceInterface::class, function ($mock) {
            $mock->shouldReceive('getAllPrayers')->andReturn([]);
            $mock->shouldReceive('getRandomPrayer')->andReturn(null);
            $mock->shouldReceive('searchPrayers')->with('xyzabc')->andReturn([]);
        });

        $response = $this->get('/doa/search?query=xyzabc');

        $response->assertStatus(200);
    }
}
