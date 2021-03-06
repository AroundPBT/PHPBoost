<?php
/*##################################################
 *		                         ContactTreeLinks.class.php
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
class ContactTreeLinks implements ModuleTreeLinksExtensionPoint
{
	public function get_actions_tree_links()
	{
		$tree = new ModuleTreeLinks();
		
		$manage_fields_link = new AdminModuleLink(LangLoader::get_message('admin.fields.manage', 'common', 'contact'), ContactUrlBuilder::manage_fields());
		$manage_fields_link->add_sub_link(new AdminModuleLink(LangLoader::get_message('admin.fields.manage', 'common', 'contact'), ContactUrlBuilder::manage_fields()));
		$manage_fields_link->add_sub_link(new AdminModuleLink(LangLoader::get_message('fields.action.add_field', 'admin-user-common'), ContactUrlBuilder::add_field()));
		$tree->add_link($manage_fields_link);
		
		$tree->add_link(new AdminModuleLink(LangLoader::get_message('configuration', 'admin-common'), ContactUrlBuilder::configuration()));
		
		return $tree;
	}
}
?>