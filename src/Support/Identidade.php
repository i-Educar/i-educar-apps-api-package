<?php

namespace iEducar\Packages\BuritiAppsApi\Support;

final class Identidade
{
    /** Azul site Buriti — contraste no nav escuro e leitura em telas de API. */
    public const COR = '#2f6bff';

    public const DESTAQUE = '#5b8cff';

    public const CLARA = '#eaf0ff';

    public const ICONE = 'fa-mobile';

    public const NOME = 'APIs';

    public const DESCRICAO = 'Acesso, funções e governança dos aplicativos';

    /** @return array{cor:string,destaque:string,clara:string,icone:string,nome:string,descricao:string} */
    public static function tokens(): array
    {
        return [
            'cor' => self::COR,
            'destaque' => self::DESTAQUE,
            'clara' => self::CLARA,
            'icone' => self::ICONE,
            'nome' => self::NOME,
            'descricao' => self::DESCRICAO,
        ];
    }
}
