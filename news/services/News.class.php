<?php
/*##################################################
 *		                         News.class.php
 *                            -------------------
 *   begin                : February 13, 2013
 *   copyright            : (C) 2013 Kevin MASSY
 *   email                : kevin.massy@phpboost.com
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
 * @author Kevin MASSY <kevin.massy@phpboost.com>
 */
class News
{
	private $id;
	private $id_cat;
	private $name;
	private $rewrited_name;
	private $contents;
	private $short_contents;
	
	private $approbation_type;
	private $start_date;
	private $end_date;
	private $end_date_enabled;
	private $top_list_enabled;
	
	private $creation_date;
	private $updated_date;
	private $author_user;

	private $picture_url;
	private $sources;
	private $keywords;
	
	const NOT_APPROVAL = 0;
	const APPROVAL_NOW = 1;
	const APPROVAL_DATE = 2;
	
	const DEFAULT_PICTURE = '/news/news.png';
	
	public function set_id($id)
	{
		$this->id = $id;
	}
	
	public function get_id()
	{
		return $this->id;
	}
	
	public function set_id_cat($id_cat)
	{
		$this->id_cat = $id_cat;
	}
	
	public function get_id_cat()
	{
		return $this->id_cat;
	}
	
	public function set_name($name)
	{
		$this->name = $name;
	}
	
	public function get_name()
	{
		return $this->name;
	}
	
	public function set_rewrited_name($rewrited_name)
	{
		$this->rewrited_name = $rewrited_name;
	}
	
	public function get_rewrited_name()
	{
		return $this->rewrited_name;
	}
	
	public function rewrited_name_is_personalized()
	{
		return $this->rewrited_name != Url::encode_rewrite($this->name);
	}
	
	public function set_contents($contents)
	{
		$this->contents = $contents;
	}
	
	public function get_contents()
	{
		return $this->contents;
	}
	
	public function set_short_contents($short_contents)
	{
		$this->short_contents = $short_contents;
	}
	
	public function get_short_contents()
	{
		return $this->short_contents;
	}
	
	public function get_real_short_contents()
	{
		if ($this->get_short_contents_enabled())
		{
			return $this->short_contents;
		}
		return substr(@strip_tags($this->contents, '<br>'), 0, NewsConfig::load()->get_number_character_to_cut());
	}
		
	public function get_short_contents_enabled()
	{
		return !empty($this->short_contents);
	}
	
	public function set_approbation_type($approbation_type)
	{
		$this->approbation_type = $approbation_type;
	}
	
	public function get_approbation_type()
	{
		return $this->approbation_type;
	}
	
	public function is_visible()
	{
		$now = new Date();
		return $this->get_approbation_type() == News::APPROVAL_NOW || ($this->get_approbation_type() == News::APPROVAL_DATE && $this->get_start_date()->is_anterior_to($now) && ($this->end_date_enabled ? $this->get_end_date()->is_posterior_to($now) : true));
	}
	
	public function get_status()
	{
		switch ($this->approbation_type) {
			case self::APPROVAL_NOW:
				return LangLoader::get_message('news.form.approved.now', 'common', 'news');
			break;
			case self::APPROVAL_DATE:
				return LangLoader::get_message('news.form.approved.date', 'common', 'news');
			break;
			case self::NOT_APPROVAL:
				return LangLoader::get_message('news.form.approved.not', 'common', 'news');
			break;
		}
	}
	
	public function set_start_date(Date $start_date)
	{
		$this->start_date = $start_date;
	}
	
	public function get_start_date()
	{
		return $this->start_date;
	}
	
	public function set_end_date(Date $end_date)
	{
		$this->end_date = $end_date;
		$this->end_date_enabled = true;
	}
	
	public function get_end_date()
	{
		return $this->end_date;
	}
	
	public function end_date_enabled()
	{
		return $this->end_date_enabled;
	}
	
	public function set_top_list_enabled($top_list_enabled)
	{
		$this->top_list_enabled = $top_list_enabled;
	}
	
	public function top_list_enabled()
	{
		return $this->top_list_enabled;
	}

	public function set_creation_date(Date $creation_date)
	{
		$this->creation_date = $creation_date;
	}
	
	public function get_creation_date()
	{
		return $this->creation_date;
	}
	
	public function set_updated_date(Date $updated_date)
	{
		$this->updated_date = $updated_date;
	}
	
