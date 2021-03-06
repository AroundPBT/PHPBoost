<?php
/*##################################################
 *                       ContentSecondParser.class.php
 *                            -------------------
 *   begin                : August 10, 2008
 *   copyright            : (C) 2008 Benoit Sautel
 *   email                : ben.popeye@phpboost.com
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
 * @package {@package}
 * @desc This class ensures the real time processing of the content. The major part of the processing is saved in the database to minimize as much as possible the treatment
 * when the content is displayed. However, some tags cannot be cached, because we cannot have return to the original code. It's for instance the case of the code tag
 * which replaces the code by a lot of html code which formats the code.
 * This kind of tag is treated in real time by this class.
 * The content you put in that parser must come from a ContentFormattingParser class (BBCodeParser or TinyMCEParser) (it can have been saved in a database between the first parsing and the real time parsing).
 * @author Beno�t Sautel <ben.popeye@phpboost.com>
 */
class ContentSecondParser extends AbstractParser
{
	/**
	 * Maximal number of characters that can be inserted in the [code] tag. After that, GeSHi has many difficulties to highligth and has the PHP execution stop (error 500).
	 */
	const MAX_CODE_LENGTH = 40000;
	/**
	 * @desc Builds a ContentSecondParser object
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @desc Parses the content of the parser. The result will be ready to be displayed.
	 */
	public function parse()
	{
		//Balise code
		if (strpos($this->content, '[[CODE') !== false)
		{
			$this->content = preg_replace_callback('`\[\[CODE(?:=([A-Za-z0-9#+-]+))?(?:,(0|1)(?:,(0|1))?)?\]\](.+)\[\[/CODE\]\]`sU', array($this, 'callbackhighlight_code'), $this->content);
		}

		//Media
		if (strpos($this->content, '[[MEDIA]]') !== false)
		{
			$this->process_media_insertion();
		}

		//Balise latex.
		if (strpos($this->content, '[[MATH]]') !== false)
		{
			$server_config = new ServerConfiguration();
			if ($server_config->has_gd_library())
			{
				require_once PATH_TO_ROOT . '/kernel/lib/php/mathpublisher/mathpublisher.php';
				$this->content = preg_replace_callback('`\[\[MATH\]\](.+)\[\[/MATH\]\]`sU', array($this, 'math_code'), $this->content);
			}
		}
		
		$this->parse_feed_tag();
		
		$this->content = Url::html_convert_root_relative2absolute($this->content, $this->path_to_root, $this->page_path);
	}

	/**
	 * @desc Transforms a PHPBoost HTML content to make it exportable and usable every where in the web.
	 * @param string $html Content to transform
	 * @return string The exportable content
	 */
	public static function export_html_text($html_content)
	{
		//Balise vid�o
		$html_content = preg_replace('`<a href="([^"]+)" style="display:block;margin:auto;width:([0-9]+)px;height:([0-9]+)px;" id="movie_[0-9]+"></a><br /><script><!--\s*insertMoviePlayer\(\'movie_[0-9]+\'\);\s*--></script>`isU',
			'<object type="application/x-shockwave-flash" width="$2" height="$3">
				<param name="FlashVars" value="flv=$1&width=$2&height=$3" />
				<param name="allowScriptAccess" value="never" />
				<param name="play" value="true" />
				<param name="movie" value="$1" />
				<param name="menu" value="false" />
				<param name="quality" value="high" />
				<param name="scalemode" value="noborder" />
				<param name="wmode" value="transparent" />
				<param name="bgcolor" value="#FFFFFF" />
			</object>',
		$html_content);

		return Url::html_convert_root_relative2absolute($html_content);
	}

