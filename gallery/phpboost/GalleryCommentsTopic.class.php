<?php
/*##################################################
 *                           GalleryCommentsTopic.class.php
 *                            -------------------
 *   begin                : April 09, 2012
 *   copyright            : (C) 2012 Kevin MASSY
 *   email                : kevin.massy@phpboost.com
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

class GalleryCommentsTopic extends CommentsTopic
{
	public function __construct()
	{
		parent::__construct('gallery');
	}
	
	public function get_authorizations()
	{
		global $CAT_GALLERY, $CONFIG_GALLERY;
		
		$cache = new Cache();
		$cache->load($this->get_module_id());
		
		$id_cat = $this->get_categorie_id();
		$cat_authorizations = $CAT_GALLERY[$id_cat]['auth'];

		$authorizations = new CommentsAuthorizations();
		$authorizations->set_authorized_access_module(AppContext::get_current_user()->check_auth($cat_authorizations, 0x01));
		return $authorizations;
	}
	
	public function is_display()
	{
		$columns = 'aprob';
		$condition = 'WHERE id = :id_in_module';
		$parameters = array('id_in_module' => $this->get_id_in_module());
		$aprobation = PersistenceContext::get_querier()->get_column_value(PREFIX . 'gallery', $columns, $condition, $parameters);
		return $aprobation > 0 ? true : false;
	}

	private function get_categorie_id()
	{
		$columns = 'idcat';
		$condition = 'WHERE id = :id_in_module';
		$parameters = array('id_in_module' => $this->get_id_in_module());
		return PersistenceContext::get_querier()->get_column_value(PREFIX . 'gallery', $columns, $condition, $parameters);
	}
}
?>