<?php

namespace iEducar\Packages\BuritiAppsApi\Services;

use iEducar\Packages\BuritiAppsApi\Support\AppsApiCatalogo;

/**
 * Fachada de orquestração — não reimplementa domínio; devolve contratos estáveis para o app.
 */
final class AppsApiFacade
{
    public function catalogo(): array
    {
        return [
            'version' => 'v1',
            'ondas' => [0, 1, 2, 3, 5],
            'auth' => 'sanctum',
            'require_login' => (bool) (function_exists('config') ? config('apps-api.require_login', true) : true),
            'apps_habilitados' => (bool) (function_exists('config') ? config('apps-api.enabled', true) : true),
            'endpoints' => AppsApiCatalogo::endpoints(),
            'exportacao_formatos' => AppsApiCatalogo::formatosExportacao(),
            'cobertura_tr' => AppsApiCatalogo::coberturaTr(),
        ];
    }

    public function cardapioDemo(string $data): array
    {
        if (class_exists(\iEducar\Packages\BuritiAlimentacaoEscolar\Services\CardapioDia::class)) {
            $c = new \iEducar\Packages\BuritiAlimentacaoEscolar\Services\CardapioDia(
                $data,
                [['refeicao' => 'almoco', 'itens' => ['arroz', 'feijao', 'fruta']]],
                'Rede'
            );

            return $c->toArray();
        }

        return [
            'data' => $data,
            'rede' => 'Rede',
            'refeicoes' => [
                ['refeicao' => 'almoco', 'itens' => ['arroz', 'feijao', 'fruta']],
            ],
            'total_itens' => 3,
            'aviso' => 'pacote alimentação não carregado — fallback contrato',
        ];
    }

    public function transporteResumo(): array
    {
        return [
            'modulo' => 'transporte-escolar-v2',
            'recursos' => ['rotas', 'viagens', 'gastos', 'carteirinha', 'portal_familia', 'passagens', 'empenhos'],
            'status' => 'onda1',
            'tr' => '5.4',
        ];
    }

    public function filaResumo(): array
    {
        return [
            'modulo' => 'pre-matricula-digital',
            'recursos' => ['protocolo_publico', 'processos_abertos', 'whatsapp_stub'],
            'status' => 'onda1',
            'tr' => '5.8',
        ];
    }

    public function diarioStatus(): array
    {
        $root = function_exists('base_path') ? base_path() : dirname(__DIR__, 4);
        $path = $root.'/packages/portabilis/i-diario';
        $existe = is_dir($path);

        // Sem path de filesystem nem detalhes de deploy (privacidade / superfície de ataque)
        return [
            'integracao' => 'i-diario',
            'disponivel' => $existe,
            'conectado_via' => 'API i-Educar + sync',
        ];
    }

    public function lotacaoResumo(int $servidorId): array
    {
        if (class_exists(\iEducar\Packages\BuritiServidorEducacional\Services\Lotacao::class)) {
            $l = new \iEducar\Packages\BuritiServidorEducacional\Services\Lotacao($servidorId, 1, 'Professor', '2026-01-01');

            return $l->toArray() + ['ativa' => $l->ativa()];
        }

        return [
            'servidor_id' => $servidorId,
            'escola_id' => 1,
            'funcao' => 'Professor',
            'inicio' => '2026-01-01',
            'ativa' => true,
            'aviso' => 'pacote servidor não carregado — fallback contrato',
        ];
    }

    public function uniformeResumo(): array
    {
        return [
            'modulo' => 'escolar-aderencia',
            'recursos' => ['medidas', 'recebimento'],
            'tr' => '5.3',
            'status' => 'onda1',
        ];
    }

    public function atendimentosResumo(): array
    {
        return [
            'modulo' => 'atendimento-estudante',
            'recursos' => ['agendamentos', 'historico'],
            'tr' => '5.9',
            'status' => 'onda2',
        ];
    }

    public function bibliotecaResumo(): array
    {
        return [
            'modulo' => 'biblioteca',
            'recursos' => ['emprestimos', 'acervo'],
            'tr' => '5.5',
            'status' => 'onda2',
        ];
    }

    public function horarioResumo(): array
    {
        return [
            'modulo' => 'horario-automatico',
            'recursos' => ['grade_semanal'],
            'tr' => '5.11',
            'status' => 'onda2',
        ];
    }

    public function eventosResumo(): array
    {
        return [
            'modulo' => 'eventos-formacao',
            'recursos' => ['formacoes', 'inscricoes'],
            'tr' => '5.12',
            'status' => 'onda2',
        ];
    }
}