	public function get_updated_date()
	{
		return $this->updated_date;
	}
	
	public function has_updated_date()
	{
		return $this->updated_date !== null;
	}
	
	public function set_author_user(User $user)
	{
		$this->author_user = $user;
	}
	
	public function get_author_user()
	{
		return $this->author_user;
	}
	
	public function set_picture(Url $picture)
	{
		$this->picture_url = $picture;
	}
	
	public function get_picture()
	{
		return $this->picture_url;
	}
	
	public function has_picture()
	{
		$picture = $this->picture_url->rel();
		return !empty($picture);
	}
	
	public function add_source($source)
	{
		$this->sources[] = $source;
	}
	
	public function set_sources($sources)
	{
		$this->sources = $sources;
	}
	
	public function get_sources()
	{
		return $this->sources;
	}
	
	public function get_keywords()
	{
		if ($this->keywords === null)
		{
			$this->keywords = NewsService::get_keywords_manager()->get_keywords($this->id);
		}
		return $this->keywords;
	}
	
	public function get_keywords_name()
	{
		return array_keys($this->get_keywords());
	}
	
	public function is_authorized_add()
	{
		return NewsAuthorizationsService::check_authorizations($this->id_cat)->write() || NewsAuthorizationsService::check_authorizations($this->id_cat)->contribution();
	}
	
	public function is_authorized_edit()
	{
		return NewsAuthorizationsService::check_authorizations($this->id_cat)->moderation() || ((NewsAuthorizationsService::check_authorizations($this->get_id_cat())->write() || (NewsAuthorizationsService::check_authorizations($this->get_id_cat())->contribution() && !$this->is_visible())) && $this->get_author_user()->get_id() == AppContext::get_current_user()->get_id() && AppContext::get_current_user()->check_level(User::MEMBER_LEVEL));
	}
	
	public function is_authorized_delete()
	{
		return NewsAuthorizationsService::check_authorizations($this->id_cat)->moderation() || ((NewsAuthorizationsService::check_authorizations($this->get_id_cat())->write() || (NewsAuthorizationsService::check_authorizations($this->get_id_cat())->contribution() && !$this->is_visible())) && $this->get_author_user()->get_id() == AppContext::get_current_user()->get_id() && AppContext::get_current_user()->check_level(User::MEMBER_LEVEL));
	}
	
	public function get_properties()
	{
		return array(
			'id' => $this->get_id(),
			'id_category' => $this->get_id_cat(),
			'name' => TextHelper::htmlspecialchars($this->get_name()),
			'rewrited_name' => TextHelper::htmlspecialchars($this->get_rewrited_name()),
			'contents' => $this->get_contents(),
			'short_contents' => $this->get_short_contents(),
			'approbation_type' => $this->get_approbation_type(),
			'start_date' => $this->get_start_date() !== null ? $this->get_start_date()->get_timestamp() : '',
			'end_date' => $this->get_end_date() !== null ? $this->get_end_date()->get_timestamp() : '',
			'top_list_enabled' => (int)$this->top_list_enabled(),
			'creation_date' => $this->get_creation_date()->get_timestamp(),
			'updated_date' => $this->get_updated_date() !== null ? $this->get_updated_date()->get_timestamp() : '',
			'author_user_id' => $this->get_author_user()->get_id(),
			'picture_url' => TextHelper::htmlspecialchars($this->get_picture()->relative()),
			'sources' => serialize($this->get_sources())
		);
	}
	
	public function set_properties(array $properties)
	{
		$this->id = $properties['id'];
		$this->id_cat = $properties['id_category'];
		$this->name = $properties['name'];
		$this->rewrited_name = $properties['rewrited_name'];
		$this->contents = $properties['contents'];
		$this->short_contents = $properties['short_contents'];
		$this->approbation_type = $properties['approbation_type'];
		$this->start_date = !empty($properties['start_date']) ? new Date(DATE_TIMESTAMP, TIMEZONE_SYSTEM, $properties['start_date']) : null;
		$this->end_date = !empty($properties['end_date']) ? new Date(DATE_TIMESTAMP, TIMEZONE_SYSTEM, $properties['end_date']) : null;
		$this->end_date_enabled = !empty($properties['end_date']);
		$this->top_list_enabled = (bool)$properties['top_list_enabled'];
		$this->creation_date = new Date(DATE_TIMESTAMP, TIMEZONE_SYSTEM, $properties['creation_date']);
		$this->updated_date = !empty($properties['updated_date']) ? new Date(DATE_TIMESTAMP, TIMEZONE_SYSTEM, $properties['updated_date']) : null;
		$this->picture_url = new Url($properties['picture_url']);
		$this->sources = !empty($properties['sources']) ? unserialize($properties['sources']) : array();

		$user = new User();
		if (!empty($properties['user_id']))
			$user->set_properties($properties);
		else
			$user->init_visitor_user();
			
		$this->set_author_user($user);
	}
	
