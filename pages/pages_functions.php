<?php
/*##################################################
 *                              pages_functions.php
 *                            -------------------
 *   begin                : August 15, 2007
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

if (defined('PHPBOOST') !== true)	exit;

//Cat�gories (affichage si on connait la cat�gorie et qu'on veut reformer l'arborescence)
function display_pages_cat_explorer($id, &$cats, $display_select_link = 1)
{
	$categories = PagesCategoriesCache::load()->get_categories();
	
	if ($id > 0)
	{
		$id_cat = $id;
		//On remonte l'arborescence des cat�gories afin de savoir quelle cat�gorie d�velopper
		do
		{
			$cats[] = (int)$categories[$id_cat]['id_parent'];
			$id_cat = (int)$categories[$id_cat]['id_parent'];
		}
		while ($id_cat > 0);
	}
	

	//Maintenant qu'on connait l'arborescence on part du d�but
	$cats_list = '<ul>' . show_pages_cat_contents(0, $cats, $id, $display_select_link) . '</ul>';
	
	//On liste les cat�gories ouvertes pour la fonction javascript
	$opened_cats_list = '';
	foreach ($cats as $key => $value)
	{
		if ($key != 0)
			$opened_cats_list .= 'cat_status[' . $key . '] = 1;' . "\n";
	}
	return '<script>
	<!--
' . $opened_cats_list . '
	-->
	</script>
	' . $cats_list;
	
}

//Fonction r�cursive pour l'affichage des cat�gories
function show_pages_cat_contents($id_cat, $cats, $id, $display_select_link)
{
	$line = '';
	foreach (PagesCategoriesCache::load()->get_categories() as $key => $cat)
	{
		//Si la cat�gorie appartient � la cat�gorie explor�e
		if ($cat['id_parent']  == $id_cat)
		{
			if (in_array($key, $cats)) //Si cette cat�gorie contient notre cat�gorie, on l'explore
			{
				$line .= '<li class="sub"><a class="parent" href="javascript:show_pages_cat_contents(' . $key . ', ' . ($display_select_link != 0 ? 1 : 0) . ');"><i class="fa fa-minus-square-o" id="img2_' . $key . '"></i><i class="fa fa-folder-open" id="img_' . $key . '"></i></a><a id="class_' . $key . '" class="' . ($key == $id ? 'selected' : '') . '" href="javascript:' . ($display_select_link != 0 ? 'select_cat' : 'open_cat') . '(' . $key . ');">' . stripslashes($cat['title']) . '</a><span id="cat_' . $key . '">
				<ul>'
				. show_pages_cat_contents($key, $cats, $id, $display_select_link) . '</ul></span></li>';
			}
			else
			{
				//On compte le nombre de cat�gories pr�sentes pour savoir si on donne la possibilit� de faire un sous dossier
				$sub_cats_number = PersistenceContext::get_querier()->count(PREFIX . "pages_cats", 'WHERE id_parent=:id_parent', array('id_parent' => $key));
				//Si cette cat�gorie contient des sous cat�gories, on propose de voir son contenu
				if ($sub_cats_number > 0)
					$line .= '<li class="sub"><a class="parent" href="javascript:show_pages_cat_contents(' . $key . ', ' . ($display_select_link != 0 ? 1 : 0) . ');"><i class="fa fa-plus-square-o" id="img2_' . $key . '"></i><i class="fa fa-folder" id="img_' . $key . '"></i></a><a class="' . ($key == $id ? 'selected' : '') . '" id="class_' . $key . '" href="javascript:' . ($display_select_link != 0 ? 'select_cat' : 'open_cat') . '(' . $key . ');">' . stripslashes($cat['title']) . '</a><span id="cat_' . $key . '"></span></li>';
				else //Sinon on n'affiche pas le "+"
					$line .= '<li class="sub"><a id="class_' . $key . '" class="' . ($key == $id ? 'selected' : '') . '" href="javascript:' . ($display_select_link != 0 ? 'select_cat' : 'open_cat') . '(' . $key . ');"><i class="fa fa-folder"></i>' . stripslashes($cat['title']) . '</a></li>';
			}
		}
	}
	return "\n" . $line;
}

//Fonction qui d�termine toutes les sous-cat�gories d'une cat�gorie (r�cursive)
function pages_find_subcats(&$array, $id_cat)
{
	//On parcourt les cat�gories et on d�termine les cat�gories filles
	foreach (PagesCategoriesCache::load()->get_categories() as $key => $cat)
	{
		if ($value['id_parent'] == $id_cat)
		{
			$array[] = $key;
			//On rappelle la fonction pour la cat�gorie fille
			pages_find_subcats($array, $key);
		}
	}
}

//Fonction "parse" pour les pages laissant passer le html tout en rempla�ant les caract�res sp�ciaux par leurs entit�s html correspondantes
function pages_parse($contents)
{
	$contents = FormatingHelper::strparse(stripslashes($contents));
	$contents = preg_replace('`\[link=([a-z0-9+#-]+)\](.+)\[/link\]`isU', '<a href="/pages/$1">$2</a>', $contents);
	
	return (string) $contents;
}

//Fonction unparse
function pages_unparse($contents)
{
	$contents = link_unparse(stripslashes($contents));
	return FormatingHelper::unparse($contents);
}

//Second parse -> � l'affichage
function pages_second_parse($contents)
{
	if (!ServerEnvironmentConfig::load()->is_url_rewriting_enabled()) //Pas de rewriting	
	{
			$contents = preg_replace('`<a href="/pages/([a-z0-9+#-]+)">(.*)</a>`sU', '<a href="/pages/pages.php?title=$1">$2</a>', $contents);
	}
	$contents = FormatingHelper::second_parse(stripslashes($contents));
	return $contents;
}

//On remplace la balise link
function link_unparse($contents)
{
	$contents = is_array($contents) ? $contents[0] : $contents;
	return preg_replace('`<a href="/pages/([a-z0-9+#-]+)">(.*)</a>`sU', "[link=$1]$2[/link]", $contents);
}

function build_pages_cat_children($cats_tree, $cats, $id_parent = 0)
{
	if (!empty($cats))
	{
		$i = 0;
		$nb_cats = count($cats);
		$children = array();
		while ($i < $nb_cats)
		{
			if (isset($cats[$i]) && $cats[$i]['id_parent'] == $id_parent)
			{
				$id = $cats[$i]['id'];
				$feeds_cat = new FeedsCat('pages', $id, stripslashes($cats[$i]['title']));

				// Decrease the complexity
				unset($cats[$i]);
				$cats = array_merge($cats); // re-index the array
				$nb_cats = count($cats);

				build_pages_cat_children($feeds_cat, $cats, $id);
				$cats_tree->add_child($feeds_cat);
			}
			else
			{
				$i++;
			}
		}
	}
}

?>