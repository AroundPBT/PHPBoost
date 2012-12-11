<?php
/*##################################################
 *                             MenuStringFilter.class.php
 *                            -------------------
 *   begin                : March 06, 2011
 *   copyright            : (C) 2011 R�gis Viarre
 *   email                : crowkait@phpboost.com
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
 * @author R�gis Viarre <crowkait@phpboost.com>
 * @desc This class represents a filter based on string comparison
 * @package {@package}
 */
class MenuStringFilter extends Filter implements MenuFilter {
	public function __construct($pattern)
    {
       parent::__construct($pattern);
    }
	
	public function get_raw_matcher($input) {
		return 'strpos(' . $input . ', "' . $this->pattern . '") !== false';
	}
	
	public function match($input) {
		return strpos($input, $this->pattern ) !== false;
	}
}
?>