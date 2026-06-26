<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\MemorizationService;
use App\Models\MemorizationList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MemorizationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MemorizationService $service;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MemorizationService();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_add_a_prayer_to_memorization(): void
    {
        $result = $this->service->addToMemorization($this->user->id, '1', 'Doa Sebelum Makan');

        $this->assertTrue($result);
        $this->assertDatabaseHas('memorization_lists', [
            'user_id'      => $this->user->id,
            'prayer_id'    => '1',
            'prayer_title' => 'Doa Sebelum Makan',
        ]);
    }

    /** @test */
    public function new_memorization_defaults_to_belum_mulai_status(): void
    {
        $this->service->addToMemorization($this->user->id, '1', 'Doa Sebelum Makan');

        $this->assertDatabaseHas('memorization_lists', [
            'user_id'   => $this->user->id,
            'prayer_id' => '1',
            'status'    => 'belum_mulai',
        ]);
    }

    /** @test */
    public function it_does_not_create_duplicate_memorization(): void
    {
        $this->service->addToMemorization($this->user->id, '1', 'Doa Sebelum Makan');
        $this->service->addToMemorization($this->user->id, '1', 'Doa Sebelum Makan');

        $this->assertEquals(1, MemorizationList::where('user_id', $this->user->id)
            ->where('prayer_id', '1')
            ->count());
    }

    /** @test */
    public function it_can_update_memorization_status_to_sedang_dihafal(): void
    {
        $this->service->addToMemorization($this->user->id, '1', 'Doa Sebelum Makan');

        $result = $this->service->updateMemorizationStatus($this->user->id, '1', 'sedang_dihafal');

        $this->assertTrue($result);
        $this->assertDatabaseHas('memorization_lists', [
            'user_id'   => $this->user->id,
            'prayer_id' => '1',
            'status'    => 'sedang_dihafal',
        ]);
    }

    /** @test */
    public function it_can_update_memorization_status_to_sudah_hafal(): void
    {
        $this->service->addToMemorization($this->user->id, '1', 'Doa Sebelum Makan');

        $result = $this->service->updateMemorizationStatus($this->user->id, '1', 'sudah_hafal');

        $this->assertTrue($result);
        $this->assertDatabaseHas('memorization_lists', [
            'user_id'   => $this->user->id,
            'prayer_id' => '1',
            'status'    => 'sudah_hafal',
        ]);
    }

    /** @test */
    public function it_rejects_invalid_memorization_status(): void
    {
        $this->service->addToMemorization($this->user->id, '1', 'Doa Sebelum Makan');

        $result = $this->service->updateMemorizationStatus($this->user->id, '1', 'status_tidak_valid');

        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_remove_a_prayer_from_memorization(): void
    {
        $this->service->addToMemorization($this->user->id, '1', 'Doa Sebelum Makan');

        $result = $this->service->removeFromMemorization($this->user->id, '1');

        $this->assertTrue($result);
        $this->assertDatabaseMissing('memorization_lists', [
            'user_id'   => $this->user->id,
            'prayer_id' => '1',
        ]);
    }

    /** @test */
    public function it_returns_false_when_removing_nonexistent_memorization(): void
    {
        $result = $this->service->removeFromMemorization($this->user->id, '999');

        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_get_all_memorizations_by_user(): void
    {
        $this->service->addToMemorization($this->user->id, '1', 'Doa Sebelum Makan');
        $this->service->addToMemorization($this->user->id, '2', 'Doa Sebelum Tidur');

        $memorizations = $this->service->getMemorizationsByUser($this->user->id);

        $this->assertCount(2, $memorizations);
    }

    /** @test */
    public function it_returns_empty_collection_when_user_has_no_memorizations(): void
    {
        $memorizations = $this->service->getMemorizationsByUser($this->user->id);

        $this->assertTrue($memorizations->isEmpty());
    }

    /** @test */
    public function memorizations_are_isolated_per_user(): void
    {
        $otherUser = User::factory()->create();
        $this->service->addToMemorization($this->user->id, '1', 'Doa Sebelum Makan');

        $myMemos    = $this->service->getMemorizationsByUser($this->user->id);
        $otherMemos = $this->service->getMemorizationsByUser($otherUser->id);

        $this->assertCount(1, $myMemos);
        $this->assertCount(0, $otherMemos);
    }
}
