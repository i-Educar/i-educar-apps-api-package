<?php

namespace iEducar\Packages\BuritiAppsApi\Http\Controllers;

use iEducar\Packages\BuritiAppsApi\Services\EquipeOperacaoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppsEquipeController
{
    public function painel(EquipeOperacaoService $svc): JsonResponse
    {
        return response()->json([
            'escopo' => 'equipe',
            'painel' => $svc->painel(),
        ]);
    }

    public function almoxPedidos(EquipeOperacaoService $svc): JsonResponse
    {
        $data = $svc->almoxPedidos();

        return response()->json([
            'escopo' => 'equipe',
            'data' => $data,
            'meta' => ['total' => count($data)],
        ]);
    }

    public function almoxPedido(int $id, EquipeOperacaoService $svc): JsonResponse
    {
        $pedido = $svc->almoxPedido($id);
        if ($pedido === null) {
            return response()->json(['message' => 'Pedido não encontrado', 'code' => 'not_found'], 404);
        }

        return response()->json(['escopo' => 'equipe', 'data' => $pedido]);
    }

    public function almoxQr(int $id, EquipeOperacaoService $svc): JsonResponse
    {
        $out = $svc->almoxQr($id);
        if (empty($out['ok'])) {
            return response()->json($out + ['code' => 'almox_qr_erro'], 422);
        }

        return response()->json(['escopo' => 'equipe'] + $out);
    }

    public function almoxEtiquetas(int $id, EquipeOperacaoService $svc): JsonResponse
    {
        $out = $svc->almoxEtiquetas($id);
        if (empty($out['ok'])) {
            return response()->json($out + ['code' => 'almox_etiquetas_erro'], 404);
        }

        return response()->json(['escopo' => 'equipe'] + $out);
    }

    public function almoxConfirmar(Request $request, EquipeOperacaoService $svc): JsonResponse
    {
        $data = $request->validate([
            'token' => 'required|string|min:8|max:120',
        ]);
        $userId = $request->user()?->getKey();
        $out = $svc->almoxConfirmarPorToken($data['token'], $userId ? (int) $userId : null);
        if (! $out['ok']) {
            $code = (string) ($out['code'] ?? 'almox_confirm_erro');
            $status = match ($code) {
                'nao_autenticado' => 401,
                'sem_permissao_entrega' => 403,
                default => 422,
            };

            return response()->json($out + ['code' => $code], $status);
        }

        return response()->json(['escopo' => 'equipe'] + $out);
    }

    public function almoxAssinar(Request $request, int $id, EquipeOperacaoService $svc): JsonResponse
    {
        $data = $request->validate([
            'etapa' => 'required|string|in:separacao,aceite,entrega,conferencia',
            'observacao' => 'nullable|string|max:2000',
        ]);
        $userId = $request->user()?->getKey();
        if (! $userId) {
            return response()->json(['ok' => false, 'message' => 'Não autenticado', 'code' => 'nao_autenticado'], 401);
        }
        $out = $svc->almoxAssinarEtapa(
            $id,
            (string) $data['etapa'],
            (int) $userId,
            isset($data['observacao']) ? (string) $data['observacao'] : null,
        );
        if (! $out['ok']) {
            $code = (string) ($out['code'] ?? 'almox_assinar_erro');
            $status = match ($code) {
                'nao_autenticado' => 401,
                'sem_permissao_etapa' => 403,
                default => 422,
            };

            return response()->json($out + ['code' => $code], $status);
        }

        return response()->json(['escopo' => 'equipe'] + $out);
    }

    public function almoxSaldos(EquipeOperacaoService $svc): JsonResponse
    {
        $data = $svc->almoxSaldos();

        return response()->json([
            'escopo' => 'equipe',
            'data' => $data,
            'meta' => ['total' => count($data)],
        ]);
    }

    public function bibliotecaEmprestimos(EquipeOperacaoService $svc): JsonResponse
    {
        $data = $svc->bibliotecaEmprestimos();

        return response()->json([
            'escopo' => 'equipe',
            'data' => $data,
            'meta' => ['total' => count($data)],
        ]);
    }

    public function contratacoesProcessos(EquipeOperacaoService $svc): JsonResponse
    {
        $data = $svc->contratacoesProcessos();

        return response()->json([
            'escopo' => 'equipe',
            'data' => $data,
            'meta' => ['total' => count($data)],
        ]);
    }
}
