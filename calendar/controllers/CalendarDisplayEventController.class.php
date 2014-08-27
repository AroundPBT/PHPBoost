<?php
/*##################################################
 *		               CalendarDisplayEventController.class.php
 *                            -------------------
 *   begin                : July 29, 2013
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
class CalendarDisplayEventController extends ModuleController
{
	private $lang;
	private $tpl;
	
	private $event;
	
	public function execute(HTTPRequestCustom $request)
	{
		$this->check_authorizations();
		
		$this->init();
		
		$this->build_view();
		
		return $this->generate_response();
	}
	
	private function init()
	{
		$this->lang = LangLoader::get('common', 'calendar');
		$this->tpl = new FileTemplate('calendar/CalendarDisplayEventController.tpl');
		$this->tpl->add_lang($this->lang);
	}
	
	private function get_event()
	{
		if ($this->event === null)
		{
			$id = AppContext::get_request()->get_getint('id', 0);
			if (!empty($id))
			{
				try {
					$this->event = CalendarService::get_event('WHERE id_event = :id', array('id' => $id));
				} catch (RowNotFoundException $e) {
					$error_controller = PHPBoostErrors::unexisting_page();
   					DispatchManager::redirect($error_controller);
				}
			}
			else
				$this->event = new CalendarEvent();
		}
		return $this->event;
	}
	
	private function build_view()
	{
		$event = $this->get_event();
		$category = CalendarService::get_categories_manager()->get_categories_cache()->get_category($event->get_content()->get_category_id());
		
		$this->tpl->put_all($event->get_array_tpl_vars());
		
		$participants_number = count($event->get_participants());
		$i = 0;
		foreach ($event->get_participants() as $participant)
		{
			$i++;
			$this->tpl->assign_block_vars('participant', array_merge($participant->get_array_tpl_vars(), array(
				'C_LAST_PARTICIPANT' => $i == $participants_number
			)));
		}
		
		if (CalendarConfig::load()->are_comments_enabled())
		{
			$comments_topic = new CalendarCommentsTopic($event);
			$comments_topic->set_id_in_module($event->get_id());
			$comments_topic->set_url(CalendarUrlBuilder::display_event_comments($category->get_id(), $category->get_rewrited_name(), $event->get_id(), $event->get_content()->get_rewrited_title()));
			
			$this->tpl->put('COMMENTS', $comments_topic->display());
		}
	}
	
	private function check_authorizations()
	{
		$event = $this->get_event();
		if (!CalendarAuthorizationsService::check_authorizations($event->get_content()->get_category_id())->read() || (!CalendarAuthorizationsService::check_authorizations($event->get_content()->get_category_id())->moderation() && !$event->get_content()->is_approved()))
		{
			$error_controller = PHPBoostErrors::user_not_authorized();
			DispatchManager::redirect($error_controller);
		}
	}
	
	private function generate_response()
	{
		$event = $this->get_event();
		
		$response = new SiteDisplayResponse($this->tpl);
		$graphical_environment = $response->get_graphical_environment();
		$graphical_environment->set_page_title($event->get_content()->get_title());
		
		$breadcrumb = $graphical_environment->get_breadcrumb();
		$breadcrumb->add($this->lang['module_title'], CalendarUrlBuilder::home());
		
		$categories = array_reverse(CalendarService::get_categories_manager()->get_parents($event->get_content()->get_category_id(), true));
		
		$category = $categories[$event->get_content()->get_category_id()];
		$breadcrumb->add($event->get_content()->get_title(), CalendarUrlBuilder::display_event($category->get_id(), $category->get_rewrited_name(), $event->get_id(), $this->event->get_content()->get_rewrited_title()));
		$graphical_environment->get_seo_meta_data()->set_canonical_url(CalendarUrlBuilder::display_event($category->get_id(), $category->get_rewrited_name(), $event->get_id(), $this->event->get_content()->get_rewrited_title()));
		
		return $response;
	}
}
?>