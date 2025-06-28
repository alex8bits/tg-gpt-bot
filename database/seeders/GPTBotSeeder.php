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
            'type' => BotTypes::WELCOME,
        ], [
            'name' => BotTypes::WELCOME->value,
            'prompt' => "Поздоровайся с пользователем. Обращайся на 'Вы'. Можно в контексте ткущего времени. Вопросов не задавай",
        ]);
        GPTBot::firstOrCreate([
            'type' => BotTypes::SPREADER,
        ], [
            'name' => BotTypes::SPREADER->value,
            'prompt' => 'Оцени, к какой теме относится последнее сообщение пользователя. Тебе переданы темы с их id. Верни просто id темы к какой ближе всего отностится ответ пользователя. Если ответ пользователя не относится ни к одной из тем, верни 0. Ответ верни в виде json вида {"id":0}',
        ]);
        GPTBot::firstOrCreate([
            'type' => BotTypes::MODERATOR,
        ], [
            'name' => BotTypes::MODERATOR->value,
            'prompt' => 'Спроси, что пользователь имел ввиду?',
        ]);
        GPTBot::firstOrCreate([
            'type' => BotTypes::COMMON,
        ], [
            'name' => 'Помните нас?',
            'theme' => 'Обращаемся с целью узнать, помнит ли клиент нашу компанию и предлагаем снова воспользоваться нашими услугами',
            'prompt' => 'Спроси пользователя, помнит ли он нашу компанию. После ответа, будем предлагать снова воспользоваться нашими услугами',
        ]);
    }
}
