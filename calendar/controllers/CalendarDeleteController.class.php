<?php
/*##################################################
 *                      CalendarDeleteController.class.php
 *                            -------------------
 *   begin                : November 20, 2012
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
 * @author Julien BRISWALTER <julien.briswalter@phpboost.com>
 */
class CalendarDeleteController extends ModuleController
{
	/**
	 * @var HTMLForm
	 */
	private $form;
	/**
	 * @var FormButtonSubmit
	 */
	private $submit_button;
	
	private $lang;
	
	private $event;
	
	public function execute(HTTPRequestCustom $request)
	{
		AppContext::get_session()->csrf_get_protect();
		
		$this->init($request);
		
		$this->get_event($request);
		
		$this->check_authorizations();
		
		$tpl = new StringTemplate('# INCLUDE FORM #');
		$tpl->add_lang($this->lang);
		
		if ($this->event->belongs_to_a_serie())
			$this->build_form();
		
		if (($this->event->belongs_to_a_serie() && $this->submit_button->has_been_submited() && $this->form->validate()) || !$this->event->belongs_to_a_serie())
		{
			$this->delete_event($this->event->belongs_to_a_serie() ? $this->form->get_value('delete_serie')->get_raw_value() : false);
			$this->redirect($request);
		}
		
		$tpl->put('FORM', $this->form->display());
		
		return $this->generate_response($tpl);
	}
	
	private function init(HTTPRequestCustom $request)
	{
		$this->lang = LangLoader::get('common', 'calendar');
	}
	
	private function build_form()
	{
		$form = new HTMLForm(__CLASS__);
		
		$fieldset = new FormFieldsetHTML('delete_serie', $this->lang['calendar.titles.delete_event']);
		$form->add_fieldset($fieldset);
		
		$fieldset->add_field(new FormFieldRadioChoice('delete_serie', LangLoader::get_message('delete', 'main'), 0,
			array(
				new FormFieldRadioChoiceOption($this->lang['calendar.titles.delete_occurrence'], 0),
				new FormFieldRadioChoiceOption($this->lang['calendar.titles.delete_all_events_of_the_serie'], 1)
			)
		));
		
		$this->submit_button = new FormButtonDefaultSubmit();
		$form->add_button($this->submit_button);
		
		$this->form = $form;
	}
	
	private function get_event(HTTPRequestCustom $request)
	{
		$id = $request->get_getint('id', 0);
		
		if (!empty($id))
		{
			try {
				$this->event = CalendarService::get_event('WHERE id_event = :id', array('id' => $id));
			} catch (RowNotFoundException $e) {
				$error_controller = PHPBoostErrors::unexisting_page();
				DispatchManager::redirect($error_controller);
			}
		}
	}
	
	private function delete_event($delete_all_serie_events = false)
	{
		$events_list = CalendarService::get_serie_events($this->event->get_content()->get_id());
		
		if ($delete_all_serie_events)
		{
			foreach ($events_list as $event)
			{
				//Delete event comments
				CommentsService::delete_comments_topic_module('calendar', $event->get_id());
				
				//Delete participants
				CalendarService::delete_all_participants($event->get_id());
			}
			
			CalendarService::delete_all_serie_events($this->event->get_content()->get_id());
			PersistenceContext::get_querier()->delete(DB_TABLE_EVENTS, 'WHERE module = :module AND id_in_module = :id', array('module' => 'calendar', 'id' => !$this->event->get_parent_id() ? $this->event->get_id() : $this->event->get_parent_id()));
		}
		else
		{
			if (!$this->event->belongs_to_a_serie() || count($events_list) == 1)
			{
				CalendarService::delete_event_content('WHERE id = :id', array('id' => $this->event->get_parent_id()));
			}
			
			//Delete event
			CalendarService::delete_event('WHERE id_event = :id', array('id' => $this->event->get_id()));
			
			if (!$this->event->get_parent_id())
				PersistenceContext::get_querier()->delete(DB_TABLE_EVENTS, 'WHERE module=:module AND id_in_module=:id', array('module' => 'calendar', 'id' => $this->event->get_id()));
			
			//Delete event comments
			CommentsService::delete_comments_topic_module('calendar', $this->event->get_id());
			
			//Delete participants
			CalendarService::delete_all_participants($this->event->get_id());
		}
		
		Feed::clear_cache('calendar');
		CalendarCurrentMonthEventsCache::invalidate();
	}
	
	private function check_authorizations()
	{
		if (!$this->event->is_authorized_to_delete())
		{
			$error_controller = PHPBoostErrors::user_not_authorized();
			DispatchManager::redirect($error_controller);
		}
		if (AppContext::get_current_user()->is_readonly())
		{
			$error_controller = PHPBoostErrors::user_in_read_only();
			DispatchManager::redirect($error_controller);
		}
	}
	
	private function redirect(HTTPRequestCustom $request)
	{
		if ($request->get_getvalue('return', '') == 'admin')
			AppContext::get_response()->redirect(CalendarUrlBuilder::manage_events($request->get_getvalue('field', ''), $request->get_getvalue('sort', ''), $request->get_getint('page', 1)));
		else
			AppContext::get_response()->redirect(CalendarUrlBuilder::home($this->event->get_start_date()->get_year() . '/'. $this->event->get_start_date()->get_month()));
	}
	
	private function generate_response(View $tpl)
	{
		$response = new SiteDisplayResponse($tpl);
		$graphical_environment = $response->get_graphical_environment();
		$graphical_environment->set_page_title($this->lang['calendar.titles.event_removal']);
		
		$breadcrumb = $graphical_environment->get_breadcrumb();
		$breadcrumb->add($this->lang['module_title'], CalendarUrlBuilder::home());
		
		$event_content = $this->event->get_content();
		$categories = array_reverse(CalendarService::get_categories_manager()->get_parents($event_content->get_category_id(), true));
		
		$category = $categories[$event_content->get_category_id()];
		$breadcrumb->add($event_content->get_title(), CalendarUrlBuilder::display_event($category->get_id(), $category->get_rewrited_name(), $event_content->get_id(), $event_content->get_rewrited_title()));
		
		$breadcrumb->add($this->lang['calendar.titles.event_removal'], CalendarUrlBuilder::delete_event($this->event->get_id()));
		$graphical_environment->get_seo_meta_data()->set_canonical_url(CalendarUrlBuilder::delete_event($this->event->get_id()));
		
		return $response;
	}
}
?>