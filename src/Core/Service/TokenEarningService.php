<?php

namespace App\Core\Service;

use App\Core\Contract\UserInterface;
use App\Core\Entity\TokenEarningLog;
use App\Core\Entity\User;
use App\Core\Enum\LogActionEnum;
use App\Core\Enum\SettingEnum;
use App\Core\Enum\TokenEarningMethodEnum;
use App\Core\Repository\TokenEarningLogRepository;
use App\Core\Repository\UserRepository;
use App\Core\Service\Logs\LogService;
use App\Core\Service\System\IpAddressProviderService;
use Doctrine\ORM\EntityManagerInterface;

class TokenEarningService
{
    public function __construct(
        private readonly TokenEarningLogRepository $tokenEarningLogRepository,
        private readonly UserRepository $userRepository,
        private readonly SettingService $settingService,
        private readonly LogService $logService,
        private readonly IpAddressProviderService $ipAddressProviderService,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function isTokenEarningEnabled(): bool
    {
        return (bool) $this->settingService->getSetting(SettingEnum::TOKEN_EARNING_ENABLED->value);
    }

    public function canClaimTokens(UserInterface $user, TokenEarningMethodEnum $method): array
    {
        $lastEarning = $this->tokenEarningLogRepository->getLastEarningByUserAndMethod($user, $method);
        
        if ($lastEarning === null) {
            return ['can_claim' => true, 'message' => null];
        }

        $cooldownMinutes = $this->getCooldownForMethod($method);
        $nextClaimTime = (clone $lastEarning->getCreatedAt())->modify("+{$cooldownMinutes} minutes");
        $now = new \DateTime();

        if ($now < $nextClaimTime) {
            $remainingMinutes = ceil(($nextClaimTime->getTimestamp() - $now->getTimestamp()) / 60);
            return [
                'can_claim' => false,
                'message' => sprintf('Please wait %d minutes before claiming again', $remainingMinutes),
                'next_claim_time' => $nextClaimTime
            ];
        }

        return ['can_claim' => true, 'message' => null];
    }

    public function awardTokens(UserInterface $user, TokenEarningMethodEnum $method, ?string $details = null): bool
    {
        $canClaim = $this->canClaimTokens($user, $method);
        if (!$canClaim['can_claim']) {
            return false;
        }

        $amount = $this->getAmountForMethod($method);
        
        // Update user balance
        /** @var User $user */
        $user->setBalance($user->getBalance() + $amount);
        $this->userRepository->save($user);

        // Create earning log
        $earningLog = (new TokenEarningLog())
            ->setUser($user)
            ->setMethod($method)
            ->setAmount($amount)
            ->setIpAddress($this->ipAddressProviderService->getIpAddress() ?? 'Unknown')
            ->setDetails($details);
        $this->tokenEarningLogRepository->save($earningLog);

        // Log the action
        $this->logService->logAction($user, LogActionEnum::EARNED_TOKENS, [
            'method' => $method->value,
            'amount' => $amount,
        ]);

        return true;
    }

    public function getAmountForMethod(TokenEarningMethodEnum $method): float
    {
        return match ($method) {
            TokenEarningMethodEnum::WATCH_AD => (float) ($this->settingService->getSetting(SettingEnum::TOKEN_EARNING_AD_AMOUNT->value) ?? 1.0),
            TokenEarningMethodEnum::JOIN_DISCORD => (float) ($this->settingService->getSetting(SettingEnum::TOKEN_EARNING_DISCORD_AMOUNT->value) ?? 5.0),
            TokenEarningMethodEnum::COMPLETE_TASK => (float) ($this->settingService->getSetting(SettingEnum::TOKEN_EARNING_TASK_AMOUNT->value) ?? 2.0),
        };
    }

    public function getCooldownForMethod(TokenEarningMethodEnum $method): int
    {
        return match ($method) {
            TokenEarningMethodEnum::WATCH_AD => (int) ($this->settingService->getSetting(SettingEnum::TOKEN_EARNING_AD_COOLDOWN_MINUTES->value) ?? 60),
            TokenEarningMethodEnum::JOIN_DISCORD => 0, // Can only claim once
            TokenEarningMethodEnum::COMPLETE_TASK => 1440, // 24 hours
        };
    }

    public function hasClaimedDiscord(UserInterface $user): bool
    {
        $log = $this->tokenEarningLogRepository->getLastEarningByUserAndMethod($user, TokenEarningMethodEnum::JOIN_DISCORD);
        return $log !== null;
    }

    public function getDiscordServerId(): ?string
    {
        return $this->settingService->getSetting(SettingEnum::TOKEN_EARNING_DISCORD_SERVER_ID->value);
    }

    public function getDiscordClientId(): ?string
    {
        return $this->settingService->getSetting(SettingEnum::DISCORD_CLIENT_ID->value);
    }

    public function getDiscordClientSecret(): ?string
    {
        return $this->settingService->getSetting(SettingEnum::DISCORD_CLIENT_SECRET->value);
    }

    public function getDiscordBotToken(): ?string
    {
        return $this->settingService->getSetting(SettingEnum::DISCORD_BOT_TOKEN->value);
    }
}
