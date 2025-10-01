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
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\widgets\WidgetsElement;
use Dotclear\Plugin\widgets\WidgetsStack;

class Widgets
{
    /**
     * Initializes the pages widget.
     *
     * @param      WidgetsStack  $widgets  The widgets
     */
    public static function initWidgets(WidgetsStack $widgets): void
    {
        $widgets->create(
            'mrvbToC',
            __('Table of Contents'),
            self::mrvb_ToC(...),
            null,
            __('Display a list of entries including posts, pages and included pages)')
        );

        $widgets->mrvbToC->setting(
            'title',
            __('Title (optional):'),
            __('Table of Contents'),
            'text'
        );
        $widgets->mrvbToC->setting(
            'count',
            __('Display entries counts'),
            0,
            'check'
        );
        $widgets->mrvbToC->setting(
            'nopassword',
            __('Display only entries without password'),
            0,
            'check'
        );
        $widgets->mrvbToC->setting(
            'selected',
            __('Display only entries marked as visible to their widget'),
            0,
            'check'
        );
        $widgets->mrvbToC->setting(
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
        $widgets->mrvbToC->setting(
            'titleposts',
            __('Title of the posts list:'),
            __('List of posts'),
            'text'
        );
        $widgets->mrvbToC->setting(
            'post',
            __('Display the posts'),
            1,
            'check'
        );
        $widgets->mrvbToC->setting(
            'nocategory',
            __('Display only the posts with categories'),
            0,
            'check'
        );
        $widgets->mrvbToC->setting(
            'hideposts',
            __('Activate script "hide/show" for the posts with categories'),
            0,
            'check'
        );
        $widgets->mrvbToC->setting(
            'titlepages',
            __('Title of the pages list:'),
            __('List of pages'),
            'text'
        );
        $widgets->mrvbToC->setting(
            'page',
            __('Display the pages'),
            0,
            'check'
        );
        $widgets->mrvbToC->setting(
            'position',
            __('Force display in ascending order of pages\'s positions'),
            0,
            'check'
        );
        $widgets->mrvbToC->setting(
            'hidepages',
            __('Activate script "hide/show" for the pages'),
            0,
            'check'
        );
        $widgets->mrvbToC->setting(
            'titlestatics',
            __('Title of the statics pages list:'),
            __('List of statics pages'),
            'text'
        );
        $widgets->mrvbToC->setting(
            'static',
            __('Display the statics pages'),
            0,
            'check'
        );
        $widgets->mrvbToC->setting(
            'hidestatics',
            __('Activate script "hide/show" for the static pages'),
            0,
            'check'
        );
        $widgets->mrvbToC->setting(
            'more',
            __('Link text used by the script to show (empty = ▼):'),
            '',
            'text'
        );
        $widgets->mrvbToC->setting(
            'less',
            __('Link text used by the script to hide (empty = ▲):'),
            '',
            'text'
        );

        $widgets->mrvbToC->addHomeOnly();
        $widgets->mrvbToC->addContentOnly();
        $widgets->mrvbToC->addClass();
        $widgets->mrvbToC->addOffline();
    }

    public static function mrvb_ToC(WidgetsElement $widgets)
    {
        if ($widgets->offline) {
            return;
        }
        if (!$widgets->checkHomeOnly(App::url()->type)) {
            return '';
        }

        $max_level = 65535;
        $level     = 0;
        $ref_level = $level;
        $more      = ($widgets->more ? html::escapeHTML($widgets->more) : '▼');
        $less      = ($widgets->less ? html::escapeHTML($widgets->less) : '▲');
        $posts     = ($widgets->titleposts ? html::escapeHTML($widgets->titleposts) : 'List of posts');
        $pages     = ($widgets->titlepages ? html::escapeHTML($widgets->titlepages) : 'List of pages');
        $statics   = ($widgets->titlestatics ? html::escapeHTML($widgets->titlestatics) : 'List of static pages');
        $iniqry    = ' SELECT P.post_url, P.blog_id, P.cat_id, P.post_dt, P.post_type, P.post_title, P.post_password, P.post_status, P.post_selected, P.post_position FROM ' . DC_DBPREFIX . 'post AS P';
        $collate   = ' ORDER BY LOWER(P.post_title) ';
        if (DC_DBDRIVER == 'mysqli' || DC_DBDRIVER == 'mysql') {
            $collate = ' ORDER BY P.post_title COLLATE utf8_unicode_ci ';
        } elseif (DC_DBDRIVER == 'pgsql') {
            $_rs = App::db()->con()->select("SELECT * FROM pg_collation WHERE (collcollate LIKE '%.utf8')");
            if (!$_rs->isEmpty()) {
                $collate = ' ORDER BY P.post_title COLLATE "' . $_rs->f('collname') . '" ';
            }
        } elseif (DC_DBDRIVER == 'sqlite' && class_exists('Collator') && method_exists(App::db()->con()->link(), 'sqliteCreateCollation')) {
            $utf8_unicode_ci = new Collator('root');
            if (App::db()->con()->link()->sqliteCreateCollation('utf8_unicode_ci', [$utf8_unicode_ci,'compare'])) {
                $collate = ' ORDER BY P.post_title COLLATE utf8_unicode_ci ';
            }
        }
        switch ($widgets->sortby) {
            case 'date-desc': $sort = ' ORDER BY P.post_dt DESC';

                break;
            case 'date-asc': $sort = ' ORDER BY P.post_dt ASC';

                break;
            case 'title': $sort = $collate;

                break;
            default: $sort = '';
        }
        $res = ($widgets->title ? $widgets->renderTitle(html::escapeHTML($widgets->title)) . "\n" : '');

        if ($widgets->post) {
            $query = $iniqry;
            $query .= ' WHERE (P.blog_id = \'' . App::blog()->id . '\') AND (P.post_status = 1) AND (P.post_type = \'post\')';
            if ($widgets->nopassword) {
                $query .= ' AND (P.post_password IS NULL)';
            }
            $query .= $sort;
            $res_post = App::db()->con()->select($query);
            if ($res_post->count() > 0) {
                $res .= '<ul>' . "\n" . '<li class="posts">' . $posts;
                if ($widgets->count) {
                    $res .= ' <span class="postcount">(' . $res_post->count() . ')</span>';
                }
                if (!$widgets->nocategory) {
                    $query = $iniqry;
                    $query .= ' WHERE (P.blog_id = \'' . App::blog()->id . '\') AND (P.cat_id IS NULL) AND (P.post_status = 1) AND (P.post_type = \'post\')';
                    if ($widgets->nopassword) {
                        $query .= ' AND (P.post_password IS NULL)';
                    }
                    $query .= $sort;
                    $res_post = App::db()->con()->select($query);
                    if ($res_post->count() > 0) {
                        $res .= "\n" . '<ul>' . "\n";
                        while ($res_post->fetch()) {
                            ($res_post->post_password == null ? $pwd = '' : $pwd = ' password');
                            $res .= '<li class="entry' . $pwd . '"><a href="' . App::blog()->url . App::url()->getBase('post') . '/' . $res_post->post_url . '">' . htmlentities($res_post->post_title, ENT_QUOTES, 'UTF-8') . '</a></li>' . "\n";
                        }
                        $res .= '</ul>' . "\n";
                    }
                }
                $rs = App::blog()->getCategories();
                if (!$rs->isEmpty()) {
                    $ref_level = $level = $rs->level - 1;
                    $cat_level = 0;
                    while ($rs->fetch()) {
                        if ($rs->level <= $max_level) {
                            $postcount = $rs->nb_total;
                            $class     = ' class="category cat' . $rs->cat_id;
                            if ((App::url()->type == 'category' && App::frontend()->context()->categories instanceof record && App::frontend()->context()->categories->cat_id == $rs->cat_id)
                            || (App::url()->type == 'post' && App::frontend()->context()->posts instanceof record && App::frontend()->context()->posts->cat_id == $rs->cat_id)) {
                                $class .= ' category-current';
                            }
                            $class .= '"';
                            if ($rs->level > $level) {
                                $cat_level += 1;
                                $res .= str_repeat('<ul>' . "\n" . '<li' . $class . '>', $rs->level - $level);
                            } elseif ($rs->level < $level) {
                                $cat_level -= 1;
                                $res .= str_repeat('</li>' . "\n" . '</ul>', -($rs->level - $level));
                            }
                            if ($rs->level <= $level) {
                                $res .= '</li>' . "\n" . '<li' . $class . '>';
                            }
                            $res .= '<a href="' . App::blog()->url . App::url()->getBase('category') . '/' . $rs->cat_url . '"';
                            $res .= '>' . html::escapeHTML(__($rs->cat_title)) . '</a>';
                            if ($widgets->count) {
                                $res .= ' <span class="postcount">(' . $postcount . ')</span>';
                            }
                            $query = $iniqry;
                            $query .= ' WHERE (P.blog_id = \'' . App::blog()->id . '\') AND (P.cat_id = ' . $rs->cat_id . ') AND (P.post_status = 1) AND (P.post_type = \'post\')';
                            if ($widgets->nopassword) {
                                $query .= ' AND (P.post_password IS NULL)';
                            }
                            $query .= $sort;
                            $res_post = App::db()->con()->select($query);
                            if ($res_post->count() > 0) {
                                if ($widgets->hideposts) {
                                    $res .= '<span class="less"><a class="read-more" href="#read">' . $more . '</a><a class="read-less" href="#read">' . $less . '</a></span>' . "\n" . '<div class="more">' . "\n";
                                }
                                $res .= '<ul>' . "\n";
                                while ($res_post->fetch()) {
                                    ($res_post->post_password == null ? $pwd = '' : $pwd = ' password');
                                    $res .= '<li class="entry' . $pwd . '"><a href="' . App::blog()->url . App::url()->getBase('post') . '/' . $res_post->post_url . '">' . htmlentities($res_post->post_title, ENT_QUOTES, 'UTF-8') . '</a></li>' . "\n";
                                }
                                $res .= '</ul>' . "\n";
                                if ($widgets->hideposts) {
                                    $res .= '</div>' . "\n";
                                }
                            }
                        }
                        $level = $rs->level;
                    }
                }
                if ($ref_level - $level < 0) {
                    $res .= str_repeat('</li>' . "\n" . '</ul>' . "\n", -($ref_level - $level));
                }
                $res .= '</li>' . "\n" . '</ul>' . "\n";
            }
        }

        if ($widgets->page) {
            $query = $iniqry;
            $query .= ' WHERE (P.blog_id = \'' . App::blog()->id . '\') AND (P.post_status = 1) AND (P.post_type = \'page\')';
            if ($widgets->nopassword) {
                $query .= ' AND (P.post_password IS NULL)';
            }
            if ($widgets->selected) {
                $query .= ' AND (P.post_selected = 0)';
            }
            if ($widgets->position) {
                $query .= ' ORDER BY P.post_position ASC';
            } else {
                $query .= $sort;
            }
            $res_post = App::db()->con()->select($query);
            if ($res_post->count() > 0) {
                $res .= '<ul>' . "\n" . '<li class="pages">' . $pages;
                if ($widgets->count) {
                    $res .= ' <span class="postcount">(' . $res_post->count() . ')</span>';
                }
                if ($widgets->hidepages) {
                    $res .= '<span class="less"><a class="read-more" href="#read">' . $more . '</a><a class="read-less" href="#read">' . $less . '</a></span>' . "\n" . '<div class="more">' . "\n";
                }
                $res .= '<ul>' . "\n";
                while ($res_post->fetch()) {
                    ($res_post->post_password == null ? $pwd = '' : $pwd = ' password');
                    $res .= '<li class="entry' . $pwd . '"><a href="' . App::blog()->url . App::url()->getBase('pages') . '/' . $res_post->post_url . '">' . htmlentities($res_post->post_title, ENT_QUOTES, 'UTF-8') . '</a></li>' . "\n";
                }
                $res .= '</ul>' . "\n";
                if ($widgets->hidepages) {
                    $res .= '</div>' . "\n";
                }
                $res .= '</li>' . "\n" . '</ul>' . "\n";
            }
        }

        if ($widgets->static) {
            $query = $iniqry;
            $query .= ' WHERE (P.blog_id = \'' . App::blog()->id . '\') AND (P.post_status = 1) AND (P.post_type = \'related\')';
            if ($widgets->nopassword) {
                $query .= ' AND (P.post_password IS NULL)';
            }
            if ($widgets->selected) {
                $query .= ' AND (P.post_selected > 0)';
            }
            $query .= $sort;
            $res_post = App::db()->con()->select($query);
            if ($res_post->count() > 0) {
                $res .= '<ul>' . "\n" . '<li class="static">' . $statics;
                if ($widgets->count) {
                    $res .= ' <span class="postcount">(' . $res_post->count() . ')</span>';
                }
                if ($widgets->hidestatics) {
                    $res .= '<span class="less"><a class="read-more" href="#read">' . $more . '</a><a class="read-less" href="#read">' . $less . '</a></span>' . "\n" . '<div class="more">' . "\n";
                }
                $res .= '<ul>' . "\n";
                while ($res_post->fetch()) {
                    ($res_post->post_password == null ? $pwd = '' : $pwd = ' password');
                    $res .= '<li class="entry' . $pwd . '"><a href="' . App::blog()->url . App::url()->getBase('related') . '/' . $res_post->post_url . '">' . htmlentities($res_post->post_title, ENT_QUOTES, 'UTF-8') . '</a></li>' . "\n";
                }
                $res .= '</ul>' . "\n";
                if ($widgets->hidestatics) {
                    $res .= '
						</div>' . "\n";
                }
                $res .= '</li>' . "\n" . '</ul>' . "\n";
            }
        }

        return $widgets->renderDiv((bool) $widgets->content_only, 'mrvbToC ' . $widgets->CSSclass, '', $res);
    }
}
