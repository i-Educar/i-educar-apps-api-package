<?php

namespace iEducar\Packages\BuritiAppsApi\Support;

/**
 * Catálogo = rotas reais (`routes/api.php`). Sem endpoints fantasmas.
 */
final class AppsApiCatalogo
{
    /**
     * @return list<array{grupo:string,metodo:string,path:string,onda:int,tr:string,auth:string,descricao:string}>
     */
    public static function endpoints(): array
    {
        return [
            // Auth (público / autenticado)
            ['grupo' => 'auth', 'metodo' => 'POST', 'path' => '/api/apps/v1/auth/login', 'onda' => 3, 'tr' => '5.13/5.14', 'auth' => 'public', 'descricao' => 'Login Sanctum (perfil familia|professor)'],
            ['grupo' => 'auth', 'metodo' => 'POST', 'path' => '/api/apps/v1/auth/logout', 'onda' => 3, 'tr' => '5.13/5.14', 'auth' => 'sanctum', 'descricao' => 'Revoga token atual'],
            ['grupo' => 'auth', 'metodo' => 'GET', 'path' => '/api/apps/v1/auth/me', 'onda' => 3, 'tr' => '5.13/5.14', 'auth' => 'sanctum', 'descricao' => 'Usuário e perfil do token'],
            // Meta / Onda 0
            ['grupo' => 'meta', 'metodo' => 'GET', 'path' => '/api/apps/v1/catalogo', 'onda' => 0, 'tr' => '5.13/5.14', 'auth' => 'public', 'descricao' => 'Catálogo alinhado às rotas'],
            ['grupo' => 'meta', 'metodo' => 'GET', 'path' => '/api/apps/v1/openapi.yaml', 'onda' => 3, 'tr' => '5.13/5.14', 'auth' => 'public', 'descricao' => 'Spec OpenAPI 3'],
            ['grupo' => 'exportacao', 'metodo' => 'GET', 'path' => '/api/apps/v1/exportacao/formatos', 'onda' => 0, 'tr' => '4.2/4.3', 'auth' => 'public', 'descricao' => 'Lista formatos TR'],
            ['grupo' => 'diario', 'metodo' => 'GET', 'path' => '/api/apps/v1/diario/status', 'onda' => 1, 'tr' => '5.3/i-Diário', 'auth' => 'public', 'descricao' => 'Health integração i-Diário'],
            // Família
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/filhos', 'onda' => 3, 'tr' => '5.13', 'auth' => 'familia', 'descricao' => 'Filhos vinculados (Eloquent)'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/filhos/{aluno}/frequencia', 'onda' => 3, 'tr' => '5.13/5.3', 'auth' => 'familia', 'descricao' => 'Frequência/faltas do aluno'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/filhos/{aluno}/notas', 'onda' => 3, 'tr' => '5.13/5.3', 'auth' => 'familia', 'descricao' => 'Notas/boletim'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/fila', 'onda' => 3, 'tr' => '5.8', 'auth' => 'familia', 'descricao' => 'Fila/pré-matrícula'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/transporte', 'onda' => 3, 'tr' => '5.4', 'auth' => 'familia', 'descricao' => 'Transporte escolar'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/cardapio', 'onda' => 3, 'tr' => '5.2', 'auth' => 'familia', 'descricao' => 'Cardápio do dia'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/uniforme', 'onda' => 3, 'tr' => '5.3', 'auth' => 'familia', 'descricao' => 'Uniforme/medidas'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/atendimentos', 'onda' => 3, 'tr' => '5.9', 'auth' => 'familia', 'descricao' => 'Atendimentos do estudante'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/biblioteca', 'onda' => 3, 'tr' => '5.5', 'auth' => 'familia', 'descricao' => 'Empréstimos/acervo'],
            // Professor
            ['grupo' => 'professor', 'metodo' => 'GET', 'path' => '/api/apps/v1/professor/turmas', 'onda' => 3, 'tr' => '5.14', 'auth' => 'professor', 'descricao' => 'Turmas do servidor'],
            ['grupo' => 'professor', 'metodo' => 'POST', 'path' => '/api/apps/v1/professor/chamada', 'onda' => 3, 'tr' => '5.14', 'auth' => 'professor', 'descricao' => 'Lançamento frequência (fila)'],
            ['grupo' => 'professor', 'metodo' => 'POST', 'path' => '/api/apps/v1/professor/notas', 'onda' => 3, 'tr' => '5.14', 'auth' => 'professor', 'descricao' => 'Lançamento notas (fila)'],
            ['grupo' => 'professor', 'metodo' => 'POST', 'path' => '/api/apps/v1/professor/sync', 'onda' => 3, 'tr' => '5.14', 'auth' => 'professor', 'descricao' => 'Sync offline idempotente'],
            ['grupo' => 'professor', 'metodo' => 'GET', 'path' => '/api/apps/v1/professor/historico-funcional', 'onda' => 3, 'tr' => '5.1', 'auth' => 'professor', 'descricao' => 'Histórico/lotação resumida'],
            ['grupo' => 'professor', 'metodo' => 'GET', 'path' => '/api/apps/v1/professor/horario', 'onda' => 3, 'tr' => '5.11', 'auth' => 'professor', 'descricao' => 'Grade semanal'],
            ['grupo' => 'professor', 'metodo' => 'GET', 'path' => '/api/apps/v1/professor/eventos', 'onda' => 3, 'tr' => '5.12', 'auth' => 'professor', 'descricao' => 'Formações/eventos'],
        ];
    }