	/**
	 * @desc Highlights a content in a supported language using the appropriate syntax highlighter.
	 * The highlighted languages are numerous: actionscript, asm, asp, bash, c, cpp, csharp, css, d, delphi, fortran, html,
	 * java, javascript, latex, lua, matlab, mysql, pascal, perl, php, python, rails, ruby, sql, text, vb, xml,
	 * PHPBoost templates and PHPBoost BBCode.
	 * @param string $contents Content to highlight
	 * @param string $language Language name
	 * @param bool $line_number Indicate whether or not the line number must be added to the code.
	 * @param bool $inline_code Indicate if the code is multi line.
	 */
	private static function highlight_code($contents, $language, $line_number, $inline_code)
	{
		$contents = TextHelper::htmlspecialchars_decode($contents);
		
		//BBCode PHPBoost
		if (strtolower($language) == 'bbcode')
		{
			$bbcode_highlighter = new BBCodeHighlighter();
			$bbcode_highlighter->set_content($contents);
			$bbcode_highlighter->parse($inline_code);
			$contents = $bbcode_highlighter->get_content();
		}
		//Templates PHPBoost
		elseif (strtolower($language) == 'tpl' || strtolower($language) == 'template')
		{
			require_once(PATH_TO_ROOT . '/kernel/lib/php/geshi/geshi.php');

			$template_highlighter = new TemplateHighlighter();
			$template_highlighter->set_content($contents);
			$template_highlighter->parse($line_number ? GESHI_NORMAL_LINE_NUMBERS : GESHI_NO_LINE_NUMBERS, $inline_code);
			$contents = $template_highlighter->get_content();
		}
		elseif ( strtolower($language) == 'plain')
		{
			$plain_code_highlighter = new PlainCodeHighlighter();
			$plain_code_highlighter->set_content($contents);
			$plain_code_highlighter->parse();
			$contents = $plain_code_highlighter->get_content();
		}
		elseif ($language != '')
		{
			require_once(PATH_TO_ROOT . '/kernel/lib/php/geshi/geshi.php');
			$Geshi = new GeSHi($contents, $language);

			if ($line_number) //Affichage des num�ros de lignes.
			{
				$Geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
			}

			//No container if we are in an inline tag
			if ($inline_code)
			{
				$Geshi->set_header_type(GESHI_HEADER_NONE);
			}

			$contents = '<pre style="display:inline;">' . $Geshi->parse_code() . '</pre>';
		}
		else
		{
			$highlight = highlight_string($contents, true);
			$font_replace = str_replace(array('<font ', '</font>'), array('<span ', '</span>'), $highlight);
			$contents = preg_replace('`color="(.*?)"`', 'style="color:$1"', $font_replace);
		}

		return $contents;
	}

	/**
	 * @static
	 * @desc Handler which highlights a string matched by the preg_replace_callback function.
	 * @param string[] $matches The matched contents: 0 => the whole string, 1 => the language, 2 => number count?,
	 * 3 => multi line?, 4 => the code to highlight.
	 * @return string the colored content
	 */
	private function callbackhighlight_code($matches)
	{
		$line_number = !empty($matches[2]);
		$inline_code = !empty($matches[3]);
		
		$content_to_highlight = $matches[4];
		
		if (strlen($content_to_highlight) > self::MAX_CODE_LENGTH)
		{
			return '<div class="error">' . LangLoader::get_message('code_too_long_error', 'editor-common') . '</div>';
		
		}

		$contents = $this->highlight_code($content_to_highlight, $matches[1], $line_number, $inline_code);

		if (!$inline_code && !empty($matches[1]))
		{
			$contents = '<span class="formatter-code">' . sprintf(LangLoader::get_message('code_langage', 'main'), strtoupper($matches[1])) . '</span><div class="code">' . $contents .'</div>';
		}
		else if (!$inline_code && empty($matches[1]))
		{
			$contents = '<span class="formatter-code">' . LangLoader::get_message('code_tag', 'main') . '</span><div class="code">' . $contents . '</div>';
		}
			
		return $contents;
	}

	/**
	 * @static
	 * @desc Parses the latex code and replaces it by an image containing the mathematic formula.
	 * @param string[] $matches 0 => the whole tag, 1 => the latex code to parse.
	 * @return string The code of the image containing the formula.
	 */
	private function math_code($matches)
	{
		$matches[1] = str_replace('<br />', '', $matches[1]);
		$code = mathimage($matches[1], 12, '/images/maths/');
		return $code;
	}

	/**
	 * Processes the media insertion it replaces the [[MEDIA]]tag[[/MEDIA]] by the Javascript API correspondig calls.
	 */
	private function process_media_insertion()
	{
		//Swf
		$this->content = preg_replace_callback('`\[\[MEDIA\]\]insertSwfPlayer\(\'([^\']+)\', ([0-9]+), ([0-9]+)\);\[\[/MEDIA\]\]`isU', array('ContentSecondParser', 'process_swf_tag'), $this->content);
		//Movie
		$this->content = preg_replace_callback('`\[\[MEDIA\]\]insertMoviePlayer\(\'([^\']+)\', ([0-9]+), ([0-9]+)\);\[\[/MEDIA\]\]`isU', array('ContentSecondParser', 'process_movie_tag'), $this->content);
		//Sound
		$this->content = preg_replace_callback('`\[\[MEDIA\]\]insertSoundPlayer\(\'([^\']+)\'\);\[\[/MEDIA\]\]`isU', array('ContentSecondParser', 'process_sound_tag'), $this->content);
		//Youtube
		$this->content = preg_replace_callback('`\[\[MEDIA\]\]insertYoutubePlayer\(\'([^\']+)\', ([0-9]+), ([0-9]+)\);\[\[/MEDIA\]\]`isU', array('ContentSecondParser', 'process_youtube_tag'), $this->content);
	}

