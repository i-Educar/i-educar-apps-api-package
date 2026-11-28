<?php

namespace iEducar\Packages\BuritiAppsApi\Services;

use iEducar\Packages\BuritiAppsApi\Support\AppsApiCatalogo;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Persistência e impacto das configurações da Apps API (SME).
 */
final class AppsConfigService
{
    private const SETTINGS_PREFIX = 'apps-api.';

    /** @var array<string, array{rotulo:string,descricao:string,grupo:string,depende?:string,impacto:list<string>}> */
    public const FEATURES_META = [
        'familia' => [
            'rotulo' => 'App Família (TR 5.13)',
            'descricao' => 'Todas as rotas /familia/* e telas do app dos responsáveis.',
            'grupo' => 'perfil',
            'impacto' => [
                'App Família deixa de autenticar perfil familia nas consultas',
                'Filhos, frequência, notas e fila ficam inacessíveis no celular',
            ],
        ],
        'professor' => [
            'rotulo' => 'App Professor (TR 5.14)',
            'descricao' => 'Todas as rotas /professor/* (turmas, chamada, notas, sync).',
            'grupo' => 'perfil',
            'impacto' => [
                'App Professor não consulta turmas nem lança chamada/notas',
                'Sync offline e histórico funcional ficam indisponíveis',
            ],
        ],
        'admin' => [
            'rotulo' => 'API Admin / governança',
            'descricao' => 'Endpoints /admin/* (suspender usuário, bloquear município).',
            'grupo' => 'perfil',
            'impacto' => [
                'Governança remota via app admin fica bloqueada (CLI ainda funciona)',
            ],
        ],
        'equipe' => [
            'rotulo' => 'App Equipe técnica',
            'descricao' => 'Rotas /equipe/* — almoxarifado (QR/etiquetas), biblioteca, contratações.',
            'grupo' => 'perfil',
            'impacto' => [
                'Leitura de QR de remessa e baixa operacional ficam indisponíveis no celular',
                'Consulta de empréstimos e processos ACT some do app unificado',
            ],
        ],
        'equipe.almox' => [
            'rotulo' => 'Equipe · Almoxarifado',
            'descricao' => 'Pedidos, QR, etiquetas, saldos e confirmação de recebimento.',
            'grupo' => 'equipe',
            'depende' => 'equipe',
            'impacto' => [
                'Operação de estoque/remessa no app fica bloqueada',
            ],
        ],
        'equipe.biblioteca' => [
            'rotulo' => 'Equipe · Biblioteca',
            'descricao' => 'GET /equipe/biblioteca/emprestimos.',
            'grupo' => 'equipe',
            'depende' => 'equipe',
            'impacto' => [
                'Lista de empréstimos some do app equipe',
            ],
        ],
        'equipe.contratacoes' => [
            'rotulo' => 'Equipe · Contratações (ACT)',
            'descricao' => 'GET /equipe/contratacoes/processos.',
            'grupo' => 'equipe',
            'depende' => 'equipe',
            'impacto' => [
                'Consulta de processos seletivos some do app equipe',
            ],
        ],
        'exportacao' => [
            'rotulo' => 'Exportação (formatos TR 4.x)',
            'descricao' => 'GET /exportacao/formatos.',
            'grupo' => 'meta',
            'impacto' => [
                'Apps não listam formatos de exportação unificada',
            ],
        ],
        'diario' => [
            'rotulo' => 'Health i-Diário',
            'descricao' => 'GET /diario/status.',
            'grupo' => 'meta',
            'impacto' => [
                'App não verifica se o i-Diário está saudável',
            ],
        ],
        'familia.filhos' => [
            'rotulo' => 'Família · Filhos vinculados',
            'descricao' => 'GET /familia/filhos — lista só alunos do responsável (PII).',
            'grupo' => 'familia',
            'depende' => 'familia',
            'impacto' => [
                'Lista de filhos some do app família',
            ],
        ],
        'familia.frequencia' => [
            'rotulo' => 'Família · Frequência',
            'descricao' => 'GET /familia/filhos/{aluno}/frequencia — escopo por vínculo.',
            'grupo' => 'familia',
            'depende' => 'familia',
            'impacto' => [
                'Frequência do aluno some do app família',
            ],
        ],
        'familia.notas' => [
            'rotulo' => 'Família · Notas',
            'descricao' => 'GET /familia/filhos/{aluno}/notas — escopo por vínculo.',
            'grupo' => 'familia',
            'depende' => 'familia',
            'impacto' => [
                'Boletim/notas some do app família',
            ],
        ],
        'familia.transporte' => [
            'rotulo' => 'Família · Transporte',
            'descricao' => 'GET /familia/transporte (TR 5.4).',
            'grupo' => 'familia',
            'depende' => 'familia',
            'impacto' => [
                'Tela “Transporte escolar” no app família fica indisponível',
                'Responsável não vê rota/veículo do aluno via API',
            ],
        ],
        'familia.cardapio' => [
            'rotulo' => 'Família · Cardápio',
            'descricao' => 'GET /familia/cardapio (TR 5.2).',
            'grupo' => 'familia',
            'depende' => 'familia',
            'impacto' => [
                'Tela “Cardápio / merenda” no app família fica indisponível',
            ],
        ],
        'familia.fila' => [
            'rotulo' => 'Família · Fila / pré-matrícula',
            'descricao' => 'GET /familia/fila (TR 5.8).',
            'grupo' => 'familia',
            'depende' => 'familia',
            'impacto' => [
                'Consulta à fila de vagas no app família fica indisponível',
            ],
        ],
        'familia.atendimentos' => [
            'rotulo' => 'Família · Atendimentos',
            'descricao' => 'GET /familia/atendimentos (TR 5.9).',
            'grupo' => 'familia',
            'depende' => 'familia',
            'impacto' => [
                'Histórico de atendimentos ao estudante some do app família',
            ],
        ],
        'familia.biblioteca' => [
            'rotulo' => 'Família · Biblioteca',
            'descricao' => 'GET /familia/biblioteca (TR 5.5).',
            'grupo' => 'familia',
            'depende' => 'familia',
            'impacto' => [
                'Empréstimos/acervo no app família ficam indisponíveis',
            ],
        ],
        'familia.uniforme' => [
            'rotulo' => 'Família · Uniforme',
            'descricao' => 'GET /familia/uniforme (TR 5.3).',
            'grupo' => 'familia',
            'depende' => 'familia',
            'impacto' => [
                'Kit/uniforme no app família fica indisponível',
            ],
        ],
        'familia.busca_ativa' => [
            'rotulo' => 'Família · Busca Ativa',
            'descricao' => 'Processos/alertas/acompanhamento (Sanctum) — domínio no módulo Busca Ativa.',
            'grupo' => 'familia',
            'depende' => 'familia',
            'impacto' => [
                'App família não consulta processos nem registra acompanhamento de busca ativa',
            ],
        ],
        'professor.turmas' => [
            'rotulo' => 'Professor · Turmas',
            'descricao' => 'GET /professor/turmas — só turmas do servidor.',
            'grupo' => 'professor',
            'depende' => 'professor',
            'impacto' => [
                'Lista de turmas some do app professor',
            ],
        ],
        'professor.lancamentos' => [
            'rotulo' => 'Professor · Chamada / notas / sync',
            'descricao' => 'POST chamada, notas e sync — exige turma do servidor.',
            'grupo' => 'professor',
            'depende' => 'professor',
            'impacto' => [
                'Lançamento offline e chamada/notas ficam bloqueados',
            ],
        ],
        'professor.horario' => [
            'rotulo' => 'Professor · Horário',
            'descricao' => 'GET /professor/horario (TR 5.11).',
            'grupo' => 'professor',
            'depende' => 'professor',
            'impacto' => [
                'Grade semanal some do app do professor',
            ],
        ],
        'professor.eventos' => [
            'rotulo' => 'Professor · Eventos / formação',
            'descricao' => 'GET /professor/eventos (TR 5.12).',
            'grupo' => 'professor',
            'depende' => 'professor',
            'impacto' => [
                'Agenda de formação/eventos some do app do professor',
            ],
        ],
        'professor.historico' => [
            'rotulo' => 'Professor · Histórico funcional',
            'descricao' => 'GET /professor/historico-funcional (TR 5.1).',
            'grupo' => 'professor',
            'depende' => 'professor',
            'impacto' => [
                'Histórico funcional do servidor some do app professor',
            ],
        ],
    ];

