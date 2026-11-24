<?php

namespace iEducar\Packages\BuritiAppsApi\Http\Controllers;

use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AppsAuthController
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
            'perfil' => 'required|in:familia,professor',
            'device_name' => 'nullable|string|max:120',
        ]);

        /** @var User|null $user */
        $user = User::query()
            ->whereHas('employee', static fn ($q) => $q->where('matricula', $data['login']))
            ->first();

        if ($user === null || ! $this->passwordMatches($user, $data['password'])) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        if ((int) ($user->employee->ativo ?? 0) !== 1) {
            return response()->json(['message' => 'Usuário inativo'], 403);
        }

        $device = $data['device_name'] ?? ('apps-'.$data['perfil']);
        $token = $user->createToken($device, [$data['perfil']]);

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'perfil' => $data['perfil'],
            'user' => [
                'id' => $user->id,
                'nome' => $user->name,
                'login' => $user->login,
                'email' => $user->email,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user !== null && method_exists($user, 'currentAccessToken')) {
            $user->currentAccessToken()?->delete();
        }

        return response()->json(['ok' => true]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $abilities = [];
        if (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
            $abilities = $user->currentAccessToken()->abilities ?? [];
        }

        return response()->json([
            'id' => $user->id,
            'nome' => $user->name,
            'login' => $user->login,
            'email' => $user->email,
            'perfil' => $abilities[0] ?? null,
            'abilities' => $abilities,
        ]);
    }

    private function passwordMatches(User $user, string $plain): bool
    {
        $hash = (string) $user->getAuthPassword();

        if ($hash === '') {
            return false;
        }

        if (Hash::check($plain, $hash)) {
            return true;
        }

        // legado md5 (LegacyUserProvider)
        return md5($plain) === $hash;
    }
}
