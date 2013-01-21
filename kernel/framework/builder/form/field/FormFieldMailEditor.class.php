<?php
/*##################################################
 *                             FormFieldTextEditor.class.php
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
 * @desc This class manages a mail address.
 * @package {@package}
 */
class FormFieldMailEditor extends FormFieldTextEditor
{
    /**
     * @desc Constructs a FormFieldMailEditor.
     * @param string $id Field identifier
     * @param string $label Field label
     * @param string $value Default value
     * @param string[] $field_options Map containing the options
     * @param FormFieldConstraint[] $constraints The constraints checked during the validation
     */
    public function __construct($id, $label, $value, $field_options = array(), array $constraints = array())
    {
        $this->css_class = "text";
        $constraints[] = new FormFieldConstraintMailAddress();
        parent::__construct($id, $label, $value, $field_options, $constraints);
    }
}
?>