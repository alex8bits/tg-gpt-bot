<?php

namespace App\Telegram\Commands;

use App\DTO\TelegramMessageData;
use App\Enums\MessageSources;
use App\Events\MessageReceivedEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\CallbackQuery;

class RememberUsCommand extends Command
{
    protected string $name = 'remember_us';
    protected string $description = 'Помните нас';

    public function handle()
    {
        $update = $this->getUpdate();

        $id = $update->getMessage()->getChat()->getId();

        $greeting = $this->getGreeting();
        $text = $greeting . '. Это химчистка "Лотос". Вы помните нас?';

        $message = new TelegramMessageData(
            identifier: $id,
            text: $text,
            source: MessageSources::Telegram);
        MessageReceivedEvent::dispatch($message, 'assistant');

        Cache::put($id . "_prompt", config('open_ai.prompt') . ' Используй следующий сценарий общения с клиентом. Сценарий описан в формате состояний. На каждом этапе задавай вопрос и жди ответа пользователя, затем переходи к следующему состоянию. Вот сценарий:' . $this->getScenario($text));

        Telegram::sendMessage([
            'chat_id' => $id,
            'text' => $text
        ]);
    }

    public function handleCallback(CallbackQuery $query)
    {
        $id = $query->from->id;

        $greeting = $this->getGreeting();
        $text = $greeting . '. Это химчистка "Лотос". Вы помните нас?';

        $message = new TelegramMessageData(
            identifier: $id,
            text: $text,
            source: MessageSources::Telegram);
        MessageReceivedEvent::dispatch($message, 'assistant');

        Cache::put($id . "_prompt", config('open_ai.prompt') . ' Используй следующий сценарий общения с клиентом. Сценарий описан в формате состояний. На каждом этапе задавай вопрос и жди ответа пользователя, затем переходи к следующему состоянию. Учитывай, что варианты ответов пользователя не обязательно будут совпадать со сценарием. Необходимо интерпретировать их по смыслу. Вот сценарий:' . $this->getScenario($text));

        Telegram::sendMessage([
            'chat_id' => $id,
            'text' => $text,
        ]);
    }

    protected function getGreeting(): string
    {
        $time = now();
        if ($time->hour < 11) {
            $greeting = "Доброе утро";
        } elseif ($time->hour < 17) {
            $greeting = "Добрый день";
        } else {
            $greeting = "Добрый вечер";
        }

        return $greeting;
    }

    protected function getScenario($text): string
    {
        $scenario = [
            'start' => [
                'message' => $text,
                'options' => [
                    'да' => 'offer_discount',
                    'конечно' => 'offer_discount',
                    'помню' => 'offer_discount',
                    'нет' => 'explain_company',
                    'не' => 'explain_company',
                    'не особо' => 'explain_company',
                    'не очень' => 'explain_company',
                    'не помню' => 'explain_company',
                    'был негативный опыт' => 'became_better',
                    'испортили вещь' => 'became_better',
                    'не смогли помочь' => 'became_better',
                    'грубый отказ' => 'rude_refusal',
                    'на хуй' => 'rude_refusal',
                    'на хер' => 'rude_refusal',
                    'нахуй' => 'rude_refusal',
                    'нахер' => 'rude_refusal',
                    'в пизду' => 'rude_refusal',
                ]
            ],
            'offer_discount' => [
                'message' => 'Сейчас мы предлагаем вам скидку 15% на следующий заказ!',
                'options' => [
                    'согласен' => 'begin_deal',
                    'да' => 'begin_deal',
                    'отлично' => 'begin_deal',
                    'не согласен' => 'end_conversation'
                ]
            ],
            'explain_company' => [
                'message' => 'Мы, пожалуй, лучшая химчистка с более чем полувековой историей. Вы пользовались нашими услугами ранее. Сейчас мы предлагаем вам скидку 15% на следующий заказ!',
                'options' => [
                    'согласен' => 'begin_deal',
                    'не согласен' => 'end_conversation'
                ]
            ],
            'became_better' => [
                'message' => 'Жаль, что Вы остались недовольны нами, но благодаря Вашим отзывам мы стали лучше.',
                'next' => 'offer_discount'
            ],
            'rude_refusal' => [
                'message' => 'Не хотели Вас лишний раз беспокоить. Просто мы дарим подарки',
                'options' => [
                    'отказ' => 'end_conversation',
                    'грубый отказ' => 'end_conversation',
                    'согласен' => 'offer_discount'
                ]
            ],
            'end_conversation' => [
                'message' => 'Хорошо, если передумаете — мы всегда на связи. Хорошего дня!'
            ],
            'begin_deal' => [
                'message' => 'Вы придёте в пункт приёмки или закажете доставку на дом?',
                'options' => [
                    'приду' => 'address_hint',
                    'доставка' => 'courier_schedule'
                ]
            ],
            'address_hint' => [
                'message' => 'Могу подсказать ближайший адрес нашего пункта',
            ],
            'courier_schedule' => [
                'message' => "Когда Вам удобно встретить нашего курьера?"
            ]
        ];

        return json_encode($scenario);
    }
}
