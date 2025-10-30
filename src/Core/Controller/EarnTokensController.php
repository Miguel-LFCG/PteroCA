<?php

namespace App\Core\Controller;

use App\Core\Enum\TokenEarningMethodEnum;
use App\Core\Service\TokenEarningService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class EarnTokensController extends AbstractController
{
    public function __construct(
        private readonly TokenEarningService $tokenEarningService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/earn-tokens', name: 'earn_tokens')]
    public function earnTokens(): Response
    {
        $this->checkPermission();

        if (!$this->tokenEarningService->isTokenEarningEnabled()) {
            $this->addFlash('danger', $this->translator->trans('pteroca.earn_tokens.feature_disabled'));
            return $this->redirectToRoute('panel', ['routeName' => 'recharge_balance']);
        }

        $user = $this->getUser();
        $hasClaimedDiscord = $this->tokenEarningService->hasClaimedDiscord($user);

        return $this->render('panel/earn_tokens/index.html.twig', [
            'balance' => $user->getBalance(),
            'ad_amount' => $this->tokenEarningService->getAmountForMethod(TokenEarningMethodEnum::WATCH_AD),
            'discord_amount' => $this->tokenEarningService->getAmountForMethod(TokenEarningMethodEnum::JOIN_DISCORD),
            'task_amount' => $this->tokenEarningService->getAmountForMethod(TokenEarningMethodEnum::COMPLETE_TASK),
            'ad_cooldown' => $this->tokenEarningService->getCooldownForMethod(TokenEarningMethodEnum::WATCH_AD),
            'has_claimed_discord' => $hasClaimedDiscord,
            'discord_client_id' => $this->tokenEarningService->getDiscordClientId(),
            'discord_server_id' => $this->tokenEarningService->getDiscordServerId(),
        ]);
    }

    #[Route('/earn-tokens/claim-ad', name: 'earn_tokens_claim_ad', methods: ['POST'])]
    public function claimAdTokens(Request $request): JsonResponse
    {
        $this->checkPermission();

        if (!$this->tokenEarningService->isTokenEarningEnabled()) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->translator->trans('pteroca.earn_tokens.feature_disabled')
            ], 400);
        }

        $user = $this->getUser();
        $canClaim = $this->tokenEarningService->canClaimTokens($user, TokenEarningMethodEnum::WATCH_AD);

        if (!$canClaim['can_claim']) {
            return new JsonResponse([
                'success' => false,
                'message' => $canClaim['message']
            ], 429);
        }

        // In a real implementation, you would verify the ad was actually watched
        // through the ad provider's webhook or callback
        $success = $this->tokenEarningService->awardTokens($user, TokenEarningMethodEnum::WATCH_AD, 'Ad watched');

        if ($success) {
            return new JsonResponse([
                'success' => true,
                'message' => $this->translator->trans('pteroca.earn_tokens.tokens_awarded'),
                'new_balance' => $user->getBalance(),
                'amount_awarded' => $this->tokenEarningService->getAmountForMethod(TokenEarningMethodEnum::WATCH_AD)
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'message' => $this->translator->trans('pteroca.earn_tokens.claim_failed')
        ], 400);
    }

    #[Route('/earn-tokens/claim-task', name: 'earn_tokens_claim_task', methods: ['POST'])]
    public function claimTaskTokens(Request $request): JsonResponse
    {
        $this->checkPermission();

        if (!$this->tokenEarningService->isTokenEarningEnabled()) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->translator->trans('pteroca.earn_tokens.feature_disabled')
            ], 400);
        }

        $user = $this->getUser();
        $canClaim = $this->tokenEarningService->canClaimTokens($user, TokenEarningMethodEnum::COMPLETE_TASK);

        if (!$canClaim['can_claim']) {
            return new JsonResponse([
                'success' => false,
                'message' => $canClaim['message']
            ], 429);
        }

        // In a real implementation, you would verify the task was actually completed
        $success = $this->tokenEarningService->awardTokens($user, TokenEarningMethodEnum::COMPLETE_TASK, 'Task completed');

        if ($success) {
            return new JsonResponse([
                'success' => true,
                'message' => $this->translator->trans('pteroca.earn_tokens.tokens_awarded'),
                'new_balance' => $user->getBalance(),
                'amount_awarded' => $this->tokenEarningService->getAmountForMethod(TokenEarningMethodEnum::COMPLETE_TASK)
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'message' => $this->translator->trans('pteroca.earn_tokens.claim_failed')
        ], 400);
    }

    #[Route('/earn-tokens/discord/callback', name: 'earn_tokens_discord_callback')]
    public function discordCallback(Request $request): Response
    {
        $this->checkPermission();

        if (!$this->tokenEarningService->isTokenEarningEnabled()) {
            $this->addFlash('danger', $this->translator->trans('pteroca.earn_tokens.feature_disabled'));
            return $this->redirectToRoute('panel', ['routeName' => 'earn_tokens']);
        }

        $code = $request->query->get('code');
        $error = $request->query->get('error');

        if ($error) {
            $this->addFlash('danger', $this->translator->trans('pteroca.earn_tokens.discord_auth_failed'));
            return $this->redirectToRoute('panel', ['routeName' => 'earn_tokens']);
        }

        if (!$code) {
            $this->addFlash('danger', $this->translator->trans('pteroca.earn_tokens.discord_invalid_code'));
            return $this->redirectToRoute('panel', ['routeName' => 'earn_tokens']);
        }

        $user = $this->getUser();

        // Check if already claimed
        if ($this->tokenEarningService->hasClaimedDiscord($user)) {
            $this->addFlash('warning', $this->translator->trans('pteroca.earn_tokens.discord_already_claimed'));
            return $this->redirectToRoute('panel', ['routeName' => 'earn_tokens']);
        }

        // In a real implementation, you would:
        // 1. Exchange the code for an access token
        // 2. Use the access token to get user's Discord guilds
        // 3. Verify they're in the required server
        // For now, we'll award tokens assuming verification passed
        
        $success = $this->tokenEarningService->awardTokens($user, TokenEarningMethodEnum::JOIN_DISCORD, 'Joined Discord server');

        if ($success) {
            $this->addFlash('success', $this->translator->trans('pteroca.earn_tokens.discord_tokens_awarded'));
        } else {
            $this->addFlash('danger', $this->translator->trans('pteroca.earn_tokens.claim_failed'));
        }

        return $this->redirectToRoute('panel', ['routeName' => 'earn_tokens']);
    }

    #[Route('/earn-tokens/check-status', name: 'earn_tokens_check_status', methods: ['GET'])]
    public function checkStatus(): JsonResponse
    {
        $this->checkPermission();

        $user = $this->getUser();

        $adStatus = $this->tokenEarningService->canClaimTokens($user, TokenEarningMethodEnum::WATCH_AD);
        $taskStatus = $this->tokenEarningService->canClaimTokens($user, TokenEarningMethodEnum::COMPLETE_TASK);

        return new JsonResponse([
            'balance' => $user->getBalance(),
            'ad_can_claim' => $adStatus['can_claim'],
            'ad_message' => $adStatus['message'] ?? null,
            'task_can_claim' => $taskStatus['can_claim'],
            'task_message' => $taskStatus['message'] ?? null,
            'discord_claimed' => $this->tokenEarningService->hasClaimedDiscord($user),
        ]);
    }
}
