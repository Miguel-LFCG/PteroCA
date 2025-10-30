<?php

namespace App\Core\Enum;

enum TokenEarningMethodEnum: string
{
    case WATCH_AD = 'watch_ad';
    case JOIN_DISCORD = 'join_discord';
    case COMPLETE_TASK = 'complete_task';
}
