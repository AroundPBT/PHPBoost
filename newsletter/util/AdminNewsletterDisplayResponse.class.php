<?php
/*##################################################
 *                           AdminNewsletterDisplayResponse.class.php
 *                            -------------------
 *   begin                : March 24, 2011
 *   copyright            : (C) 2011 Kevin MASSY
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

class AdminNewsletterDisplayResponse extends AdminMenuDisplayResponse
{
	public function __construct($view, $title_page)
	{
		parent::__construct($view);

		$lang = LangLoader::get('newsletter_common', 'newsletter');
		$picture = '/newsletter/newsletter.png';
		$this->set_title($lang['newsletter']);
		$this->add_link($lang['newsletter.home'], NewsletterUrlBuilder::home(), $picture);
		$this->add_link($lang['newsletter.archives'], NewsletterUrlBuilder::archives(), $picture);
		$this->add_link($lang['newsletter.streams'], NewsletterUrlBuilder::streams(), $picture);
		$this->add_link($lang['streams.add'], NewsletterUrlBuilder::add_stream(), $picture);
		$this->add_link($lang['newsletter.config'], NewsletterUrlBuilder::configuration(), $picture);

		$env = $this->get_graphical_environment();
		$env->set_page_title($title_page);
	}
}
?>