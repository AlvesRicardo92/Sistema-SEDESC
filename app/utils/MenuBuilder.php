<?php

namespace App\Utils;

/**
 * Classe utilitária para construir menus dinamicamente com base nas permissões do usuário.
 */
class MenuBuilder
{
    /**
     * Define a estrutura do menu e as permissões necessárias para cada item.
     * O formato da chave é 'GrupoDePermissaoID' (ex: 'A', 'B', 'C').
     * O valor é um array de itens de menu, onde cada item tem:
     * - 'label': O texto a ser exibido no menu.
     * - 'route': A rota (URL) para onde o item do menu aponta.
     * - 'permission_index': O índice (base 0) do caractere na string de permissões que controla a visibilidade.
     */
    private static array $menuItems = [
        'A' => [ // Exemplo de grupo A
            ['label' => 'Dashboard', 'route' => '/dashboard', 'permission_index' => 0], // A1
            ['label' => 'Procedimentos', 'route' => '/procedimentos/listar', 'permission_index' => 1], // A2
            ['label' => 'Usuários', 'route' => '/usuarios/listar', 'permission_index' => 2], // A3
            ['label' => 'Territórios', 'route' => '/territorios/listar', 'permission_index' => 3], // A4
            ['label' => 'Bairros', 'route' => '/bairros/listar', 'permission_index' => 4], // A5
            ['label' => 'Pessoas', 'route' => '/pessoas/listar', 'permission_index' => 5], // A6
            ['label' => 'Demandantes', 'route' => '/demandantes/listar', 'permission_index' => 6], // A7
            ['label' => 'Migrações', 'route' => '/migracoes/listar', 'permission_index' => 7], // A8
            ['label' => 'Motivos Migração', 'route' => '/motivos_migracao/listar', 'permission_index' => 8], // A9
            ['label' => 'Sexos', 'route' => '/sexos/listar', 'permission_index' => 9], // A10
            ['label' => 'Auditorias', 'route' => '/auditorias/listar', 'permission_index' => 10], // A11
            ['label' => 'Avisos', 'route' => '/avisos/listar', 'permission_index' => 11], // A12
        ],
        // Você pode adicionar mais grupos de permissão (B, C, etc.) aqui
        // 'B' => [
        //     ['label' => 'Relatórios', 'route' => '/relatorios', 'permission_index' => 0], // B1
        // ]
    ];

    /**
     * Constrói o HTML do menu com base nas permissões do usuário.
     *
     * @param string $userPermissions A string de permissões do usuário logado (ex: 'A0011000000').
     * @return string O HTML dos itens do menu (<li>).
     */
    public static function buildMenu(string $userPermissions): string
    {
        $html = '';
        $permissionGroup = substr($userPermissions, 0, 1); // Pega o primeiro caractere (ex: 'A')
        $permissionString = substr($userPermissions, 1);    // Pega o restante da string de permissões (ex: '0011000000')

        if (isset(self::$menuItems[$permissionGroup])) {
            foreach (self::$menuItems[$permissionGroup] as $item) {
                $permissionIndex = $item['permission_index'];

                // Verifica se o caractere na posição da permissão é '1'
                if (isset($permissionString[$permissionIndex]) && $permissionString[$permissionIndex] === '1') {
                    $html .= '<li class="nav-item">';
                    $html .= '<a class="nav-link" href="' . htmlspecialchars($item['route']) . '">' . htmlspecialchars($item['label']) . '</a>';
                    $html .= '</li>';
                }
            }
        }

        return $html;
    }
}
