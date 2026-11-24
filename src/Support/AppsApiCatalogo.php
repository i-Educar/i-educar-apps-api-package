<?php

namespace iEducar\Packages\BuritiAppsApi\Support;

/**
 * Catálogo de recursos da API apps (Ondas 0–2) para Android/iOS.
 */
final class AppsApiCatalogo
{
    /**
     * @return list<array{grupo:string,metodo:string,path:string,onda:int,tr:string,descricao:string}>
     */
    public static function endpoints(): array
    {
        return [
            // Auth
            ['grupo' => 'auth', 'metodo' => 'POST', 'path' => '/api/apps/v1/auth/login', 'onda' => 3, 'tr' => '5.13/5.14', 'descricao' => 'Login Sanctum (responsável/professor)'],
            ['grupo' => 'auth', 'metodo' => 'POST', 'path' => '/api/apps/v1/auth/logout', 'onda' => 3, 'tr' => '5.13/5.14', 'descricao' => 'Revoga token'],
            // Onda 0 export
            ['grupo' => 'exportacao', 'metodo' => 'GET', 'path' => '/api/apps/v1/exportacao/formatos', 'onda' => 0, 'tr' => '4.2/4.3', 'descricao' => 'Lista formatos TR'],
            // Onda 1 família / escolar
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/filhos', 'onda' => 1, 'tr' => '5.13', 'descricao' => 'Filhos vinculados ao responsável'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/filhos/{id}/frequencia', 'onda' => 1, 'tr' => '5.13/5.3', 'descricao' => 'Frequência (núcleo/i-Diário)'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/filhos/{id}/notas', 'onda' => 1, 'tr' => '5.13/5.3', 'descricao' => 'Notas/boletim'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/fila', 'onda' => 1, 'tr' => '5.8', 'descricao' => 'Situação na fila/pré-matrícula'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/transporte', 'onda' => 1, 'tr' => '5.4', 'descricao' => 'Rotas/status transporte'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/uniforme', 'onda' => 1, 'tr' => '5.3', 'descricao' => 'Medidas/recebimento uniforme'],
            // Onda 2
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/cardapio', 'onda' => 2, 'tr' => '5.2', 'descricao' => 'Cardápio do dia + restrições'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/atendimentos', 'onda' => 2, 'tr' => '5.9', 'descricao' => 'Agendamentos do estudante'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/biblioteca', 'onda' => 2, 'tr' => '5.5', 'descricao' => 'Empréstimos/consulta acervo'],
            // Professor
            ['grupo' => 'professor', 'metodo' => 'GET', 'path' => '/api/apps/v1/professor/turmas', 'onda' => 1, 'tr' => '5.14', 'descricao' => 'Turmas do professor (i-Diário/core)'],
            ['grupo' => 'professor', 'metodo' => 'POST', 'path' => '/api/apps/v1/professor/chamada', 'onda' => 1, 'tr' => '5.14', 'descricao' => 'Lançamento frequência (sync)'],
            ['grupo' => 'professor', 'metodo' => 'POST', 'path' => '/api/apps/v1/professor/notas', 'onda' => 1, 'tr' => '5.14', 'descricao' => 'Lançamento notas'],
            ['grupo' => 'professor', 'metodo' => 'POST', 'path' => '/api/apps/v1/professor/sync', 'onda' => 1, 'tr' => '5.14', 'descricao' => 'Fila offline idempotente'],
            ['grupo' => 'professor', 'metodo' => 'GET', 'path' => '/api/apps/v1/professor/historico-funcional', 'onda' => 2, 'tr' => '5.1', 'descricao' => 'Histórico funcional resumido'],
            ['grupo' => 'professor', 'metodo' => 'GET', 'path' => '/api/apps/v1/professor/horario', 'onda' => 2, 'tr' => '5.11', 'descricao' => 'Grade semanal'],
            ['grupo' => 'professor', 'metodo' => 'GET', 'path' => '/api/apps/v1/professor/eventos', 'onda' => 2, 'tr' => '5.12', 'descricao' => 'Formações/eventos'],
            // Diário bridge
            ['grupo' => 'diario', 'metodo' => 'GET', 'path' => '/api/apps/v1/diario/status', 'onda' => 1, 'tr' => '5.3/i-Diário', 'descricao' => 'Health/config integração i-Diário'],
            // Meta
            ['grupo' => 'meta', 'metodo' => 'GET', 'path' => '/api/apps/v1/catalogo', 'onda' => 0, 'tr' => '5.13/5.14', 'descricao' => 'Catálogo OpenAPI resumido'],
        ];
    }

    /** @return list<string> */
    public static function formatosExportacao(): array
    {
        return ['pdf', 'odt', 'docx', 'ods', 'xlsx', 'html', 'xml', 'csv', 'txt', 'jpeg'];
    }
}