    public function carregarPersistido(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $rows = DB::table('settings')
            ->where('key', 'like', self::SETTINGS_PREFIX.'%')
            ->get(['key', 'value']);

        foreach ($rows as $row) {
            $key = (string) $row->key;
            $path = substr($key, strlen(self::SETTINGS_PREFIX));
            $valor = $this->castValor($path, $row->value);
            Config::set('apps-api.'.$path, $valor);
        }
    }

    /**
     * @param  array{
     *   enabled?:bool,
     *   require_login?:bool,
     *   token_ttl_minutes?:int,
     *   suspension_message?:string,
     *   features?:array<string,bool>
     * }  $dados
     */
    public function salvar(array $dados): array
    {
        $enabled = (bool) ($dados['enabled'] ?? false);
        $requireLogin = (bool) ($dados['require_login'] ?? true);
        $ttl = max(5, min(60 * 24 * 365, (int) ($dados['token_ttl_minutes'] ?? 43200)));
        $mensagem = trim((string) ($dados['suspension_message'] ?? ''));
        if ($mensagem === '') {
            $mensagem = (string) config('apps-api.suspension_message');
        }

        $featuresIn = is_array($dados['features'] ?? null) ? $dados['features'] : [];
        $features = [];
        foreach (array_keys(self::FEATURES_META) as $chave) {
            $features[$chave] = array_key_exists($chave, $featuresIn)
                ? (bool) $featuresIn[$chave]
                : false;
        }

        // Filho desligado se o perfil pai estiver desligado.
        foreach ($features as $chave => $on) {
            $depende = self::FEATURES_META[$chave]['depende'] ?? null;
            if ($depende && empty($features[$depende])) {
                $features[$chave] = false;
            }
        }

        $this->gravarSetting('enabled', $enabled ? '1' : '0', 'boolean');
        $this->gravarSetting('require_login', $requireLogin ? '1' : '0', 'boolean');
        $this->gravarSetting('token_ttl_minutes', (string) $ttl, 'integer');
        $this->gravarSetting('suspension_message', $mensagem, 'string');
        foreach ($features as $chave => $on) {
            $this->gravarSetting('features.'.$chave, $on ? '1' : '0', 'boolean');
        }

        Config::set('apps-api.enabled', $enabled);
        Config::set('apps-api.require_login', $requireLogin);
        Config::set('apps-api.token_ttl_minutes', $ttl);
        Config::set('apps-api.suspension_message', $mensagem);
        Config::set('apps-api.features', $features);

        if (class_exists(\App\Services\CacheService::class)) {
            try {
                \App\Services\CacheService::clearSettings();
            } catch (\Throwable) {
                //
            }
        }

        return $this->estadoAtual();
    }

