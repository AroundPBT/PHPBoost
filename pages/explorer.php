<?php
/*##################################################
 *                               explorer.php
 *                            -------------------
 *   begin                : August 13, 2007
 *   copyright            : (C) 2007 Sautel Benoit
 *   email                : ben.popeye@phpboost.com
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

require_once('../kernel/begin.php'); 
require_once('../pages/pages_begin.php'); 
define('TITLE', $LANG['pages_explorer']);
$cat = retrieve(GET, 'cat', 0);
$Bread_crumb->add($LANG['pages'], url('pages.php'));
$Bread_crumb->add($LANG['pages_explorer'], url('explorer.php'));
require_once('../kernel/header.php');

//Configuration des authorisations
$config_authorizations = $pages_config->get_authorizations();

$tpl = new FileTemplate('pages/explorer.tpl');

$module_data_path = $tpl->get_pictures_data_path();

//Liste des dossiers de la racine
$root = '';
foreach (PagesCategoriesCache::load()->get_categories() as $key => $cat)
{
	if ($cat['id_parent'] == 0)
	{
		//Autorisation particulière ?
		$special_auth = !empty($cat['auth']);
		//Vérification de l'autorisation d'éditer la page
		if (($special_auth && AppContext::get_current_user()->check_auth($cat['auth'], READ_PAGE)) || (!$special_auth && AppContext::get_current_user()->check_auth($config_authorizations, READ_PAGE)))
		{
			$root .= '<li><a href="javascript:open_cat(' . $key . '); show_pages_cat_contents(' . $cat['id_parent'] . ', 0);"><i class="fa fa-folder"></i>' . stripslashes($cat['title']) . '</a></li>';
		}
	}
}
//Liste des fichiers de la racine
$result = PersistenceContext::get_querier()->select("SELECT title, id, encoded_title, auth
	FROM " . PREFIX . "pages
	WHERE id_cat = 0 AND is_cat = 0
	ORDER BY is_cat DESC, title ASC");
while ($row = $result->fetch())
{
	//Autorisation particulière ?
	$special_auth = !empty($row['auth']);
	$array_auth = unserialize($row['auth']);
	//Vérification de l'autorisation d'éditer la page
	if (($special_auth && AppContext::get_current_user()->check_auth($array_auth, READ_PAGE)) || (!$special_auth && AppContext::get_current_user()->check_auth($config_authorizations, READ_PAGE)))
	{
		$root .= '<li><a href="' . url('pages.php?title=' . $row['encoded_title'], $row['encoded_title']) . '"><i class="fa fa-file"></i>' . stripslashes($row['title']) . '</a></li>';
	}
}
$result->dispose();

$tpl->put_all(array(
	'PAGES_PATH' => $module_data_path,
	'TITLE' => $LANG['pages_explorer'],
	'L_ROOT' => $LANG['pages_root'],
	'SELECTED_CAT' => $cat > 0 ? $cat : 0,
	'ROOT_CONTENTS' => $root,
	'L_CATS' => $LANG['pages_cats_tree']
));

$contents = '';
$result = PersistenceContext::get_querier()->select("SELECT c.id, p.title, p.encoded_title
FROM " . PREFIX . "pages_cats c
LEFT JOIN " . PREFIX . "pages p ON p.id = c.id_page
WHERE c.id_parent = 0
ORDER BY p.title ASC");
while ($row = $result->fetch())
{
	$sub_cats_number = PersistenceContext::get_querier()->count(PREFIX . "pages_cats", 'WHERE id_parent=:id_parent', array('id_parent' => $row['id']));
	if ($sub_cats_number > 0)
	{	
		$tpl->assign_block_vars('list', array(
			'DIRECTORY' => '<li class="sub"><a class="parent" href="javascript:show_pages_cat_contents(' . $row['id'] . ', 0);"><i class="fa fa-plus-square-o" id="img2_' . $row['id'] . '"></i><i class="fa fa-folder" id ="img_' . $row['id'] . '"></i></a><a id="class_' . $row['id'] . '" href="javascript:open_cat(' . $row['id'] . ');">' . stripslashes($row['title']) . '</a><span id="cat_' . $row['id'] . '"></span></li>'
		));
	}
	else
	{
		$tpl->assign_block_vars('list', array(
			'DIRECTORY' => '<li class="sub"><a id="class_' . $row['id'] . '" href="javascript:open_cat(' . $row['id'] . ');"><i class="fa fa-folder"></i>' . stripslashes($row['title']) . '</a><span id="cat_' . $row['id'] . '"></span></li>'
		));
	}
}
$result->dispose();
$tpl->put_all(array(
	'SELECTED_CAT' => 0,
	'CAT_0' => 'selected',
	'CAT_LIST' => ''
));

$tpl->display();


require_once('../kernel/footer.php');

?>