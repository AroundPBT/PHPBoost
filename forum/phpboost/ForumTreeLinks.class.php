<?php
/*##################################################
 *		                         ForumTreeLinks.class.php
 *                            -------------------
 *   begin                : November 23, 2013
 *   copyright            : (C) 2013 Julien BRISWALTER
 *   email                : julienseth78@phpboost.com
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

/**
 * @author Julien BRISWALTER <julienseth78@phpboost.com>
 */
class ForumTreeLinks implements ModuleTreeLinksExtensionPoint
{
	public function get_actions_tree_links()
	{
		$lang = LangLoader::get('common', 'forum');
		$tree = new ModuleTreeLinks();
		
		$manage_categories_link = new AdminModuleLink(LangLoader::get_message('categories.manage', 'categories-common'), ForumUrlBuilder::manage_categories());
		$manage_categories_link->add_sub_link(new AdminModuleLink(LangLoader::get_message('categories.manage', 'categories-common'), ForumUrlBuilder::manage_categories()));
		$manage_categories_link->add_sub_link(new AdminModuleLink(LangLoader::get_message('category.add', 'categories-common'), ForumUrlBuilder::add_category()));
		$tree->add_link($manage_categories_link);
		
		$manage_ranks_link = new AdminModuleLink($lang['forum.manage_ranks'], ForumUrlBuilder::manage_ranks());
		$manage_ranks_link->add_sub_link(new AdminModuleLink($lang['forum.manage_ranks'], ForumUrlBuilder::manage_ranks()));
		$manage_ranks_link->add_sub_link(new AdminModuleLink($lang['forum.actions.add_rank'], ForumUrlBuilder::add_rank()));
		$tree->add_link($manage_ranks_link);
		
		$tree->add_link(new AdminModuleLink(LangLoader::get_message('configuration', 'admin-common'), ForumUrlBuilder::configuration()));
		
		$tree->add_link(new AdminModuleLink(LangLoader::get_message('moderation_panel', 'main'), ForumUrlBuilder::moderation_panel()));
		
		return $tree;
	}
}
?>