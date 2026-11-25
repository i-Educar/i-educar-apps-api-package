<?php

namespace iEducar\Packages\BuritiAppsApi\Support;

final class Identidade
{
    /** Teal institucional — evita o “roxo genérico” e combina com apps móveis. */
    public const COR = '#0f5c6e';

    public const DESTAQUE = '#1a7f90';

    public const CLARA = '#e7f4f6';

    public const ICONE = 'fa-mobile';

    public const NOME = 'Apps da rede';

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
