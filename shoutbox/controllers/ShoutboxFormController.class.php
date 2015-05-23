<?php
/*##################################################
 *		                         ShoutboxFormController.class.php
 *                            -------------------
 *   begin                : October 14, 2014
 *   copyright            : (C) 2014 julienseth78
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
class ShoutboxFormController extends ModuleController
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
	
	private $view;
	
	private $message;
	
	public function execute(HTTPRequestCustom $request)
	{
		$this->init();
		
		$this->check_authorizations();
		
		$this->build_form();
		
		if ($this->submit_button->has_been_submited() && $this->form->validate())
		{
			$id = $this->save();
			AppContext::get_response()->redirect($request->get_getvalue('redirect', ShoutboxUrlBuilder::home(1, $id)));
		}
		
		$this->view->put('FORM', $this->form->display());
		
		return $this->generate_response($this->view);
	}
	
	public static function get_view()
	{
		$object = new self();
		$object->init();
		$object->build_form();
		if ($object->submit_button->has_been_submited() && $object->form->validate())
		{
			$id = $object->save();
			AppContext::get_response()->redirect(AppContext::get_request()->get_getvalue('redirect', ShoutboxUrlBuilder::home(1, $id)));
		}
		$object->view->put('FORM', ShoutboxAuthorizationsService::check_authorizations()->write() && !AppContext::get_current_user()->is_readonly() ? $object->form->display() : '');
		return $object->view;
	}
	
	private function init()
	{
		$this->lang = LangLoader::get('common', 'shoutbox');
		$this->view = new StringTemplate('# INCLUDE FORM #');
		$this->view->add_lang($this->lang);
	}
	
	private function build_form()
	{
		$config = ShoutboxConfig::load();
		
		$formatter = AppContext::get_content_formatting_service()->get_default_factory();
		$formatter->set_forbidden_tags($config->get_forbidden_formatting_tags());
		
		$form = new HTMLForm(__CLASS__);
		
		$fieldset = new FormFieldsetHTML('message', $this->message === null ? $this->lang['shoutbox.add'] : $this->lang['shoutbox.edit']);
		$form->add_fieldset($fieldset);
		
		if (!AppContext::get_current_user()->check_level(User::MEMBER_LEVEL))
		{
			$fieldset->add_field(new FormFieldTextEditor('pseudo', LangLoader::get_message('form.name', 'common'), $this->get_message()->get_login(), array(
				'required' => true, 'maxlength' => 25)
			));
		}
		
		$fieldset->add_field(new FormFieldRichTextEditor('contents', LangLoader::get_message('message', 'main'), $this->get_message()->get_contents(), 
			array('formatter' => $formatter, 'rows' => 10, 'cols' => 47, 'required' => true), 
			array(
				new FormFieldConstraintMaxLinks($config->get_max_links_number_per_message(), true),
				new FormFieldConstraintAntiFlood(ShoutboxService::get_last_message_timestamp_from_user($this->get_message()->get_author_user()->get_id())
			))
		));
		
		$this->submit_button = new FormButtonDefaultSubmit();
		$form->add_button($this->submit_button);
		$form->add_button(new FormButtonReset());
		
		$this->form = $form;
	}
	
	private function get_message()
	{
		if ($this->message === null)
		{
			$id = AppContext::get_request()->get_getint('id', 0);
			if (!empty($id))
			{
				try {
					$this->message = ShoutboxService::get_message('WHERE id=:id', array('id' => $id));
				} catch (RowNotFoundException $e) {
					$error_controller = PHPBoostErrors::unexisting_page();
   					DispatchManager::redirect($error_controller);
				}
			}
			else
			{
				$this->message = new ShoutboxMessage();
				$this->message->init_default_properties();
			}
		}
		return $this->message;
	}
	
	private function check_authorizations()
	{
		$message = $this->get_message();
		
		if ($message->get_id() === null)
		{
			if (!ShoutboxAuthorizationsService::check_authorizations()->write())
			{
				$error_controller = PHPBoostErrors::user_not_authorized();
				DispatchManager::redirect($error_controller);
			}
		}
		else
		{
			if (!$message->is_authorized_to_edit())
			{
				$error_controller = PHPBoostErrors::user_not_authorized();
				DispatchManager::redirect($error_controller);
			}
		}
		if (AppContext::get_current_user()->is_readonly())
		{
			$controller = PHPBoostErrors::user_in_read_only();
			DispatchManager::redirect($controller);
		}
	}
	
	private function save()
	{
		$message = $this->get_message();
		
		if ($this->form->has_field('pseudo'))
			$message->set_login($this->form->get_value('pseudo'));
		$message->set_contents($this->form->get_value('contents'));
		
		if ($message->get_id() === null)
		{
			$message->set_creation_date(new Date());
			$id_message = ShoutboxService::add($message);
		}
		else
		{
			$id_message = $message->get_id();
			ShoutboxService::update($message);
		}
		
		return $id_message;
	}
	
	private function generate_response(View $tpl)
	{
		$message = $this->get_message();
		$page = AppContext::get_request()->get_getint('page', 1);
		$redirect = AppContext::get_request()->get_getvalue('redirect', ShoutboxUrlBuilder::home()->relative());
		
		$response = new SiteDisplayResponse($tpl);
		$graphical_environment = $response->get_graphical_environment();
		
		$breadcrumb = $graphical_environment->get_breadcrumb();
		$breadcrumb->add($this->lang['module_title'], ShoutboxUrlBuilder::home($page));
		
		if ($message->get_id() === null)
		{
			$graphical_environment->set_page_title($this->lang['shoutbox.add'], $this->lang['module_title']);
			$breadcrumb->add($this->lang['shoutbox.add'], ShoutboxUrlBuilder::add());
			$graphical_environment->get_seo_meta_data()->set_canonical_url(ShoutboxUrlBuilder::add());
		}
		else
		{
			$graphical_environment->set_page_title($this->lang['shoutbox.edit'], $this->lang['module_title']);
			$breadcrumb->add($this->lang['shoutbox.edit'], ShoutboxUrlBuilder::edit($message->get_id(), $redirect));
			$graphical_environment->get_seo_meta_data()->set_canonical_url(ShoutboxUrlBuilder::edit($message->get_id(), $redirect));
		}
		
		return $response;
	}
}
?>