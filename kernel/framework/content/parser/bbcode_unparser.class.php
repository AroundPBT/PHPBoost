<?php
/*##################################################
*                         bbcode_unparser.class.php
*                            -------------------
*   begin                : July 3 2008
*   copyright            : (C) 2008 Benoit Sautel
*   email                :  ben.popeye@phpboost.com
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

import('content/parser/content_unparser');

/**
 * @package parser
 * @author Beno�t Sautel <ben.popeye@phpboost.com>
 * @desc BBCode unparser. It converts a content using the PHPBoost HTML reference code (for example
 * coming from a database) to the PHPBoost BBCode syntax.
 */
class BBCodeUnparser extends ContentUnparser
{
	/**
	 * @desc Builds a BBCodeUnparser object
	 */
	function BBCodeUnparser()
	{
		//We call the parent constructor
		parent::ContentUnparser();
	}
	
	/**
	 * @desc Unparses the content of the parser.
	 * Converts it from HTML syntax to BBcode syntax
	 */
	function unparse()
	{
		//Isolement du code source et du code HTML qui ne sera pas prot�g�
		$this->_unparse_html(PICK_UP);
		$this->_unparse_code(PICK_UP);
		
		//Si on n'est pas � la racine du site plus un dossier, on remplace les liens relatifs g�n�r�s par le BBCode
		if (PATH_TO_ROOT != '..')
		{
			$this->content = str_replace('"' . PATH_TO_ROOT . '/', '"../', $this->content);
		}
		
		//Smilies
		$this->_unparse_smilies();
		
		//Remplacement des balises simples
		$this->_unparse_simple_tags();

		//Unparsage de la balise table.
		if (strpos($this->content, '<table class="bb_table"') !== false)
			$this->_unparse_table();

		//Unparsage de la balise table.
		if (strpos($this->content, '<li class="bb_li"') !== false)
			$this->_unparse_list();
		
		$this->_unparse_code(REIMPLANT);
		$this->_unparse_html(REIMPLANT);
	}
	
	## Private ##
	/**
	 * @desc Unparse the smiley's code of the content of the parser.
	 * Replace the HTML code by the smiley code (for instance :) or :|)
	 */
	function _unparse_smilies()
	{
		//Smilies
		@include(PATH_TO_ROOT . '/cache/smileys.php');
		if (!empty($_array_smiley_code))
		{
			//Cr�ation du tableau de remplacement
			foreach ($_array_smiley_code as $code => $img)
			{	
				$smiley_img_url[] = '`<img src="../images/smileys/' . preg_quote($img) . '(.*) />`sU';
				$smiley_code[] = $code;
			}	
			$this->content = preg_replace($smiley_img_url, $smiley_code, $this->content);
		}
	}
	
