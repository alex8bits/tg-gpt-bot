<?php

namespace App\Enums;

enum FeedbackStates: string
{
    case NEW = 'новая';
    case PROCESS = 'в обработке';
    case CLOSED = 'закрыта';
}
