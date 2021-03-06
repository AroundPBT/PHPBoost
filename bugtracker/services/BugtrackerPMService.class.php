<?php
/*##################################################
 *                        BugtrackerPMService.class.php
 *                            -------------------
 *   begin                : October 12, 2012
 *   copyright            : (C) 2012 Julien BRISWALTER
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
 * @desc PMService of the bugtracker module
 */
class BugtrackerPMService
{
	/**
	 * @desc Send a PM to a member.
	 * @param string $pm_type Type of PM ('assigned', 'comment', 'pending', 'in_progress', 'delete', 'edit', 'fixed', 'rejected', 'reopen')
	 * @param int $recipient_id ID of the PM's recipient
	 * @param int $bug_id ID of the bug which is concerned
	 * @param string $message (optional) Message to include in the PM
	 */
	public static function send_PM($pm_type, $recipient_id, $bug_id, $message = '')
	{
		//Load module lang
		$lang = LangLoader::get('common', 'bugtracker');
		
		//Send the PM if the recipient is not a guest
		if ($recipient_id > 0)
		{
			//Get current user
			$current_user = AppContext::get_current_user();
			
			$author = $current_user->get_id() != User::VISITOR_LEVEL ? $current_user->get_display_name() : LangLoader::get_message('visitor', 'user-common');
			
			$pm_content = StringVars::replace_vars($lang['pm.' . $pm_type . '.contents'], array('author' => $author, 'id' => $bug_id)) . (!empty($message) ? ($pm_type != 'edit' ? StringVars::replace_vars($lang['pm.with_comment'], array('comment' => $message)) : StringVars::replace_vars($lang['pm.edit_fields'], array('fields' => $message))) : '') . ($pm_type != 'delete' ? StringVars::replace_vars($lang['pm.bug_link'], array('link' => BugtrackerUrlBuilder::detail($bug_id)->relative())) : '');
			
			//Send the PM
			PrivateMsg::start_conversation(
				$recipient_id, 
				StringVars::replace_vars($lang['pm.' . $pm_type . '.title'], array('id' => $bug_id)), 
				$pm_content, 
				-1, 
				PrivateMsg::SYSTEM_PM
			);
		}
	}
	
	 /**
	 * @desc Send a PM to a list of members.
	 * @param string $pm_type Type of PM ('assigned', 'pending', 'in_progress', 'comment', 'delete', 'edit', 'fixed', 'rejected', 'reopen')
	 * @param int $bug_id ID of the bug which is concerned
	 * @param string $message (optional) Message to include in the PM
	 * @param string[] $recipients_list (optional) Recipients to whom send the PM
	 */
	public static function send_PM_to_updaters($pm_type, $bug_id, $message = '', $recipients_list = array())
	{
		//Load configuration
		$config = BugtrackerConfig::load();
		$pm_enabled = $config->are_pm_enabled();
		
		//Check is the sending of PM is enabled for the selected type
		$pm_type_enabled = '';
		switch ($pm_type)
		{
			case 'assigned':
				$pm_type_enabled = $config->are_pm_assign_enabled();
				break;
			case 'pending':
				$pm_type_enabled = $config->are_pm_pending_enabled();
				break;
			case 'in_progress':
				$pm_type_enabled = $config->are_pm_in_progress_enabled();
				break;
			case 'comment':
				$pm_type_enabled = $config->are_pm_comment_enabled();
				break;
			case 'delete':
				$pm_type_enabled = $config->are_pm_delete_enabled();
				break;
			case 'edit':
				$pm_type_enabled = $config->are_pm_edit_enabled();
				break;
			case 'fixed':
				$pm_type_enabled = $config->are_pm_fix_enabled();
				break;
			case 'rejected':
				$pm_type_enabled = $config->are_pm_reject_enabled();
				break;
			case 'reopen':
				$pm_type_enabled = $config->are_pm_reopen_enabled();
				break;
		}
		
		//Retrieve the list of members which updated the bug
		if (empty($recipients_list))
			$recipients_list = BugtrackerService::get_updaters_list($bug_id);
		
		//Send the PM to each recipient
		foreach ($recipients_list as $recipient)
		{
			if ($pm_enabled && $pm_type_enabled)
			{
				self::send_PM($pm_type, $recipient, $bug_id, $message);
			}
		}
	}
}
?>