	/**
	 * @desc Unparsed the simple tags of the content of the parser.
	 * The simple tags are the ones which are processable in a few lines
	 */
	function _unparse_simple_tags()
	{
		$array_str = array( 
			'<br />', '<strong>', '</strong>', '<em>', '</em>', '<strike>', '</strike>', '<hr class="bb_hr" />', '<sup>', '</sup>',
			'<sub>', '</sub>', '<pre>', '</pre>'
		);
		$array_str_replace = array( 
			'', '[b]', '[/b]', '[i]', '[/i]', '[s]', '[/s]', '[line]', '[sup]', '[/sup]', '[sub]', '[/sub]', '[pre]', '[/pre]'
		);
		$this->content = str_replace($array_str, $array_str_replace, $this->content);

		$array_preg = array( 
			'`<img src="([^?\n\r\t].*)" alt="[^"]*"(?: class="[^"]+")? />`iU',
			'`<span style="color:([^;]+);">(.*)</span>`isU',
			'`<span style="background-color:([^;]+);">(.*)</span>`isU',
			'`<span style="text-decoration: underline;">(.*)</span>`isU',
			'`<span style="font-size: ([0-9]+)px;">(.*)</span>`isU',
			'`<span style="font-family: ([ a-z0-9,_-]+);">(.*)</span>`isU',
			'`<p style="text-align:(left|center|right|justify)">(.*)</p>`isU',
			'`<p class="float_(left|right)">(.*)</p>`isU',
			'`<span id="([a-z0-9_-]+)">(.*)</span>`isU',
			'`<acronym title="([^"]+)" class="bb_acronym">(.*)</acronym>`isU',
			'`<a href="mailto:(.*)">(.*)</a>`isU',
			'`<a href="([^"]+)">(.*)</a>`isU',
			'`<h3 class="title([1-2]+)">(.*)</h3>`isU',
			'`<h4 class="stitle1">(.*)</h4>`isU',
			'`<h4 class="stitle2">(.*)</h4>`isU',
			'`<span class="(success|question|notice|warning|error)">(.*)</span>`isU',
			'`<object type="application/x-shockwave-flash" data="\.\./(?:kernel|includes)/data/dewplayer\.swf\?son=(.*)" width="200" height="20">(.*)</object>`isU',
			'`<object type="application/x-shockwave-flash" data="\.\./(?:kernel|includes)/data/movieplayer\.swf" width="([^"]+)" height="([^"]+)">(?:\s|(?:<br />))*<param name="FlashVars" value="flv=(.+)&width=[0-9]+&height=[0-9]+" />.*</object>`isU',
			'`<object type="application/x-shockwave-flash" data="([^"]+)" width="([^"]+)" height="([^"]+)">(.*)</object>`isU',
			'`<!-- START HTML -->' . "\n" . '(.+)' . "\n" . '<!-- END HTML -->`isU',
			'`\[\[MATH\]\](.+)\[\[/MATH\]\]`sU'
		);
		
		$array_preg_replace = array( 
			"[img]$1[/img]",
			"[color=$1]$2[/color]",
			"[bgcolor=$1]$2[/bgcolor]",
			"[u]$1[/u]",	
			"[size=$1]$2[/size]",
			"[font=$1]$2[/font]",
			"[align=$1]$2[/align]",
			"[float=$1]$2[/float]",
			"[anchor=$1]$2[/anchor]",
			"[acronym=$1]$2[/acronym]",
			"[mail=$1]$2[/mail]",
			"[url=$1]$2[/url]",
			"[title=$1]$2[/title]",
			"[title=3]$1[/title]",
			"[title=4]$1[/title]",
			"[style=$1]$2[/style]",
			"[sound]$1[/sound]",
			"[movie=$1,$2]$3[/movie]",
			"[swf=$2,$3]$1[/swf]",
			"[html]$1[/html]",
			"[math]$1[/math]"
		);	
		$this->content = preg_replace($array_preg, $array_preg_replace, $this->content);
		
		##Remplacement des balises imbriqu�es
		//Citations
		$this->_parse_imbricated('<span class="text_blockquote">', '`<span class="text_blockquote">(.*):</span><div class="blockquote">(.*)</div>`sU', '[quote=$1]$2[/quote]', $this->content);
		
		//Texte cach�
		$this->_parse_imbricated('<span class="text_hide">', '`<span class="text_hide">(.*):</span><div class="hide" onclick="bb_hide\(this\)"><div class="hide2">(.*)</div></div>`sU', '[hide]$2[/hide]', $this->content);
		
		//Indentation
		$this->_parse_imbricated('<div class="indent">', '`<div class="indent">(.+)</div>`sU', '[indent]$1[/indent]', $this->content);
		
		//Bloc
		$this->_parse_imbricated('<div class="bb_block"', '`<div class="bb_block">(.+)</div>`sU', '[block]$1[/block]', $this->content);
		$this->_parse_imbricated('<div class="bb_block" style=', '`<div class="bb_block" style="([^"]+)">(.+)</div>`sU', '[block style="$1"]$2[/block]', $this->content);
		
		//Bloc de formulaire
		$this->content = preg_replace_callback('`<fieldset class="bb_fieldset" style="([^"]*)"><legend>(.*)</legend>(.+)</fieldset>`sU', array(&$this, '_unparse_fieldset'), $this->content);
		
		//Liens Wikip�dia
		$this->content = preg_replace_callback('`<a href="http://([a-z]+).wikipedia.org/wiki/([^"]+)" class="wikipedia_link">(.*)</a>`sU', array(&$this, '_unparse_wikipedia_link'), $this->content);
	}
	
