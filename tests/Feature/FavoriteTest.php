<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Favorite;
use App\Contracts\DoaServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        // Mock DoaService agar halaman favorit tidak hit API eksternal
        $this->mock(DoaServiceInterface::class, function ($mock) {
            $mock->shouldReceive('getAllPrayers')->andReturn([]);
            $mock->shouldReceive('getRandomPrayer')->andReturn(null);
            $mock->shouldReceive('getPrayerById')->andReturn([
                'id'      => '1',
                'doa'     => 'Doa Sebelum Makan',
                'ayat'    => 'بِسْمِ اللّٰه',
                'latin'   => 'Bismillah',
                'artinya' => 'Dengan nama Allah',
            ]);
        });
    }

    /** @test */
    public function favorites_page_loads_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)->get(route('favorites.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function guest_is_redirected_to_login_when_accessing_favorites(): void
    {
        $response = $this->get(route('favorites.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function user_can_add_a_prayer_to_favorites(): void
    {
        $response = $this->actingAs($this->user)->post(route('favorites.toggle'), [
            'prayer_id'    => '1',
            'prayer_title' => 'Doa Sebelum Makan',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('favorites', [
            'user_id'      => $this->user->id,
            'prayer_id'    => '1',
            'prayer_title' => 'Doa Sebelum Makan',
        ]);
    }

    /** @test */
    public function user_can_remove_a_prayer_from_favorites_by_toggling(): void
    {
        // Tambah dulu
        Favorite::create([
            'user_id'      => $this->user->id,
            'prayer_id'    => '1',
            'prayer_title' => 'Doa Sebelum Makan',
        ]);

        // Toggle lagi → harus dihapus
        $response = $this->actingAs($this->user)->post(route('favorites.toggle'), [
            'prayer_id'    => '1',
            'prayer_title' => 'Doa Sebelum Makan',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('favorites', [
            'user_id'   => $this->user->id,
            'prayer_id' => '1',
        ]);
    }

    /** @test */
    public function guest_cannot_toggle_favorites(): void
    {
        $response = $this->post(route('favorites.toggle'), [
            'prayer_id'    => '1',
            'prayer_title' => 'Doa Sebelum Makan',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('favorites', ['prayer_id' => '1']);
    }

    /** @test */
    public function favorites_page_shows_user_favorites(): void
    {
        Favorite::create([
            'user_id'      => $this->user->id,
            'prayer_id'    => '1',
            'prayer_title' => 'Doa Sebelum Makan',
        ]);

        $response = $this->actingAs($this->user)->get(route('favorites.index'));

        $response->assertStatus(200);
        $response->assertSee('Doa Sebelum Makan');
    }

    /** @test */
    public function favorites_page_shows_empty_state_when_no_favorites(): void
    {
        $response = $this->actingAs($this->user)->get(route('favorites.index'));

        $response->assertStatus(200);
        // Tidak ada data favorit, halaman harus tetap 200 tanpa crash
    }

    /** @test */
    public function favorites_are_not_shared_between_users(): void
    {
        $otherUser = User::factory()->create();

        Favorite::create([
            'user_id'      => $otherUser->id,
            'prayer_id'    => '5',
            'prayer_title' => 'Doa Orang Lain',
        ]);

        $response = $this->actingAs($this->user)->get(route('favorites.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Doa Orang Lain');
    }

    /** @test */
    public function toggle_favorite_requires_prayer_id(): void
    {
        $response = $this->actingAs($this->user)->post(route('favorites.toggle'), [
            'prayer_title' => 'Doa Sebelum Makan',
            // prayer_id sengaja tidak dikirim
        ]);

        $response->assertSessionHasErrors('prayer_id');
    }
}
