<?php
/*##################################################
 *                             FormFieldSimpleSelectChoice.class.php
 *                            -------------------
 *   begin                : April 28, 2009
 *   copyright            : (C) 2009 Viarre R�gis
 *   email                : crowkait@phpboost.com
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
 * @author R�gis Viarre <crowkait@phpboost.com>
 * @desc This class manage select fields.
 * It provides you additionnal field options :
 * <ul>
 * 	<li>multiple : Type of select field, mutiple allow you to check several options.</li>
 * </ul>
 * @package {@package}
 */
class FormFieldSimpleSelectChoice extends AbstractFormFieldChoice
{
	private $default_value;
	
	/**
	 * @desc Constructs a FormFieldSimpleSelectChoice.
	 * @param string $id Field id
	 * @param string $label Field label
	 * @param mixed $value Default value (either a FormFieldEnumOption object or a string corresponding to the FormFieldEnumOption's raw value)
	 * @param FormFieldEnumOption[] $options Enumeration of the possible values
	 * @param string[] $field_options Map of the field options (this field has no specific option, there are only the inherited ones)
	 * @param FormFieldConstraint List of the constraints
	 */
	public function __construct($id, $label, $value, array $options, array $field_options = array(), array $constraints = array())
	{
		$this->default_value = $value;
		parent::__construct($id, $label, $value, $options, $field_options, $constraints);
		$this->set_css_form_field_class('form-field-select');
	}

	/**
	 * @return string The html code for the select.
	 */
	public function display()
	{
		$template = $this->get_template_to_use();

		$this->assign_common_template_variables($template);

		$template->assign_block_vars('fieldelements', array(
			'ELEMENT' => $this->get_html_code()->render(),
		));

		return $template;
	}

	private function get_html_code()
	{
		$tpl_src = '<select name="${escape(NAME)}" id="${escape(HTML_ID)}" class="${escape(CSS_CLASS)}" # IF C_DISABLED # disabled="disabled" # ENDIF # >' .
			'# START options # # INCLUDE options.OPTION # # END options #' .
			'</select>';

		$tpl = new StringTemplate($tpl_src);

		$tpl->put_all(array(
			'NAME' => $this->get_html_id(),
			'ID' => $this->get_id(),
			'HTML_ID' => $this->get_html_id(),
			'CSS_CLASS' => $this->get_css_class(),
			'C_DISABLED' => $this->is_disabled()
		));

		foreach ($this->get_options() as $option)
		{
			$tpl->assign_block_vars('options', array(), array(
				'OPTION' => $option->display()
			));
		}

		return $tpl;
	}

	protected function get_option($raw_value)
	{
		foreach ($this->get_options() as $option)
		{
			$result = $option->get_option($raw_value);
			if ($result !== null)
			{
				return $result;
			}
		}
		return null;
	}
	
	protected function assign_common_template_variables(Template $template)
	{
		parent::assign_common_template_variables($template);
		$template->put('C_REQUIRED_AND_HAS_VALUE', $this->is_required() && $this->default_value);
	}

	protected function get_default_template()
	{
		return new FileTemplate('framework/builder/form/FormField.tpl');
	}

	protected function get_js_specialization_code()
	{
		return ($this->is_required() ? '
		jQuery("#'. $this->get_html_id() .'_field").change(function() {
			HTMLForms.get("' . $this->get_form_id() . '").getField("'. $this->get_id() . '").enableValidationMessage();
			HTMLForms.get("' . $this->get_form_id() . '").getField("'. $this->get_id() . '").liveValidate();
		});' : '');
	}
}
?>