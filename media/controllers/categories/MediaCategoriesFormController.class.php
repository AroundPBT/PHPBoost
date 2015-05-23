<?php
/*##################################################
 *                               MediaCategoriesFormController.class.php
 *                            -------------------
 *   begin                : February 4, 2015
 *   copyright            : (C) 2015 Julien BRISWALTER
 *   email                : julienseth78@phpboost.com
 *
 *
 ###################################################
 *
 * This program is a free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 ###################################################*/

 /**
 * @author Julien BRISWALTER <julienseth78@phpboost.com>
 */

class MediaCategoriesFormController extends AbstractRichCategoriesFormController
{
	protected function generate_response(View $view)
	{
		return new AdminMediaDisplayResponse($view, $this->get_title());
	}
	
	protected function get_categories_manager()
	{
		return MediaService::get_categories_manager();
	}
	
	protected function get_id_category()
	{
		return AppContext::get_request()->get_getint('id', 0);
	}
	
	protected function get_categories_management_url()
	{
		return MediaUrlBuilder::manage_categories();
	}
	
	protected function get_options_fields(FormFieldset $fieldset)
	{
		parent::get_options_fields($fieldset);
		$fieldset->add_field(new FormFieldSimpleSelectChoice('content_type', LangLoader::get_message('content_type', 'common', 'media'), $this->get_category()->get_content_type(),
			array(
				new FormFieldSelectChoiceOption(LangLoader::get_message('content_type.music_and_video', 'common', 'media'), MediaConfig::CONTENT_TYPE_MUSIC_AND_VIDEO),
				new FormFieldSelectChoiceOption(LangLoader::get_message('content_type.music', 'common', 'media'), MediaConfig::CONTENT_TYPE_MUSIC),
				new FormFieldSelectChoiceOption(LangLoader::get_message('content_type.video', 'common', 'media'), MediaConfig::CONTENT_TYPE_VIDEO)
			)
		));
	}
	
	protected function set_properties()
	{
		parent::set_properties();
		$this->get_category()->set_content_type($this->form->get_value('content_type')->get_raw_value());
	}
}
?>