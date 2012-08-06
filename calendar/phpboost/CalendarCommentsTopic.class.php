<?php
/*##################################################
 *                           CalendarCommentsTopic.class.php
 *                            -------------------
 *   begin                : May 06, 2011
 *   copyright            : (C) 2011 Kévin MASSY
 *   email                : soldier.weasel@gmail.com
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

class CalendarCommentsTopic extends CommentsTopic
{
	public function __construct()
	{
		parent::__construct('calendar');
	}
	
	public function get_authorizations()
	{
		require_once(PATH_TO_ROOT .'/'. $this->get_module_id() . '/calendar_constants.php');
		
		$authorizations = new CommentsAuthorizations();
		$authorizations->set_authorized_access_module(AppContext::get_current_user()->check_auth(CalendarConfig::load()->get_authorizations(), AUTH_CALENDAR_READ));
		return $authorizations;
	}
	
	public function is_display()
	{
		return true;
	}
}
?>