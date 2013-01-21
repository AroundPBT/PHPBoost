<?php
/*##################################################
 *                          	  index.php
 *                            -------------------
 *   begin                : April 12 2010
 *   copyright            : (C) 2010 Benoit Sautel
 *   email                : benoit.sautel@phpboost.com
 *
 *
 ###################################################
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 ###################################################*/

defined('PATH_TO_ROOT') or define('PATH_TO_ROOT', '../..');

require_once PATH_TO_ROOT . '/kernel/init.php';

$url_controller_mappers = array(
	new UrlControllerMapper('AdminMailConfigController', '`^/mail/?$`'),
	new UrlControllerMapper('AdminGeneralConfigController', '`^(?:/general/?)?$`'),
	new UrlControllerMapper('AdminAdvancedConfigController', '`^/advanced/?$`'),
	new UrlControllerMapper('SendMailUnlockAdminController', '`^/advanced/unlock/?$`')
);
DispatchManager::dispatch($url_controller_mappers);
?>