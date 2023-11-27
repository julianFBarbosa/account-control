<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    private function getUserValidationRules($accountType)
    {
        if ($accountType == "PERSON") {
            return [
                "agency" => "required",
                "number" => "required",
                "digit" => "required",
                "type" => "required|in:PERSON,COMPANY",
                "cpf" => "required_if:type,PERSON|size:11|unique:accounts,cpf",
            ];
        }
        return [
            "agency" => "required",
            "number" => "required",
            "digit" => "required",
            "type" => "required|in:PERSON,COMPANY",
            "social_reason" => "required_if:type,COMPANY",
            "fantasy_name" => "required_if:type,COMPANY",
            "cnpj" => "required_if:type,COMPANY|size:14|unique:accounts,cnpj",
        ];
    }
    private function getUserValidationMessages($accountType)
    {
        if ($accountType == "PERSON") {
            return [
                "agency.required" => "O Campo agência é obrigatório",
                "number.required" => "O Campo número é obrigatório",
                "digit.required" => "O Campo dígito é obrigatório",
                "type.required" => "O Campo de tipo de conta é obrigatório",
                "cpf.required_if" => "O campo de CPF é obrigatório.",
                "cpf.size" => "Tamanho do CPF é inválido.",
                "cpf.unique" => "Uma conta com esse CPF já existe.",
                "type.in" => "Tipo de conta inválido",
            ];
        }

        return [
            "agency.required" => "O Campo agência é obrigatório",
            "number.required" => "O Campo número é obrigatório",
            "digit.required" => "O Campo dígito é obrigatório",
            "type.required" => "O Campo de tipo de conta é obrigatório",
            "social_reason.required_if" => "O Campo razão social é obrigatório",
            "fantasy_name.required_if" => "O Campo nome fantasia é obrigatório",
            "cnpj.required_if" => "O Campo de CNPJ é obrigatório",
            "cnpj.size" => "Tamanho do CNPJ é inválido.",
            "cnpj.unique" => "Uma conta com esse CNPJ já existe.",
            "type.in" => "Tipo de conta inválido",
        ];
    }

    private function filterAccountDataByType($user, $request): array
    {
        if ($request->type == "PERSON") {
            if ($request->cpf != $user->cpf) {
                return ['error' => "O CPF inserido não corresponde com o salvo no seu perfil"];
            }

            return [
                "user_id" => $user->id,
                "name" => $user->name,
                "cpf" => $user->cpf,
                "agency" => $request->agency,
                "number" => $request->number,
                "digit" => $request->digit,
                "type" => $request->type,
            ];
        }

        if ($request->cnpj != $user->cnpj) {
            return ['error' => "O CNPJ inserido não corresponde com o salvo no seu perfil"];
        }

        return [
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
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $email)
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
        $accountType = $request->type;
        $rules = $this->getUserValidationRules($accountType);
        $messages = $this->getUserValidationMessages($accountType);

        $validator = Validator::make($request->all(), $rules, $messages);

        $errors = $validator->errors();

        if (sizeof($errors)) {
            return response()->json(['errors' => $errors], 401);
        }

        $user = User::where('email', $email)->first();

        $data = $this->filterAccountDataByType($user, $request);

        if (array_key_exists("error", $data)) {
            return response()->json(['errors' => $data["error"]], 401);
        }

        Account::create($data);

        return response()->json(['account' => $data], 201);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $email)
    {
        dd($email);
        $account = User::where("email", $email)->get();
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
