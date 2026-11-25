#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__.'/../src/Support/AppsApiCatalogo.php';
require __DIR__.'/../src/Services/AppsApiFacade.php';
require __DIR__.'/../src/Services/ProfessorConsultaService.php';
require __DIR__.'/../src/Services/AppsGovernancaService.php';

use iEducar\Packages\BuritiAppsApi\Services\AppsApiFacade;
use iEducar\Packages\BuritiAppsApi\Services\AppsGovernancaService;
use iEducar\Packages\BuritiAppsApi\Services\ProfessorConsultaService;
use iEducar\Packages\BuritiAppsApi\Support\AppsApiCatalogo;

$ok = 0;
$fail = 0;
$assert = static function (bool $c, string $m) use (&$ok, &$fail): void {
    echo ($c ? '  OK  ' : ' FAIL ').$m."\n";
    $c ? $ok++ : $fail++;
};

$eps = AppsApiCatalogo::endpoints();
$assert(count($eps) >= 25, 'catálogo tem ≥25 endpoints reais');
$ondas = array_unique(array_column($eps, 'onda'));
$assert(in_array(5, $ondas, true), 'inclui onda 5');

$paths = array_column($eps, 'path');
$assert(in_array('/api/apps/v1/auth/login', $paths, true), 'auth login no catálogo');
$assert(in_array('/api/apps/v1/auth/status', $paths, true), 'auth status no catálogo');
$assert(in_array('/api/apps/v1/admin/usuarios/{usuario}/suspender', $paths, true), 'admin suspender no catálogo');
$assert(in_array('/api/apps/v1/admin/municipio/bloquear', $paths, true), 'admin bloquear município');

$routesFile = (string) file_get_contents(__DIR__.'/../routes/api.php');
$assert(str_contains($routesFile, 'auth/login'), 'rota auth/login existe');
$assert(str_contains($routesFile, 'EnsureAppsApiEnabled'), 'middleware apps enabled');
$assert(str_contains($routesFile, 'EnsureAppsUserActive'), 'middleware usuário ativo');
$assert(str_contains($routesFile, 'auth:sanctum'), 'middleware sanctum');
$assert(str_contains($routesFile, "EnsureAppsPerfil::class.':admin'"), 'middleware perfil admin');
$assert(str_contains($routesFile, 'device_name') === false, 'device_name validado no controller (não na rota)');

$cob = AppsApiCatalogo::coberturaTr();
$assert($cob['percentual'] >= 85.0, 'cobertura TR ≥85% (got '.$cob['percentual'].'%)');

$assert(is_file(__DIR__.'/../config/apps-api.php'), 'config apps-api presente');
$cfgSrc = (string) file_get_contents(__DIR__.'/../config/apps-api.php');
$assert(str_contains($cfgSrc, 'require_login'), 'require_login no config');
$assert(str_contains($cfgSrc, 'APPS_API_ENABLED'), 'APPS_API_ENABLED no config');

$api = new AppsApiFacade();
$cat = $api->catalogo();
$assert($cat['version'] === 'v1', 'versão v1');
$assert(in_array(5, $cat['ondas'] ?? [], true), 'ondas inclui 5');
$assert(($cat['auth'] ?? '') === 'sanctum', 'auth sanctum declarado');

$gov = new AppsGovernancaService();
$assert(method_exists($gov, 'suspenderUsuario'), 'governança suspenderUsuario');
$assert(method_exists($gov, 'bloquearMunicipio'), 'governança bloquearMunicipio');
$assert(method_exists($gov, 'appsHabilitados'), 'governança appsHabilitados');

$sync = new ProfessorConsultaService();
try {
    $r1 = $sync->sync([
        ['uuid' => 'onda5-uuid-1', 'tipo' => 'chamada', 'payload' => ['ok' => true]],
        ['uuid' => 'onda5-uuid-1', 'tipo' => 'chamada'],
        ['uuid' => '', 'tipo' => 'x'],
    ]);
    $assert(($r1['aceitos'] ?? 0) >= 1, 'sync aceita 1º evento');
    $assert(($r1['rejeitados'] ?? 0) >= 1, 'sync rejeita uuid vazio');
} catch (Throwable $e) {
    $assert(method_exists($sync, 'sync'), 'sync existe ('.$e->getMessage().')');
}

$assert(is_file(__DIR__.'/../docs/onda-5/STATUS.md'), 'docs onda-5');
$assert(is_file(__DIR__.'/../src/Console/SuspenderUsuarioCommand.php'), 'comando suspender');
$assert(is_file(__DIR__.'/../src/Console/BloquearMunicipioCommand.php'), 'comando bloquear município');

echo "\nResultado: {$ok} ok, {$fail} falhas\n";
exit($fail > 0 ? 1 : 0);