	/**
	 * Inserts the javascript calls for the swf tag.
	 * @param $matches The matched elements
	 * @return The movie insertion code containing javascrpt calls
	 */
	private static function process_swf_tag($matches)
	{
		return "<object type=\"application/x-shockwave-flash\" data=\"" . $matches[1] . "\" width=\"" . $matches[2] . "\" height=\"" . $matches[3] . "\">" .
			"<param name=\"allowScriptAccess\" value=\"never\" />" .
			"<param name=\"play\" value=\"true\" />" .
			"<param name=\"movie\" value=\"" . $matches[1] . "\" />" .
			"<param name=\"menu\" value=\"false\" />" .
			"<param name=\"quality\" value=\"high\" />" .
			"<param name=\"scalemode\" value=\"noborder\" />" .
			"<param name=\"wmode\" value=\"transparent\" />" .
			"<param name=\"bgcolor\" value=\"#000000\" />" .
			"</object>";
	}

	/**
	 * Inserts the javascript calls for the movie tag.
	 * @param $matches The matched elements
	 * @return The movie insertion code containing javascrpt calls
	 */
	private static function process_movie_tag($matches)
	{
		$id = 'movie_' . AppContext::get_uid();
		return '<a class="video-player" href="' . $matches[1] . '" style="display:block;margin:auto;width:' . $matches[2] . 'px;height:' . $matches[3] . 'px;" id="' . $id .  '"></a><br />' .
			'<script><!--' . "\n" .
			'insertMoviePlayer(\'' . $id . '\');' .
			"\n" . '--></script>';
	}

	/**
	 * Inserts the javascript calls for the sound tag.
	 * @param $matches The matched elements
	 * @return The movie insertion code containing javascrpt calls
	 */
	private static function process_sound_tag($matches)
	{
		//Balise son
		return '<audio controls><source src="'. $matches[1] .'" /></audio>';
	}
	
	private static function process_youtube_tag($matches)
	{
		$matches[1] = str_replace(array('/watch?v=', '/embed/'), '/v/', $matches[1]);
		return self::process_swf_tag($matches);
	}
	
	private function parse_feed_tag()
	{
		$this->content = preg_replace_callback('`\[\[FEED((?: [a-z]+="[^"]+")*)\]\]([a-z]+)\[\[/FEED\]\]`U', array(__CLASS__, 'inject_feed'), $this->content);
	}
	
	private static function inject_feed(array $matches)
	{
		$module = $matches[2];
		$args = self::parse_feed_tag_args($matches[1]);
		$name = !empty($args['name']) ? $args['name'] : Feed::DEFAULT_FEED_NAME;
		$cat = !empty($args['cat']) ? $args['cat'] : 0;
		$tpl = false;
		$number = !empty($args['number']) ? $args['number'] : 10;
		
		$result = '';
		
		try
		{
			$result = Feed::get_parsed($module, $name, $cat, $tpl, $number);
		}
		catch (Exception $e)
		{
		}
		
		if (!empty($result))
		{
			return $result;
		}
		else
		{
			$error = StringVars::replace_vars(LangLoader::get_message('feed_tag_error', 'editor-common'), array('module' => $module));
			return '<div class="error">' . $error . '</div>';
		}
	}
	
	private static function parse_feed_tag_args($matches)
	{
		$args = explode(' ', trim($matches));
		$result = array();
		
		foreach ($args as $arg)
		{
			$param = array();
			
			if (!preg_match('`([a-z]+)="([^"]+)"`U', $arg, $param))
			{
				break;
			}
			
			$name = $param[1];
			$value = $param[2];
			
			if (in_array($name, array('name', 'cat', 'number')))
			{
				$result[$name] = $value;
			}
		}
		
		return $result;
	}
}
?>