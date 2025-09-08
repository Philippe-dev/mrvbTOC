<?php
/**
 * @brief mrvbToC, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Mirovinben (https://www.mirovinben.fr/)
 *
 * @copyright AGPL-3.0
 */
declare(strict_types=1);

namespace Dotclear\Plugin\mrvbTOC;

use Dotclear\Helper\Process\TraitProcess;
use Dotclear\App;

class Backend
{
    use TraitProcess;
    
    public static function init(): bool
    {
        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        App::behavior()->addBehavior('initWidgets', Widgets::initWidgets(...));

        return true;
    }

}
