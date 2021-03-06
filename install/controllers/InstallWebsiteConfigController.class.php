<?php
/*##################################################
 *                         InstallWebsiteConfigController.class.php
 *                            -------------------
 *   begin                : October 03 2010
 *   copyright            : (C) 2010 Loic Rouchon
 *   email                : loic.rouchon@phpboost.com
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

class InstallWebsiteConfigController extends InstallController
{
	/**
	 * @var Template
	 */
	private $view;

	/**
	 * @var HTMLForm
	 */
	private $form;
	/**
	 * @var HTMLForm
	 */
	private $submit_button;
	
	private $security_config;
	private $authentication_config;
	private $server_configuration;

	public function execute(HTTPRequestCustom $request)
	{
		parent::load_lang($request);
		$this->init();
		$this->build_form();
		if ($this->submit_button->has_been_submited() && $this->form->validate())
		{
			$this->handle_form();
		}
		return $this->create_response();
	}

	private function init()
	{
		$this->security_config = SecurityConfig::load();
		$this->authentication_config = AuthenticationConfig::load();
		$this->server_configuration = new ServerConfiguration();
	}

	private function build_form()
	{
		$admin_user_lang = LangLoader::get('admin-user-common');
		$this->form = new HTMLForm('websiteForm', '', false);

		$fieldset = new FormFieldsetHTML('yourSite', $this->lang['website.yours']);
		$this->form->add_fieldset($fieldset);

		$host = new FormFieldUrlEditor('host', $this->lang['website.host'], $this->current_server_host(),
		array('description' => $this->lang['website.host.explanation'], 'required' => $this->lang['website.host.required']));
		$host->add_event('change', $this->warning_if_not_equals($host, $this->lang['website.host.warning']));
		$fieldset->add_field($host);
		
		$path = new FormFieldTextEditor('path', $this->lang['website.path'], $this->current_server_path(),
		array('description' => $this->lang['website.path.explanation']));
		$path->add_event('change', $this->warning_if_not_equals($path, $this->lang['website.path.warning']));
		$fieldset->add_field($path);
		
		$fieldset->add_field(new FormFieldTextEditor('name', $this->lang['website.name'], '', array('required' => $this->lang['website.name.required'])));
		
		$fieldset->add_field(new FormFieldTextEditor('slogan', $this->lang['website.slogan'], ''));
		
		$fieldset->add_field(new FormFieldMultiLineTextEditor('description', $this->lang['website.description'], '',
			array('description' => $this->lang['website.description.explanation'])
		));
		
		$fieldset->add_field(new FormFieldTimezone('timezone', $this->lang['website.timezone'], 'Europe/Paris',
			array('description' => $this->lang['website.timezone.explanation'])
		));
		
		$fieldset = new FormFieldsetHTML('security_config', $admin_user_lang['members.config-security']);
		$this->form->add_fieldset($fieldset);
		
		$fieldset->add_field(new FormFieldNumberEditor('internal_password_min_length', $admin_user_lang['security.config.internal-password-min-length'], $this->security_config->get_internal_password_min_length(),
			array('min' => 6, 'max' => 30),
			array(new FormFieldConstraintRegex('`^[0-9]+$`i'), new FormFieldConstraintIntegerRange(6, 30))
		));
		
		$fieldset->add_field(new FormFieldSimpleSelectChoice('internal_password_strength', $admin_user_lang['security.config.internal-password-strength'], $this->security_config->get_internal_password_strength(),
			array(
				new FormFieldSelectChoiceOption($admin_user_lang['security.config.internal-password-strength.weak'], SecurityConfig::PASSWORD_STRENGTH_WEAK),
				new FormFieldSelectChoiceOption($admin_user_lang['security.config.internal-password-strength.medium'], SecurityConfig::PASSWORD_STRENGTH_MEDIUM),
				new FormFieldSelectChoiceOption($admin_user_lang['security.config.internal-password-strength.strong'], SecurityConfig::PASSWORD_STRENGTH_STRONG)
			)
		));
		
		$fieldset->add_field(new FormFieldCheckbox('login_and_email_forbidden_in_password', $admin_user_lang['security.config.login-and-email-forbidden-in-password'], $this->security_config->are_login_and_email_forbidden_in_password()));

		if ($this->server_configuration->has_curl_library())
		{
			$fieldset = new FormFieldsetHTML('authentication_config', $admin_user_lang['members.config-authentication']);
			$this->form->add_fieldset($fieldset);
			
			$fieldset->add_field(new FormFieldCheckbox('fb_auth_enabled', $admin_user_lang['authentication.config.fb-auth-enabled'], $this->authentication_config->is_fb_auth_enabled(),
				array('description' => $admin_user_lang['authentication.config.fb-auth-enabled-explain'], 'events' => array('click' => '
					if (HTMLForms.getField("fb_auth_enabled").getValue()) { 
						HTMLForms.getField("fb_app_id").enable(); 
						HTMLForms.getField("fb_app_key").enable(); 
					} else { 
						HTMLForms.getField("fb_app_id").disable(); 
						HTMLForms.getField("fb_app_key").disable(); 
					}'
				)
			)));
			
			$fieldset->add_field(new FormFieldTextEditor('fb_app_id', $admin_user_lang['authentication.config.fb-app-id'], $this->authentication_config->get_fb_app_id(), 
				array('required' => true, 'hidden' => !$this->authentication_config->is_fb_auth_enabled())
			));
			
			$fieldset->add_field(new FormFieldPasswordEditor('fb_app_key', $admin_user_lang['authentication.config.fb-app-key'], $this->authentication_config->get_fb_app_key(), 
				array('required' => true, 'hidden' => !$this->authentication_config->is_fb_auth_enabled())
			));
			
			$fieldset->add_field(new FormFieldCheckbox('google_auth_enabled', $admin_user_lang['authentication.config.google-auth-enabled'], $this->authentication_config->is_google_auth_enabled(),
				array('description' => $admin_user_lang['authentication.config.google-auth-enabled-explain'], 'events' => array('click' => '
					if (HTMLForms.getField("google_auth_enabled").getValue()) { 
						HTMLForms.getField("google_client_id").enable(); 
						HTMLForms.getField("google_client_secret").enable(); 
					} else { 
						HTMLForms.getField("google_client_id").disable(); 
						HTMLForms.getField("google_client_secret").disable(); 
					}'
				)
			)));
			
			$fieldset->add_field(new FormFieldTextEditor('google_client_id', $admin_user_lang['authentication.config.google-client-id'], $this->authentication_config->get_google_client_id(), 
				array('required' => true, 'hidden' => !$this->authentication_config->is_google_auth_enabled())
			));
			
			$fieldset->add_field(new FormFieldPasswordEditor('google_client_secret', $admin_user_lang['authentication.config.google-client-secret'], $this->authentication_config->get_google_client_secret(), 
				array('required' => true, 'hidden' => !$this->authentication_config->is_google_auth_enabled())
			));
		}

		$action_fieldset = new FormFieldsetSubmit('actions');
		$back = new FormButtonLinkCssImg($this->lang['step.previous'], InstallUrlBuilder::database(), 'fa fa-arrow-left');
		$action_fieldset->add_element($back);
		$this->submit_button = new FormButtonSubmitCssImg($this->lang['step.next'], 'fa fa-arrow-right', 'website');
		$action_fieldset->add_element($this->submit_button);
		$this->form->add_fieldset($action_fieldset);
	}

	private function handle_form()
	{
		$installation_services = new InstallationServices();
		$installation_services->configure_website(
		$this->form->get_value('host'), $this->form->get_value('path'),
		$this->form->get_value('name'), $this->form->get_value('slogan'), $this->form->get_value('description'),
		$this->form->get_value('timezone')->get_raw_value());
		
		$this->security_config->set_internal_password_min_length($this->form->get_value('internal_password_min_length'));
		$this->security_config->set_internal_password_strength($this->form->get_value('internal_password_strength')->get_raw_value());
		
		if ($this->form->get_value('login_and_email_forbidden_in_password'))
			$this->security_config->forbid_login_and_email_in_password();
		else
			$this->security_config->allow_login_and_email_in_password();
		
		SecurityConfig::save();
		
		if ($this->server_configuration->has_curl_library())
		{
			if ($this->form->get_value('fb_auth_enabled'))
			{
				$this->authentication_config->enable_fb_auth();
				$this->authentication_config->set_fb_app_id($this->form->get_value('fb_app_id'));
				$this->authentication_config->set_fb_app_key($this->form->get_value('fb_app_key'));
			}
			else
				$this->authentication_config->disable_fb_auth();
			
			if ($this->form->get_value('google_auth_enabled'))
			{
				$this->authentication_config->enable_google_auth();
				$this->authentication_config->set_google_client_id($this->form->get_value('google_client_id'));
				$this->authentication_config->set_google_client_secret($this->form->get_value('google_client_secret'));
			}
			else
				$this->authentication_config->disable_google_auth();
			
			AuthenticationConfig::save();
		}
		
		AppContext::get_response()->redirect(InstallUrlBuilder::admin());
	}

	private function current_server_host()
	{
		return Appcontext::get_request()->get_site_url();
	}

	private function current_server_path()
	{
		$server_path = !empty($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : getenv('PHP_SELF');
		if (!$server_path)
		{
			$server_path = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');
		}
		$server_path = trim(preg_replace('`/install$`', '', dirname($server_path)));
		return $server_path = ($server_path == '/') ? '' : $server_path;
	}

	private function warning_if_not_equals(FormField $field, $message)
	{
		$tpl = new StringTemplate('var field = $FF(${escapejs(ID)});
var value = ${escapejs(VALUE)};
if (field.getValue()!=value && !confirm(${escapejs(MESSAGE)})){field.setValue(value);}');
		$tpl->put('ID', $field->get_id());
		$tpl->put('VALUE', $field->get_value());
		$tpl->put('MESSAGE', $message);
		return $tpl->render();
	}

	/**
	 * @param Template $view
	 * @return InstallDisplayResponse
	 */
	private function create_response()
	{
		$this->view = new FileTemplate('install/website.tpl');
		$this->view->put('WEBSITE_FORM', $this->form->display());
		$step_title = $this->lang['step.websiteConfig.title'];
		$response = new InstallDisplayResponse(4, $step_title, $this->view);
		return $response;
	}
}
?>