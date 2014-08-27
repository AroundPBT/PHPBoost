<?php
/*##################################################
 *                      BugtrackerHistoryListController.class.php
 *                            -------------------
 *   begin                : November 11, 2012
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

class BugtrackerHistoryListController extends ModuleController
{
	private $lang;
	private $view;
	private $bug;
	
	public function execute(HTTPRequestCustom $request)
	{
		$this->init($request);
		
		$this->check_authorizations();
		
		$this->build_view($request);
		
		return $this->build_response($this->view);
	}

	private function build_view($request)
	{
		$current_page = $request->get_getint('page', 1);
		
		//Configuration load
		$config = BugtrackerConfig::load();
		$types = $config->get_types();
		$categories = $config->get_categories();
		$severities = $config->get_severities();
		$priorities = $config->get_priorities();
		$versions = $config->get_versions();
		
		$history_lines_number = BugtrackerService::count_history($this->bug->get_id());
		$pagination = $this->get_pagination($history_lines_number, $current_page);
		$main_lang = LangLoader::get('main');
		
		$this->view->put_all(array(
			'C_PAGINATION'	=> $pagination->has_several_pages(),
			'C_HISTORY'		=> $history_lines_number,
			'PAGINATION'	=> $pagination->display()
		));
		
		$result = PersistenceContext::get_querier()->select("SELECT *
		FROM " . BugtrackerSetup::$bugtracker_table . " b
		JOIN " . BugtrackerSetup::$bugtracker_history_table . " bh ON (bh.bug_id = b.id)
		LEFT JOIN " . DB_TABLE_MEMBER . " member ON (member.user_id = bh.updater_id)
		WHERE b.id = '" . $this->bug->get_id() . "'
		ORDER BY update_date DESC
		LIMIT :number_items_per_page OFFSET :display_from",
			array(
				'number_items_per_page' => $pagination->get_number_items_per_page(),
				'display_from' => $pagination->get_display_from()
			)
		);
		
		while ($row = $result->fetch())
		{
			switch ($row['updated_field'])
			{
				case 'type': 
					$old_value = !empty($row['old_value']) && isset($types[$row['old_value']]) ? stripslashes($types[$row['old_value']]) : $this->lang['notice.none'];
					$new_value = !empty($row['new_value']) && isset($types[$row['new_value']]) ? stripslashes($types[$row['new_value']]) : $this->lang['notice.none'];
					break;
				
				case 'category': 
					$old_value = !empty($row['old_value']) && isset($categories[$row['old_value']]) ? stripslashes($categories[$row['old_value']]) : $this->lang['notice.none_e'];
					$new_value = !empty($row['new_value']) && isset($categories[$row['new_value']]) ? stripslashes($categories[$row['new_value']]) : $this->lang['notice.none_e'];
					break;
				
				case 'severity': 
					$old_value = !empty($row['old_value']) ? stripslashes($severities[$row['old_value']]['name']) : $this->lang['notice.none'];
					$new_value = !empty($row['new_value']) ? stripslashes($severities[$row['new_value']]['name']) : $this->lang['notice.none'];
					break;
				
				case 'priority': 
					$old_value = !empty($row['old_value']) ? stripslashes($priorities[$row['old_value']]) : $this->lang['notice.none_e'];
					$new_value = !empty($row['new_value']) ? stripslashes($priorities[$row['new_value']]) : $this->lang['notice.none_e'];
					break;
				
				case 'detected_in': 
				case 'fixed_in': 
					$old_value = !empty($row['old_value']) && isset($versions[$row['old_value']]) ? stripslashes($versions[$row['old_value']]['name']) : $this->lang['notice.none_e'];
					$new_value = !empty($row['new_value']) && isset($versions[$row['new_value']]) ? stripslashes($versions[$row['new_value']]['name']) : $this->lang['notice.none_e'];
					break;
				
				case 'status': 
					$old_value = $this->lang['status.' . $row['old_value']];
					$new_value = $this->lang['status.' . $row['new_value']];
					break;
				
				case 'reproductible': 
					$old_value = ($row['old_value'] ) ? $main_lang['yes'] : $main_lang['no'];
					$new_value = ($row['new_value'] == true) ? $main_lang['yes'] : $main_lang['no'];
					break;
				
				default:
					$old_value = $row['old_value'];
					$new_value = $row['new_value'];
			}
			
			$update_date = new Date(DATE_TIMESTAMP, TIMEZONE_SYSTEM, $row['update_date']);
			
			$user = new User();
			if (!empty($row['user_id']))
				$user->set_properties($row);
			else
				$user->init_visitor_user();
			
			$user_group_color = User::get_group_color($user->get_groups(), $user->get_level(), true);
			
			$this->view->assign_block_vars('history', array(
				'C_UPDATER_GROUP_COLOR'	=> !empty($user_group_color),
				'C_UPDATER_EXIST'		=> $user->get_id() !== User::VISITOR_LEVEL,
				'UPDATED_FIELD'			=> (!empty($row['updated_field']) ? $this->lang['labels.fields.' . $row['updated_field']] : $this->lang['notice.none']),
				'OLD_VALUE'				=> stripslashes($old_value),
				'NEW_VALUE'				=> stripslashes($new_value),
				'COMMENT'				=> $row['change_comment'],
				'UPDATE_DATE_SHORT'		=> $update_date->format(Date::FORMAT_DAY_MONTH_YEAR),
				'UPDATE_DATE'			=> $update_date->format(Date::FORMAT_DAY_MONTH_YEAR_HOUR_MINUTE),
				'UPDATER'				=> $user->get_pseudo(),
				'UPDATER_LEVEL_CLASS'	=> UserService::get_level_class($user->get_level()),
				'UPDATER_GROUP_COLOR'	=> $user_group_color,
				'LINK_UPDATER_PROFILE'	=> UserUrlBuilder::profile($user->get_id())->rel(),
			));
		}
		$result->dispose();
	}
	
	private function init($request)
	{
		$this->lang = LangLoader::get('common', 'bugtracker');
		$id = $request->get_int('id', 0);
		
		try {
			$this->bug = BugtrackerService::get_bug('WHERE id=:id', array('id' => $id));
		} catch (RowNotFoundException $e) {
			$controller = new UserErrorController(LangLoader::get_message('error', 'status-messages-common'), $this->lang['error.e_unexist_bug']);
			DispatchManager::redirect($controller);
		}
		
		
		$this->view = new FileTemplate('bugtracker/BugtrackerHistoryListController.tpl');
		$this->view->add_lang($this->lang);
	}
	
	private function check_authorizations()
	{
		if (!BugtrackerAuthorizationsService::check_authorizations()->read())
		{
			$error_controller = PHPBoostErrors::user_not_authorized();
			DispatchManager::redirect($error_controller);
		}
	}
	
	private function get_pagination($history_lines_number, $page)
	{
		$pagination = new ModulePagination($page, $history_lines_number, (int)BugtrackerConfig::load()->get_items_per_page());
		$pagination->set_url(BugtrackerUrlBuilder::history($this->bug->get_id() . '/%d'));
		
		if ($pagination->current_page_is_empty() && $page > 1)
		{
			$error_controller = PHPBoostErrors::unexisting_page();
			DispatchManager::redirect($error_controller);
		}
		
		return $pagination;
	}
	
	private function build_response(View $view)
	{
		$request = AppContext::get_request();
		$success = $request->get_value('success', '');
		$page = $request->get_int('page', 1);
		
		$body_view = BugtrackerViews::build_body_view($view, 'history', $this->bug->get_id());
		
		//Success messages
		switch ($success)
		{
			case 'add':
				$errstr = StringVars::replace_vars($this->lang['success.add'], array('id' => $this->bug->get_id()));
				break;
			default:
				$errstr = '';
		}
		if (!empty($errstr))
			$body_view->put('MSG', MessageHelper::display($errstr, E_USER_SUCCESS, 5));
		
		$response = new SiteDisplayResponse($body_view);
		$graphical_environment = $response->get_graphical_environment();
		$graphical_environment->set_page_title($this->lang['titles.history'] . ' #' . $this->bug->get_id());
		$graphical_environment->get_seo_meta_data()->set_canonical_url(BugtrackerUrlBuilder::history($this->bug->get_id() . '/' . $page));
		
		$breadcrumb = $graphical_environment->get_breadcrumb();
		$breadcrumb->add($this->lang['module_title'], BugtrackerUrlBuilder::home());
		$breadcrumb->add($this->lang['titles.detail'] . ' #' . $this->bug->get_id(), BugtrackerUrlBuilder::detail($this->bug->get_id() . '-' . $this->bug->get_rewrited_title()));
		$breadcrumb->add($this->lang['titles.history'], BugtrackerUrlBuilder::history($this->bug->get_id() . '/' . $page));
		
		return $response;
	}
}
?>