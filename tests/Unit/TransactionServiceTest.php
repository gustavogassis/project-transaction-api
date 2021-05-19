<?php

namespace Tests\Unit;

use App\Exceptions\TransactionException;
use App\Exceptions\UserException;
use App\Models\User;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Services\AuthorizationService;
use App\Services\NotificationService;
use App\Services\TransactionService;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    private UserRepository $userRepositoryMock;
    private TransactionRepository $transactionRepositoryMock;
    private AuthorizationService $authorizationServiceMock;
    private NotificationService $notificationServiceMock;
    private TransactionService $instance;

    public function setUp(): void
    {
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->transactionRepositoryMock = $this->createMock(TransactionRepository::class);
        $this->authorizationServiceMock = $this->createMock(AuthorizationService::class);
        $this->notificationServiceMock = $this->createMock(NotificationService::class);

        $this->instance = new TransactionService(
            $this->userRepositoryMock,
            $this->transactionRepositoryMock,
            $this->authorizationServiceMock,
            $this->notificationServiceMock
        );
    }

    public function testCreateATransactionWithAnInexistentUserShouldReturnAnException(): void
    {
        $value = 28.00;
        $payerId = 5;
        $payeeId = 2;

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('find')
            ->with($payerId)
            ->will($this->throwException(UserException::forUserNotFound($payerId)));

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("O usuário com o id 5 não foi encontrado.");

        $this->instance->makeTransaction($value, $payerId, $payeeId);
    }

    public function testCreateATransactionWithInsufficientBalanceShouldReturnAnException(): void
    {
        $value = 66.0;
        $payerId = 5;
        $payeeId = 2;

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('hasBalance')
            ->with($payerId, $value)
            ->willReturn(false);

        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage(
            "O usuário com id 5 não possui saldo suficiente para transferir o valor de 66,00 reais."
        );

        $this->instance->makeTransaction($value, $payerId, $payeeId);
    }

    public function testCreateATransactionForTheSameUserShouldReturnAnException(): void
    {
        $value = 45.0;
        $payerId = 3;
        $payeeId = 3;

        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage("O usuário 3 não pode transferir para ele mesmo.");

        $this->instance->makeTransaction($value, $payerId, $payeeId);
    }

    public function testCreateATransactionWithUnauthorizedUserShouldReturnAnException(): void
    {
        $value = 17.0;
        $payerId = 4;
        $payeeId = 3;

        $userMock = $this->createMock(User::class);

        $userMock->method('isLojista')->willReturn(true);

        $this->userRepositoryMock
            ->expects($this->any())
            ->method('find')
            ->willReturn($userMock);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('hasBalance')
            ->with($payerId, $value)
            ->willReturn(true);

        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage("O tipo do usuário 4 não pode realizar uma transação.");

        $this->instance->makeTransaction($value, $payerId, $payeeId);
    }

    public function testCreateAnUnauthorizedTransactionShouldReturnAnException(): void
    {
        $value = 7.0;
        $payerId = 2;
        $payeeId = 8;

        $this->authorizationServiceMock
            ->expects($this->once())
            ->method('checkTransactionAuthorization')
            ->willReturn(false);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('hasBalance')
            ->with($payerId, $value)
            ->willReturn(true);

        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage(
            "A transação entre os usuários 2 e 8 no valor de 7,00 reais não foi autorizada"
        );

        $this->instance->makeTransaction($value, $payerId, $payeeId);
    }
}
