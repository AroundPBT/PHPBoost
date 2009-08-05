<?php
/*##################################################
 *                          bbcode_editor.class.php
 *                            -------------------
 *   begin                : January 01 2009
 *   copyright            : (C) 2009 R�gis Viarre
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

/**
* @package content
*/

/**
* Constant definition
*/
define('PATH_TO_ROOT', '../../../..');
require_once(PATH_TO_ROOT . '/kernel/begin.php');
define('TITLE', $LANG['all_smiley']);
require_once(PATH_TO_ROOT . '/kernel/header_no_display.php');

$tpl_smileys = new template('framework/content/smileys.tpl');

//Chargement de la configuration.
$Cache->load('smileys');
$height_max = 50;
$width_max = 50;
$smile_by_line = 4;

$field = retrieve(GET, 'field', 'contents');
$tpl_smileys->assign_vars(array(
	'PATH_TO_ROOT' => TPL_PATH_TO_ROOT,
	'SITE_NAME' => $CONFIG['site_name'],
	'TITLE' => stripslashes(TITLE),
	'THEME' => $CONFIG['theme'],
	'FIELD' => $field ,
	'COLSPAN' => $smile_by_line + 1,
	'L_XML_LANGUAGE' => $LANG['xml_lang'],
	'L_SMILEY' => $LANG['smiley'],
	'L_CLOSE' => $LANG['close'],
	'L_REQUIRE_TEXT' => $LANG['require_text']
));

$nbr_smile = count($_array_smiley_code);
$j = 0;
foreach($_array_smiley_code as $code_smile => $url_smile)
{
    $width_source = 18; //Valeur par d�faut.
    $height_source = 18;

    // On recup�re la hauteur et la largeur de l'image.
    list($width_source, $height_source) = @getimagesize(PATH_TO_ROOT . '/images/smileys/' . $url_smile);
    if( $width_source > $width_max || $height_source > $height_max )
    {
        if( $width_source > $height_source )
        {
            $ratio = $width_source / $height_source;
            $width = $width_max;
            $height = $width / $ratio;
        }
        else
        {
            $ratio = $height_source / $width_source;
            $height = $height_max;
            $width = $height / $ratio;
        }
    }
    else
    {
        $width = $width_source;
        $height = $height_source;
    }

    $img = '<img src="' . PATH_TO_ROOT . '/images/smileys/' . $url_smile . '" height="' . $height . '" width="' . $width . '" alt="' . $code_smile . '" title="' . $code_smile . '" />';

    //On gen�re le tableau pour $smile_by_line colonnes
    $multiple_x = $j / $smile_by_line ;
    $tr_start = (is_int($multiple_x)) ? '<tr>' : '';
    $j++;
    $multiple_x = $j / $smile_by_line ;
    $tr_end = (is_int($multiple_x)) ? '</tr>' : '';

    //Si la ligne n'est pas compl�te on termine par </tr>.
    if( $nbr_smile == $j )
    {
        $tr_end = '</tr>';
    }

    $tpl_smileys->assign_block_vars('smiley', array(
		'IMG' => $img,
		'CODE' => addslashes($code_smile),
		'TR_START' => $tr_start,
		'TR_END' => $tr_end,
    ));

    //Cr�ation des cellules du tableau si besoin est.
    if( $nbr_smile == $j && $nbr_smile > $smile_by_line )
    {
        while( !is_int($j / $smile_by_line) )
        {
            $tpl_smileys->assign_block_vars('smiley.td', array(
				'TD' => '<td>&nbsp;</td>'
				));
				$j++;
        }
    }
}

$tpl_smileys->parse();

?>