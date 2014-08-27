<?php
/*##################################################
 *                               admin_forum_config.php
 *                            -------------------
 *   begin                : October 30, 2005
 *   copyright            : (C) 2005 Viarre R�gis
 *   email                : crowkait@phpboost.com
 *
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

 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 ###################################################*/

require_once('../admin/admin_begin.php');
load_module_lang('forum'); //Chargement de la langue du module.
define('TITLE', $LANG['administration']);
require_once('../admin/admin_header.php');

$get_id = retrieve(GET, 'id', 0);	

//Si c'est confirm� on execute
if (!empty($_POST['valid']))
{
	$result = $Sql->query_while("SELECT id, special 
	FROM " . PREFIX . "forum_ranks", __LINE__, __FILE__);
	while ($row = $Sql->fetch_assoc($result))
	{
		$name = retrieve(POST, $row['id'] . 'name', '');
		$msg = retrieve(POST, $row['id'] . 'msg', 0);
		$icon = retrieve(POST, $row['id'] . 'icon', '');

		if (!empty($name) && $row['special'] != 1)
			$Sql->query_inject("UPDATE " . PREFIX . "forum_ranks SET name = '" . $name . "', msg = '" . $msg . "', icon = '" . $icon . "' WHERE id = '" . $row['id'] . "'", __LINE__, __FILE__);
		else
			$Sql->query_inject("UPDATE " . PREFIX . "forum_ranks SET name = '" . $name . "', icon = '" . $icon . "' WHERE id = '" . $row['id'] . "'", __LINE__, __FILE__);
	}
	$Sql->query_close($result);

	ForumRanksCache::invalidate();
		
	AppContext::get_response()->redirect(HOST . SCRIPT);	
}
elseif (!empty($_GET['del']) && !empty($get_id)) //Suppression du rang.
{
	//On supprime dans la bdd.
	$Sql->query_inject("DELETE FROM " . PREFIX . "forum_ranks WHERE id = '" . $get_id . "'", __LINE__, __FILE__);	

	###### R�g�n�ration du cache des rangs #######
	ForumRanksCache::invalidate();
	
	AppContext::get_response()->redirect(HOST . SCRIPT); 	
}
else //Sinon on rempli le formulaire	 
{	
	$template = new FileTemplate('forum/admin_ranks.tpl');

	$template->put_all(array(
		'L_REQUIRE_RANK_NAME' => $LANG['require_rank_name'],
		'L_REQUIRE_NBR_MSG_RANK' => $LANG['require_nbr_msg_rank'],
		'L_CONFIRM_DEL_RANK' => $LANG['confirm_del_rank'],
		'L_FORUM_MANAGEMENT' => $LANG['forum_management'],
		'L_CAT_MANAGEMENT' => $LANG['cat_management'],
		'L_ADD_CAT' => $LANG['cat_add'],
		'L_FORUM_CONFIG' => $LANG['forum_config'],
		'L_FORUM_GROUPS' => $LANG['forum_groups_config'],
		'L_FORUM_RANKS_MANAGEMENT' => $LANG['rank_management'],
		'L_FORUM_ADD_RANKS' => $LANG['rank_add'],
		'L_RANK_NAME' => $LANG['rank_name'],
		'L_NBR_MSG' => $LANG['nbr_msg'],
		'L_IMG_ASSOC' => $LANG['img_assoc'],
		'L_DELETE' => $LANG['delete'],
		'L_UPDATE' => $LANG['update'],
		'L_RESET' => $LANG['reset'],
		'L_ADD' => $LANG['add']
	));

	//On recup�re les images des groupes
	
	$rank_options_array = array();
	$image_folder_path = new Folder(PATH_TO_ROOT . '/forum/templates/images/ranks');
	foreach ($image_folder_path->get_files('`\.(png|jpg|bmp|gif)$`i') as $image)
	{
		$file = $image->get_name();
		$rank_options_array[] = $file;
	}	
	
	$ranks_cache = ForumRanksCache::load()->get_ranks();
	
	foreach($ranks_cache as $msg => $row)
	{				
		if ($row['special'] == 0)
			$del = '<a href="admin_ranks.php?del=1&amp;id=' . $row['id'] . '" class="fa fa-delete" data-confirmation="delete-element"></a>';
		else
			$del = $LANG['special_rank'];

		$rank_options = '<option value="">--</option>';
		foreach ($rank_options_array as $icon)
		{			
			$selected = ($icon == $row['icon']) ? ' selected="selected"' : '';
			$rank_options .= '<option value="' . $icon . '"' . $selected . '>' . $icon . '</option>';
		}
		
		$template->assign_block_vars('rank', array(
			'ID' => $row['id'],
			'RANK' => $row['name'],
			'MSG' => ($row['special'] == 0) ? '<input type="text" maxlength="6" size="6" name="' . $row['id'] . 'msg" value="' . $msg . '">' : $LANG['special_rank'],
			'RANK_OPTIONS' => $rank_options,
			'IMG_RANK' => $row['icon'],
			'DELETE' => $del
		));
	}
	
	$template->display();
}

require_once('../admin/admin_footer.php');
?>