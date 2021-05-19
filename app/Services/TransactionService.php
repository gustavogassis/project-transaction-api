<?php

namespace App\Services;

use App\Exceptions\TransactionException;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Services\AuthorizationService;
use App\Services\NotificationService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    private UserRepository $userRepository;
    private TransactionRepository $transactionRepository;
    private AuthorizationService $authorizationService;
    private NotificationService $notificationService;

    public function __construct(
        UserRepository $userRepository,
        TransactionRepository $transactionRepository,
        AuthorizationService $authorizationService,
        NotificationService $notificationService
    ) {
        $this->userRepository = $userRepository;
        $this->transactionRepository = $transactionRepository;
        $this->authorizationService = $authorizationService;
        $this->notificationService = $notificationService;
    }

    public function makeTransaction(float $value, int $payerId, int $payeeId): string
    {
        if ($payerId === $payeeId) {
            throw TransactionException::forTransactionToSameUser($payerId);
        }

        $payer = $this->userRepository->find($payerId);
        $payee = $this->userRepository->find($payeeId);

        if (!$this->userRepository->hasBalance($payerId, $value)) {
            throw TransactionException::forInsufficientBalance($payerId, $value);
        }

        if ($payer->isLojista()) {
            throw TransactionException::forUserTypeNotAllowedToMakeTransaction($payerId);
        }

        $isAuthorized = $this->authorizationService->checkTransactionAuthorization($payerId, $payeeId, $value);

        if (!$isAuthorized) {
            throw TransactionException::forUnauthorizedTransaction($payerId, $payeeId, $value);
        }

        $transactionId = $this->transactionRepository->nextUuid();

        DB::beginTransaction();

        try {
            $this->transactionRepository->save([
                'payer_id' => $payerId,
                'payee_id' => $payeeId,
                'value' => -$value,
                'transaction_id' => $transactionId,
                'status' => 'paid'
            ]);

            $this->transactionRepository->save([
                'payer_id' => $payeeId,
                'payee_id' => $payerId,
                'value' => $value,
                'transaction_id' => $transactionId,
                'status' => 'received'
            ]);

            $this->userRepository->updateBalance($payerId);
            $this->userRepository->updateBalance($payeeId);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        DB::commit();

        try {
            $this->notificationService->notifyUser($payee, $value);
        } catch (Exception $e) {
            Log::error(sprintf(
                'Houve um erro ao notificar o usuário %d sobre a transação %s.',
                $payeeId,
                $transactionId
            ));
        }

        return $transactionId;
    }
}
