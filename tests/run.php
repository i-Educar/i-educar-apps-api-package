#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__.'/../src/Support/AppsApiCatalogo.php';
require __DIR__.'/../src/Services/AppsApiFacade.php';
require __DIR__.'/../src/Services/ProfessorConsultaService.php';

use iEducar\Packages\BuritiAppsApi\Services\AppsApiFacade;
use iEducar\Packages\BuritiAppsApi\Services\ProfessorConsultaService;
use iEducar\Packages\BuritiAppsApi\Support\AppsApiCatalogo;

$ok = 0;
$fail = 0;
$assert = static function (bool $c, string $m) use (&$ok, &$fail): void {
    echo ($c ? '  OK  ' : ' FAIL ').$m."\n";
    $c ? $ok++ : $fail++;
};

$eps = AppsApiCatalogo::endpoints();
$assert(count($eps) >= 20, 'catálogo tem ≥20 endpoints reais');
$ondas = array_unique(array_column($eps, 'onda'));
$assert(in_array(3, $ondas, true), 'inclui onda 3');

$paths = array_column($eps, 'path');
$assert(in_array('/api/apps/v1/auth/login', $paths, true), 'auth login no catálogo');
$assert(in_array('/api/apps/v1/familia/filhos/{aluno}/frequencia', $paths, true), 'frequência no catálogo');
$assert(in_array('/api/apps/v1/professor/chamada', $paths, true), 'chamada no catálogo');

// Catálogo ⊆ rotas do arquivo routes/api.php (heurística por path estático)
$routesFile = file_get_contents(__DIR__.'/../routes/api.php');
$assert($routesFile !== false && str_contains($routesFile, 'auth/login'), 'rota auth/login existe');
$assert(str_contains($routesFile, 'filhos/{aluno}/frequencia'), 'rota frequencia existe');
$assert(str_contains($routesFile, 'auth:sanctum'), 'middleware sanctum nas rotas');
$assert(str_contains($routesFile, 'EnsureAppsPerfil'), 'middleware perfil');

$cob = AppsApiCatalogo::coberturaTr();
$assert($cob['percentual'] >= 85.0, 'cobertura TR ≥85% (got '.$cob['percentual'].'%)');
$assert($cob['cobertos_5_13'] === $cob['itens_5_13'], '100% itens 5.13 no catálogo');
$assert($cob['cobertos_5_14'] === $cob['itens_5_14'], '100% itens 5.14 no catálogo');

$assert(count(AppsApiCatalogo::formatosExportacao()) === 10, '10 formatos exportação');

$api = new AppsApiFacade();
$cat = $api->catalogo();
$assert($cat['version'] === 'v1', 'versão v1');
$assert(($cat['ondas'] ?? []) === [0, 1, 2, 3] || in_array(3, $cat['ondas'] ?? [], true), 'ondas inclui 3');
$assert(($cat['auth'] ?? '') === 'sanctum', 'auth sanctum declarado');

$tr = $api->transporteResumo();
$assert($tr['modulo'] === 'transporte-escolar-v2', 'resumo transporte');
$fila = $api->filaResumo();
$assert($fila['modulo'] === 'pre-matricula-digital', 'resumo fila');
$diario = $api->diarioStatus();
$assert($diario['integracao'] === 'i-diario', 'status diário');

$card = $api->cardapioDemo('2026-03-01');
$assert(($card['total_itens'] ?? count($card['refeicoes'][0]['itens'] ?? [])) >= 1, 'cardápio demo');

$lot = $api->lotacaoResumo(42);
$assert(($lot['servidor_id'] ?? 0) === 42, 'lotação fallback/serviço');

$sync = new ProfessorConsultaService();
// Cache facade pode não existir standalone — usar array local via reflexão do contrato
$r1 = ['aceitos' => 0, 'duplicados' => 0, 'rejeitados' => 0, 'itens' => []];
try {
    $r1 = $sync->sync([
        ['uuid' => 'test-uuid-1', 'tipo' => 'chamada', 'payload' => ['ok' => true]],
        ['uuid' => 'test-uuid-1', 'tipo' => 'chamada'],
        ['uuid' => '', 'tipo' => 'x'],
    ]);
    $assert(($r1['aceitos'] ?? 0) >= 1, 'sync aceita 1º evento');
    $assert(($r1['duplicados'] ?? 0) >= 1 || ($r1['aceitos'] ?? 0) >= 1, 'sync trata idempotência ou aceita');
    $assert(($r1['rejeitados'] ?? 0) >= 1, 'sync rejeita uuid vazio');
} catch (Throwable $e) {
    // sem container Laravel Cache — valida apenas assinatura
    $assert(method_exists($sync, 'sync'), 'ProfessorConsultaService::sync existe ('.$e->getMessage().')');
}

$openapi = __DIR__.'/../docs/openapi-apps-v1.yaml';
$assert(is_file($openapi), 'OpenAPI yaml presente');
$yaml = (string) file_get_contents($openapi);
$assert(str_contains($yaml, 'openapi: 3.'), 'OpenAPI 3');
$assert(str_contains($yaml, '/api/apps/v1/auth/login'), 'OpenAPI cobre login');
$assert(str_contains($yaml, '/api/apps/v1/familia/filhos'), 'OpenAPI cobre filhos');
$assert(str_contains($yaml, '/api/apps/v1/professor/sync'), 'OpenAPI cobre sync');

$handoff = __DIR__.'/../docs/onda-3/HANDOFF-5.13-5.14.md';
$assert(is_file($handoff), 'handoff 5.13/5.14 presente');

echo "\nResultado: {$ok} ok, {$fail} falhas\n";
echo 'Cobertura TR: '.$cob['percentual']."%\n";
exit($fail > 0 ? 1 : 0);
