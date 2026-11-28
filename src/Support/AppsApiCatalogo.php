<?php

namespace iEducar\Packages\BuritiAppsApi\Support;

/**
 * Catálogo = rotas reais (`routes/api.php`). Sem endpoints fantasmas.
 */
final class AppsApiCatalogo
{
    /**
     * @return list<array{
     *   grupo:string,
     *   metodo:string,
     *   path:string,
     *   onda:int,
     *   tr:string,
     *   auth:string,
     *   descricao:string,
     *   escopo:string,
     *   pii:bool
     * }>
     */
    public static function endpoints(): array
    {
        return [
            ['grupo' => 'auth', 'metodo' => 'GET', 'path' => '/api/apps/v1/auth/status', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'public', 'descricao' => 'Status apps (habilitado/suspenso município)', 'escopo' => 'publico', 'pii' => false],
            ['grupo' => 'auth', 'metodo' => 'POST', 'path' => '/api/apps/v1/auth/login', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'public', 'descricao' => 'Login Sanctum (perfil + elegibilidade + throttle 10/min)', 'escopo' => 'credencial', 'pii' => true],
            ['grupo' => 'auth', 'metodo' => 'POST', 'path' => '/api/apps/v1/auth/logout', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'sanctum', 'descricao' => 'Revoga token atual', 'escopo' => 'proprio', 'pii' => false],
            ['grupo' => 'auth', 'metodo' => 'POST', 'path' => '/api/apps/v1/auth/logout-all', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'sanctum', 'descricao' => 'Revoga todos os tokens do usuário', 'escopo' => 'proprio', 'pii' => false],
            ['grupo' => 'auth', 'metodo' => 'GET', 'path' => '/api/apps/v1/auth/me', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'sanctum', 'descricao' => 'Usuário autenticado (sem abilities no payload)', 'escopo' => 'proprio', 'pii' => true],
            ['grupo' => 'meta', 'metodo' => 'GET', 'path' => '/api/apps/v1/catalogo', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'sanctum', 'descricao' => 'Catálogo (login obrigatório)', 'escopo' => 'autenticado', 'pii' => false],
            ['grupo' => 'meta', 'metodo' => 'GET', 'path' => '/api/apps/v1/openapi.yaml', 'onda' => 3, 'tr' => '5.13/5.14', 'auth' => 'public', 'descricao' => 'Spec OpenAPI 3 (metadados)', 'escopo' => 'publico', 'pii' => false],
            ['grupo' => 'exportacao', 'metodo' => 'GET', 'path' => '/api/apps/v1/exportacao/formatos', 'onda' => 5, 'tr' => '4.2/4.3', 'auth' => 'sanctum', 'descricao' => 'Formatos TR', 'escopo' => 'autenticado', 'pii' => false],
            ['grupo' => 'diario', 'metodo' => 'GET', 'path' => '/api/apps/v1/diario/status', 'onda' => 5, 'tr' => '5.3/i-Diário', 'auth' => 'sanctum', 'descricao' => 'Health i-Diário (sem path de FS)', 'escopo' => 'autenticado', 'pii' => false],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/filhos', 'onda' => 3, 'tr' => '5.13', 'auth' => 'familia', 'descricao' => 'Filhos vinculados ao responsável', 'escopo' => 'responsavel', 'pii' => true],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/filhos/{aluno}/frequencia', 'onda' => 3, 'tr' => '5.13/5.3', 'auth' => 'familia', 'descricao' => 'Frequência (403 se aluno não vinculado)', 'escopo' => 'responsavel+aluno', 'pii' => true],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/filhos/{aluno}/notas', 'onda' => 3, 'tr' => '5.13/5.3', 'auth' => 'familia', 'descricao' => 'Notas (403 se aluno não vinculado)', 'escopo' => 'responsavel+aluno', 'pii' => true],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/fila', 'onda' => 3, 'tr' => '5.8', 'auth' => 'familia', 'descricao' => 'Fila (escopo alunos vinculados)', 'escopo' => 'responsavel', 'pii' => false],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/transporte', 'onda' => 3, 'tr' => '5.4', 'auth' => 'familia', 'descricao' => 'Transporte (escopo alunos vinculados)', 'escopo' => 'responsavel', 'pii' => false],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/cardapio', 'onda' => 3, 'tr' => '5.2', 'auth' => 'familia', 'descricao' => 'Cardápio (rede + escopo)', 'escopo' => 'responsavel', 'pii' => false],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/uniforme', 'onda' => 3, 'tr' => '5.3', 'auth' => 'familia', 'descricao' => 'Uniforme (escopo alunos vinculados)', 'escopo' => 'responsavel', 'pii' => false],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/atendimentos', 'onda' => 3, 'tr' => '5.9', 'auth' => 'familia', 'descricao' => 'Atendimentos (escopo alunos vinculados)', 'escopo' => 'responsavel', 'pii' => true],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/biblioteca', 'onda' => 3, 'tr' => '5.5', 'auth' => 'familia', 'descricao' => 'Biblioteca (escopo alunos vinculados)', 'escopo' => 'responsavel', 'pii' => false],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/busca-ativa/processos', 'onda' => 5, 'tr' => '5.13/5.3', 'auth' => 'familia', 'descricao' => 'Processos de busca ativa dos filhos', 'escopo' => 'responsavel', 'pii' => true],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/busca-ativa/processos/{id}', 'onda' => 5, 'tr' => '5.13/5.3', 'auth' => 'familia', 'descricao' => 'Detalhe do processo (escopo filho)', 'escopo' => 'responsavel+aluno', 'pii' => true],
            ['grupo' => 'familia', 'metodo' => 'POST', 'path' => '/api/apps/v1/familia/busca-ativa/processos/{id}/acompanhamento', 'onda' => 5, 'tr' => '5.13/5.3', 'auth' => 'familia', 'descricao' => 'Registrar retorno familiar', 'escopo' => 'responsavel+aluno', 'pii' => true],
            ['grupo' => 'familia', 'metodo' => 'GET', 'path' => '/api/apps/v1/familia/filhos/{aluno}/busca-ativa/alertas', 'onda' => 5, 'tr' => '5.13/5.3', 'auth' => 'familia', 'descricao' => 'Alertas do filho', 'escopo' => 'responsavel+aluno', 'pii' => true],
            ['grupo' => 'professor', 'metodo' => 'GET', 'path' => '/api/apps/v1/professor/turmas', 'onda' => 3, 'tr' => '5.14', 'auth' => 'professor', 'descricao' => 'Turmas do servidor', 'escopo' => 'servidor', 'pii' => false],
            ['grupo' => 'professor', 'metodo' => 'POST', 'path' => '/api/apps/v1/professor/chamada', 'onda' => 3, 'tr' => '5.14', 'auth' => 'professor', 'descricao' => 'Chamada (turma do servidor)', 'escopo' => 'servidor+turma', 'pii' => true],
            ['grupo' => 'professor', 'metodo' => 'POST', 'path' => '/api/apps/v1/professor/notas', 'onda' => 3, 'tr' => '5.14', 'auth' => 'professor', 'descricao' => 'Notas (turma do servidor)', 'escopo' => 'servidor+turma', 'pii' => true],
            ['grupo' => 'professor', 'metodo' => 'POST', 'path' => '/api/apps/v1/professor/sync', 'onda' => 3, 'tr' => '5.14', 'auth' => 'professor', 'descricao' => 'Sync offline idempotente', 'escopo' => 'servidor+turma', 'pii' => true],
            ['grupo' => 'professor', 'metodo' => 'GET', 'path' => '/api/apps/v1/professor/historico-funcional', 'onda' => 3, 'tr' => '5.1', 'auth' => 'professor', 'descricao' => 'Histórico do próprio servidor', 'escopo' => 'proprio', 'pii' => true],
            ['grupo' => 'professor', 'metodo' => 'GET', 'path' => '/api/apps/v1/professor/horario', 'onda' => 3, 'tr' => '5.11', 'auth' => 'professor', 'descricao' => 'Horário (turmas vinculadas)', 'escopo' => 'servidor', 'pii' => false],
            ['grupo' => 'professor', 'metodo' => 'GET', 'path' => '/api/apps/v1/professor/eventos', 'onda' => 3, 'tr' => '5.12', 'auth' => 'professor', 'descricao' => 'Eventos (turmas vinculadas)', 'escopo' => 'servidor', 'pii' => false],
            ['grupo' => 'equipe', 'metodo' => 'GET', 'path' => '/api/apps/v1/equipe/painel', 'onda' => 10, 'tr' => '5.7/5.5/5.15', 'auth' => 'equipe', 'descricao' => 'Painel e atalhos da equipe técnica', 'escopo' => 'funcionario', 'pii' => false],
            ['grupo' => 'equipe', 'metodo' => 'GET', 'path' => '/api/apps/v1/equipe/almox/pedidos', 'onda' => 10, 'tr' => '5.7', 'auth' => 'equipe', 'descricao' => 'Lista pedidos/notas do almoxarifado', 'escopo' => 'funcionario', 'pii' => false],
            ['grupo' => 'equipe', 'metodo' => 'GET', 'path' => '/api/apps/v1/equipe/almox/pedidos/{id}', 'onda' => 10, 'tr' => '5.7', 'auth' => 'equipe', 'descricao' => 'Detalhe do pedido', 'escopo' => 'funcionario', 'pii' => false],
            ['grupo' => 'equipe', 'metodo' => 'GET', 'path' => '/api/apps/v1/equipe/almox/pedidos/{id}/qr', 'onda' => 10, 'tr' => '5.7.3', 'auth' => 'equipe', 'descricao' => 'Emite/reimprime payload QR da remessa', 'escopo' => 'funcionario', 'pii' => false],
            ['grupo' => 'equipe', 'metodo' => 'GET', 'path' => '/api/apps/v1/equipe/almox/pedidos/{id}/etiquetas', 'onda' => 10, 'tr' => '5.7', 'auth' => 'equipe', 'descricao' => 'Dados para impressão de etiquetas', 'escopo' => 'funcionario', 'pii' => false],
            ['grupo' => 'equipe', 'metodo' => 'POST', 'path' => '/api/apps/v1/equipe/almox/confirmar', 'onda' => 10, 'tr' => '5.7.3', 'auth' => 'equipe', 'descricao' => 'Confirma recebimento via token do QR', 'escopo' => 'funcionario', 'pii' => false],
            ['grupo' => 'equipe', 'metodo' => 'POST', 'path' => '/api/apps/v1/equipe/almox/pedidos/{id}/assinar', 'onda' => 10, 'tr' => '5.7', 'auth' => 'equipe', 'descricao' => 'Assina etapa (permissão por etapa)', 'escopo' => 'funcionario', 'pii' => false],
            ['grupo' => 'equipe', 'metodo' => 'GET', 'path' => '/api/apps/v1/equipe/almox/saldos', 'onda' => 10, 'tr' => '5.7', 'auth' => 'equipe', 'descricao' => 'Saldos do catálogo', 'escopo' => 'funcionario', 'pii' => false],
            ['grupo' => 'equipe', 'metodo' => 'GET', 'path' => '/api/apps/v1/equipe/biblioteca/emprestimos', 'onda' => 10, 'tr' => '5.5', 'auth' => 'equipe', 'descricao' => 'Empréstimos em aberto/histórico', 'escopo' => 'funcionario', 'pii' => true],
            ['grupo' => 'equipe', 'metodo' => 'GET', 'path' => '/api/apps/v1/equipe/contratacoes/processos', 'onda' => 10, 'tr' => '5.15', 'auth' => 'equipe', 'descricao' => 'Processos seletivos ACT', 'escopo' => 'funcionario', 'pii' => false],
            ['grupo' => 'admin', 'metodo' => 'GET', 'path' => '/api/apps/v1/admin/governanca', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'admin', 'descricao' => 'Status governança', 'escopo' => 'admin', 'pii' => false],
            ['grupo' => 'admin', 'metodo' => 'POST', 'path' => '/api/apps/v1/admin/usuarios/{usuario}/suspender', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'admin', 'descricao' => 'Suspende usuário (escopo SME)', 'escopo' => 'admin+escopo', 'pii' => true],
            ['grupo' => 'admin', 'metodo' => 'POST', 'path' => '/api/apps/v1/admin/usuarios/{usuario}/reativar', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'admin', 'descricao' => 'Reativa usuário', 'escopo' => 'admin+escopo', 'pii' => true],
            ['grupo' => 'admin', 'metodo' => 'POST', 'path' => '/api/apps/v1/admin/municipio/bloquear', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'admin', 'descricao' => 'Desliga município', 'escopo' => 'admin', 'pii' => false],
            ['grupo' => 'admin', 'metodo' => 'POST', 'path' => '/api/apps/v1/admin/municipio/liberar', 'onda' => 5, 'tr' => '5.13/5.14', 'auth' => 'admin', 'descricao' => 'Libera município', 'escopo' => 'admin', 'pii' => false],
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
