<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MemorizationList;
use App\Contracts\DoaServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MemorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

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
    public function memorization_page_loads_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)->get(route('memorization.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function guest_is_redirected_to_login_when_accessing_memorization(): void
    {
        $response = $this->get(route('memorization.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function user_can_add_a_prayer_to_memorization(): void
    {
        $response = $this->actingAs($this->user)->post(route('memorization.add'), [
            'prayer_id'    => '1',
            'prayer_title' => 'Doa Sebelum Makan',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('memorization_lists', [
            'user_id'      => $this->user->id,
            'prayer_id'    => '1',
            'prayer_title' => 'Doa Sebelum Makan',
            'status'       => 'belum_mulai',
        ]);
    }

    /** @test */
    public function adding_duplicate_prayer_to_memorization_shows_feedback(): void
    {
        MemorizationList::create([
            'user_id'      => $this->user->id,
            'prayer_id'    => '1',
            'prayer_title' => 'Doa Sebelum Makan',
            'status'       => 'belum_mulai',
        ]);

        $response = $this->actingAs($this->user)->post(route('memorization.add'), [
            'prayer_id'    => '1',
            'prayer_title' => 'Doa Sebelum Makan',
        ]);

        // Harus redirect kembali (bukan error) dan memberi session feedback
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Tetap hanya satu record
        $this->assertEquals(1, MemorizationList::where('user_id', $this->user->id)
            ->where('prayer_id', '1')
            ->count());
    }

    /** @test */
    public function guest_cannot_add_to_memorization(): void
    {
        $response = $this->post(route('memorization.add'), [
            'prayer_id'    => '1',
            'prayer_title' => 'Doa Sebelum Makan',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('memorization_lists', ['prayer_id' => '1']);
    }

    /** @test */
    public function user_can_update_memorization_status_to_sedang_dihafal(): void
    {
        MemorizationList::create([
            'user_id'      => $this->user->id,
            'prayer_id'    => '1',
            'prayer_title' => 'Doa Sebelum Makan',
            'status'       => 'belum_mulai',
        ]);

        $response = $this->actingAs($this->user)->post(route('memorization.update'), [
            'prayer_id' => '1',
            'status'    => 'sedang_dihafal',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('memorization_lists', [
            'user_id'   => $this->user->id,
            'prayer_id' => '1',
            'status'    => 'sedang_dihafal',
        ]);
    }

    /** @test */
    public function user_can_update_memorization_status_to_sudah_hafal(): void
    {
        MemorizationList::create([
            'user_id'      => $this->user->id,
            'prayer_id'    => '1',
            'prayer_title' => 'Doa Sebelum Makan',
            'status'       => 'sedang_dihafal',
        ]);

        $response = $this->actingAs($this->user)->post(route('memorization.update'), [
            'prayer_id' => '1',
            'status'    => 'sudah_hafal',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('memorization_lists', [
            'user_id'   => $this->user->id,
            'prayer_id' => '1',
            'status'    => 'sudah_hafal',
        ]);
    }

    /** @test */
    public function user_cannot_update_memorization_with_invalid_status(): void
    {
        MemorizationList::create([
            'user_id'      => $this->user->id,
            'prayer_id'    => '1',
            'prayer_title' => 'Doa Sebelum Makan',
            'status'       => 'belum_mulai',
        ]);

        $response = $this->actingAs($this->user)->post(route('memorization.update'), [
            'prayer_id' => '1',
            'status'    => 'status_asal_asalan',
        ]);

        $response->assertSessionHasErrors('status');
    }

    /** @test */
    public function user_can_remove_prayer_from_memorization(): void
    {
        MemorizationList::create([
            'user_id'      => $this->user->id,
            'prayer_id'    => '1',
            'prayer_title' => 'Doa Sebelum Makan',
            'status'       => 'belum_mulai',
        ]);

        $response = $this->actingAs($this->user)->post(route('memorization.remove'), [
            'prayer_id' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('memorization_lists', [
            'user_id'   => $this->user->id,
            'prayer_id' => '1',
        ]);
    }

    /** @test */
    public function guest_cannot_remove_from_memorization(): void
    {
        $response = $this->post(route('memorization.remove'), ['prayer_id' => '1']);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function memorization_page_shows_user_memorizations(): void
    {
        MemorizationList::create([
            'user_id'      => $this->user->id,
            'prayer_id'    => '1',
            'prayer_title' => 'Doa Sebelum Makan',
            'status'       => 'belum_mulai',
        ]);

        $response = $this->actingAs($this->user)->get(route('memorization.index'));

        $response->assertStatus(200);
        $response->assertSee('Doa Sebelum Makan');
    }

    /** @test */
    public function memorization_page_shows_empty_state_when_no_data(): void
    {
        $response = $this->actingAs($this->user)->get(route('memorization.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function memorization_data_is_isolated_per_user(): void
    {
        $otherUser = User::factory()->create();

        MemorizationList::create([
            'user_id'      => $otherUser->id,
            'prayer_id'    => '5',
            'prayer_title' => 'Doa Punya User Lain',
            'status'       => 'sudah_hafal',
        ]);

        $response = $this->actingAs($this->user)->get(route('memorization.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Doa Punya User Lain');
    }

    /** @test */
    public function add_memorization_requires_prayer_id(): void
    {
        $response = $this->actingAs($this->user)->post(route('memorization.add'), [
            'prayer_title' => 'Doa Sebelum Makan',
            // prayer_id sengaja tidak dikirim
        ]);

        $response->assertSessionHasErrors('prayer_id');
    }
}
