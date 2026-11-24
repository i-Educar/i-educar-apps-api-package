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
            'ondas' => [0, 1, 2],
            'endpoints' => AppsApiCatalogo::endpoints(),
            'exportacao_formatos' => AppsApiCatalogo::formatosExportacao(),
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

        return ['data' => $data, 'refeicoes' => [], 'aviso' => 'pacote alimentação não carregado'];
    }

    public function transporteResumo(): array
    {
        return [
            'modulo' => 'transporte-escolar-v2',
            'recursos' => ['rotas', 'viagens', 'gastos', 'carteirinha', 'portal_familia', 'passagens', 'empenhos'],
            'status' => 'onda1',
        ];
    }

    public function filaResumo(): array
    {
        return [
            'modulo' => 'pre-matricula-digital',
            'recursos' => ['protocolo_publico', 'processos_abertos', 'whatsapp_stub'],
            'status' => 'onda1',
        ];
    }

    public function diarioStatus(): array
    {
        return [
            'integracao' => 'i-diario',
            'path_esperado' => 'packages/portabilis/i-diario',
            'conectado_via' => 'API i-Educar (url_novo_educacao + token) + sync entity',
            'repo' => 'https://github.com/i-Educar/i-diario',
        ];
    }

    public function lotacaoResumo(int $servidorId): array
    {
        if (class_exists(\iEducar\Packages\BuritiServidorEducacional\Services\Lotacao::class)) {
            $l = new \iEducar\Packages\BuritiServidorEducacional\Services\Lotacao($servidorId, 1, 'Professor', '2026-01-01');

            return $l->toArray() + ['ativa' => $l->ativa()];
        }

        return ['servidor_id' => $servidorId, 'aviso' => 'pacote servidor não carregado'];
    }
}
