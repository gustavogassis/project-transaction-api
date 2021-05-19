<?php

namespace App\Http\Controllers;

use App\Exceptions\DomainError;
use App\Services\TransactionService;
use Arr;
use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    private TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'value' => 'required|numeric|between:0.01,10000',
                'payer' => 'required|numeric',
                'payee' => 'required|numeric'
            ]);

            $data = $request->all();

            $value = $data['value'];
            $payer = $data['payer'];
            $payee = $data['payee'];

            $transactionId = $this->transactionService->makeTransaction($value, $payer, $payee);

            return response()->json([
                'success' => true,
                "transaction_id" => $transactionId
            ], 201);
        } catch (DomainError $e) {
            return response()->json([
                "success" => false,
                "type" => $e->getType(),
                "message" => $e->getMessage()
            ], $e->getCode());
        } catch (ValidationException $e) {
            return response()->json([
                "success" => false,
                "type" => "erro_de_validacao",
                "message" => "Houve um erro de validação.",
                "errors" => Arr::flatten($e->errors())
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "type" => "erro_interno",
                "message" => "Houve um erro interno no sistema."
            ], 500);
        }
    }
}
