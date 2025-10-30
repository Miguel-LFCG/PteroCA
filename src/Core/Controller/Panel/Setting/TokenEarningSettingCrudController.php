<?php

namespace App\Core\Controller\Panel\Setting;

use App\Core\Enum\SettingContextEnum;
use App\Core\Enum\SettingEnum;
use App\Core\Enum\SettingTypeEnum;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class TokenEarningSettingCrudController extends AbstractSettingCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, 'Token Earning Settings');
    }

    public function getSettingContext(): SettingContextEnum
    {
        return SettingContextEnum::TOKEN_EARNING;
    }

    public function getSettingConfiguration(): array
    {
        return [
            [
                'name' => SettingEnum::TOKEN_EARNING_ENABLED->value,
                'label' => 'Enable Token Earning',
                'type' => SettingTypeEnum::CHECKBOX,
                'help' => 'Enable or disable the token earning feature for users',
                'required' => false,
            ],
            [
                'name' => SettingEnum::TOKEN_EARNING_AD_AMOUNT->value,
                'label' => 'Ad Watch Token Amount',
                'type' => SettingTypeEnum::NUMBER,
                'help' => 'Amount of tokens awarded for watching an ad',
                'required' => true,
            ],
            [
                'name' => SettingEnum::TOKEN_EARNING_AD_COOLDOWN_MINUTES->value,
                'label' => 'Ad Watch Cooldown (minutes)',
                'type' => SettingTypeEnum::NUMBER,
                'help' => 'Minimum time in minutes between ad watches',
                'required' => true,
            ],
            [
                'name' => SettingEnum::TOKEN_EARNING_DISCORD_AMOUNT->value,
                'label' => 'Discord Join Token Amount',
                'type' => SettingTypeEnum::NUMBER,
                'help' => 'Amount of tokens awarded for joining Discord server',
                'required' => true,
            ],
            [
                'name' => SettingEnum::TOKEN_EARNING_DISCORD_SERVER_ID->value,
                'label' => 'Discord Server ID',
                'type' => SettingTypeEnum::TEXT,
                'help' => 'Your Discord server/guild ID',
                'required' => false,
            ],
            [
                'name' => SettingEnum::DISCORD_CLIENT_ID->value,
                'label' => 'Discord OAuth Client ID',
                'type' => SettingTypeEnum::TEXT,
                'help' => 'Discord OAuth application client ID',
                'required' => false,
            ],
            [
                'name' => SettingEnum::DISCORD_CLIENT_SECRET->value,
                'label' => 'Discord OAuth Client Secret',
                'type' => SettingTypeEnum::PASSWORD,
                'help' => 'Discord OAuth application client secret',
                'required' => false,
            ],
            [
                'name' => SettingEnum::DISCORD_BOT_TOKEN->value,
                'label' => 'Discord Bot Token',
                'type' => SettingTypeEnum::PASSWORD,
                'help' => 'Discord bot token for verifying server membership',
                'required' => false,
            ],
            [
                'name' => SettingEnum::TOKEN_EARNING_TASK_AMOUNT->value,
                'label' => 'Task Completion Token Amount',
                'type' => SettingTypeEnum::NUMBER,
                'help' => 'Amount of tokens awarded for completing a task',
                'required' => true,
            ],
        ];
    }
}
