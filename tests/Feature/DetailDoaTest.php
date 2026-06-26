<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Favorite;
use App\Models\MemorizationList;
use App\Contracts\DoaServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DetailDoaTest extends TestCase
{
    use RefreshDatabase;

    protected array $fakePrayer = [
        'id'      => '1',
        'doa'     => 'Doa Sebelum Makan',
        'ayat'    => 'بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ',
        'latin'   => 'Bismillahirrahmanirrahim',
        'artinya' => 'Dengan menyebut nama Allah Yang Maha Pengasih lagi Maha Penyayang',
        'riwayat' => 'HR. Abu Dawud',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(DoaServiceInterface::class, function ($mock) {
            $mock->shouldReceive('getPrayerById')
                ->with('1')
                ->andReturn($this->fakePrayer);

            $mock->shouldReceive('getPrayerById')
                ->with('999')
                ->andReturn(null);

            $mock->shouldReceive('getAllPrayers')->andReturn([]);
            $mock->shouldReceive('getRandomPrayer')->andReturn(null);
        });
    }

    /** @test */
    public function detail_page_loads_successfully_for_guest(): void
    {
        $response = $this->get(route('doa.detail', '1'));

        $response->assertStatus(200);
    }

    /** @test */
    public function detail_page_shows_prayer_information(): void
    {
        $response = $this->get(route('doa.detail', '1'));

        $response->assertStatus(200);
        $response->assertSee('Doa Sebelum Makan');
        $response->assertSee('Bismillahirrahmanirrahim');
        $response->assertSee('Dengan menyebut nama Allah');
        $response->assertSee('HR. Abu Dawud');
    }

    /** @test */
    public function detail_page_shows_arabic_text(): void
    {
        $response = $this->get(route('doa.detail', '1'));

        $response->assertSee('بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ');
    }

    /** @test */
    public function detail_page_returns_404_for_invalid_id(): void
    {
        $response = $this->get(route('doa.detail', '999'));

        $response->assertStatus(404);
    }

    /** @test */
    public function detail_page_shows_login_links_for_guest(): void
    {
        $response = $this->get(route('doa.detail', '1'));

        // Guest harus diarahkan ke login untuk favorit dan hafalan
        $response->assertSee(route('login'));
    }

    /** @test */
    public function detail_page_shows_favorit_form_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('doa.detail', '1'));

        $response->assertStatus(200);
        $response->assertSee(route('favorites.toggle'));
    }

    /** @test */
    public function detail_page_shows_hafalan_form_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('doa.detail', '1'));

        $response->assertStatus(200);
        $response->assertSee(route('memorization.add'));
    }

    /** @test */
    public function detail_page_shows_favorited_status_when_prayer_is_favorited(): void
    {
        $user = User::factory()->create();

        Favorite::create([
            'user_id'      => $user->id,
            'prayer_id'    => '1',
            'prayer_title' => 'Doa Sebelum Makan',
        ]);

        $response = $this->actingAs($user)->get(route('doa.detail', '1'));

        $response->assertStatus(200);
        $response->assertSee('Hapus Favorit');
    }

    /** @test */
    public function detail_page_shows_not_favorited_state_when_not_favorited(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('doa.detail', '1'));

        $response->assertStatus(200);
        $response->assertSee('Tambah Favorit');
    }

    /** @test */
    public function detail_page_shows_memorized_status_when_prayer_is_memorized(): void
    {
        $user = User::factory()->create();

        MemorizationList::create([
            'user_id'      => $user->id,
            'prayer_id'    => '1',
            'prayer_title' => 'Doa Sebelum Makan',
            'status'       => 'sedang_dihafal',
        ]);

        $response = $this->actingAs($user)->get(route('doa.detail', '1'));

        $response->assertStatus(200);
        $response->assertSee('Sudah di Hafalan');
    }

    /** @test */
    public function detail_page_shows_add_hafalan_button_when_not_memorized(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('doa.detail', '1'));

        $response->assertStatus(200);
        $response->assertSee('Tambah Hafalan');
    }

    /** @test */
    public function detail_page_has_back_button(): void
    {
        $response = $this->get(route('doa.detail', '1'));

        $response->assertStatus(200);
        $response->assertSee('Kembali');
    }

    /** @test */
    public function detail_page_uses_correct_route_pattern(): void
    {
        // Verifikasi bahwa URL mengikuti pola /doa/detail/{id}
        $this->assertEquals('/doa/detail/1', route('doa.detail', '1', false));
    }
}
