<?php

namespace App\Repositories;

use App\Models\Transaction;
use Ramsey\Uuid\Uuid;

class TransactionRepository
{
    public function save(array $data): void
    {
        $transaction = new Transaction($data);
        $transaction->save();
    }

    public function nextUuid(): string
    {
        return Uuid::uuid4();
    }
}
