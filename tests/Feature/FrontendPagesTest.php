<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\GameSession;
use App\Models\Level;
use App\Models\Player;
use App\Models\PlayerLevelProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FrontendPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LevelSeeder::class);
    }

    public function test_home_page_renders_successfully(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Home'));
    }

    public function test_map_page_requires_player_session(): void
    {
        $this->get('/map')->assertRedirect('/');

        $player = Player::create(['nickname' => 'Budi']);
        $lvl1 = Level::where('grade', 4)->where('level_number', 1)->first();
        PlayerLevelProgress::create([
            'player_id' => $player->id,
            'level_id' => $lvl1->id,
            'is_unlocked' => true,
        ]);

        $this->withSession(['player_id' => $player->id])
            ->get('/map')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Map')
                ->has('levels', 10)
            );
    }

    public function test_play_and_results_pages_render_for_session(): void
    {
        $player = Player::create(['nickname' => 'Siti']);
        $lvl1 = Level::where('grade', 4)->where('level_number', 1)->first();
        $session = GameSession::create([
            'player_id' => $player->id,
            'level_id' => $lvl1->id,
            'status' => 'active',
            'question_seeds' => range(100, 109),
        ]);

        $this->withSession(['player_id' => $player->id])
            ->get("/play/{$session->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Play')
                ->has('questions', 10)
            );

        $this->withSession(['player_id' => $player->id])
            ->get("/results/{$session->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Results'));
    }

    public function test_join_and_classroom_pages_render(): void
    {
        $this->get('/join')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Join'));

        $classroom = Classroom::create([
            'code' => 'JEL4JA',
            'name' => 'Kelas 5A',
            'grade' => 5,
            'teacher_name' => 'Pak Budi',
        ]);

        $player = Player::create([
            'nickname' => 'Ahmad',
            'class_id' => $classroom->id,
        ]);

        $this->get("/classroom/{$classroom->code}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Classroom')
                ->has('students', 1)
            );

        $this->get("/report/{$classroom->code}/{$player->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Report'));
    }
}
