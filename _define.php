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

$this->registerModule(
	/* Name        */	"mrvbToC",
	/* Description */	"Mrvb Table of Contents",
	/* Author      */	"Mirovinben",
	/* Version     */	'0.3.11',
	/* Properties  */	array(
							'permissions' => 'usage,contentadmin',
							'type'        => 'plugin',
							'dc_min'      => '2.7',
							'support'     => 'http://www.mirovinben.fr/blog/index.php?post/id3629',
							'details'     => 'http://plugins.dotaddict.org/dc2/details/mrvbTOC'
						)
);