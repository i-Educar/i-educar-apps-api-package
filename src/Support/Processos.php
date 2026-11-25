<?php

namespace iEducar\Packages\BuritiAppsApi\Support;

final class Processos
{
    public const RAIZ = 882101;

    public const CONFIG = 882110;

    public const FUNCOES = 882111;

    public const GOVERNANCA = 882112;

    /** @return array<int, string> */
    public static function titulos(): array
    {
        return [
            self::RAIZ => 'Apps API',
            self::CONFIG => 'Configuração',
            self::FUNCOES => 'Funções disponíveis',
            self::GOVERNANCA => 'Governança',
        ];
    }
}