    /** @return list<string> */
    public static function formatosExportacao(): array
    {
        return ['pdf', 'odt', 'docx', 'ods', 'xlsx', 'html', 'xml', 'csv', 'txt', 'jpeg'];
    }

    /**
     * Cobertura declarada dos itens de dados 5.13/5.14 (gate Onda 3).
     *
     * @return array{itens_5_13:int,cobertos_5_13:int,itens_5_14:int,cobertos_5_14:int,percentual:float}
     */
    public static function coberturaTr(): array
    {
        // Itens de dados essenciais do TR (PoC ≥85%)
        $familia = [
            'auth', 'filhos', 'frequencia', 'notas', 'fila', 'transporte', 'cardapio', 'uniforme', 'atendimentos', 'biblioteca',
        ];
        $professor = [
            'auth', 'turmas', 'chamada', 'notas', 'sync', 'historico_funcional', 'horario', 'eventos',
        ];

        $eps = self::endpoints();
        $paths = array_column($eps, 'path');

        $mapFamilia = [
            'auth' => '/api/apps/v1/auth/login',
            'filhos' => '/api/apps/v1/familia/filhos',
            'frequencia' => '/api/apps/v1/familia/filhos/{aluno}/frequencia',
            'notas' => '/api/apps/v1/familia/filhos/{aluno}/notas',
            'fila' => '/api/apps/v1/familia/fila',
            'transporte' => '/api/apps/v1/familia/transporte',
            'cardapio' => '/api/apps/v1/familia/cardapio',
            'uniforme' => '/api/apps/v1/familia/uniforme',
            'atendimentos' => '/api/apps/v1/familia/atendimentos',
            'biblioteca' => '/api/apps/v1/familia/biblioteca',
        ];
        $mapProfessor = [
            'auth' => '/api/apps/v1/auth/login',
            'turmas' => '/api/apps/v1/professor/turmas',
            'chamada' => '/api/apps/v1/professor/chamada',
            'notas' => '/api/apps/v1/professor/notas',
            'sync' => '/api/apps/v1/professor/sync',
            'historico_funcional' => '/api/apps/v1/professor/historico-funcional',
            'horario' => '/api/apps/v1/professor/horario',
            'eventos' => '/api/apps/v1/professor/eventos',
        ];

        $c13 = count(array_filter($mapFamilia, static fn ($p) => in_array($p, $paths, true)));
        $c14 = count(array_filter($mapProfessor, static fn ($p) => in_array($p, $paths, true)));
        $total = count($familia) + count($professor);
        $cobertos = $c13 + $c14;

        return [
            'itens_5_13' => count($familia),
            'cobertos_5_13' => $c13,
            'itens_5_14' => count($professor),
            'cobertos_5_14' => $c14,
            'percentual' => round(100 * $cobertos / max(1, $total), 1),
        ];
    }
}
