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
            ['grupo' => 'auth', 'metodo' => 'GET', 'path' => '/api/apps/v1/auth/status', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'public', 'descricao' => 'Status apps (habilitado/suspenso município)'],
            ['grupo' => 'auth', 'metodo' => 'POST', 'path' => '/api/apps/v1/auth/login', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'public', 'descricao' => 'Login Sanctum obrigatório (device_name + perfil)'],
            ['grupo' => 'auth', 'metodo' => 'POST', 'path' => '/api/apps/v1/auth/logout', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'sanctum', 'descricao' => 'Revoga token atual'],
            ['grupo' => 'auth', 'metodo' => 'POST', 'path' => '/api/apps/v1/auth/logout-all', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'sanctum', 'descricao' => 'Revoga todos os tokens do usuário'],
            ['grupo' => 'auth', 'metodo' => 'GET', 'path' => '/api/apps/v1/auth/me', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'sanctum', 'descricao' => 'Usuário autenticado'],
            ['grupo' => 'meta', 'metodo' => 'GET', 'path' => '/api/apps/v1/catalogo', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'sanctum', 'descricao' => 'Catálogo (login obrigatório)'],
            ['grupo' => 'meta', 'metodo' => 'GET', 'path' => '/api/apps/v1/openapi.yaml', 'onda' => 3, 'tr' => '5.13/5.14', 'auth' => 'public', 'descricao' => 'Spec OpenAPI 3'],
            ['grupo' => 'exportacao', 'metodo' => 'GET', 'path' => '/api/apps/v1/exportacao/formatos', 'onda' => 5, 'tr' => '4.2/4.3', 'auth' => 'sanctum', 'descricao' => 'Formatos TR'],
            ['grupo' => 'diario', 'metodo' => 'GET', 'path' => '/api/apps/v1/diario/status', 'onda' => 5, 'tr' => '5.3/i-Diário', 'auth' => 'sanctum', 'descricao' => 'Health i-Diário'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/filhos', 'onda' => 3, 'tr' => '5.13', 'auth' => 'familia', 'descricao' => 'Filhos vinculados'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/filhos/{aluno}/frequencia', 'onda' => 3, 'tr' => '5.13/5.3', 'auth' => 'familia', 'descricao' => 'Frequência'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/filhos/{aluno}/notas', 'onda' => 3, 'tr' => '5.13/5.3', 'auth' => 'familia', 'descricao' => 'Notas'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/fila', 'onda' => 3, 'tr' => '5.8', 'auth' => 'familia', 'descricao' => 'Fila'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/transporte', 'onda' => 3, 'tr' => '5.4', 'auth' => 'familia', 'descricao' => 'Transporte'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/cardapio', 'onda' => 3, 'tr' => '5.2', 'auth' => 'familia', 'descricao' => 'Cardápio'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/uniforme', 'onda' => 3, 'tr' => '5.3', 'auth' => 'familia', 'descricao' => 'Uniforme'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/atendimentos', 'onda' => 3, 'tr' => '5.9', 'auth' => 'familia', 'descricao' => 'Atendimentos'],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/biblioteca', 'onda' => 3, 'tr' => '5.5', 'auth' => 'familia', 'descricao' => 'Biblioteca'],
            ['grupo' => 'professor', 'metodo' => 'GET', 'path' => '/api/apps/v1/professor/turmas', 'onda' => 3, 'tr' => '5.14', 'auth' => 'professor', 'descricao' => 'Turmas'],
            ['grupo' => 'professor', 'metodo' => 'POST', 'path' => '/api/apps/v1/professor/chamada', 'onda' => 3, 'tr' => '5.14', 'auth' => 'professor', 'descricao' => 'Chamada'],
            ['grupo' => 'professor', 'metodo' => 'POST', 'path' => '/api/apps/v1/professor/notas', 'onda' => 3, 'tr' => '5.14', 'auth' => 'professor', 'descricao' => 'Notas'],
            ['grupo' => 'professor', 'metodo' => 'POST', 'path' => '/api/apps/v1/professor/sync', 'onda' => 3, 'tr' => '5.14', 'auth' => 'professor', 'descricao' => 'Sync'],
            ['grupo' => 'professor', 'metodo' => 'GET', 'path' => '/api/apps/v1/professor/historico-funcional', 'onda' => 3, 'tr' => '5.1', 'auth' => 'professor', 'descricao' => 'Histórico'],
            ['grupo' => 'professor', 'metodo' => 'GET', 'path' => '/api/apps/v1/professor/horario', 'onda' => 3, 'tr' => '5.11', 'auth' => 'professor', 'descricao' => 'Horário'],
            ['grupo' => 'professor', 'metodo' => 'GET', 'path' => '/api/apps/v1/professor/eventos', 'onda' => 3, 'tr' => '5.12', 'auth' => 'professor', 'descricao' => 'Eventos'],
            ['grupo' => 'admin', 'metodo' => 'GET', 'path' => '/api/apps/v1/admin/governanca', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'admin', 'descricao' => 'Status governança'],
            ['grupo' => 'admin', 'metodo' => 'POST', 'path' => '/api/apps/v1/admin/usuarios/{usuario}/suspender', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'admin', 'descricao' => 'Suspende usuário e revoga sessões app+web'],
            ['grupo' => 'admin', 'metodo' => 'POST', 'path' => '/api/apps/v1/admin/usuarios/{usuario}/reativar', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'admin', 'descricao' => 'Reativa usuário'],
            ['grupo' => 'admin', 'metodo' => 'POST', 'path' => '/api/apps/v1/admin/municipio/bloquear', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'admin', 'descricao' => 'Desliga município (apps+Suspender cliente)'],
            ['grupo' => 'admin', 'metodo' => 'POST', 'path' => '/api/apps/v1/admin/municipio/liberar', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'admin', 'descricao' => 'Libera município'],
        ];
    }

    /** @return list<string> */
    public static function formatosExportacao(): array
    {
        return ['pdf', 'odt', 'docx', 'ods', 'xlsx', 'html', 'xml', 'csv', 'txt', 'jpeg'];
    }

    /**
     * @return array{itens_5_13:int,cobertos_5_13:int,itens_5_14:int,cobertos_5_14:int,percentual:float}
     */
    public static function coberturaTr(): array
    {
        $familia = [
            'auth', 'filhos', 'frequencia', 'notas', 'fila', 'transporte', 'cardapio', 'uniforme', 'atendimentos', 'biblioteca',
        ];
        $professor = [
            'auth', 'turmas', 'chamada', 'notas', 'sync', 'historico_funcional', 'horario', 'eventos',
        ];

        $paths = array_column(self::endpoints(), 'path');

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
