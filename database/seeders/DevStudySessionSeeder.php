<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\Deck;
use App\Models\Note;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevStudySessionSeeder extends Seeder
{
    public function run(): void
    {
        $now = CarbonImmutable::now();

        $user = User::query()->updateOrCreate(
            ['email' => 'dev.study@example.com'],
            [
                'name' => 'Dev Study User',
                'password' => Hash::make('password'),
            ]
        );

        $deck = Deck::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'name' => 'Seeded Study Session Deck',
            ],
            [
                'description' => 'Fixture deck for study session development.',
                'color' => 'amber',
                'is_archived' => false,
            ]
        );

        Card::query()->where('user_id', $user->id)->where('deck_id', $deck->id)->delete();
        Note::query()->where('user_id', $user->id)->where('deck_id', $deck->id)->delete();

        $fixtures = [
            [
                'front_text' => 'relearning prompt',
                'back_text' => 'relearning answer',
                'state' => 'relearning',
                'current_step' => 0,
                'due_at' => $now->subMinutes(2),
            ],
            [
                'front_text' => 'review prompt',
                'back_text' => 'review answer',
                'state' => 'review',
                'current_step' => 0,
                'due_at' => $now->subMinutes(1),
            ],
            [
                'front_text' => 'learning prompt',
                'back_text' => 'learning answer',
                'state' => 'learning',
                'current_step' => 1,
                'due_at' => $now->subSeconds(30),
            ],
            [
                'front_text' => 'new prompt',
                'back_text' => 'new answer',
                'state' => 'new',
                'current_step' => 0,
                'due_at' => null,
            ],
        ];

        foreach ($fixtures as $fixture) {
            $note = Note::query()->create([
                'user_id' => $user->id,
                'deck_id' => $deck->id,
                'front_text' => $fixture['front_text'],
                'back_text' => $fixture['back_text'],
                'front_plain_text' => $fixture['front_text'],
                'back_plain_text' => $fixture['back_text'],
                'source_type' => 'dev_seed',
                'source_file_name' => 'dev-study-session-seeder',
                'source_raw_line' => $fixture['front_text']."\t".$fixture['back_text'],
            ]);

            Card::query()->create([
                'note_id' => $note->id,
                'user_id' => $user->id,
                'deck_id' => $deck->id,
                'state' => $fixture['state'],
                'current_step' => $fixture['current_step'],
                'learning_steps_json' => [1, 10],
                'relearning_steps_json' => [10],
                'due_at' => $fixture['due_at'],
                'last_reviewed_at' => null,
                'stability' => 1.0,
                'difficulty' => 5.0,
                'elapsed_days' => 0,
                'scheduled_days' => 0,
                'reps' => 0,
                'lapses' => 0,
                'last_rating' => null,
                'is_suspended' => false,
            ]);
        }
    }
}
