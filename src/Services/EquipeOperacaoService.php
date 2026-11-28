<?php

namespace iEducar\Packages\BuritiAppsApi\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Operações da equipe técnica no app unificado (almox, biblioteca, ACT…).
 * Orquestra pacotes de domínio sem duplicar regras.
 */
final class EquipeOperacaoService
{
    /** @return array<string, mixed> */
    public function painel(): array
    {
        return [
            'modulos' => [
                'almoxarifado' => class_exists(\iEducar\Packages\BuritiAlmoxarifadoEscolar\Services\PedidoCarrinho::class),
                'biblioteca' => Schema::hasTable('biblioteca_escolar.poc_registro')
                    || Schema::hasTable('biblioteca.poc_registro')
                    || $this->temSchema('biblioteca_escolar')
                    || $this->temSchema('biblioteca'),
                'contratacoes' => class_exists(\iEducar\Packages\BuritiContratacoesTemporarias\Services\PocRegistro::class)
                    || $this->temSchema('contratacoes_temporarias')
                    || $this->temSchema('contratacoes'),
                'assinatura' => class_exists(\iEducar\Packages\BuritiAssinaturaDigital\Support\Processos::class),
            ],
            'atalhos' => [
                ['chave' => 'almox.qr', 'rotulo' => 'Ler QR de remessa', 'path' => '/equipe/almox/confirmar'],
                ['chave' => 'almox.pedidos', 'rotulo' => 'Pedidos / notas', 'path' => '/equipe/almox/pedidos'],
                ['chave' => 'almox.etiquetas', 'rotulo' => 'Etiquetas', 'path' => '/equipe/almox/pedidos/{id}/etiquetas'],
                ['chave' => 'biblioteca', 'rotulo' => 'Empréstimos', 'path' => '/equipe/biblioteca/emprestimos'],
                ['chave' => 'act', 'rotulo' => 'Contratações', 'path' => '/equipe/contratacoes/processos'],
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    public function almoxPedidos(int $limite = 80): array
    {
        if (! class_exists(\iEducar\Packages\BuritiAlmoxarifadoEscolar\Services\PocRegistro::class)) {
            return [];
        }
        $out = [];
        foreach (\iEducar\Packages\BuritiAlmoxarifadoEscolar\Services\PocRegistro::listar('pedido', $limite) as $r) {
            $p = $r['payload'] ?? [];
            $out[] = [
                'id' => (int) $r['id'],
                'escola' => (string) ($p['escola'] ?? '—'),
                'status' => (string) ($p['status'] ?? ''),
                'itens' => count($p['itens'] ?? []),
                'setor' => (string) ($p['setor'] ?? ''),
                'solicitante' => (string) ($p['solicitante'] ?? ''),
                'fechado_em' => (string) ($p['fechado_em'] ?? ''),
                'qr_token' => ! empty($p['qr_token']),
                'created_at' => (string) ($r['created_at'] ?? ''),
            ];
        }

        return $out;
    }

    /** @return array<string, mixed>|null */
    public function almoxPedido(int $id): ?array
    {
        if (! class_exists(\iEducar\Packages\BuritiAlmoxarifadoEscolar\Services\PedidoCarrinho::class)) {
            return null;
        }
        try {
            $pedido = \iEducar\Packages\BuritiAlmoxarifadoEscolar\Services\PedidoCarrinho::obter($id);
        } catch (Throwable) {
            return null;
        }
        $p = $pedido['payload'] ?? [];

        return [
            'id' => (int) $pedido['id'],
            'status' => (string) ($p['status'] ?? ''),
            'escola' => (string) ($p['escola'] ?? '—'),
            'setor' => (string) ($p['setor'] ?? ''),
            'solicitante' => (string) ($p['solicitante'] ?? ''),
            'itens' => array_values($p['itens'] ?? []),
            'itens_nao_atendidos' => array_values($p['itens_nao_atendidos'] ?? []),
            'assinaturas' => $p['assinaturas'] ?? [],
            'baixa_estoque' => ! empty($p['baixa_estoque']),
            'nota_url' => url('/almoxarifado-escolar/pedidos/'.$id.'/nota'),
            'pdf_url' => url('/almoxarifado-escolar/pedidos/'.$id.'/nota/pdf'),
        ];
    }

    /** @return array<string, mixed> */
    public function almoxQr(int $id): array
    {
        if (! class_exists(\iEducar\Packages\BuritiAlmoxarifadoEscolar\Services\QrCodeRemessa::class)) {
            return ['ok' => false, 'message' => 'Módulo almoxarifado indisponível'];
        }
        try {
            $emit = \iEducar\Packages\BuritiAlmoxarifadoEscolar\Services\QrCodeRemessa::emitir($id);
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }

        return [
            'ok' => true,
            'pedido_id' => $emit['pedido_id'],
            'token' => $emit['token'],
            'codigo' => $emit['codigo'],
            'url_confirmacao' => $emit['url'],
            'api_path' => $emit['api_path'] ?? '/api/apps/v1/equipe/almox/confirmar',
            'api_method' => $emit['api_method'] ?? 'POST',
            'escola' => $emit['escola'],
            'status' => $emit['status'],
            'itens' => $emit['itens'],
            // Canônico para o APP: POST autenticado + permissão de entrega.
            'payload_scan' => $emit['payload_scan'] ?? [
                'tipo' => 'almox_confirmar_recebimento',
                'token' => $emit['token'],
                'endpoint' => '/api/apps/v1/equipe/almox/confirmar',
                'metodo' => 'POST',
            ],
        ];
    }

    /** @return array{ok:bool,message:string,pedido_id?:int,code?:string} */
    public function almoxConfirmarPorToken(string $token, ?int $usuarioId = null): array
    {
        if (! class_exists(\iEducar\Packages\BuritiAlmoxarifadoEscolar\Services\QrCodeRemessa::class)) {
            return ['ok' => false, 'message' => 'Módulo almoxarifado indisponível'];
        }
        $token = trim($token);
        $pedidoId = \iEducar\Packages\BuritiAlmoxarifadoEscolar\Services\QrCodeRemessa::validarToken($token);
        if ($pedidoId === null) {
            return ['ok' => false, 'message' => 'QR/token inválido ou expirado', 'code' => 'token_invalido'];
        }
        if ($usuarioId === null || $usuarioId < 1) {
            return ['ok' => false, 'message' => 'Usuário não autenticado', 'code' => 'nao_autenticado', 'pedido_id' => $pedidoId];
        }
        if (class_exists(\iEducar\Packages\BuritiAlmoxarifadoEscolar\Support\PermissoesAlmoxarifado::class)
            && class_exists(\App\User::class)) {
            $user = \App\User::query()->find($usuarioId);
            if (! $user instanceof \App\User
                || ! \iEducar\Packages\BuritiAlmoxarifadoEscolar\Support\PermissoesAlmoxarifado::podeAssinar($user, 'entrega')) {
                return [
                    'ok' => false,
                    'message' => 'Sem permissão para confirmar entrega (assinatura via QR/API)',
                    'code' => 'sem_permissao_entrega',
                    'pedido_id' => $pedidoId,
                ];
            }
        }
        $ok = \iEducar\Packages\BuritiAlmoxarifadoEscolar\Services\QrCodeRemessa::confirmarRecebimento($token, $usuarioId);

        return $ok
            ? ['ok' => true, 'message' => 'Recebimento confirmado (entrega)', 'pedido_id' => $pedidoId]
            : ['ok' => false, 'message' => 'Não foi possível confirmar o recebimento', 'pedido_id' => $pedidoId];
    }

    /**
     * Assina etapa do pedido (APP) — mesmos gates da web.
     *
     * @return array{ok:bool,message:string,pedido_id?:int,code?:string}
     */
    public function almoxAssinarEtapa(int $pedidoId, string $etapa, int $usuarioId, ?string $observacao = null): array
    {
        if (! class_exists(\iEducar\Packages\BuritiAlmoxarifadoEscolar\Services\PedidoCarrinho::class)) {
            return ['ok' => false, 'message' => 'Módulo almoxarifado indisponível'];
        }
        if (! class_exists(\App\User::class)) {
            return ['ok' => false, 'message' => 'Usuário indisponível', 'code' => 'nao_autenticado'];
        }
        $user = \App\User::query()->find($usuarioId);
        if (! $user instanceof \App\User) {
            return ['ok' => false, 'message' => 'Usuário não autenticado', 'code' => 'nao_autenticado'];
        }
        if (! \iEducar\Packages\BuritiAlmoxarifadoEscolar\Support\PermissoesAlmoxarifado::podeAssinar($user, $etapa)) {
            return [
                'ok' => false,
                'message' => 'Sem permissão para a etapa '.$etapa,
                'code' => 'sem_permissao_etapa',
                'pedido_id' => $pedidoId,
            ];
        }
        $dados = [
            'id' => (int) $user->getAuthIdentifier(),
            'nome' => (string) ($user->name ?? $user->login ?? $user->email ?? 'Usuário'),
            'login' => isset($user->login) ? (string) $user->login : null,
        ];
        try {
            \iEducar\Packages\BuritiAlmoxarifadoEscolar\Services\PedidoCarrinho::assinar(
                $pedidoId,
                $etapa,
                $dados['nome'],
                $observacao,
                $dados['id'],
                $dados['login'],
            );
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage(), 'pedido_id' => $pedidoId];
        }

        return [
            'ok' => true,
            'message' => 'Etapa registrada',
            'pedido_id' => $pedidoId,
            'etapa' => $etapa,
        ];
    }

    /** @return array<string, mixed> */
    public function almoxEtiquetas(int $id): array
    {
        $pedido = $this->almoxPedido($id);
        if ($pedido === null) {
            return ['ok' => false, 'message' => 'Pedido não encontrado'];
        }
        $etiquetas = [];
        foreach ($pedido['itens'] as $i => $item) {
            $etiquetas[] = [
                'seq' => $i + 1,
                'sku' => (string) ($item['sku'] ?? ''),
                'titulo' => (string) ($item['titulo'] ?? ''),
                'quantidade' => (int) ($item['quantidade'] ?? 1),
                'escola' => $pedido['escola'],
                'pedido_id' => $id,
                'codigo_barras' => strtoupper((string) ($item['sku'] ?? 'PED'.$id.'-'.($i + 1))),
            ];
        }

        return [
            'ok' => true,
            'pedido_id' => $id,
            'escola' => $pedido['escola'],
            'etiquetas' => $etiquetas,
            'print_url' => url('/almoxarifado-escolar/pedidos/'.$id.'/etiquetas'),
        ];
    }

    /** @return list<array<string, mixed>> */
    public function almoxSaldos(int $limite = 100): array
    {
        if (! class_exists(\iEducar\Packages\BuritiAlmoxarifadoEscolar\Services\PocRegistro::class)) {
            return [];
        }
        $out = [];
        foreach (\iEducar\Packages\BuritiAlmoxarifadoEscolar\Services\PocRegistro::listar('item', $limite) as $r) {
            $p = $r['payload'] ?? [];
            $out[] = [
                'id' => (int) $r['id'],
                'sku' => (string) ($p['sku'] ?? $r['titulo'] ?? ''),
                'titulo' => (string) ($p['titulo'] ?? $r['titulo'] ?? ''),
                'saldo' => (int) ($p['saldo'] ?? 0),
                'minimo' => (int) ($p['minimo'] ?? 0),
                'unidade' => (string) ($p['unidade'] ?? 'un'),
            ];
        }

        return $out;
    }

    /** @return list<array<string, mixed>> */
    public function bibliotecaEmprestimos(int $limite = 80): array
    {
        $tabela = null;
        foreach (['biblioteca_escolar.poc_registro', 'biblioteca.poc_registro'] as $cand) {
            if (Schema::hasTable($cand)) {
                $tabela = $cand;
                break;
            }
        }
        if ($tabela === null) {
            return [];
        }
        $out = [];
        try {
            $rows = DB::table($tabela)->where('tipo', 'emprestimo')->orderByDesc('id')->limit($limite)->get();
        } catch (Throwable) {
            return [];
        }
        foreach ($rows as $r) {
            $p = is_string($r->payload ?? null) ? (json_decode((string) $r->payload, true) ?: []) : (array) ($r->payload ?? []);
            $out[] = [
                'id' => (int) $r->id,
                'titulo' => (string) ($p['titulo'] ?? $r->titulo ?? ''),
                'leitor' => (string) ($p['leitor'] ?? ''),
                'status' => (string) ($p['status'] ?? (empty($p['devolvido_em']) ? 'emprestado' : 'devolvido')),
                'emprestado_em' => (string) ($p['emprestado_em'] ?? ''),
                'previsto_em' => (string) ($p['devolucao_prevista'] ?? $p['previsto_em'] ?? ''),
                'devolvido_em' => (string) ($p['devolvido_em'] ?? ''),
            ];
        }

        return $out;
    }

    /** @return list<array<string, mixed>> */
    public function contratacoesProcessos(int $limite = 40): array
    {
        $regClass = \iEducar\Packages\BuritiContratacoesTemporarias\Services\PocRegistro::class;
        if (! class_exists($regClass)) {
            return [];
        }
        $out = [];
        foreach ($regClass::listar('processo', $limite) as $r) {
            $p = $r['payload'] ?? [];
            $out[] = [
                'id' => (int) $r['id'],
                'titulo' => (string) ($p['titulo'] ?? $r['titulo'] ?? ''),
                'area' => (string) ($p['area'] ?? ''),
                'status' => (string) ($p['status'] ?? ''),
                'publicado' => ! empty($p['publicado']),
                'url' => url('/contratacoes-temporarias/processos/'.$r['id']),
            ];
        }

        return $out;
    }

    private function temSchema(string $schema): bool
    {
        try {
            return (bool) DB::selectOne(
                'select 1 from information_schema.schemata where schema_name = ? limit 1',
                [$schema]
            );
        } catch (Throwable) {
            return false;
        }
    }
}