    /** @return array<string, mixed> */
    public function estadoAtual(): array
    {
        $gov = app(AppsGovernancaService::class);
        $features = [];
        foreach (array_keys(self::FEATURES_META) as $chave) {
            $features[$chave] = $this->featureAtiva($chave);
        }

        return [
            'enabled' => (bool) config('apps-api.enabled', true),
            'apps_habilitados' => $gov->appsHabilitados(),
            'require_login' => (bool) config('apps-api.require_login', true),
            'token_ttl_minutes' => (int) config('apps-api.token_ttl_minutes', 43200),
            'suspension_message' => $gov->mensagemSuspensao(),
            'features' => $features,
            'tokens_ativos' => $this->contarTokensApps(),
        ];
    }

    public function featureAtiva(string $chave): bool
    {
        $features = (array) config('apps-api.features', []);
        if (! array_key_exists($chave, $features)) {
            return true;
        }
        if (! (bool) $features[$chave]) {
            return false;
        }
        $depende = self::FEATURES_META[$chave]['depende'] ?? null;
        if ($depende !== null && ! $this->featureAtiva($depende)) {
            return false;
        }

        return true;
    }

    /**
     * Resolve a chave de feature a partir do path da request (ex.: familia/cardapio).
     */
    public function featureParaPath(string $pathRelativo): ?string
    {
        $path = trim($pathRelativo, '/');
        $mapa = [
            'familia/filhos' => 'familia.filhos',
            'familia/transporte' => 'familia.transporte',
            'familia/cardapio' => 'familia.cardapio',
            'familia/fila' => 'familia.fila',
            'familia/atendimentos' => 'familia.atendimentos',
            'familia/biblioteca' => 'familia.biblioteca',
            'familia/uniforme' => 'familia.uniforme',
            'familia/busca-ativa/processos' => 'familia.busca_ativa',
            'professor/turmas' => 'professor.turmas',
            'professor/chamada' => 'professor.lancamentos',
            'professor/notas' => 'professor.lancamentos',
            'professor/sync' => 'professor.lancamentos',
            'professor/horario' => 'professor.horario',
            'professor/eventos' => 'professor.eventos',
            'professor/historico-funcional' => 'professor.historico',
            'exportacao/formatos' => 'exportacao',
            'diario/status' => 'diario',
        ];

        if (isset($mapa[$path])) {
            return $mapa[$path];
        }
        // frequencia/notas por aluno (rota real ou placeholder do catálogo)
        if (preg_match('#^familia/filhos/(?:\d+|\{aluno\})/frequencia$#', $path)) {
            return 'familia.frequencia';
        }
        if (preg_match('#^familia/filhos/(?:\d+|\{aluno\})/notas$#', $path)) {
            return 'familia.notas';
        }
        if (preg_match('#^familia/filhos/(?:\d+|\{aluno\})/busca-ativa/alertas$#', $path)) {
            return 'familia.busca_ativa';
        }
        if (preg_match('#^familia/busca-ativa/#', $path)) {
            return 'familia.busca_ativa';
        }
        if (str_starts_with($path, 'familia')) {
            return 'familia';
        }
        if (str_starts_with($path, 'professor')) {
            return 'professor';
        }
        if (preg_match('#^equipe/almox/#', $path) || $path === 'equipe/almox/confirmar') {
            return 'equipe.almox';
        }
        if (str_starts_with($path, 'equipe/biblioteca')) {
            return 'equipe.biblioteca';
        }
        if (str_starts_with($path, 'equipe/contratacoes')) {
            return 'equipe.contratacoes';
        }
        if (str_starts_with($path, 'equipe')) {
            return 'equipe';
        }
        if (str_starts_with($path, 'admin')) {
            return 'admin';
        }

        return null;
    }

