<?php
/*
 * @brief mrvbToC, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Mirovinben (https://www.mirovinben.fr/)
 *
 * @copyright AGPL-3.0
 */

$this->registerModule(
    'mrvbToC',
    'Table of Contents',
    'Mirovinben and contributors',
    '0.6',
    [
        'date'        => '2025-09-08T00:00:08+0100',
        'requires'    => [['core', '2.36']],
        'permissions' => 'My',
        'type'        => 'plugin',
        'support'     => 'https://www.mirovinben.fr/blog/index.php?post/id3629',
        'details'     => 'https://plugins.dotaddict.org/dc2/details/mrvbTOC',
    ]
);
