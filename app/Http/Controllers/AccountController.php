<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    private function findItem($array, $id)
    {
        $item = $array->{$id};

        return $item;
    }

    private function validate_user($email)
    {
        $user = User::where("email", $email)->first();

        if (!$user) {
            $user->error = "Esse usuário não existe.";
            return $user;
        }

        $hasEqualCpf = Account::where("cpf", $user->cpf)->first();

        if ($hasEqualCpf) {
            $user->error = "CPF já está vinculado a outra conta";
            return $user;
        }

        $hasEqualCnpj = Account::where("cnpj", $user->cnpj)->first();

        if ($hasEqualCnpj) {
            $user->error = "CNPJ já está vinculado a outra conta";
            return $user;
        }

        return $user;
    }
    private function validate_account($request, $user)
    {
        $account = Account::where('user_id', $user->id)
            ->where("type", $request->type)
            ->first();

        // if (!$account) {
        //     return false;
        // }

        if ($request->type == "PERSON") {
            $requiredFields = [
                "agency" => "agency",
                "number" => "number",
                "digit" => "digit",
                "cpf" => "cpf"
            ];

            foreach ($requiredFields as $key => $requiredField) {
                $field = $this->findItem($request, $requiredField);

                if (!$field) {
                    return ['error' => "Campo {$key} é obrigatório."];
                }
            }
        }

        return $account;
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $_request, $email)
    {
        $accounts = Account::join("users", "accounts.user_id", '=', "users.id")
            ->select("accounts.*", 'users.*')
            ->where('users.email', $email)
            ->get();

        return response()->json($accounts, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $email)
    {
        $user = $this->validate_user($email);

        if ($user->error) {
            return response()->json(['error' => $user->error], 401);
        };

        $accountIsInvalid = $this->validate_account($request, $user);

        if ($accountIsInvalid) {
            return response()->json(['error' => $accountIsInvalid], 401);
        };

        try {
            $request->validate([
                "agency" => "required",
                "number" => "required",
                "digit" => "required",
                "type" => "required|in:PERSON,COMPANY",
                "social_reason" => "required",
                "fantasy_name" => "required"
            ], [
                "agency.required" => "O Campo agência é obrigatório",
                "number.required" => "O Campo número é obrigatório",
                "digit.required" => "O Campo dígito é obrigatório",
                "type.required" => "O Campo de tipo de conta é obrigatório",
                "social_reason.required" => "O Campo razão social é obrigatório",
                "fantasy_name.required" => "O Campo nome fantasia é obrigatório",
                "type.in" => "Tipo de conta inválido",
            ]);

            if ($request->type == "PERSON") {
                $account = User::where('email', $email)->first();

                $data = [
                    "user_id" => $user->id,
                    "name" => $user->name,
                    "cpf" => $user->cpf,
                    "agency" => $request->agency,
                    "number" => $request->number,
                    "digit" => $request->digit,
                    "type" => $request->type,
                ];
                // Account::create($data);
                return response()->json(['account' => $data], 201);
            }

            if ($request->type == "COMPANY") {
                $data = [
                    "user_id" => $user->id,
                    "name" => $user->name,
                    "agency" => $request->agency,
                    "number" => $request->number,
                    "digit" => $request->digit,
                    "type" => $request->type,
                    "social_reason" => $request->social_reason,
                    "fantasy_name" => $request->fantasy_name,
                    "cnpj" => $request->cnpj,
                ];

                // Account::create($data);
                return response()->json(['account' => $data], 201);
            }
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAccountRequest $request, Account $account)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        //
    }
}
