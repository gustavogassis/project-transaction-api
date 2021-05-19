<?php

namespace App\Repositories;

use App\Exceptions\UserException;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserRepository
{
    public function userExists(int $id): bool
    {
        $user = User::find($id);

        return $user !== null;
    }

    public function hasBalance(int $id, float $transferValue): bool
    {
        $balance = User::find($id)->balance;

        return $transferValue <= $balance;
    }

    public function find($id): User
    {
        $user = User::find($id);

        if ($user === null) {
            throw UserException::forUserNotFound($id);
        }

        return $user;
    }

    public function updateBalance($id): void
    {
        $user = User::find($id);
        $incrementBalance = DB::table('transactions')->where('payer_id', '=', $id)->sum('value');
        $user->balance = $incrementBalance;

        $user->save();
    }
}
