<?php
/*##################################################
 *                               AdminForumDisplayResponse.class.php
 *                            -------------------
 *   begin                : February 25, 2015
 *   copyright            : (C) 2015 Julien BRISWALTER
 *   email                : julienseth78@phpboost.com
 *
 *
 ###################################################
 *
 * This program is a free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 ###################################################*/

 /**
 * @author Julien BRISWALTER <julienseth78@phpboost.com>
 */

class AdminForumDisplayResponse extends AdminMenuDisplayResponse
{
	public function __construct($view, $title_page)
	{
		parent::__construct($view);
		
		$lang = LangLoader::get('common', 'forum');
		$this->set_title($lang['module_title']);
		
		$this->add_link(LangLoader::get_message('categories.management', 'categories-common'), ForumUrlBuilder::manage_categories());
		$this->add_link(LangLoader::get_message('category.add', 'categories-common'), ForumUrlBuilder::add_category());
		$this->add_link($lang['forum.ranks_management'], ForumUrlBuilder::manage_ranks());
		$this->add_link($lang['forum.actions.add_rank'], ForumUrlBuilder::add_rank());
		$this->add_link(LangLoader::get_message('configuration', 'admin-common'), ForumUrlBuilder::configuration());
		
		$env = $this->get_graphical_environment();
		$env->set_page_title($title_page, $lang['module_title']);
	}
}
?>
