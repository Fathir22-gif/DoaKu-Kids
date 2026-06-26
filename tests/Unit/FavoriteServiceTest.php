<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\FavoriteService;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FavoriteServiceTest extends TestCase
{
    use RefreshDatabase;

    protected FavoriteService $service;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FavoriteService();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_add_a_prayer_to_favorites(): void
    {
        $result = $this->service->addToFavorite($this->user->id, '1', 'Doa Sebelum Makan');

        $this->assertTrue($result);
        $this->assertDatabaseHas('favorites', [
            'user_id'      => $this->user->id,
            'prayer_id'    => '1',
            'prayer_title' => 'Doa Sebelum Makan',
        ]);
    }

    /** @test */
    public function it_does_not_create_duplicate_favorite(): void
    {
        $this->service->addToFavorite($this->user->id, '1', 'Doa Sebelum Makan');
        $this->service->addToFavorite($this->user->id, '1', 'Doa Sebelum Makan');

        $this->assertEquals(1, Favorite::where('user_id', $this->user->id)
            ->where('prayer_id', '1')
            ->count());
    }

    /** @test */
    public function it_can_remove_a_prayer_from_favorites(): void
    {
        $this->service->addToFavorite($this->user->id, '1', 'Doa Sebelum Makan');

        $result = $this->service->removeFromFavorite($this->user->id, '1');

        $this->assertTrue($result);
        $this->assertDatabaseMissing('favorites', [
            'user_id'   => $this->user->id,
            'prayer_id' => '1',
        ]);
    }

    /** @test */
    public function it_returns_false_when_removing_nonexistent_favorite(): void
    {
        $result = $this->service->removeFromFavorite($this->user->id, '999');

        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_get_all_favorites_by_user(): void
    {
        $this->service->addToFavorite($this->user->id, '1', 'Doa Sebelum Makan');
        $this->service->addToFavorite($this->user->id, '2', 'Doa Sebelum Tidur');

        $favorites = $this->service->getFavoritesByUser($this->user->id);

        $this->assertCount(2, $favorites);
    }

    /** @test */
    public function it_returns_empty_collection_when_user_has_no_favorites(): void
    {
        $favorites = $this->service->getFavoritesByUser($this->user->id);

        $this->assertTrue($favorites->isEmpty());
    }

    /** @test */
    public function it_can_check_if_prayer_is_favorited(): void
    {
        $this->service->addToFavorite($this->user->id, '1', 'Doa Sebelum Makan');

        $this->assertTrue($this->service->isFavorited($this->user->id, '1'));
        $this->assertFalse($this->service->isFavorited($this->user->id, '99'));
    }

    /** @test */
    public function favorites_are_isolated_per_user(): void
    {
        $otherUser = User::factory()->create();
        $this->service->addToFavorite($this->user->id, '1', 'Doa Sebelum Makan');

        $this->assertTrue($this->service->isFavorited($this->user->id, '1'));
        $this->assertFalse($this->service->isFavorited($otherUser->id, '1'));
    }
}
