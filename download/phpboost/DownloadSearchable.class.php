<?php
/*##################################################
 *                               DownloadSearchable.class.php
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

class DownloadSearchable extends AbstractSearchableExtensionPoint
{
	public function get_search_request($args)
	{
		$now = new Date();
		$authorized_categories = DownloadService::get_authorized_categories(Category::ROOT_CATEGORY);
		$weight = isset($args['weight']) && is_numeric($args['weight']) ? $args['weight'] : 1;
		
		return "SELECT " . $args['id_search'] . " AS id_search,
			d.id AS id_content,
			d.name AS title,
			( 2 * FT_SEARCH_RELEVANCE(d.name, '" . $args['search'] . "') + (FT_SEARCH_RELEVANCE(d.contents, '" . $args['search'] . "') +
			FT_SEARCH_RELEVANCE(d.short_contents, '" . $args['search'] . "')) / 2 ) / 3 * " . $weight . " AS relevance,
			CONCAT('" . PATH_TO_ROOT . "/download/index.php?url=/', id_category, '-', IF(id_category != 0, cat.rewrited_name, 'root'), '/', d.id, '-', d.rewrited_name) AS link
			FROM " . DownloadSetup::$download_table . " d
			LEFT JOIN ". DownloadSetup::$download_cats_table ." cat ON d.id_category = cat.id
			WHERE ( FT_SEARCH(d.name, '" . $args['search'] . "') OR FT_SEARCH(d.contents, '" . $args['search'] . "') OR FT_SEARCH_RELEVANCE(d.short_contents, '" . $args['search'] . "') )
			AND id_category IN (" . implode(", ", $authorized_categories) . ")
			AND (approbation_type = 1 OR (approbation_type = 2 AND start_date < '" . $now->get_timestamp() . "' AND (end_date > '" . $now->get_timestamp() . "' OR end_date = 0)))
			ORDER BY relevance DESC
			LIMIT 100 OFFSET 0";
	}
}
?>