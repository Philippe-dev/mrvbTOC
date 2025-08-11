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

use Dotclear\App;
use Dotclear\Core\Process;
use Dotclear\Helper\L10n;

class Frontend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        App::behavior()->addBehavior('publicHeadContent', [self::class,  'publicHeadContent']);
        App::behavior()->addBehavior('publicFooterContent', [self::class,  'publicFooterContent']);
        App::behavior()->addBehavior('initWidgets', [Widgets::class, 'initWidgets']);

        L10n::set(dirname(__FILE__) . '/locales/' . App::lang()->getLang() . '/main');

        return true;
    }

    public static function publicHeadContent()
    {
        echo
            '<style type="text/css">
            .mrvbToC .less.active .read-more,
            .mrvbToC .read-less{display: none;}
            .mrvbToC .less.active .read-less{display: inline-block;}
        </style>' . "\n";
    }

    public static function publicFooterContent()
    {
        echo
        '<script type="text/javascript">
        //<![CDATA[
        // mrvbToC : show/hide "more"
        $(document).ready(function() {
            $(".more").hide();
            $(".read-more").click(function () {
                $(this).closest(".less").addClass("active");
                $(this).closest(".less").next().stop(true).slideDown("1000");
            });
            $(".read-less").click(function () {
                $(this).closest(".less").removeClass("active");
                $(this).closest(".less").next().stop(true).slideUp("1000");
            });
        });
        //]]>
        </script>' . "\n";
    }
}
