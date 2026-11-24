#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__.'/../src/Support/AppsApiCatalogo.php';
require __DIR__.'/../src/Services/AppsApiFacade.php';

use iEducar\Packages\BuritiAppsApi\Services\AppsApiFacade;
use iEducar\Packages\BuritiAppsApi\Support\AppsApiCatalogo;

$ok = 0;
$fail = 0;
$assert = static function (bool $c, string $m) use (&$ok, &$fail): void {
    echo ($c ? '  OK  ' : ' FAIL ').$m."\n";
    $c ? $ok++ : $fail++;
};

$eps = AppsApiCatalogo::endpoints();
$assert(count($eps) >= 15, 'catálogo tem ≥15 endpoints');
$ondas = array_unique(array_column($eps, 'onda'));
$assert(in_array(0, $ondas, true) && in_array(1, $ondas, true) && in_array(2, $ondas, true), 'cobre ondas 0,1,2');
$assert(count(AppsApiCatalogo::formatosExportacao()) === 10, '10 formatos exportação');

$api = new AppsApiFacade();
$cat = $api->catalogo();
$assert($cat['version'] === 'v1', 'versão v1');
$assert(isset($cat['endpoints']), 'catalogo endpoints');
$tr = $api->transporteResumo();
$assert($tr['modulo'] === 'transporte-escolar-v2', 'resumo transporte');
$fila = $api->filaResumo();
$assert($fila['modulo'] === 'pre-matricula-digital', 'resumo fila');
$diario = $api->diarioStatus();
$assert($diario['integracao'] === 'i-diario', 'status diário');

// cardápio (com ou sem pacote alimentação carregado via require)
require_once dirname(__DIR__, 2).'/i-educar-alimentacao-escolar-package/src/Services/CardapioDia.php';
$card = $api->cardapioDemo('2026-03-01');
$assert(($card['total_itens'] ?? 0) >= 1, 'cardápio demo');

echo "\nResultado: {$ok} ok, {$fail} falhas\n";
exit($fail > 0 ? 1 : 0);
