<?php
/*##################################################
 *                             pop_up_comments.php
 *                            -------------------
 *   begin                : June 24, 2008
 *   copyright          : (C) 2008, Sautel Benoit
 *   email                : ben.popeye@phpboost.com
 *
 *
###################################################
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 * 
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
###################################################*/

define('PATH_TO_ROOT', '../../..');
require_once(PATH_TO_ROOT . '/kernel/begin.php');
define('TITLE', $LANG['title_com']);
require_once(PATH_TO_ROOT . '/kernel/header_no_display.php');

if( !empty($_GET['com']) )
{
	if( !preg_match('`([0-9]+)([a-z]+)([0-9]*)`', trim($_GET['com']), $array_get) )
		$array_get = array('', '', '', '');
	$idcom = (empty($array_get[3]) && !empty($_POST['idcom'])) ? numeric($_POST['idcom']) : $array_get[3];
	
	require_once(PATH_TO_ROOT . '/kernel/framework/content/comments.class.php');
	$Comments = new Comments($array_get[2], $array_get[1], transid('?com=' . $array_get[1] . $array_get[2] . '%s', ''), $array_get[2]);
	$Comments->set_arg($idcom, PATH_TO_ROOT . '/kernel/framework/content/pop_up_comments.php'); //On met � jour les attributs de l'objet.
	
	//On affiche les commentaires
	echo $Comments->Display(POP_UP_WINDOW, null, retrieve(GET, 'path_to_root', TSTRING_UNSECURE));
}

include_once(PATH_TO_ROOT . '/kernel/footer_no_display.php');
?>