    /**
     * Resumo para a tela SME: volume de endpoints e recomendações de privacidade.
     *
     * @return array{
     *   total:int,
     *   por_grupo:array<string,int>,
     *   veredito:string,
     *   recomendacoes:list<string>
     * }
     */
    public function diagnosticoEndpoints(): array
    {
        $porGrupo = [];
        foreach (AppsApiCatalogo::endpoints() as $e) {
            $g = $e['grupo'];
            $porGrupo[$g] = ($porGrupo[$g] ?? 0) + 1;
        }
        $total = array_sum($porGrupo);

        return [
            'total' => $total,
            'por_grupo' => $porGrupo,
            'veredito' => 'adequado',
            'recomendacoes' => [
                'Manter os endpoints TR 5.13/5.14 — não há excesso de rotas; stubs de domínio já estão no contrato.',
                'Não criar novos stubs até haver fonte real (transporte, biblioteca, uniforme).',
                'Busca Ativa e Notificador ficam em pacotes próprios (não duplicar em /api/apps/v1).',
                'Login limita 10 tentativas/min; perfil família/professor exige vínculo; escrita professor valida turma.',
                'Dados de filho (frequência/notas) só com vínculo responsável/mãe/pai — fail-closed.',
            ],
        ];
    }

    /**
     * Avisos de impacto com base no estado atual (e opcionalmente num rascunho).
     *
     * @param  array<string, bool>|null  $featuresDraft
     * @return list<array{nivel:string,titulo:string,corpo:string,funcoes:list<string>}>
     */
    public function avisosImpacto(?bool $enabledDraft = null, ?array $featuresDraft = null): array
    {
        $estado = $this->estadoAtual();
        $enabled = $enabledDraft ?? (bool) $estado['enabled'];
        $features = $featuresDraft ?? $estado['features'];
        $avisos = [];

        if (! $enabled || ! $estado['apps_habilitados']) {
            $funcoes = array_map(
                static fn (array $e): string => $e['metodo'].' '.$e['path'].' — '.$e['descricao'],
                array_values(array_filter(
                    AppsApiCatalogo::endpoints(),
                    static fn (array $e): bool => ! in_array($e['path'], [
                        '/api/apps/v1/auth/status',
                        '/api/apps/v1/openapi.yaml',
                    ], true)
                ))
            );
            $avisos[] = [
                'nivel' => 'fail',
                'titulo' => 'Apps API desabilitada para o município',
                'corpo' => 'Login, consultas família/professor e governança remota ficam indisponíveis (HTTP 503). '
                    .'Somente auth/status e openapi.yaml continuam públicos para o app ler o motivo.',
                'funcoes' => $funcoes,
            ];
        }

        foreach (self::FEATURES_META as $chave => $meta) {
            $ativa = (bool) ($features[$chave] ?? true);
            if ($ativa) {
                continue;
            }
            $endpoints = $this->endpointsDaFeature($chave);
            $avisos[] = [
                'nivel' => 'warn',
                'titulo' => $meta['rotulo'].' desligada',
                'corpo' => implode(' · ', $meta['impacto']),
                'funcoes' => $endpoints,
            ];
        }

        if (! (bool) $estado['require_login'] && $enabled) {
            $avisos[] = [
                'nivel' => 'warn',
                'titulo' => 'Login obrigatório desligado',
                'corpo' => 'Catálogo e rotas de domínio podem ficar acessíveis sem Bearer — use só em homologação.',
                'funcoes' => ['GET /api/apps/v1/catalogo'],
            ];
        }

        return $avisos;
    }

