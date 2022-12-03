<?php
/**
 * @brief mrvbToC, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Mirovinben (https://www.mirovinben.fr/)
 *
 * @copyright GPL-2.0 [https://www.gnu.org/licenses/gpl-2.0.html]
 */
if (!defined('DC_RC_PATH')) {
    return;
}

$this->registerModule(
    'mrvbToC',
    'Mrvb Table of Contents',
    'Mirovinben and contributors',
    '0.4',
    [
        'permissions' => 'usage,contentadmin',
        'type'        => 'plugin',
        'dc_min'      => '2.24',
        'support'     => 'https://www.mirovinben.fr/blog/index.php?post/id3629',
        'details'     => 'https://plugins.dotaddict.org/dc2/details/mrvbTOC',
    ]
);
