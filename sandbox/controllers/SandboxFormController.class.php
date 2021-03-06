<?php
/*##################################################
 *                       SandboxFormController.class.php
 *                            -------------------
 *   begin                : December 20, 2009
 *   copyright            : (C) 2009 Benoit Sautel
 *   email                : ben.popeye@phpboost.com
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

class SandboxFormController extends ModuleController
{
	private $view;
	private $lang;
	
	/**
	 * @var FormButtonSubmit
	 */
	private $preview_button;
	/**
	 * @var FormButtonDefaultSubmit
	 */
	private $submit_button;

	public function execute(HTTPRequestCustom $request)
	{
		$this->init();
		
		$form = $this->build_form();
		
		if ($this->submit_button->has_been_submited() || $this->preview_button->has_been_submited())
		{
			if ($form->validate())
			{
				$this->view->put_all(array(
					'C_RESULT' => true,
					'TEXT' => $form->get_value('text'),
					'MAIL' => $form->get_value('mail'),
					'WEB' => $form->get_value('siteweb'),
					'AGE' => $form->get_value('age'),
					'MULTI_LINE_TEXT' => $form->get_value('multi_line_text'),
					'RICH_TEXT' => $form->get_value('rich_text'),
					'RADIO' => $form->get_value('radio')->get_label(),
					'CHECKBOX' => var_export($form->get_value('checkbox'), true),
					'SELECT' => $form->get_value('select')->get_label(),
					'HIDDEN' => $form->get_value('hidden'),
					'DATE' => $form->get_value('date')->format(Date::FORMAT_DAY_MONTH_YEAR),
					'DATE_TIME' => $form->get_value('date_time')->format(Date::FORMAT_DAY_MONTH_YEAR_HOUR_MINUTE),
					'H_T_TEXT_FIELD' => $form->get_value('alone'),
					'C_PREVIEW' => $this->preview_button->has_been_submited()                
				));
				
				$file = $form->get_value('file');
				if ( $file !== null)
				{
					$this->view->put_all(array('FILE' => $file->get_name() . ' - ' . $file->get_size() . 'b - ' . $file->get_mime_type()));
				}
			}
		}
		
		$this->view->put('form', $form->display());
		
		return $this->generate_response();
	}
	
	private function init()
	{
		$this->lang = LangLoader::get('common', 'sandbox');
		$this->view = new FileTemplate('sandbox/SandboxFormController.tpl');
		$this->view->add_lang($this->lang);
	}
	
	private function build_form()
	{
		$security_config = SecurityConfig::load();
		$form = new HTMLForm('sandboxForm');

		// FIELDSET
		$fieldset = new FormFieldsetHTML('fieldset_1', 'Fieldset');
		$form->add_fieldset($fieldset);

		$fieldset->set_description('Ceci est ma description');
		
		// SINGLE LINE TEXT
		$fieldset->add_field(new FormFieldTextEditor('text', 'Champ texte', 'toto', array(
			'maxlength' => 25, 'description' => 'Contraintes lettres, chiffres et tiret bas'),
			array(new FormFieldConstraintRegex('`^[a-z0-9_ ]+$`i'))
		));
		$fieldset->add_field(new FormFieldTextEditor('textdisabled', 'Champ d�sactiv�', '', array(
			'maxlength' => 25, 'description' => 'd�sactiv�', 'disabled' => true)
		));
		$fieldset->add_field(new FormFieldUrlEditor('siteweb', 'Site web', 'http://www.phpboost.com/index.php', array(
			'description' => 'Url valide')
		));
		$fieldset->add_field(new FormFieldMailEditor('mail', 'Mail', 'team.hein@phpboost.com', array(
			'description' => 'Mail valide')
		));
		$fieldset->add_field(new FormFieldMailEditor('mail_multiple', 'Mail multiple', 'team.hein@phpboost.com, test@phpboost.com', array(
			'description' => 'Mails valides, s�par�s par une virgule', 'multiple' => true)
		));
		$fieldset->add_field(new FormFieldTelEditor('tel', 'Num�ro de t�l�phone', '0123456789', array(
			'description' => 'Num�ro de t�l�phone valide')
		));
		$fieldset->add_field(new FormFieldTextEditor('text2', 'Champ texte2', 'toto2', array(
			'maxlength' => 25, 'description' => 'Champs requis rempli', 'required' => true)
		));
		$fieldset->add_field(new FormFieldTextEditor('text3', 'Champ requis', '', array(
			'maxlength' => 25, 'description' => 'Champs requis vide', 'required' => true)
		));
		$fieldset->add_field(new FormFieldNumberEditor('number', 'Nombre requis', 20, array(
			'min' => 0, 'max' => 1000, 'description' => 'Intervalle 0 � 1000', 'required' => true),
			array(new FormFieldConstraintIntegerRange(0, 1000))
		));
		$fieldset->add_field(new FormFieldNumberEditor('age', 'Age', 20, array(
			'min' => 10, 'max' => 100, 'description' => 'Intervalle 10 � 100'),
			array(new FormFieldConstraintIntegerRange(10, 100))
		));
		$fieldset->add_field(new FormFieldDecimalNumberEditor('decimal', 'Nombre d�cimal', 5.5, array(
			'min' => 0, 'step' => 0.1)
		));

		// RANGE
		$fieldset->add_field($password = new FormFieldRangeEditor('range', 'Longueur', 4, array(
			'min' => 1, 'max' => 10, 'description' => 'Slider horizontal')
		));

		// PASSWORD
		$fieldset->add_field($password = new FormFieldPasswordEditor('password', 'Mot de passe', 'aaaaaa', array(
			'description' => 'Minimum ' . $security_config->get_internal_password_min_length() . ' caract�res'),
			array(new FormFieldConstraintLengthMin($security_config->get_internal_password_min_length()))
		));
		$fieldset->add_field($password_bis = new FormFieldPasswordEditor('password_bis', 'Confirmation du mot de passe', 'aaaaaa', array(
			'description' => 'Minimum ' . $security_config->get_internal_password_min_length() . ' caract�res'),
			array(new FormFieldConstraintLengthMin($security_config->get_internal_password_min_length()))
		));
	   
		// SHORT MULTI LINE TEXT
		$fieldset->add_field(new FormFieldShortMultiLineTextEditor('short_multi_line_text', 'Champ texte multi lignes moyen', 'titi',
			array('rows' => 7, 'required' => true)
		));
		
		// MULTI LINE TEXT
		$fieldset->add_field(new FormFieldMultiLineTextEditor('multi_line_text', 'Champ texte multi lignes', 'toto',
				array('rows' => 6, 'cols' => 47, 'description' => 'Description', 'required' => true)
		));

		// RICH TEXT
		$fieldset->add_field(new FormFieldRichTextEditor('rich_text', 'Champ texte riche dans �diteur', 'toto <strong>tata</strong>',
			array('required' => true)
		));

		//Checkbox
		$fieldset->add_field(new FormFieldMultipleCheckbox('multiple_check_box', 'Plusieurs checkbox', array(), 
			array(
				new FormFieldMultipleCheckboxOption('meet', 'la viande'), 
				new FormFieldMultipleCheckboxOption('fish', 'le poisson')
			),
			array('required' => true)
		));
		
		// RADIO
		$default_option = new FormFieldRadioChoiceOption('Choix 1', '1');
		$fieldset->add_field(new FormFieldRadioChoice('radio', 'Choix �num�ration', '',
			array(
				$default_option,
				new FormFieldRadioChoiceOption('Choix 2', '2')
			),
			array('required' => true)
		));

		// CHECKBOX
		$fieldset->add_field(new FormFieldCheckbox('checkbox', 'Case � cocher', FormFieldCheckbox::CHECKED));

		// SELECT
		$fieldset->add_field(new FormFieldSimpleSelectChoice('select', 'Liste d�roulante', '',
			array(
				new FormFieldSelectChoiceOption('', ''),
				new FormFieldSelectChoiceOption('Choix 1', '1'),
				new FormFieldSelectChoiceOption('Choix 2', '2'),
				new FormFieldSelectChoiceOption('Choix 3', '3'),
				new FormFieldSelectChoiceGroupOption('Groupe 1', array(
					new FormFieldSelectChoiceOption('Choix 4', '4'),
					new FormFieldSelectChoiceOption('Choix 5', '5'),
				)),
				new FormFieldSelectChoiceGroupOption('Groupe 2', array(
					new FormFieldSelectChoiceOption('Choix 6', '6'),
					new FormFieldSelectChoiceOption('Choix 7', '7'),
				))
			),
			array('required' => true)
		));
		
		// SELECT MULTIPLE
		$fieldset->add_field(new FormFieldMultipleSelectChoice('multiple_select', 'Liste d�roulante multiple', array('1', '2'),
			array(
				new FormFieldSelectChoiceOption('Choix 1', '1'),
				new FormFieldSelectChoiceOption('Choix 2', '2'),
				new FormFieldSelectChoiceOption('Choix 3', '3')
			),
			array('required' => true)
		));
		
		$fieldset->add_field(new FormFieldTimezone('timezone', 'TimeZone', 'UTC+0'));
		
		$fieldset->add_field(new FormFieldAjaxSearchUserAutoComplete('user_completition', 'Auto compl�tion utilisateurs', ''));
		
		$fieldset->add_element(new FormButtonButton('Envoyer'));

		$fieldset2 = new FormFieldsetHTML('fieldset2', 'Fieldset 2');
		$form->add_fieldset($fieldset2);

		// CAPTCHA
		$fieldset2->add_field(new FormFieldCaptcha('captcha'));

		// HIDDEN
		$fieldset2->add_field(new FormFieldHidden('hidden', 'hidden'));

		// FREE FIELD
		$fieldset2->add_field(new FormFieldFree('free', 'Champ libre', 'Valeur champ libre', array()));

		// DATE
		$fieldset2->add_field(new FormFieldDate('date', 'Date', null,
			array('required' => true)
		));

		// DATE TIME
		$fieldset2->add_field(new FormFieldDateTime('date_time', 'Heure', null,
			array('required' => true)
		));

		// COLOR PICKER
		$fieldset2->add_field(new FormFieldColorPicker('color', 'Couleur', '#CC99FF'));

		// SEARCH
		$fieldset2->add_field(new FormFieldSearch('search', 'Recherche', ''));
		
		// FILE PICKER
		$fieldset2->add_field(new FormFieldFilePicker('file', 'Fichier'));
		
		// MULTIPLE FILE PICKER
		$fieldset2->add_field(new FormFieldMultipleFilePicker('multiple_files', 'Plusieurs Fichiers'));
		
		// UPLOAD FILE
		$fieldset2->add_field(new FormFieldUploadFile('upload_file', 'Lien vers un fichier', '', array('required' => true)));
		
		// UPLOAD PICTURE FILE
		$fieldset2->add_field(new FormFieldUploadPictureFile('upload_picture_file', 'Lien vers une image', '', array('required' => true)));

		// AUTH
		$fieldset3 = new FormFieldsetHTML('fieldset3', 'Autorisations');
		$auth_settings = new AuthorizationsSettings(array(new ActionAuthorization('Action 1', 1, 'Autorisations pour l\'action 1'), new ActionAuthorization('Action 2', 2)));
		$auth_settings->build_from_auth_array(array('r1' => 3, 'r0' => 2, 'm1' => 1, '1' => 2));
		$auth_setter = new FormFieldAuthorizationsSetter('auth', $auth_settings);
		$fieldset3->add_field($auth_setter);
		$form->add_fieldset($fieldset3);

		// VERTICAL FIELDSET
		$vertical_fieldset = new FormFieldsetVertical('fieldset4');
		$vertical_fieldset->set_description('Ceci est ma description');
		$form->add_fieldset($vertical_fieldset);
		$vertical_fieldset->add_field(new FormFieldTextEditor('alone', 'Texte', 'fieldset s�par�'));
		$vertical_fieldset->add_field(new FormFieldCheckbox('cbhor', 'A cocher', FormFieldCheckbox::UNCHECKED));

		// HORIZONTAL FIELDSET
		$horizontal_fieldset = new FormFieldsetHorizontal('fieldset5');
		$horizontal_fieldset->set_description('Ceci est ma description');
		$form->add_fieldset($horizontal_fieldset);
		$horizontal_fieldset->add_field(new FormFieldTextEditor('texthor', 'Texte', 'fieldset s�par�', array('required' => true)));
		$horizontal_fieldset->add_field(new FormFieldCheckbox('cbvert', 'A cocher', FormFieldCheckbox::CHECKED));

		// BUTTONS
		$buttons_fieldset = new FormFieldsetSubmit('buttons');
		$buttons_fieldset->add_element(new FormButtonReset());
		$this->preview_button = new FormButtonSubmit('Pr�visualiser', 'preview', 'alert("Voulez-vous vraiment pr�visualiser ?")');
		$buttons_fieldset->add_element($this->preview_button);
		$this->submit_button = new FormButtonDefaultSubmit();
		$buttons_fieldset->add_element($this->submit_button);
		$buttons_fieldset->add_element(new FormButtonButton('Bouton', 'alert("coucou");'));
		$form->add_fieldset($buttons_fieldset);

		// FORM CONSTRAINTS
		$form->add_constraint(new FormConstraintFieldsEquality($password, $password_bis));

		return $form;
	}
	
	private function generate_response()
	{
		$response = new SiteDisplayResponse($this->view);
		$graphical_environment = $response->get_graphical_environment();
		$graphical_environment->set_page_title($this->lang['title.form_builder'], $this->lang['module_title']);
		
		$breadcrumb = $graphical_environment->get_breadcrumb();
		$breadcrumb->add($this->lang['module_title'], SandboxUrlBuilder::home()->rel());
		$breadcrumb->add($this->lang['title.form_builder'], SandboxUrlBuilder::form()->rel());
		
		return $response;
	}
}
?>
