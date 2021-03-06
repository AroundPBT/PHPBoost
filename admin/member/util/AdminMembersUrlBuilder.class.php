<?php
/*##################################################
 *                       AdminMembersUrlBuilder.class.php
 *                            -------------------
 *   begin                : May 05, 2012
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

class AdminMembersUrlBuilder
{
	private static $dispatcher = '/admin/member';
	
	/*
	 * @ return Url
	*/
	public static function management()
	{
		return DispatchManager::get_url(self::$dispatcher, '/management/');
	}
	
	/*
	 * @ return Url
	 */
	public static function add()
	{
		return DispatchManager::get_url(self::$dispatcher, '/add/');
	}
		
	/*
	 * @ return Url
	 */
	public static function delete($id)
	{
		return DispatchManager::get_url(self::$dispatcher, $id. '/delete/');
	}
	
	/*
	 * @ return Url
	*/
	public static function configuration()
	{
		return DispatchManager::get_url(self::$dispatcher, '/config/');
	}
}
?>