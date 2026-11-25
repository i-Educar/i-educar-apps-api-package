<?php

namespace iEducar\Packages\BuritiAppsApi\Http\Controllers;

use App\User;
use iEducar\Packages\BuritiAppsApi\Services\AppsGovernancaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AppsAuthController
{
    public function __construct(private readonly AppsGovernancaService $gov) {}

    /**
     * Status público — o app Android consulta antes/depois do login.
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'apps_habilitados' => $this->gov->appsHabilitados(),
            'require_login' => (bool) config('apps-api.require_login', true),
            'mensagem' => $this->gov->appsHabilitados() ? null : $this->gov->mensagemSuspensao(),
            'auth' => 'sanctum',
            'perfis' => ['familia', 'professor', 'admin'],
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        if (! $this->gov->appsHabilitados()) {
            return response()->json([
                'message' => $this->gov->mensagemSuspensao(),
                'code' => 'apps_api_disabled',
            ], 503);
        }

        $data = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
            'perfil' => 'required|in:familia,professor,admin',
            'device_name' => 'required|string|min:3|max:120',
        ]);

        /** @var User|null $user */
        $user = User::query()
            ->whereHas('employee', static fn ($q) => $q->where('matricula', $data['login']))
            ->first();

        if ($user === null || ! $this->passwordMatches($user, $data['password'])) {
            return response()->json(['message' => 'Credenciais inválidas', 'code' => 'invalid_credentials'], 401);
        }

        if (! $this->gov->usuarioPodeUsarApp($user)) {
            return response()->json(['message' => 'Usuário suspenso ou inativo', 'code' => 'user_suspended'], 403);
        }

        if ($data['perfil'] === 'admin' && (! method_exists($user, 'isAdmin') || ! $user->isAdmin())) {
            return response()->json(['message' => 'Perfil admin negado', 'code' => 'forbidden_perfil'], 403);
        }

        // Um dispositivo = um token apps ativo por perfil (revoga anteriores do mesmo device)
        if (method_exists($user, 'tokens')) {
            $user->tokens()
                ->where('name', $data['device_name'])
                ->delete();
        }

        $ttl = (int) config('apps-api.token_ttl_minutes', 60 * 24 * 30);
        $expires = $ttl > 0 ? now()->addMinutes($ttl) : null;
        $token = $user->createToken($data['device_name'], [$data['perfil']], $expires);

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'perfil' => $data['perfil'],
            'expires_at' => $expires?->toIso8601String(),
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

    public function logoutAll(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $n = 0;
        if (method_exists($user, 'tokens')) {
            $n = (int) $user->tokens()->count();
            $user->tokens()->delete();
        }

        return response()->json(['ok' => true, 'tokens_revogados' => $n]);
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
            'ativo' => $this->gov->usuarioPodeUsarApp($user),
            'apps_habilitados' => $this->gov->appsHabilitados(),
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

        return md5($plain) === $hash;
    }
}
