<?php
/**
 * @brief mrvbToC, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Mirovinben (http://www.mirovinben.fr/)
 *
 * @copyright GPL-2.0 [https://www.gnu.org/licenses/gpl-2.0.html]
 */
if (!defined('DC_RC_PATH')) {
    return;
}

dcCore::app()->addBehavior('initWidgets', ['mrvbToCBehaviors','initWidgets']);

class mrvbToCBehaviors
{
    public static function initWidgets($w)
    {
        $w->create('mrvbToC', __('Mrvb: ToC'), ['tplmrvbToC','mrvb_ToC'], null, __('Table of Contents'));
        $w->mrvbToC->setting(
            'title',
            __('Title (optional):'),
            __('Table of Contents'),
            'text'
        );
        $w->mrvbToC->setting(
            'count',
            __('Display entries counts'),
            0,
            'check'
        );
        $w->mrvbToC->setting(
            'nopassword',
            __('Display only entries without password'),
            0,
            'check'
        );
        $w->mrvbToC->setting(
            'selected',
            __('Display only entries marked as visible to their widget'),
            0,
            'check'
        );
        $w->mrvbToC->setting(
            'sortby',
            __('Order by:'),
            'date-asc',
            'combo',
            [
                __('Publication dates (in chronological order)')         => 'date-asc',
                __('Publication dates (in reverse chronological order)') => 'date-desc',
                __('Titles (in alphabetical order)')                     => 'title',
            ]
        );
        $w->mrvbToC->setting(
            'titleposts',
            __('Title of the posts list:'),
            __('List of posts'),
            'text'
        );
        $w->mrvbToC->setting(
            'post',
            __('Display the posts'),
            1,
            'check'
        );
        $w->mrvbToC->setting(
            'nocategory',
            __('Display only the posts with categories'),
            0,
            'check'
        );
        $w->mrvbToC->setting(
            'hideposts',
            __('Activate script "hide/show" for the posts with categories'),
            0,
            'check'
        );
        $w->mrvbToC->setting(
            'titlepages',
            __('Title of the pages list:'),
            __('List of pages'),
            'text'
        );
        $w->mrvbToC->setting(
            'page',
            __('Display the pages'),
            0,
            'check'
        );
        $w->mrvbToC->setting(
            'position',
            __('Force display in ascending order of pages\'s positions'),
            0,
            'check'
        );
        $w->mrvbToC->setting(
            'hidepages',
            __('Activate script "hide/show" for the pages'),
            0,
            'check'
        );
        $w->mrvbToC->setting(
            'titlestatics',
            __('Title of the statics pages list:'),
            __('List of statics pages'),
            'text'
        );
        $w->mrvbToC->setting(
            'static',
            __('Display the statics pages'),
            0,
            'check'
        );
        $w->mrvbToC->setting(
            'hidestatics',
            __('Activate script "hide/show" for the static pages'),
            0,
            'check'
        );
        $w->mrvbToC->setting(
            'more',
            __('Link text used by the script to show (empty = ▼):'),
            '',
            'text'
        );
        $w->mrvbToC->setting(
            'less',
            __('Link text used by the script to hide (empty = ▲):'),
            '',
            'text'
        );
        $w->mrvbToC->setting(
            'homeonly',
            __('Display on:'),
            0,
            'combo',
            [
                __('All pages')           => 0,
                __('Home page only')      => 1,
                __('Except on home page') => 2,
            ]
        );
        $w->mrvbToC->setting(
            'content_only',
            __('Content only'),
            0,
            'check'
        );
        $w->mrvbToC->setting(
            'CSSclass',
            __('CSS class:'),
            '',
            'text'
        );
        $w->mrvbToC->setting(
            'offline',
            __('Offline'),
            0,
            'check'
        );
    }
}
