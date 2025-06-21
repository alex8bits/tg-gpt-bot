<?php

namespace Database\Seeders;

use App\Enums\BotTypes;
use App\Models\GPTBot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GPTBotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GPTBot::firstOrCreate([
            'type' => BotTypes::GREETER,
        ], [
            'name' => BotTypes::GREETER->value,
            'prompt' => "Поздоровася с пользователем",
        ]);
        GPTBot::firstOrCreate([
            'type' => BotTypes::SPREADER,
        ], [
            'name' => BotTypes::SPREADER->value,
            'prompt' => "Оцени, к какой теме относится последнее сообщение пользователя",
        ]);
        GPTBot::firstOrCreate([
            'type' => BotTypes::COMMON,
        ], [
            'name' => 'Помните нас?',
            'prompt' => "Спроси пользователя, помнит ли он нашу компанию",
        ]);
    }
}
