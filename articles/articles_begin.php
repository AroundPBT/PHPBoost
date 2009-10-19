<?php
/*##################################################
 *                              articles_begin.php
 *                            -------------------
 *   begin                : October 18, 2007
 *   copyright          : (C) 2007 Viarre r�gis
 *   email                : crowkait@phpboost.com
 *
 *
 ###################################################
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
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

if (defined('PHPBOOST') !== true)
exit;

require_once('articles_constants.php');
load_module_lang('articles');

$Cache->load('articles');

if (empty($idartcat))//Racine.
{
	$ARTICLES_CAT[0]['auth'] = $CONFIG_ARTICLES['global_auth'];
	$ARTICLES_CAT[0]['visible'] = 1;
	$ARTICLES_CAT[0]['name'] = $LANG['root'];
	$ARTICLES_CAT[0]['order'] = -1;
	$ARTICLES_CAT[0]['id_parent'] = 0;
	$ARTICLES_CAT[0]['tpl_articles'] = 'articles.tpl';
	$ARTICLES_CAT[0]['tpl_cat'] = 'articles_cat.tpl';
	$ARTICLES_CAT[0]['options'] = Array ( 'note' => 1, 'com' => 1, 'impr' => 1, 'date' => 1, 'author' => 1 ) ; 
	$idartcat=0;
}

if (isset($ARTICLES_CAT[$idartcat]) )
{
	require_once('articles_cats.class.php');
	$articles_categories = new ArticlesCats();
	$articles_categories->bread_crumb($idartcat);

	if (!empty($idart))
	{
		$articles = $Sql->query_array(DB_TABLE_ARTICLES, '*', "WHERE visible = 1 AND id = '" . $idart . "' AND idcat = " . $idartcat, __LINE__, __FILE__);
		$idartcat = $articles['idcat'];

		define('TITLE', $ARTICLES_LANG['title_articles'] . ' - ' . addslashes($articles['title']));

		$Bread_crumb->add($articles['title'], 'articles' . url('.php?cat=' . $idartcat . '&amp;id=' . $idart, '-' . $idartcat . '-' . $idart . '+' . url_encode_rewrite($articles['title']) . '.php'));

		if (!empty($get_note))
		$Bread_crumb->add($LANG['note'], '');
		elseif (!empty($_GET['i']))
		$Bread_crumb->add($LANG['com'], '');
			
	}
	else
	define('TITLE', $ARTICLES_LANG['title_articles'] . ' - ' . addslashes($ARTICLES_CAT[$idartcat]['name']));
}
else
{
	$Bread_crumb->add($ARTICLES_LANG['title_articles'], 'articles.php');
	if (!defined('TITLE'))
	define('TITLE', $ARTICLES_LANG['title_articles']);
}

?>