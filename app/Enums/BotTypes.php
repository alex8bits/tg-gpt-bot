<?php

namespace App\Enums;

enum BotTypes: string
{
    case COMMON = 'обычный';
    case GREETER = 'приветственный';
    case SPREADER = 'распределитель';
}