	/**
	 * @desc Unparses the table tag
	 */
	function _unparse_table()
	{
		//On boucle pour parcourir toutes les imbrications
		while (strpos($this->content, '<table class="bb_table"') !== false)
			$this->content = preg_replace('`<table class="bb_table"([^>]*)>(.*)</table>`sU', '[table$1]$2[/table]', $this->content);
		while (strpos($this->content, '<tr class="bb_table_row"') !== false)
			$this->content = preg_replace('`<tr class="bb_table_row">(.*)</tr>`sU', '[row]$1[/row]', $this->content);
		while (strpos($this->content, '<th class="bb_table_head"') !== false)
			$this->content = preg_replace('`<th class="bb_table_head"([^>]*)>(.*)</th>`sU', '[head$1]$2[/head]', $this->content);
		while (strpos($this->content, '<td class="bb_table_col"') !== false)
			$this->content = preg_replace('`<td class="bb_table_col"([^>]*)>(.*)</td>`sU', '[col$1]$2[/col]', $this->content);
	}

	/**
	 * @desc Unparses the list tag
	 */
	function _unparse_list()
	{
		//On boucle tant qu'il y a de l'imbrication
		while (strpos($this->content, '<ul class="bb_ul">') !== false)
			$this->content = preg_replace('`<ul( style="[^"]+")? class="bb_ul">(.+)</ul>`sU', '[list$1]$2[/list]', $this->content);
		while (strpos($this->content, '<ol class="bb_ol">') !== false)
			$this->content = preg_replace('`<ol( style="[^"]+")? class="bb_ol">(.+)</ol>`sU', '[list=ordered$1]$2[/list]', $this->content);
		while (strpos($this->content, '<li class="bb_li">') !== false)
			$this->content = preg_replace('`<li class="bb_li">(.+)</li>`isU', '[*]$1', $this->content);
	}
	
	/**
	 * @desc Callback which allows to unparse the fieldset tag
	 * @param string[] $matches Content matched by a regular expression
	 * @return string The string in which the fieldset tag are parsed
	 */
	function _unparse_fieldset($matches)
	{
		$style = '';
		$legend = '';
		
		if (!empty($matches[1]))
			$style = ' style="' . $matches[1] . '"';
		
		if (!empty($matches[2]))
			$legend = ' legend="' . $matches[2] . '"';
		
		if (!empty($legend) || !empty($style)) 
			return '[fieldset' . $legend . $style . ']' . $matches[3] . '[/fieldset]';
		else
			return '[fieldset]' . $matches[3] . '[/fieldset]'; 
	}
	
	/**
	 * @desc Callback which allows to unparse the Wikipedia tag
	 * @param string[] $matches Content matched by a regular expression
	 * @return string The string in which the wikipedia tag are parsed
	 */
	function _unparse_wikipedia_link($matches)
	{
		global $LANG;
		
		//On est dans la langue par d�faut
		if ($matches[1] == $LANG['wikipedia_subdomain'])
			$lang = '';
		else
			$lang = $matches[1];
			
		//L'intitul� du lien est diff�rent du nom de l'article
		if ($matches[2] != $matches[3])
			$page_name = $matches[2];
		else
			$page_name = '';
		
		return '[wikipedia' . (!empty($page_name) ? ' page="' . $page_name . '"' : '') . (!empty($lang) ? ' lang="' . $lang . '"' : '') . ']' . $matches[3] . '[/wikipedia]';
	}
}

?>