<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of mrvbToC, a plugin for Dotclear 2
#
# © Mirovinben (http://www.mirovinben.fr/)
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }
require_once dirname(__FILE__).'/_widgets.php';

$core->addBehavior('publicHeadContent',array('mrvbToCpublicBehaviors','publicHeadContent'));
$core->addBehavior('publicFooterContent',array('mrvbToCpublicBehaviors','publicFooterContent'));

class mrvbToCpublicBehaviors
{
	public static function publicHeadContent($core)
	{
		echo
'<style type="text/css">
	.mrvbToC .less.active .read-more,
	.mrvbToC .read-less{display: none;}
	.mrvbToC .less.active .read-less{display: inline-block;}
</style>'."\n";
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
</script>'."\n";
	}
}

class tplmrvbToC
{
	public static function mrvb_ToC($w)
	{
		global $core, $_ctx;
		if ($w->offline) {
			return;
		}
		if (($w->homeonly == 1 && $core->url->type != 'default') ||
			($w->homeonly == 2 && $core->url->type == 'default')) {
			return;
		}
		$max_level = 65535;
		$level = 0;
		$ref_level = $level;
		$more = ($w->more ? html::escapeHTML($w->more) : '▼');
		$less = ($w->less ? html::escapeHTML($w->less) : '▲');
		$posts = ($w->titleposts ? html::escapeHTML($w->titleposts) : 'List of posts');
		$pages = ($w->titlepages ? html::escapeHTML($w->titlepages) : 'List of pages');
		$statics = ($w->titlestatics ? html::escapeHTML($w->titlestatics) : 'List of static pages');
		$iniqry = ' SELECT P.post_url, P.blog_id, P.cat_id, P.post_dt, P.post_type, P.post_title, P.post_password, P.post_status, P.post_selected, P.post_position FROM '.DC_DBPREFIX.'post AS P';
		$collate = ' ORDER BY LOWER(P.post_title) ';
		if (DC_DBDRIVER == 'mysqli' || DC_DBDRIVER == 'mysql') {
			$collate = ' ORDER BY P.post_title COLLATE utf8_unicode_ci ';
		} elseif (DC_DBDRIVER == 'pgsql') {
			$_rs = $core->con->select("SELECT * FROM pg_collation WHERE (collcollate LIKE '%.utf8')");
			if(!$_rs->isEmpty()) {
				$collate = ' ORDER BY P.post_title COLLATE "'.$_rs->f('collname').'" ';
			}
		} elseif (DC_DBDRIVER == 'sqlite' && class_exists('Collator') && method_exists($core->con->link(),'sqliteCreateCollation')) {
			$utf8_unicode_ci = new Collator('root');
			if ($core->con->link()->sqliteCreateCollation('utf8_unicode_ci',array($utf8_unicode_ci,'compare'))) {
				$collate = ' ORDER BY P.post_title COLLATE utf8_unicode_ci ';
			}		
		}		
		switch ($w->sortby){
			case 'date-desc' : $sort = ' ORDER BY P.post_dt DESC'; break;
			case 'date-asc'  : $sort = ' ORDER BY P.post_dt ASC'; break;
			case 'title'     : $sort = $collate; break;
			default          : $sort = '';
		}
		$res  = ($w->title ? $w->renderTitle(html::escapeHTML($w->title))."\n" : '');

		if ($w->post) {
			$query  = $iniqry;
			$query .= ' WHERE (P.blog_id = \''.$core->blog->id.'\') AND (P.post_status = 1) AND (P.post_type = \'post\')';
			if ($w->nopassword){
				$query .= ' AND (P.post_password IS NULL)';
			}
			$query .= $sort;
			$res_post = $core->con->select($query);
			if ($res_post->count() > 0){
				$res .= '<ul>'."\n".'<li class="posts">'.$posts;
				if ($w->count){
					$res .= ' <span class="postcount">('.$res_post->count().')</span>';
				}
				if (!$w->nocategory){
					$query  = $iniqry;
					$query .= ' WHERE (P.blog_id = \''.$core->blog->id.'\') AND (P.cat_id IS NULL) AND (P.post_status = 1) AND (P.post_type = \'post\')';
					if ($w->nopassword){
						$query .= ' AND (P.post_password IS NULL)';
					}
					$query .= $sort;
					$res_post = $core->con->select($query);
					if ($res_post->count() > 0){
						$res .= "\n".'<ul>'."\n";
						while ($res_post->fetch()) {
							($res_post->post_password == NULL ? $pwd = '' : $pwd = ' password');
							$res .= '<li class="entry'.$pwd.'"><a href="'.$core->blog->url.$core->url->getBase('post').'/'.$res_post->post_url.'">'.htmlentities($res_post->post_title, ENT_QUOTES, 'UTF-8').'</a></li>'."\n";
						}
						$res .= '</ul>'."\n";
					}
				}
				$rs = $core->blog->getCategories();
				if (!$rs->isEmpty()) {
					$ref_level = $level = $rs->level-1;
					$cat_level = 0;
					while ($rs->fetch()) {
						if ($rs->level <= $max_level ) {
							$postcount = $rs->nb_total;
							$class = ' class="category cat'.$rs->cat_id;
							if (($core->url->type == 'category' && $_ctx->categories instanceof record && $_ctx->categories->cat_id == $rs->cat_id)
							|| ($core->url->type == 'post' && $_ctx->posts instanceof record && $_ctx->posts->cat_id == $rs->cat_id)) {
									$class .= ' category-current';
							}
							$class .= '"';
							if ($rs->level > $level) {
									$cat_level += 1;
									$res .= str_repeat('<ul>'."\n".'<li'.$class.'>',$rs->level - $level);
							} elseif ($rs->level < $level) {
									$cat_level -= 1;
									$res .= str_repeat('</li>'."\n".'</ul>',-($rs->level - $level));
							}
							if ($rs->level <= $level) {
									$res .= '</li>'."\n".'<li'.$class.'>';
							}
							$res .= '<a href="'.$core->blog->url.$core->url->getBase('category').'/'.$rs->cat_url.'"';
							$res .= '>'.html::escapeHTML(__($rs->cat_title)).'</a>';
							if ($w->count){
								$res .= ' <span class="postcount">('.$postcount.')</span>';
							}
							$query  = $iniqry;
							$query .= ' WHERE (P.blog_id = \''.$core->blog->id.'\') AND (P.cat_id = '.$rs->cat_id.') AND (P.post_status = 1) AND (P.post_type = \'post\')';
							if ($w->nopassword){
								$query .= ' AND (P.post_password IS NULL)';
							}
							$query .= $sort;
							$res_post = $core->con->select($query);
							if ($res_post->count() > 0){
								if ($w->hideposts) {
									$res .= '<span class="less"><a class="read-more" href="#read">'.$more.'</a><a class="read-less" href="#read">'.$less.'</a></span>'."\n".'<div class="more">'."\n";
								}
								$res .= '<ul>'."\n";
								while ($res_post->fetch()) {
									($res_post->post_password == NULL ? $pwd = '' : $pwd = ' password');
									$res .= '<li class="entry'.$pwd.'"><a href="'.$core->blog->url.$core->url->getBase('post').'/'.$res_post->post_url.'">'.htmlentities($res_post->post_title, ENT_QUOTES, 'UTF-8').'</a></li>'."\n";
								}
								$res .= '</ul>'."\n";
								if ($w->hideposts) {
									$res .= '</div>'."\n";
								}
							}
						}
						$level = $rs->level;
					}
				}
				if ($ref_level - $level < 0) {
					$res .= str_repeat('</li>'."\n".'</ul>'."\n",-($ref_level - $level));
				}
				$res .= '</li>'."\n".'</ul>'."\n";
			}
		}

		if ($w->page) {
			$query  = $iniqry;
			$query .= ' WHERE (P.blog_id = \''.$core->blog->id.'\') AND (P.post_status = 1) AND (P.post_type = \'page\')';
			if ($w->nopassword){
				$query .= ' AND (P.post_password IS NULL)';
			}
			if ($w->selected){
				$query .= ' AND (P.post_selected = 0)';
			}
			if ($w->position){
				$query .= ' ORDER BY P.post_position ASC';
			} else {
				$query .= $sort;
			}
			$res_post = $core->con->select($query);
			if ($res_post->count() > 0){
				$res .= '<ul>'."\n".'<li class="pages">'.$pages;
				if ($w->count){
					$res .= ' <span class="postcount">('.$res_post->count().')</span>';
				}
				if ($w->hidepages) {
					$res .= '<span class="less"><a class="read-more" href="#read">'.$more.'</a><a class="read-less" href="#read">'.$less.'</a></span>'."\n".'<div class="more">'."\n";
				}
				$res .= '<ul>'."\n";
				while ($res_post->fetch()) {
					($res_post->post_password == NULL ? $pwd = '' : $pwd = ' password');
					$res .= '<li class="entry'.$pwd.'"><a href="'.$core->blog->url.$core->url->getBase('pages').'/'.$res_post->post_url.'">'.htmlentities($res_post->post_title, ENT_QUOTES, 'UTF-8').'</a></li>'."\n";
				}
				$res .= '</ul>'."\n";
				if ($w->hidepages) {
					$res .= '</div>'."\n";
				}
				$res .= '</li>'."\n".'</ul>'."\n";
			}
		}

		if ($w->static) {
			$query  = $iniqry;
			$query .= ' WHERE (P.blog_id = \''.$core->blog->id.'\') AND (P.post_status = 1) AND (P.post_type = \'related\')';
			if ($w->nopassword){
				$query .= ' AND (P.post_password IS NULL)';
			}
			if ($w->selected){
				$query .= ' AND (P.post_selected > 0)';
			}
			$query .= $sort;
			$res_post = $core->con->select($query);
			if ($res_post->count() > 0){
				$res .= '<ul>'."\n".'<li class="static">'.$statics;
				if ($w->count){
					$res .= ' <span class="postcount">('.$res_post->count().')</span>';
				}
				if ($w->hidestatics) {
					$res .= '<span class="less"><a class="read-more" href="#read">'.$more.'</a><a class="read-less" href="#read">'.$less.'</a></span>'."\n".'<div class="more">'."\n";
				}
				$res .= '<ul>'."\n";
				while ($res_post->fetch()) {
					($res_post->post_password == NULL ? $pwd = '' : $pwd = ' password');
					$res .= '<li class="entry'.$pwd.'"><a href="'.$core->blog->url.$core->url->getBase('related').'/'.$res_post->post_url.'">'.htmlentities($res_post->post_title, ENT_QUOTES, 'UTF-8').'</a></li>'."\n";
				}
				$res .= '</ul>'."\n";
				if ($w->hidestatics) {
					$res .= '
						</div>'."\n";
				}
				$res .= '</li>'."\n".'</ul>'."\n";
			}
		}

		return $w->renderDiv($w->content_only,'mrvbToC '.$w->CSSclass,'',$res);
	}
}