    /**
     * @return list<array{grupo:string,metodo:string,path:string,auth:string,descricao:string,feature:?string,disponivel:bool}>
     */
    public function funcoesComStatus(): array
    {
        $lista = [];
        foreach (AppsApiCatalogo::endpoints() as $e) {
            $feature = $this->featureDoEndpoint($e);
            $disponivel = true;
            if (! app(AppsGovernancaService::class)->appsHabilitados()) {
                $disponivel = in_array($e['path'], [
                    '/api/apps/v1/auth/status',
                    '/api/apps/v1/openapi.yaml',
                ], true);
            } elseif ($feature !== null) {
                $disponivel = $this->featureAtiva($feature);
            }
            $lista[] = $e + [
                'feature' => $feature,
                'disponivel' => $disponivel,
            ];
        }

        return $lista;
    }

    /** @param array{grupo:string,path:string} $e */
    private function featureDoEndpoint(array $e): ?string
    {
        $rel = preg_replace('#^/api/apps/v1/#', '', $e['path']) ?? '';

        return $this->featureParaPath($rel);
    }

    /** @return list<string> */
    private function endpointsDaFeature(string $chave): array
    {
        $out = [];
        foreach ($this->funcoesComStatus() as $f) {
            if (($f['feature'] ?? null) === $chave || ($f['grupo'] === explode('.', $chave)[0] && ! str_contains($chave, '.') && ($f['feature'] ?? '') === $chave)) {
                $out[] = $f['metodo'].' '.$f['path'].' — '.$f['descricao'];
            }
        }
        if ($out === [] && isset(self::FEATURES_META[$chave])) {
            $out = self::FEATURES_META[$chave]['impacto'];
        }

        return $out;
    }

    private function contarTokensApps(): int
    {
        if (! Schema::hasTable('personal_access_tokens')) {
            return 0;
        }
        try {
            return (int) DB::table('personal_access_tokens')->where('name', 'like', 'apps-%')->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function castValor(string $path, mixed $raw): mixed
    {
        $v = is_string($raw) ? $raw : (string) $raw;
        if ($path === 'token_ttl_minutes') {
            return (int) $v;
        }
        if ($path === 'suspension_message') {
            return $v;
        }
        if ($path === 'enabled' || $path === 'require_login' || str_starts_with($path, 'features.')) {
            return in_array(strtolower($v), ['1', 'true', 'yes', 'on'], true);
        }

        return $v;
    }

    private function gravarSetting(string $path, string $value, string $type): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }
        $key = self::SETTINGS_PREFIX.$path;
        $exists = DB::table('settings')->where('key', $key)->exists();
        if ($exists) {
            DB::table('settings')->where('key', $key)->update([
                'value' => $value,
                'type' => $type,
                'updated_at' => now(),
            ]);

            return;
        }
        DB::table('settings')->insert([
            'key' => $key,
            'value' => $value,
            'type' => $type,
            'description' => 'Apps API · '.$path,
            'setting_category_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