	public function init_default_properties()
	{
		$this->id_cat = Category::ROOT_CATEGORY;
		$this->approbation_type = self::APPROVAL_NOW;
		$this->start_date = new Date();
		$this->end_date = new Date();
		$this->creation_date = new Date();
		$this->sources = array();
		$this->picture_url = new Url(self::DEFAULT_PICTURE);
		$this->end_date_enabled = false;
	}
	
	public function clean_start_and_end_date()
	{
		$this->start_date = null;
		$this->end_date = null;
		$this->end_date_enabled = false;
	}
	
	public function clean_end_date()
	{
		$this->end_date = null;
		$this->end_date_enabled = false;
	}
	
	public function get_array_tpl_vars()
	{
		$category = NewsService::get_categories_manager()->get_categories_cache()->get_category($this->id_cat);
		$user = $this->get_author_user();
		$user_group_color = User::get_group_color($user->get_groups(), $user->get_level(), true);
		$number_comments = CommentsService::get_number_comments('news', $this->id);
		
		return array(
			'C_EDIT' => $this->is_authorized_edit(),
			'C_DELETE' => $this->is_authorized_delete(),
			'C_PICTURE' => $this->has_picture(),
			'C_USER_GROUP_COLOR' => !empty($user_group_color),
			'C_AUTHOR_DISPLAYED' => NewsConfig::load()->get_author_displayed(), 

			//News
			'ID' => $this->id,
			'NAME' => $this->name,
			'CONTENTS' => FormatingHelper::second_parse($this->contents),
			'DESCRIPTION' => $this->get_real_short_contents(),
			'DATE' => $this->creation_date->format(Date::FORMAT_DAY_MONTH_YEAR_HOUR_MINUTE_TEXT),
			'DATE_ISO8601' => $this->creation_date->format(Date::FORMAT_ISO8601),
			'STATUS' => $this->get_status(),
			'C_AUTHOR_EXIST' => $user->get_id() !== User::VISITOR_LEVEL,
			'PSEUDO' => $user->get_pseudo(),
			'USER_LEVEL_CLASS' => UserService::get_level_class($user->get_level()),
			'USER_GROUP_COLOR' => $user_group_color,
		
			'C_COMMENTS' => !empty($number_comments),
			'L_COMMENTS' => CommentsService::get_lang_comments('news', $this->id),
			'NUMBER_COMMENTS' => CommentsService::get_number_comments('news', $this->id),
			
			//Category
			'CATEGORY_ID' => $category->get_id(),
			'CATEGORY_NAME' => $category->get_name(),
			'CATEGORY_DESCRIPTION' => $category->get_description(),
			'CATEGORY_IMAGE' => $category->get_image(),
			
			'U_SYNDICATION' => SyndicationUrlBuilder::rss('news', $this->id_cat)->rel(),
			'U_AUTHOR_PROFILE' => UserUrlBuilder::profile($this->get_author_user()->get_id())->rel(),
			'U_LINK' => NewsUrlBuilder::display_news($category->get_id(), $category->get_rewrited_name(), $this->id, $this->rewrited_name)->rel(),
			'U_CATEGORY' => NewsUrlBuilder::display_category($category->get_id(), $category->get_rewrited_name())->rel(),
			'U_EDIT' => NewsUrlBuilder::edit_news($this->id)->rel(),
			'U_DELETE' => NewsUrlBuilder::delete_news($this->id)->rel(),
			'U_PICTURE' => $this->get_picture()->rel(),
			'U_COMMENTS' => NewsUrlBuilder::display_comments_news($category->get_id(), $category->get_rewrited_name(), $this->id, $this->rewrited_name)->rel()
		);
	}
}
?>