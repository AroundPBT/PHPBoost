<?php
/*##################################################
 *                               explorer.php
 *                            -------------------
 *   begin                : May 31, 2007
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
include_once('../wiki/wiki_functions.php'); 
load_module_lang('wiki');

define('TITLE', $LANG['wiki_explorer']);

$bread_crumb_key = 'wiki_explorer';
require_once('../wiki/wiki_bread_crumb.php');

$cat = retrieve(GET, 'cat', 0);

require_once('../kernel/header.php');

$template = new FileTemplate('wiki/explorer.tpl');

$module_data_path = $template->get_pictures_data_path();

//Contenu de la racine:
$root = '';
foreach (WikiCategoriesCache::load()->get_categories() as $key => $cat)
{
	if ($cat['id_parent'] == 0)
		$root .= '<li><a href="javascript:open_cat(' . $key . '); show_wiki_cat_contents(' . $cat['id_parent'] . ', 0);"><i class="fa fa-folder"></i>' . stripslashes($cat['title']) . '</a></li>';
}
$result = PersistenceContext::get_querier()->select("SELECT title, id, encoded_title
	FROM " . PREFIX . "wiki_articles a
	WHERE id_cat = 0
	AND a.redirect = 0
	ORDER BY is_cat DESC, title ASC");
while ($row = $result->fetch())
{
	$root .= '<li><a href="' . url('wiki.php?title=' . $row['encoded_title'], $row['encoded_title']) . '"><i class="fa fa-file"></i>' . stripslashes($row['title']) . '</a></li>';
}
$result->dispose();


$template->put_all(array(
	'TITLE' => $LANG['wiki_explorer'],
	'L_ROOT' => $LANG['wiki_root'],
	'SELECTED_CAT' => $cat > 0 ? $cat : 0,
	'ROOT_CONTENTS' => $root,
	'L_CATS' => $LANG['wiki_cats_tree'],
));

$contents = '';
$result = PersistenceContext::get_querier()->select("SELECT c.id, a.title, a.encoded_title
FROM " . PREFIX . "wiki_cats c
LEFT JOIN " . PREFIX . "wiki_articles a ON a.id = c.article_id
WHERE c.id_parent = 0
ORDER BY title ASC");
while ($row = $result->fetch())
{
	$sub_cats_number = PersistenceContext::get_querier()->count(PREFIX . "wiki_cats", 'WHERE id_parent = :id', array('id' => $row['id']));
	if ($sub_cats_number > 0)
	{	
		$template->assign_block_vars('list', array(
			'DIRECTORY' => '<li class="sub"><a class="parent" href="javascript:show_wiki_cat_contents(' . $row['id'] . ', 0);"><i class="fa fa-plus-square-o" id="img2_' . $row['id'] . '"></i><i class="fa fa-folder" id ="img_' . $row['id'] . '"></i></a><a id="class_' . $row['id'] . '" href="javascript:open_cat(' . $row['id'] . ');">' . stripslashes($row['title']) . '</a><span id="cat_' . $row['id'] . '"></span></li>'
		));
	}
	else
	{
		$template->assign_block_vars('list', array(
			'DIRECTORY' => '<li class="sub"><a id="class_' . $row['id'] . '" href="javascript:open_cat(' . $row['id'] . ');"><i class="fa fa-folder"></i>' . stripslashes($row['title']) . '</a><span id="cat_' . $row['id'] . '"></span></li>'
		));
	}
}
$result->dispose();
$template->put_all(array(
	'SELECTED_CAT' => 0,
	'CAT_0' => 'selected',
	'CAT_LIST' => '',
	'CURRENT_CAT' => $LANG['wiki_no_selected_cat']
));

echo $template->render();


require_once('../kernel/footer.php');

?>