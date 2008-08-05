<?php
/*##################################################
 *                             menu.class.php
 *                            -------------------
*   begin                : July 08, 2008
 *   copyright          : (C) 2008 Viarre R�gis
 *   email                : crowkait@phpboost.com
 *
 *
###################################################
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 * 
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
###################################################*/

define('MENU_MODULE', 0x01); //Menu de type module.
define('MENU_LINKS', 0x02); //Menu de type liens.
define('MENU_PERSONNAL', 0x04); //Menu de type menu personnel.
define('MENU_CONTENTS', 0x08); //Menu de type contenu.

class Menu
{
	## Public Methods ##
	//Constructeur.
	function Menu($type)
	{
		$this->type = $type;
	}
	
	function code_format($name, $contents, $location, $auth, $use_tpl = '0')
	{
		switch($this->type)
		{
			case MENU_MODULE:
				return 'if( $Member->Check_auth(' . var_export(unserialize($auth), true) . ', AUTH_MENUS) ){' . "\n"
				. "\t" . 'include_once(PATH_TO_ROOT . \'/' . $name . '/' . $contents . "');\n"
				. "\t" . '$MODULES_MINI[\'' . $location . '\'] .= $Template->Pparse(\'' . str_replace('.php', '', $contents) . '\', TEMPLATE_STRING_MODE);' 
				. "\n" . '}';
			
			case MENU_LINKS:
				/*$Template->Set_filenames(array('links_menu' => 'links_menu.tpl'));
				$links_list = array(
					0 => array('Liens', '', 0, true, array('r-1' => 1,'r0' => 1,'r1' => 1,'r2' => 1)), 
					1 => array('Accueil', 'index.php', 1, false, array('r-1' => 1,'r0' => 1,'r1' => 1,'r2' => 1)), 
					2 => array('Forum', '../forum/index.php', 1, false, array('r-1' => 1,'r0' => 1,'r1' => 1,'r2' => 1))
				);
				foreach($links_list as $link_info)
				{
					if( $links_info[3] )
					{
						$Template->Assign_block_vars('title', array(
							'NAME' => $links_info[0],
							'URL' => $links_info[1]
						));
					}
					else
					{
						$Template->Assign_block_vars('links', array(
							'NAME' => $links_info[0],
							'URL' => $links_info[1]
						));
					}
				}
				$MODULES_MINI['left'] .= $Template->Pparse('links_menu', TEMPLATE_STRING_MODE);*/
				return '';
			
			case MENU_PERSONNAL:
				return 'if( $Member->Check_auth(' . var_export(unserialize($auth), true) . ', AUTH_MENUS) ){' . "\n"
				. "\t" . 'include_once(\'PATH_TO_ROOT . \'/menus/' . $contents . "');\n"
				. "\t" . '$MODULES_MINI[\'' . $location . '\'] .= $Template->Pparse(\'' . str_replace('.php', '', $contents) . '\', TEMPLATE_STRING_MODE);' 
				. "\n" . '}';
		
			case MENU_CONTENTS:
				$code = 'if( $Member->Check_auth(' . var_export(unserialize($auth), true) . ', AUTH_MENUS) ){' . "\n";
				if( $use_tpl == '0' )
					$code .= '$MODULES_MINI[\'' . $location . '\'] .= ' . var_export($contents, true) . ';' . "\n";
				else
				{
					switch($location)
					{
						case 'left':
						case 'right':
							$code .= "\$Template->Set_filenames(array('modules_mini' => 'modules_mini.tpl'));\n"
							. "\$Template->Assign_vars(array('MODULE_MINI_NAME' => " . var_export($name, true) . ", 'MODULE_MINI_CONTENTS' => " . var_export($contents, true) . "));\n"
							. '$MODULES_MINI[\'' . $location . '\'] .= $Template->Pparse(\'modules_mini\', TEMPLATE_STRING_MODE);';
						break;
						case 'header':
						case 'subheader':
						case 'topcentral':
						case 'bottomcentral':
						case 'topfooter':
						case 'footer':
							$code .= "\$Template->Set_filenames(array('modules_mini_horizontal' => 'modules_mini_horizontal.tpl'));"
							. "\t\$Template->Assign_vars(array('MODULE_MINI_NAME' => " . var_export($name, true) . ", 'MODULE_MINI_CONTENTS' => " . var_export($contents, true) . "));\n"
							. '$MODULES_MINI[\'' . $location . '\'] .= $Template->Pparse(\'modules_mini_horizontal\', TEMPLATE_STRING_MODE);';
							
						break;		
					}	
				}				
				$code .=  "\n" 
				. '}';
				return $code;
		}
		
	}
	
	## Private Methods ##
	
	## Private attributes ##
	var $type = MENU_MODULE; //Type de menu.
}

?>