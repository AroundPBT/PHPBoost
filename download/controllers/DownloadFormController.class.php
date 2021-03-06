<?php
/*##################################################
 *                               DownloadFormController.class.php
 *                            -------------------
 *   begin                : August 24, 2014
 *   copyright            : (C) 2014 Julien BRISWALTER
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

class DownloadFormController extends ModuleController
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
	private $common_lang;
	
	private $config;
	
	private $downloadfile;
	private $is_new_downloadfile;
	
	public function execute(HTTPRequestCustom $request)
	{
		$this->init();
		
		$this->check_authorizations();
		
		$this->build_form($request);
		
		$tpl = new StringTemplate('# INCLUDE FORM #');
		$tpl->add_lang($this->lang);
		
		if ($this->submit_button->has_been_submited() && $this->form->validate())
		{
			$this->save();
			$this->redirect();
		}
		
		$tpl->put('FORM', $this->form->display());
		
		return $this->generate_response($tpl);
	}
	
	private function init()
	{
		$this->config = DownloadConfig::load();
		$this->lang = LangLoader::get('common', 'download');
		$this->common_lang = LangLoader::get('common');
	}
	
	private function build_form(HTTPRequestCustom $request)
	{
		$form = new HTMLForm(__CLASS__);
		
		$fieldset = new FormFieldsetHTML('download',  $this->get_downloadfile()->get_id() === null ? $this->lang['download.add'] : $this->lang['download.edit']);
		$form->add_fieldset($fieldset);
		
		$fieldset->add_field(new FormFieldTextEditor('name', $this->common_lang['form.name'], $this->get_downloadfile()->get_name(), array('required' => true)));
		
		$search_category_children_options = new SearchCategoryChildrensOptions();
		$search_category_children_options->add_authorizations_bits(Category::READ_AUTHORIZATIONS);
		$search_category_children_options->add_authorizations_bits(Category::CONTRIBUTION_AUTHORIZATIONS);
		$fieldset->add_field(DownloadService::get_categories_manager()->get_select_categories_form_field('id_category', $this->common_lang['form.category'], $this->get_downloadfile()->get_id_category(), $search_category_children_options));
		
		$fieldset->add_field(new FormFieldUploadFile('url', $this->common_lang['form.url'], $this->get_downloadfile()->get_url()->relative(), array('required' => true)));
		
		if ($this->get_downloadfile()->get_id() !== null && $this->get_downloadfile()->get_number_downloads() > 0)
		{
			$fieldset->add_field(new FormFieldCheckbox('reset_number_downloads', $this->lang['download.form.reset_number_downloads']));
		}
		
		$fieldset->add_field(new FormFieldRichTextEditor('contents', $this->common_lang['form.description'], $this->get_downloadfile()->get_contents(), array('rows' => 15, 'required' => true)));
		
		$fieldset->add_field(new FormFieldCheckbox('short_contents_enabled', $this->common_lang['form.short_contents.enabled'], $this->get_downloadfile()->is_short_contents_enabled(), 
			array('description' => StringVars::replace_vars($this->common_lang['form.short_contents.enabled.description'], array('number' => DownloadConfig::NUMBER_CARACTERS_BEFORE_CUT)), 'events' => array('click' => '
			if (HTMLForms.getField("short_contents_enabled").getValue()) {
				HTMLForms.getField("short_contents").enable();
			} else { 
				HTMLForms.getField("short_contents").disable();
			}'))
		));
		
		$fieldset->add_field(new FormFieldRichTextEditor('short_contents', $this->common_lang['form.description'], $this->get_downloadfile()->get_short_contents(), array(
			'hidden' => !$this->get_downloadfile()->is_short_contents_enabled(),
		)));
		
		if ($this->config->is_author_displayed())
		{
			$fieldset->add_field(new FormFieldCheckbox('author_display_name_enabled', $this->lang['download.form.author_display_name_enabled'], $this->get_downloadfile()->is_author_display_name_enabled(), 
				array('events' => array('click' => '
				if (HTMLForms.getField("author_display_name_enabled").getValue()) {
					HTMLForms.getField("author_display_name").enable();
				} else { 
					HTMLForms.getField("author_display_name").disable();
				}'))
			));
			
			$fieldset->add_field(new FormFieldTextEditor('author_display_name', $this->lang['download.form.author_display_name'], $this->get_downloadfile()->get_author_display_name(), array(
				'hidden' => !$this->get_downloadfile()->is_author_display_name_enabled(),
			)));
		}
		
		$other_fieldset = new FormFieldsetHTML('other', $this->common_lang['form.other']);
		$form->add_fieldset($other_fieldset);
		
		$other_fieldset->add_field(new FormFieldUploadPictureFile('picture', $this->common_lang['form.picture'], $this->get_downloadfile()->get_picture()->relative()));
		
		$other_fieldset->add_field(DownloadService::get_keywords_manager()->get_form_field($this->get_downloadfile()->get_id(), 'keywords', $this->common_lang['form.keywords'], array('description' => $this->common_lang['form.keywords.description'])));
		
		if (!$this->is_contributor_member())
		{
			$publication_fieldset = new FormFieldsetHTML('publication', $this->common_lang['form.approbation']);
			$form->add_fieldset($publication_fieldset);
			
			$publication_fieldset->add_field(new FormFieldDateTime('creation_date', $this->common_lang['form.date.creation'], $this->get_downloadfile()->get_creation_date(),
				array('required' => true)
			));
			
			$publication_fieldset->add_field(new FormFieldSimpleSelectChoice('approbation_type', $this->common_lang['form.approbation'], $this->get_downloadfile()->get_approbation_type(),
				array(
					new FormFieldSelectChoiceOption($this->common_lang['form.approbation.not'], DownloadFile::NOT_APPROVAL),
					new FormFieldSelectChoiceOption($this->common_lang['form.approbation.now'], DownloadFile::APPROVAL_NOW),
					new FormFieldSelectChoiceOption($this->common_lang['status.approved.date'], DownloadFile::APPROVAL_DATE),
				),
				array('events' => array('change' => '
				if (HTMLForms.getField("approbation_type").getValue() == 2) {
					jQuery("#' . __CLASS__ . '_start_date_field").show();
					HTMLForms.getField("end_date_enabled").enable();
				} else { 
					jQuery("#' . __CLASS__ . '_start_date_field").hide();
					HTMLForms.getField("end_date_enabled").disable();
				}'))
			));
			
			$publication_fieldset->add_field(new FormFieldDateTime('start_date', $this->common_lang['form.date.start'], ($this->get_downloadfile()->get_start_date() === null ? new Date() : $this->get_downloadfile()->get_start_date()), array('hidden' => ($this->get_downloadfile()->get_approbation_type() != DownloadFile::APPROVAL_DATE))));
			
			$publication_fieldset->add_field(new FormFieldCheckbox('end_date_enabled', $this->common_lang['form.date.end.enable'], $this->get_downloadfile()->is_end_date_enabled(), array(
			'hidden' => ($this->get_downloadfile()->get_approbation_type() != DownloadFile::APPROVAL_DATE),
			'events' => array('click' => '
			if (HTMLForms.getField("end_date_enabled").getValue()) {
				HTMLForms.getField("end_date").enable();
			} else { 
				HTMLForms.getField("end_date").disable();
			}'
			))));
			
			$publication_fieldset->add_field(new FormFieldDateTime('end_date', $this->common_lang['form.date.end'], ($this->get_downloadfile()->get_end_date() === null ? new Date() : $this->get_downloadfile()->get_end_date()), array('hidden' => !$this->get_downloadfile()->is_end_date_enabled())));
		}
		
		$this->build_contribution_fieldset($form);
		
		$fieldset->add_field(new FormFieldHidden('referrer', $request->get_url_referrer()));
		
		$this->submit_button = new FormButtonDefaultSubmit();
		$form->add_button($this->submit_button);
		$form->add_button(new FormButtonReset());
		
		$this->form = $form;
	}
	
	private function build_contribution_fieldset($form)
	{
		if ($this->is_contributor_member())
		{
			$fieldset = new FormFieldsetHTML('contribution', LangLoader::get_message('contribution', 'user-common'));
			$fieldset->set_description(MessageHelper::display($this->lang['download.form.contribution.explain'] . ' ' . LangLoader::get_message('contribution.explain', 'user-common'), MessageHelper::WARNING)->render());
			$form->add_fieldset($fieldset);
			
			$fieldset->add_field(new FormFieldRichTextEditor('contribution_description', LangLoader::get_message('contribution.description', 'user-common'), '', array('description' => LangLoader::get_message('contribution.description.explain', 'user-common'))));
		}
	}
	
	private function is_contributor_member()
	{
		return (!DownloadAuthorizationsService::check_authorizations()->write() && DownloadAuthorizationsService::check_authorizations()->contribution());
	}
	
	private function get_downloadfile()
	{
		if ($this->downloadfile === null)
		{
			$id = AppContext::get_request()->get_getint('id', 0);
			if (!empty($id))
			{
				try {
					$this->downloadfile = DownloadService::get_downloadfile('WHERE download.id=:id', array('id' => $id));
				} catch (RowNotFoundException $e) {
					$error_controller = PHPBoostErrors::unexisting_page();
					DispatchManager::redirect($error_controller);
				}
			}
			else
			{
				$this->is_new_downloadfile = true;
				$this->downloadfile = new DownloadFile();
				$this->downloadfile->init_default_properties(AppContext::get_request()->get_getint('id_category', Category::ROOT_CATEGORY));
			}
		}
		return $this->downloadfile;
	}
	
	private function check_authorizations()
	{
		$downloadfile = $this->get_downloadfile();
		
		if ($downloadfile->get_id() === null)
		{
			if (!$downloadfile->is_authorized_to_add())
			{
				$error_controller = PHPBoostErrors::user_not_authorized();
				DispatchManager::redirect($error_controller);
			}
		}
		else
		{
			if (!$downloadfile->is_authorized_to_edit())
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
		$downloadfile = $this->get_downloadfile();
		
		$downloadfile->set_name($this->form->get_value('name'));
		$downloadfile->set_rewrited_name(Url::encode_rewrite($downloadfile->get_name()));
		$downloadfile->set_id_category($this->form->get_value('id_category')->get_raw_value());
		$downloadfile->set_url(new Url($this->form->get_value('url')));
		$downloadfile->set_contents($this->form->get_value('contents'));
		$downloadfile->set_short_contents(($this->form->get_value('short_contents_enabled') ? $this->form->get_value('short_contents') : ''));
		$downloadfile->set_picture(new Url($this->form->get_value('picture')));
		
		if ($this->config->is_author_displayed())
			$downloadfile->set_author_display_name(($this->form->get_value('author_display_name') && $this->form->get_value('author_display_name') !== $downloadfile->get_author_user()->get_display_name() ? $this->form->get_value('author_display_name') : ''));
		
		$file_size = $status = 0;
		
		$file = new File($downloadfile->get_url()->rel());
		if ($file->exists())
			$file_size = $file->get_file_size();
		
		if (empty($file_size))
		{
			$file_headers = get_headers($downloadfile->get_url()->absolute(), true);
			if (is_array($file_headers))
			{
				if (isset($file_headers['Content-Length']))
					$file_size = $file_headers['Content-Length'];
				
				if(preg_match('/^HTTP\/[12]\.[01] (\d\d\d)/', $file_headers[0], $matches))
					$status = (int)$matches[1];
			}
		}
		
		$file_size = ($status == 200 && empty($file_size) && $downloadfile->get_size()) ? $downloadfile->get_size() : $file_size;
		
		$downloadfile->set_size($file_size);
		
		if ($this->get_downloadfile()->get_id() !== null && $downloadfile->get_number_downloads() > 0 && $this->form->get_value('reset_number_downloads'))
		{
			$downloadfile->set_number_downloads(0);
		}
		
		if ($this->is_contributor_member())
		{
			$downloadfile->set_approbation_type(DownloadFile::NOT_APPROVAL);
			$downloadfile->clean_start_and_end_date();
		}
		else
		{
			$downloadfile->set_creation_date($this->form->get_value('creation_date'));
			$downloadfile->set_approbation_type($this->form->get_value('approbation_type')->get_raw_value());
			if ($downloadfile->get_approbation_type() == DownloadFile::APPROVAL_DATE)
			{
				$downloadfile->set_start_date($this->form->get_value('start_date'));
				
				if ($this->form->get_value('end_date_enable'))
				{
					$downloadfile->set_end_date($this->form->get_value('end_date'));
				}
				else
				{
					$downloadfile->clean_end_date();
				}
			}
			else
			{
				$downloadfile->clean_start_and_end_date();
			}
		}
		
		if ($this->is_new_downloadfile)
		{
			$id = DownloadService::add($downloadfile);
		}
		else
		{
			$downloadfile->set_updated_date(new Date());
			$id = $downloadfile->get_id();
			DownloadService::update($downloadfile);
		}
		
		$this->contribution_actions($downloadfile, $id);
		
		DownloadService::get_keywords_manager()->put_relations($id, $this->form->get_value('keywords'));
		
		Feed::clear_cache('download');
		DownloadCache::invalidate();
	}
	
	private function contribution_actions(DownloadFile $downloadfile, $id)
	{
		if ($downloadfile->get_id() === null)
		{
			if ($this->is_contributor_member())
			{
				$contribution = new Contribution();
				$contribution->set_id_in_module($id);
				$contribution->set_description(stripslashes($this->form->get_value('contribution_description')));
				$contribution->set_entitled($downloadfile->get_name());
				$contribution->set_fixing_url(DownloadUrlBuilder::edit($id)->relative());
				$contribution->set_poster_id(AppContext::get_current_user()->get_id());
				$contribution->set_module('download');
				$contribution->set_auth(
					Authorizations::capture_and_shift_bit_auth(
						DownloadService::get_categories_manager()->get_heritated_authorizations($downloadfile->get_id_category(), Category::MODERATION_AUTHORIZATIONS, Authorizations::AUTH_CHILD_PRIORITY),
						Category::MODERATION_AUTHORIZATIONS, Contribution::CONTRIBUTION_AUTH_BIT
					)
				);
				ContributionService::save_contribution($contribution);
			}
		}
		else
		{
			$corresponding_contributions = ContributionService::find_by_criteria('download', $id);
			if (count($corresponding_contributions) > 0)
			{
				$downloadfile_contribution = $corresponding_contributions[0];
				$downloadfile_contribution->set_status(Event::EVENT_STATUS_PROCESSED);
				
				ContributionService::save_contribution($downloadfile_contribution);
			}
		}
		$downloadfile->set_id($id);
	}
	
	private function redirect()
	{
		$downloadfile = $this->get_downloadfile();
		$category = $downloadfile->get_category();
		
		if ($this->is_new_downloadfile && $this->is_contributor_member() && !$downloadfile->is_visible())
		{
			DispatchManager::redirect(new UserContributionSuccessController());
		}
		elseif ($downloadfile->is_visible())
		{
			if ($this->is_new_downloadfile)
				AppContext::get_response()->redirect(DownloadUrlBuilder::display($category->get_id(), $category->get_rewrited_name(), $downloadfile->get_id(), $downloadfile->get_rewrited_name()), StringVars::replace_vars($this->lang['download.message.success.add'], array('name' => $downloadfile->get_name())));
			else
				AppContext::get_response()->redirect(($this->form->get_value('referrer') ? $this->form->get_value('referrer') : DownloadUrlBuilder::display($category->get_id(), $category->get_rewrited_name(), $downloadfile->get_id(), $downloadfile->get_rewrited_name())), StringVars::replace_vars($this->lang['download.message.success.edit'], array('name' => $downloadfile->get_name())));
		}
		else
		{
			if ($this->is_new_downloadfile)
				AppContext::get_response()->redirect(DownloadUrlBuilder::display_pending(), StringVars::replace_vars($this->lang['download.message.success.add'], array('name' => $downloadfile->get_name())));
			else
				AppContext::get_response()->redirect(($this->form->get_value('referrer') ? $this->form->get_value('referrer') : DownloadUrlBuilder::display_pending()), StringVars::replace_vars($this->lang['download.message.success.edit'], array('name' => $downloadfile->get_name())));
		}
	}
	
	private function generate_response(View $tpl)
	{
		$downloadfile = $this->get_downloadfile();
		
		$response = new SiteDisplayResponse($tpl);
		$graphical_environment = $response->get_graphical_environment();
		
		$breadcrumb = $graphical_environment->get_breadcrumb();
		$breadcrumb->add($this->lang['module_title'], DownloadUrlBuilder::home());
		
		if ($downloadfile->get_id() === null)
		{
			$graphical_environment->set_page_title($this->lang['download.add'], $this->lang['module_title']);
			$breadcrumb->add($this->lang['download.add'], DownloadUrlBuilder::add($downloadfile->get_id_category()));
			$graphical_environment->get_seo_meta_data()->set_description($this->lang['download.add']);
			$graphical_environment->get_seo_meta_data()->set_canonical_url(DownloadUrlBuilder::add($downloadfile->get_id_category()));
		}
		else
		{
			$graphical_environment->set_page_title($this->lang['download.edit'], $this->lang['module_title']);
			$graphical_environment->get_seo_meta_data()->set_description($this->lang['download.edit']);
			$graphical_environment->get_seo_meta_data()->set_canonical_url(DownloadUrlBuilder::edit($downloadfile->get_id()));
			
			$categories = array_reverse(DownloadService::get_categories_manager()->get_parents($downloadfile->get_id_category(), true));
			foreach ($categories as $id => $category)
			{
				if ($category->get_id() != Category::ROOT_CATEGORY)
					$breadcrumb->add($category->get_name(), DownloadUrlBuilder::display_category($category->get_id(), $category->get_rewrited_name()));
			}
			$category = $downloadfile->get_category();
			$breadcrumb->add($downloadfile->get_name(), DownloadUrlBuilder::display($category->get_id(), $category->get_rewrited_name(), $downloadfile->get_id(), $downloadfile->get_rewrited_name()));
			$breadcrumb->add($this->lang['download.edit'], DownloadUrlBuilder::edit($downloadfile->get_id()));
		}
		
		return $response;
	}
}
?>
