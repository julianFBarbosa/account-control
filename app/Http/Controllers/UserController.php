<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has("name")) {
            $filteredUsers = User::where('name', 'LIKE', '%' . $request->get("name") . '%')
                ->get();

            return response()->json($filteredUsers);
        }

        if ($request->has("document")) {
            $filteredUsers = User::where('cpf', 'LIKE', '%' . $request->get("document") . '%')
                ->orWhere('cnpj', 'LIKE', '%' . $request->get("document") . '%')
                ->get();

            return response()->json($filteredUsers);
        }

        $users = User::all();
        return response()->json($users);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|max:255',
                'cpf' => 'required|unique:users,cpf|size:11',
                'cnpj' => 'required|unique:users,cnpj|size:14',
                'phone' => 'required|size:11',
                'password' => 'required',
                'email' => 'required|email|unique:users,email',
            ], [
                'name.required' => 'Campo nome não pode ser vazio.',
                'cpf.required' => 'Campo cpf não pode ser vazio.',
                'phone.required' => 'Campo phone não pode ser vazio.',
                'password.required' => 'Campo password não pode ser vazio.',
                'email.required' => 'Campo email não pode ser vazio.',
                'email.email' => 'O email inserido é inválido.',
                'name.max' => 'Campo nome não pode ter mais de 255 caracteres.',
                'cpf.unique' => 'Esse CPF já existe.',
                'cpf.size' => 'tamanho de cpf inválido.',
                'cnpj.required' => 'Campo CNPJ não pode ser vazio.',
                'cnpj.unique' => 'Esse CNPJ já existe.',
                'cnpj.size' => 'tamanho de CNPJ inválido.',
                'phone.size' => 'tamanho de número de telefone inválido.',
                'email.unique' => 'Esse e-mail já existe.',
            ]);

            $data = $request->all();

            User::create($data);

            return response()->json(['user' => $data], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $email)
    {
        $user = User::where('email', $email)->first();
        $emailExists = User::where('email', $request->email)->first();

        if ($emailExists) {
            return response()
                ->json(['error' => "O Email escolhido já está em uso"], 401);
        }

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($email)
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->delete();
        }

        return response()->json(['message' => 'Usuário removido com sucesso.']);
    }
}
