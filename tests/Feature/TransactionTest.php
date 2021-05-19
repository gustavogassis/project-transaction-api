<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function testTransactionAreCreatedCorrectly(): void
    {
        $this->seed();

        $payload = [
            'value' => 20.00,
            'payer' =>  1,
            'payee' => 5
        ];

        $this->json('POST', '/api/transaction', $payload)
            ->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'transaction_id'
        ]);
    }

    public function testTryCreateATransactionWithInsufficientBalanceShouldReturnAnErrorMessage(): void
    {
        $this->seed();

        $payload = [
            'value' => 50.00,
            'payer' =>  3,
            'payee' => 4
        ];

        $this->json('POST', '/api/transaction', $payload)
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'type' => 'saldo_insuficiente',
                'message' => 'O usuário com id 3 não possui saldo suficiente para transferir o valor de 50,00 reais.'
        ]);
    }

    public function testTryCreateATransactionWithUnauthorizedUserShouldReturnAnErrorMessage(): void
    {
        $this->seed();

        $payload = [
            'value' => 35.00,
            'payer' =>  4,
            'payee' => 2
        ];

        $this->json('POST', '/api/transaction', $payload)
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'type' => 'usuario_nao_autorizado',
                'message' => 'O tipo do usuário 4 não pode realizar uma transação.'
        ]);
    }

    public function testTryCreateATransactionForTheSameUserShouldReturnAnErrorMessage(): void
    {
        $this->seed();

        $payload = [
            'value' => 28.00,
            'payer' =>  1,
            'payee' => 1
        ];

        $this->json('POST', '/api/transaction', $payload)
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'type' => 'transacao_para_o_mesmo_usuario',
                'message' => 'O usuário 1 não pode transferir para ele mesmo.'
        ]);
    }

    public function testTryCreateATransactionWithAnInvalidTypeFieldShouldReturnAnErrorMessage(): void
    {
        $this->seed();

        $payload = [
            'value' => 28.00,
            'payer' =>  "Jon Snow",
            'payee' => 5
        ];

        $this->json('POST', '/api/transaction', $payload)
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'type' => 'erro_de_validacao',
                'message' => 'Houve um erro de validação.',
                'errors' => [
                    "The payer must be a number."
                ]
        ]);
    }

    public function testTryCreateATransactionWithoutAFieldShouldReturnAnErrorMessage(): void
    {
        $this->seed();

        $payload = [
            'value' => 28.00,
            'payer' =>  2
        ];

        $this->json('POST', '/api/transaction', $payload)
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'type' => 'erro_de_validacao',
                'message' => 'Houve um erro de validação.',
                'errors' => [
                    "The payee field is required."
                ]
        ]);
    }

    public function testTryCreateATransactionWithAnInvalidValueShouldReturnAnErrorMessage(): void
    {
        $this->seed();

        $payload = [
            'value' => -17.00,
            'payer' =>  2,
            'payee' => 3
        ];

        $this->json('POST', '/api/transaction', $payload)
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'type' => 'erro_de_validacao',
                'message' => 'Houve um erro de validação.',
                'errors' => [
                    "The value must be between 0.01 and 10000."
                ]
        ]);
    }

    public function testTryCreateATransactionWithoutAllFieldsShouldReturnAnErrorMessage(): void
    {
        $this->seed();

        $payload = [];

        $this->json('POST', '/api/transaction', $payload)
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'type' => 'erro_de_validacao',
                'message' => 'Houve um erro de validação.',
                'errors' => [
                    "The value field is required.",
                    "The payer field is required.",
                    "The payee field is required."
                ]
        ]);
    }
}
