<?php
/*##################################################
 *                         GalleryDisplayCategoryController.class.php
 *                            -------------------
 *   begin                : February 4, 2015
 *   copyright            : (C) 2015 Julien BRISWALTER
 *   email                : julienseth78@phpboost.com
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

class GalleryDisplayCategoryController extends ModuleController
{
	private $lang;
	private $tpl;
	
	private $category;
	private $db_querier;

	public function __construct()
	{
		$this->db_querier = PersistenceContext::get_querier();
	}
	
	public function execute(HTTPRequestCustom $request)
	{
		$this->check_authorizations();
		
		$this->init();
		
		$this->build_view();
		
		return $this->generate_response();
	}
	
	private function build_view()
	{
		global $LANG, $Bread_crumb;
		
		load_module_lang('gallery');
		$g_idpics = retrieve(GET, 'id', 0);
		$g_views = retrieve(GET, 'views', false);
		$g_notes = retrieve(GET, 'notes', false);
		$g_sort = retrieve(GET, 'sort', '');
		$g_sort = !empty($g_sort) ? 'sort=' . $g_sort : '';
		
		//R�cup�ration du mode d'ordonnement.
		if (preg_match('`([a-z]+)_([a-z]+)`', $g_sort, $array_match))
		{
			$g_type = $array_match[1];
			$g_mode = $array_match[2];
		}
		else
			list($g_type, $g_mode) = array('date', 'desc');
		
		$comments_topic = new GalleryCommentsTopic();
		$config = GalleryConfig::load();
		$category = $this->get_category();
		
		$categories = GalleryService::get_categories_manager()->get_categories_cache()->get_childrens($category->get_id());
		$authorized_categories = GalleryService::get_authorized_categories($category->get_id());
		$Gallery = new Gallery();
		
		$nbr_pics = $this->db_querier->count(GallerySetup::$gallery_table, 'WHERE idcat=:idcat AND aprob = 1', array('idcat' => $category->get_id()));
		$total_cat = $category->get_id() == Category::ROOT_CATEGORY ? count($categories) - 1 : count($categories);
		
		//On cr�e une pagination si le nombre de cat�gories est trop important.
		$page = AppContext::get_request()->get_getint('p', 1);
		$pagination = new ModulePagination($page, $total_cat, $config->get_pics_number_per_page());
		$pagination->set_url(new Url('/gallery/gallery.php?p=%d&amp;cat=' . $category->get_id() . '&amp;id=' . $g_idpics . '&amp;' . $g_sort));
		
		if ($pagination->current_page_is_empty() && $page > 1)
		{
			$error_controller = PHPBoostErrors::unexisting_page();
			DispatchManager::redirect($error_controller);
		}
		
		//Colonnes des cat�gories.
		$nbr_column_cats = ($total_cat > $config->get_columns_number()) ? $config->get_columns_number() : $total_cat;
		$nbr_column_cats = !empty($nbr_column_cats) ? $nbr_column_cats : 1;
		$column_width_cats = floor(100/$nbr_column_cats);
	
		//Colonnes des images.
		$nbr_column_pics = ($nbr_pics > $config->get_columns_number()) ? $config->get_columns_number() : $nbr_pics;
		$nbr_column_pics = !empty($nbr_column_pics) ? $nbr_column_pics : 1;
		$column_width_pics = floor(100/$nbr_column_pics);
	
		$is_admin = AppContext::get_current_user()->check_level(User::ADMIN_LEVEL);
		$is_modo = GalleryAuthorizationsService::check_authorizations($category->get_id())->moderation();
		
		$module_data_path = $this->tpl->get_pictures_data_path();
		$rewrite_title = Url::encode_rewrite($category->get_name());
		
		$this->tpl->put_all(array(
			'C_PAGINATION' => $pagination->has_several_pages(),
			'ARRAY_JS' => '',
			'NBR_PICS' => 0,
			'MAX_START' => 0,
			'START_THUMB' => 0,
			'END_THUMB' => 0,
			'PAGINATION' => $pagination->display(),
			'COLUMNS_NUMBER' => $nbr_column_pics,
			'COLUMN_WIDTH_CATS' => $column_width_cats,
			'COLUMN_WIDTH_PICS' => $column_width_pics,
			'CAT_ID' => $category->get_id(),
			'DISPLAY_MODE' => $config->get_pics_enlargement_mode(),
			'GALLERY' => $category->get_id() != Category::ROOT_CATEGORY ? $this->lang['module_title'] . ' - ' . $category->get_name() : $this->lang['module_title'],
			'HEIGHT_MAX' => $config->get_mini_max_height(),
			'WIDTH_MAX' => $column_width_pics,
			'MODULE_DATA_PATH' => $module_data_path,
			'L_APROB' => $LANG['aprob'],
			'L_UNAPROB' => $LANG['unaprob'],
			'L_FILE_FORBIDDEN_CHARS' => $LANG['file_forbidden_chars'],
			'L_TOTAL_IMG' => $category->get_id() != Category::ROOT_CATEGORY ? sprintf($LANG['total_img_cat'], $nbr_pics) : '',
			'L_ADD_IMG' => $LANG['add_pic'],
			'L_GALLERY' => $this->lang['module_title'],
			'L_CATEGORIES' => ($category->get_id_parent() >= 0) ? $LANG['sub_album'] : $LANG['album'],
			'L_NAME' => $LANG['name'],
			'L_EDIT' => LangLoader::get_message('edit', 'common'),
			'L_MOVETO' => $LANG['moveto'],
			'L_DELETE' => LangLoader::get_message('delete', 'common'),
			'L_SUBMIT' => $LANG['submit'],
			'L_ALREADY_VOTED' => $LANG['already_vote'],
			'L_ORDER_BY' => LangLoader::get_message('sort_by', 'common') . (isset($LANG[$g_type]) ? ' ' . strtolower($LANG[$g_type]) : ''),
			'L_DIRECTION' => $LANG['direction'],
			'L_DISPLAY' => LangLoader::get_message('display', 'common'),
			'U_INDEX' => url('.php'),
			'U_BEST_VIEWS' => PATH_TO_ROOT . '/gallery/gallery' . url('.php?views=1&amp;cat=' . $category->get_id(), '-' . $category->get_id() . '.php?views=1'),
			'U_BEST_NOTES' => PATH_TO_ROOT . '/gallery/gallery' . url('.php?notes=1&amp;cat=' . $category->get_id(), '-' . $category->get_id() . '.php?notes=1'),
			'U_ASC' => PATH_TO_ROOT . '/gallery/gallery' . url('.php?cat=' . $category->get_id() . '&amp;sort=' . $g_type . '_' . 'asc', '-' . $category->get_id() . '.php?sort=' . $g_type . '_' . 'asc'),
			'U_DESC' => PATH_TO_ROOT . '/gallery/gallery' . url('.php?cat=' . $category->get_id() . '&amp;sort=' . $g_type . '_' . 'desc', '-' . $category->get_id() . '.php?sort=' . $g_type . '_' . 'desc'),
			'U_ORDER_BY_NAME' => PATH_TO_ROOT . '/gallery/gallery' . url('.php?sort=name_desc&amp;cat=' . $category->get_id(), '-' . $category->get_id() . '+' . $rewrite_title . '.php?sort=name_desc'),
			'U_ORDER_BY_DATE' => PATH_TO_ROOT . '/gallery/gallery' . url('.php?sort=date_desc&amp;cat=' . $category->get_id(), '-' . $category->get_id() . '+' . $rewrite_title . '.php?sort=date_desc'),
			'U_ORDER_BY_VIEWS' => PATH_TO_ROOT . '/gallery/gallery' . url('.php?sort=views_desc&amp;cat=' . $category->get_id(), '-' . $category->get_id() . '+' . $rewrite_title . '.php?sort=views_desc'),
			'U_ORDER_BY_NOTES' => PATH_TO_ROOT . '/gallery/gallery' . url('.php?sort=notes_desc&amp;cat=' . $category->get_id(), '-' . $category->get_id() . '+' . $rewrite_title . '.php?sort=notes_desc'),
			'U_ORDER_BY_COM' => PATH_TO_ROOT . '/gallery/gallery' . url('.php?sort=com_desc&amp;cat=' . $category->get_id(), '-' . $category->get_id() . '+' . $rewrite_title . '.php?sort=com_desc'),
			'L_BEST_VIEWS' => $LANG['best_views'],
			'L_BEST_NOTES' => $LANG['best_notes'],
			'L_ASC' => $LANG['asc'],
			'L_DESC' => $LANG['desc'],
			'L_DATE' => LangLoader::get_message('date', 'date-common'),
			'L_VIEWS' => $LANG['views'],
			'L_NOTES' => LangLoader::get_message('notes', 'common'),
			'L_COM' => $LANG['com_s']
		));
		
		##### Cat�gorie disponibles #####
		if ($total_cat > 0 && empty($g_idpics))
		{
			$this->tpl->put('C_GALLERY_CATS', true);
			
			$j = 0;
			$result = $this->db_querier->select("SELECT @id_cat:= gallery_cats.id, gallery_cats.*, gallery.path,
			(SELECT COUNT(*) FROM " . GallerySetup::$gallery_table . "
			WHERE idcat = @id_cat AND aprob = 1
			) AS nbr_pics,
			(SELECT COUNT(*) FROM " . GallerySetup::$gallery_table . "
			WHERE idcat = @id_cat AND aprob = 0
			) AS nbr_pics_unaprob
			FROM " . GallerySetup::$gallery_cats_table . " gallery_cats
			LEFT JOIN " . GallerySetup::$gallery_table . " gallery ON gallery.idcat = gallery_cats.id
			WHERE gallery_cats.id_parent = :id_category
			AND gallery_cats.id IN :authorized_categories
			ORDER BY gallery_cats.id_parent, gallery_cats.c_order
			LIMIT :number_items_per_page OFFSET :display_from", array(
				'id_category' => $category->get_id(),
				'authorized_categories' => $authorized_categories,
				'number_items_per_page' => $pagination->get_number_items_per_page(),
				'display_from' => $pagination->get_display_from()
			));
			while ($row = $result->fetch())
			{
				//Si la miniature n'existe pas (cache vid�) on reg�n�re la miniature � partir de l'image en taille r�elle.
				if (!file_exists('pics/thumbnails/' . $row['path']))
					$Gallery->Resize_pics('pics/' . $row['path']); //Redimensionnement + cr�ation miniature
	
				$this->tpl->assign_block_vars('cat_list', array(
					'IDCAT' => $row['id'],
					'CAT' => $row['name'],
					'DESC' => $row['description'],
					'IMG' => !empty($row['path']) ? '<img src="'. PATH_TO_ROOT.'/gallery/pics/thumbnails/' . $row['path'] . '" alt="" />' : '',
					'EDIT' => $is_admin ? '<a href="' . GalleryUrlBuilder::edit_category($row['id'])->rel() . '" title="' . LangLoader::get_message('edit', 'common') . '" class="fa fa-edit"></a>' : '',
					'OPEN_TR' => is_int($j++/$nbr_column_cats) ? '<tr>' : '',
					'CLOSE_TR' => is_int($j/$nbr_column_cats) ? '</tr>' : '',
					'L_NBR_PICS' => sprintf($LANG['nbr_pics_info'], $row['nbr_pics']),
					'U_CAT' => GalleryUrlBuilder::get_link_cat($row['id'],$row['name'])
				));
			}
			$result->dispose();
	
			//Cr�ation des cellules du tableau si besoin est.
			while (!is_int($j/$nbr_column_cats))
			{
				$this->tpl->assign_block_vars('end_table_cats', array(
					'TD_END' => '<td style="margin:15px 0px;width:' . $nbr_column_cats . '%">&nbsp;</td>',
					'TR_END' => (is_int(++$j/$nbr_column_cats)) ? '</tr>' : ''
				));
			}
		}
	
		##### Affichage des photos #####
		if ($nbr_pics > 0)
		{
			switch ($g_type)
			{
				case 'name' :
				$sort_type = 'g.name';
				break;
				case 'date' :
				$sort_type = 'g.timestamp';
				break;
				case 'views' :
				$sort_type = 'g.views';
				break;
				case 'notes' :
				$sort_type = 'notes.average_notes';
				break;
				case 'com' :
				$sort_type = 'com.number_comments';
				break;
				default :
				$sort_type = 'g.timestamp';
			}
			switch ($g_mode)
			{
				case 'desc' :
				$sort_mode = 'DESC';
				break;
				case 'asc' :
				$sort_mode = 'ASC';
				break;
				default:
				$sort_mode = 'DESC';
			}
			$g_sql_sort = ' ORDER BY ' . $sort_type . ' ' . $sort_mode;
			if ($g_views)
				$g_sql_sort = ' ORDER BY g.views DESC';
			elseif ($g_notes)
				$g_sql_sort = ' ORDER BY notes.average_notes DESC';
	
			$this->tpl->put('C_GALLERY_PICS', true);
			
			//Affichage d'une photo demand�e.
			if (!empty($g_idpics))
			{
				$info_pics = $this->db_querier->select_single_row_query("SELECT g.*, m.display_name, m.groups, m.level, notes.average_notes, notes.number_notes, note.note
					FROM " . GallerySetup::$gallery_table . " g
					LEFT JOIN " . DB_TABLE_MEMBER . " m ON m.user_id = g.user_id
					LEFT JOIN " . DB_TABLE_COMMENTS_TOPIC . " com ON com.id_in_module = g.id AND com.module_id = 'gallery'
					LEFT JOIN " . DB_TABLE_AVERAGE_NOTES . " notes ON notes.id_in_module = g.id AND notes.module_name = 'gallery'
					LEFT JOIN " . DB_TABLE_NOTE . " note ON note.id_in_module = g.id AND note.module_name = 'gallery' AND note.user_id = :user_id
					WHERE g.idcat = :idcat AND g.id = :id AND g.aprob = 1
					" . $g_sql_sort, array(
						'user_id' => AppContext::get_current_user()->get_id(),
						'idcat' => $category->get_id(),
						'id' => $g_idpics
					));
				if (!empty($info_pics['id']))
				{
					$Bread_crumb->add(stripslashes($info_pics['name']), PATH_TO_ROOT . '/gallery/gallery' . url('.php?cat=' . $info_pics['idcat'] . '&amp;id=' . $info_pics['id'], '-' . $info_pics['idcat'] . '-' . $info_pics['id'] . '.php'));
					
					//Affichage miniatures.
					$id_previous = 0;
					$id_next = 0;
					$nbr_pics_display_before = floor(($nbr_column_pics - 1)/2); //Nombres de photos de chaque c�t� de la miniature de la photo affich�e.
					$nbr_pics_display_after = ($nbr_column_pics - 1) - floor($nbr_pics_display_before);
					list($i, $reach_pics_pos, $pos_pics, $thumbnails_before, $thumbnails_after, $start_thumbnails, $end_thumbnails) = array(0, false, 0, 0, 0, $nbr_pics_display_before, $nbr_pics_display_after);
					$array_pics = array();
					$array_js = 'var array_pics = new Array();';
					$result = $this->db_querier->select("SELECT g.id, g.idcat, g.path
					FROM " . GallerySetup::$gallery_table . " g
					WHERE g.idcat = :idcat AND g.aprob = 1
					" . $g_sql_sort, array(
						'idcat' => $category->get_id()
					));
					while ($row = $result->fetch())
					{
						//Si la miniature n'existe pas (cache vid�) on reg�n�re la miniature � partir de l'image en taille r�elle.
						if (!file_exists(PATH_TO_ROOT . '/gallery/pics/thumbnails/' . $row['path']))
							$Gallery->Resize_pics(PATH_TO_ROOT . '/gallery/pics/' . $row['path']); //Redimensionnement + cr�ation miniature
	
						//Affichage de la liste des miniatures sous l'image.
						$array_pics[] = '<td style="text-align:center;height:' . ($config->get_mini_max_height() + 16) . 'px"><span id="thumb' . $i . '"><a href="gallery' . url('.php?cat=' . $row['idcat'] . '&amp;id=' . $row['id'] . '&amp;sort=' . $g_sort, '-' . $row['idcat'] . '-' . $row['id'] . '.php?sort=' . $g_sort) . '#pics_max' . '"><img src="pics/thumbnails/' . $row['path'] . '" alt="" / ></a></span></td>';
	
						if ($row['id'] == $g_idpics)
						{
							$reach_pics_pos = true;
							$pos_pics = $i;
						}
						else
						{
							if (!$reach_pics_pos)
							{
								$thumbnails_before++;
								$id_previous = $row['id'];
							}
							else
							{
								$thumbnails_after++;
								if (empty($id_next))
									$id_next = $row['id'];
							}
						}
						$array_js .= 'array_pics[' . $i . '] = new Array();' . "\n";
						$array_js .= 'array_pics[' . $i . '][\'link\'] = \'' . GalleryUrlBuilder::get_link_item($row['idcat'],$row['id']) . '#pics_max' . "';\n";
						$array_js .= 'array_pics[' . $i . '][\'path\'] = \'' . $row['path'] . "';\n";
						$i++;
					}
					$result->dispose();
					
					$activ_note = ($config->is_notation_enabled() && AppContext::get_current_user()->check_level(User::MEMBER_LEVEL) );
					if ($activ_note)
					{
						//Affichage notation.
						$notation = new Notation();
						$notation->set_module_name('gallery');
						$notation->set_id_in_module($info_pics['id']);
						$notation->set_notation_scale($config->get_notation_scale());
						$notation->set_number_notes($info_pics['number_notes']);
						$notation->set_average_notes($info_pics['average_notes']);
						$notation->set_user_already_noted(!empty($info_pics['note']));
					}

					if ($thumbnails_before < $nbr_pics_display_before)
						$end_thumbnails += $nbr_pics_display_before - $thumbnails_before;
					if ($thumbnails_after < $nbr_pics_display_after)
						$start_thumbnails += $nbr_pics_display_after - $thumbnails_after;
	
					$html_protected_name = $info_pics['name'];
	
					$comments_topic->set_id_in_module($info_pics['id']);
					$comments_topic->set_url(new Url('/gallery/gallery.php?cat='. $category->get_id() .'&id=' . $g_idpics . '&com=0'));
					
					//Liste des cat�gories.
					$search_category_children_options = new SearchCategoryChildrensOptions();
					$search_category_children_options->add_authorizations_bits(Category::READ_AUTHORIZATIONS);
					$search_category_children_options->add_authorizations_bits(Category::WRITE_AUTHORIZATIONS);
					$categories_tree = GalleryService::get_categories_manager()->get_select_categories_form_field($info_pics['id'] . 'cat', '', $info_pics['idcat'], $search_category_children_options);
					$method = new ReflectionMethod('AbstractFormFieldChoice', 'get_options');
					$method->setAccessible(true);
					$categories_tree_options = $method->invoke($categories_tree);
					$cat_list = '';
					foreach ($categories_tree_options as $option)
					{
						$cat_list .= $option->display()->render();
					}
					
					$group_color = User::get_group_color($info_pics['groups'], $info_pics['level']);
					
					//Affichage de l'image et de ses informations.
					$this->tpl->put_all(array(
						'C_GALLERY_PICS_MAX' => true,
						'C_GALLERY_PICS_MODO' => $is_modo,
						'C_AUTHOR_DISPLAYED' => $config->is_author_displayed(),
						'C_VIEWS_COUNTER_ENABLED' => $config->is_views_counter_enabled(),
						'C_TITLE_ENABLED' => $config->is_title_enabled(),
						'C_COMMENTS_ENABLED' => $config->are_comments_enabled(),
						'C_NOTATION_ENABLED' => $config->is_notation_enabled(),
						'ID' => $info_pics['id'],
						'IMG_MAX' => '<img src="' . PATH_TO_ROOT . '/gallery/show_pics' . url('.php?id=' . $g_idpics . '&amp;cat=' . $category->get_id()) . '" alt="" />',
						'NAME' => '<span id="fi_' . $info_pics['id'] . '">' . stripslashes($info_pics['name']) . '</span> <span id="fi' . $info_pics['id'] . '"></span>',
						'POSTOR' => '<a class="small ' . UserService::get_level_class($info_pics['level']) . '"' . (!empty($group_color) ? ' style="color:' . $group_color . '"' : '') . ' href="'. UserUrlBuilder::profile($info_pics['user_id'])->rel() .'">' . $info_pics['display_name'] . '</a>',
						'DATE' => Date::to_format($info_pics['timestamp'], Date::FORMAT_DAY_MONTH_YEAR),
						'VIEWS' => ($info_pics['views'] + 1),
						'DIMENSION' => $info_pics['width'] . ' x ' . $info_pics['height'],
						'SIZE' => NumberHelper::round($info_pics['weight']/1024, 1),
						'L_COMMENTS' => CommentsService::get_number_and_lang_comments('gallery', $info_pics['id']),
						'KERNEL_NOTATION' => $activ_note ? NotationService::display_active_image($notation) : '',
						'COLSPAN' => ($config->get_columns_number() + 2),
						'CAT' => $cat_list,
						'RENAME' => $html_protected_name,
						'RENAME_CUT' => $html_protected_name,
						'IMG_APROB' => ($info_pics['aprob'] == 1) ? 'fa fa-eye-slash' : 'fa fa-eye',
						'ARRAY_JS' => $array_js,
						'NBR_PICS' => ($i - 1),
						'MAX_START' => ($i - 1) - $nbr_column_pics,
						'START_THUMB' => (($pos_pics - $start_thumbnails) > 0) ? ($pos_pics - $start_thumbnails) : 0,
						'END_THUMB' => ($pos_pics + $end_thumbnails),
						'L_KB' => LangLoader::get_message('unit.kilobytes', 'common'),
						'L_INFORMATIONS' => $LANG['informations'],
						'L_NAME' => $LANG['name'],
						'L_POSTOR' => $LANG['postor'],
						'L_VIEWS' => $LANG['views'],
						'L_ADD_ON' => $LANG['add_on'],
						'L_DIMENSION' => $LANG['dimension'],
						'L_SIZE' => $LANG['size'],
						'L_NOTE' => LangLoader::get_message('note', 'common'),
						'L_COM' => $LANG['com'],
						'L_EDIT' => LangLoader::get_message('edit', 'common'),
						'L_APROB_IMG' => ($info_pics['aprob'] == 1) ? $LANG['unaprob'] : $LANG['aprob'],
						'L_THUMBNAILS' => $LANG['thumbnails'],
						'U_DEL' => url('gallery.php?del=' . $info_pics['id'] . '&amp;token=' . AppContext::get_session()->get_token() . '&amp;cat=' . $category->get_id()),
						'U_MOVE' => url('gallery.php?id=' . $info_pics['id'] . '&amp;token=' . AppContext::get_session()->get_token() . '&amp;move=\' + this.options[this.selectedIndex].value'),
						'U_PREVIOUS' => ($pos_pics > 0) ? '<a href="' . GalleryUrlBuilder::get_link_item($category->get_id(),$id_previous) . '#pics_max"><i class="fa fa-arrow-left fa-2x"></i></a> <a href="' . GalleryUrlBuilder::get_link_item($category->get_id(),$id_previous) . '#pics_max">' . $LANG['previous'] . '</a>' : '',
						'U_NEXT' => ($pos_pics < ($i - 1)) ? '<a href="' . GalleryUrlBuilder::get_link_item($category->get_id(),$id_next) . '#pics_max">' . $LANG['next'] . '</a> <a href="' . GalleryUrlBuilder::get_link_item($category->get_id(),$id_next) . '#pics_max"><i class="fa fa-arrow-right fa-2x"></i></a>' : '',
						'U_LEFT_THUMBNAILS' => (($pos_pics - $start_thumbnails) > 0) ? '<span id="display_left"><a href="javascript:display_thumbnails(\'left\')"><i class="fa fa-arrow-left fa-2x"></i></a></span>' : '<span id="display_left"></span>',
						'U_RIGHT_THUMBNAILS' => (($pos_pics - $start_thumbnails) <= ($i - 1) - $nbr_column_pics) ? '<span id="display_right"><a href="javascript:display_thumbnails(\'right\')"><i class="fa fa-arrow-right fa-2x"></i></a></span>' : '<span id="display_right"></span>',
						'U_COMMENTS' => GalleryUrlBuilder::get_link_item($info_pics['idcat'],$info_pics['id'],0,$g_sort) .'#comments_list'
					));
	
					//Affichage de la liste des miniatures sous l'image.
					$i = 0;
					foreach ($array_pics as $pics)
					{
						if ($i >= ($pos_pics - $start_thumbnails) && $i <= ($pos_pics + $end_thumbnails))
						{
							$this->tpl->assign_block_vars('list_preview_pics', array(
								'PICS' => $pics
							));
						}
						$i++;
					}
	
					//Commentaires
					if (isset($_GET['com']))
					{
						if ($config->are_comments_enabled())
						{
							$this->tpl->put_all(array(
								'COMMENTS' => CommentsService::display($comments_topic)->render()
							));
						}
						else
						{
							$error_controller = PHPBoostErrors::user_not_authorized();
							DispatchManager::redirect($error_controller);
						}
					}
				}
			}
			else
			{
				$sort = retrieve(GET, 'sort', '');
				
				//On cr�e une pagination si le nombre de photos est trop important.
				$page = AppContext::get_request()->get_getint('pp', 1);
				$pagination = new ModulePagination($page, $nbr_pics, $config->get_pics_number_per_page());
				$pagination->set_url(new Url('/gallery/gallery.php?pp=%d' . (!empty($sort) ? '&amp;sort=' . $sort : '') . '&amp;cat=' . $category->get_id()));
				
				if ($pagination->current_page_is_empty() && $page > 1)
				{
					$error_controller = PHPBoostErrors::unexisting_page();
					DispatchManager::redirect($error_controller);
				}
				
				$this->tpl->put_all(array(
					'C_GALLERY_MODO' => $is_modo,
					'C_PICTURE_NAME_DISPLAYED' => $config->is_title_enabled(),
					'C_AUTHOR_DISPLAYED' => $config->is_author_displayed(),
					'C_VIEWS_COUNTER_ENABLED' => $config->is_views_counter_enabled(),
					'C_COMMENTS_ENABLED' => $config->are_comments_enabled(),
					'C_PAGINATION' => $pagination->has_several_pages(),
					'PAGINATION' => $pagination->display(),
					'L_EDIT' => LangLoader::get_message('edit', 'common'),
					'L_VIEW' => $LANG['view'],
					'L_VIEWS' => $LANG['views']
				));
				
				$is_connected = AppContext::get_current_user()->check_level(User::MEMBER_LEVEL);
				$j = 0;
				$result = $this->db_querier->select("SELECT g.id, g.idcat, g.name, g.path, g.timestamp, g.aprob, g.width, g.height, g.user_id, g.views, g.aprob, m.display_name, m.groups, m.level, notes.average_notes, notes.number_notes, note.note
				FROM " . GallerySetup::$gallery_table . " g
				LEFT JOIN " . DB_TABLE_MEMBER . " m ON m.user_id = g.user_id
				LEFT JOIN " . DB_TABLE_COMMENTS_TOPIC . " com ON com.id_in_module = g.id AND com.module_id = 'gallery'
				LEFT JOIN " . DB_TABLE_AVERAGE_NOTES . " notes ON notes.id_in_module = g.id AND notes.module_name = 'gallery'
				LEFT JOIN " . DB_TABLE_NOTE . " note ON note.id_in_module = g.id AND note.module_name = 'gallery' AND note.user_id = :user_id
				WHERE g.idcat = :idcat AND g.aprob = 1
				" . $g_sql_sort . "
				LIMIT :number_items_per_page OFFSET :display_from", array(
					'user_id' => AppContext::get_current_user()->get_id(),
					'idcat' => $category->get_id(),
					'number_items_per_page' => $pagination->get_number_items_per_page(),
					'display_from' => $pagination->get_display_from()
				));
				while ($row = $result->fetch())
				{
					//Si la miniature n'existe pas (cache vid�) on reg�n�re la miniature � partir de l'image en taille r�elle.
					if (!file_exists(PATH_TO_ROOT . '/gallery/pics/thumbnails/' . $row['path']))
						$Gallery->Resize_pics(PATH_TO_ROOT . '/gallery/pics/' . $row['path']); //Redimensionnement + cr�ation miniature
					
					//Affichage de l'image en grand.
					if ($config->get_pics_enlargement_mode() == GalleryConfig::FULL_SCREEN) //Ouverture en popup plein �cran.
					{
						$display_link = PATH_TO_ROOT.'/gallery/show_pics' . url('.php?id=' . $row['id'] . '&amp;cat=' . $row['idcat']) . '" data-lightbox="1" onmousedown="increment_view(' . $row['id'] . ');" title="' . str_replace('"', '', stripslashes($row['name']));
					}
					elseif ($config->get_pics_enlargement_mode() == GalleryConfig::POPUP) //Ouverture en popup simple.
						$display_link = 'javascript:increment_view(' . $row['id'] . ');display_pics_popup(\'' . PATH_TO_ROOT . '/gallery/show_pics' . url('.php?id=' . $row['id'] . '&amp;cat=' . $row['idcat']) . '\', \'' . $row['width'] . '\', \'' . $row['height'] . '\')';
					elseif ($config->get_pics_enlargement_mode() == GalleryConfig::RESIZE) //Ouverture en agrandissement simple.
						$display_link = 'javascript:increment_view(' . $row['id'] . ');display_pics(' . $row['id'] . ', \'' . PATH_TO_ROOT . '/gallery/show_pics' . url('.php?id=' . $row['id'] . '&amp;cat=' . $row['idcat']) . '\')';
					else //Ouverture nouvelle page.
						$display_link = url('gallery.php?cat=' . $row['idcat'] . '&amp;id=' . $row['id'], 'gallery-' . $row['idcat'] . '-' . $row['id'] . '.php') . '#pics_max';
					
					//Liste des cat�gories.
					$search_category_children_options = new SearchCategoryChildrensOptions();
					$search_category_children_options->add_authorizations_bits(Category::READ_AUTHORIZATIONS);
					$search_category_children_options->add_authorizations_bits(Category::WRITE_AUTHORIZATIONS);
					$categories_tree = GalleryService::get_categories_manager()->get_select_categories_form_field($row['id'] . 'cat', '', $row['idcat'], $search_category_children_options);
					$method = new ReflectionMethod('AbstractFormFieldChoice', 'get_options');
					$method->setAccessible(true);
					$categories_tree_options = $method->invoke($categories_tree);
					$cat_list = '';
					foreach ($categories_tree_options as $option)
					{
						$cat_list .= $option->display()->render();
					}
					
					$notation = new Notation();
					$notation->set_module_name('gallery');
					$notation->set_notation_scale($config->get_notation_scale());
					$notation->set_id_in_module($row['id']);
					$notation->set_number_notes( $row['number_notes']);
					$notation->set_average_notes($row['average_notes']);
					$notation->set_user_already_noted(!empty($row['note']));
					
					$group_color = User::get_group_color($row['groups'], $row['level']);
					
					$comments_topic->set_id_in_module($row['id']);
					
					$html_protected_name = $row['name'];
					$this->tpl->assign_block_vars('pics_list', array(
						'C_IMG_APROB' => $row['aprob'] == 1,
						'C_OPEN_TR' => is_int($j++/$nbr_column_pics),
						'C_CLOSE_TR' => is_int($j/$nbr_column_pics),
						'ID' => $row['id'],
						'APROB' => $row['aprob'],
						'PATH' => $row['path'],
						'NAME' => stripslashes($row['name']),
						'SHORT_NAME' => TextHelper::wordwrap_html(stripslashes($row['name']), 22, ' '),
						'POSTOR' => $LANG['by'] . (!empty($row['display_name']) ? ' <a class="small '.UserService::get_level_class($row['level']).'"' . (!empty($group_color) ? ' style="color:' . $group_color . '"' : '') . ' href="'. UserUrlBuilder::profile($row['user_id'])->rel() .'">' . $row['display_name'] . '</a>' : ' ' . $LANG['guest']),
						'VIEWS' => $row['views'],
						'L_VIEWS' => $row['views'] > 1 ? $LANG['views'] : $LANG['view'],
						'L_COMMENTS' => CommentsService::get_number_and_lang_comments('gallery', $row['id']),
						'KERNEL_NOTATION' => $config->is_notation_enabled() && $is_connected ? NotationService::display_active_image($notation) : NotationService::display_static_image($notation),
						'CAT' => $cat_list,
						'RENAME' => $html_protected_name,
						'RENAME_CUT' => $html_protected_name,
						'L_APROB_IMG' => ($row['aprob'] == 1) ? $LANG['unaprob'] : $LANG['aprob'],
						'U_PICTURE_LINK' => PATH_TO_ROOT . '/gallery/gallery' . url('.php?cat=' . $row['idcat'] . '&amp;id=' . $row['id'], '-' . $row['idcat'] . '-' . $row['id'] . '.php'),
						'U_PICTURE' => PATH_TO_ROOT.'/gallery/pics/thumbnails/' . $row['path'],
						'U_DEL' => url('gallery.php?del=' . $row['id'] . '&amp;token=' . AppContext::get_session()->get_token() . '&amp;cat=' . $category->get_id()),
						'U_MOVE' => url('gallery.php?id=' . $row['id'] . '&amp;token=' . AppContext::get_session()->get_token() . '&amp;move=\' + this.options[this.selectedIndex].value'),
						'U_DISPLAY' => $display_link,
						'U_COMMENTS' => PATH_TO_ROOT . '/gallery/gallery' . url('.php?cat=' . $row['idcat'] . '&amp;id=' . $row['id'] . '&amp;com=0', '-' . $row['idcat'] . '-' . $row['id'] . '.php?com=0') . '#comments_list'
					));
				}
				$result->dispose();
	
				//Cr�ation des cellules du tableau si besoin est.
				while (!is_int($j/$nbr_column_pics))
				{
					$this->tpl->assign_block_vars('end_table', array(
						'TD_END' => '<td style="margin:15px 0px;width:' . $column_width_pics . '%">&nbsp;</td>',
						'TR_END' => (is_int(++$j/$nbr_column_pics)) ? '</tr>' : ''
					));
				}
			}
		}
	}
	
	private function init()
	{
		$this->lang = LangLoader::get('common', 'gallery');
		$this->tpl = new FileTemplate('gallery/gallery.tpl');
		$this->tpl->add_lang($this->lang);
	}
	
	private function get_category()
	{
		if ($this->category === null)
		{
			$id = AppContext::get_request()->get_getint('cat', 0);
			if (!empty($id))
			{
				try {
					$this->category = GalleryService::get_categories_manager()->get_categories_cache()->get_category($id);
				} catch (CategoryNotFoundException $e) {
					$error_controller = PHPBoostErrors::unexisting_page();
   					DispatchManager::redirect($error_controller);
				}
			}
			else
			{
				$this->category = GalleryService::get_categories_manager()->get_categories_cache()->get_category(Category::ROOT_CATEGORY);
			}
		}
		return $this->category;
	}
	
	private function check_authorizations()
	{
		$id_cat = $this->get_category()->get_id();
		if (!GalleryAuthorizationsService::check_authorizations($id_cat)->read())
		{
			$error_controller = PHPBoostErrors::user_not_authorized();
			DispatchManager::redirect($error_controller);
		}
	}
	
	private function generate_response()
	{
		$response = new SiteDisplayResponse($this->tpl);
		
		$graphical_environment = $response->get_graphical_environment();
		$graphical_environment->set_page_title($this->get_category()->get_name(), $this->lang['module_title']);
		$graphical_environment->get_seo_meta_data()->set_description($this->get_category()->get_description());
		$graphical_environment->get_seo_meta_data()->set_canonical_url(GalleryUrlBuilder::display_category($this->get_category()->get_id(), $this->get_category()->get_rewrited_name(), AppContext::get_request()->get_getint('page', 1)));
		
		$breadcrumb = $graphical_environment->get_breadcrumb();
		$breadcrumb->add($this->lang['module_title'], GalleryUrlBuilder::home());
		
		$categories = array_reverse(GalleryService::get_categories_manager()->get_parents($this->get_category()->get_id(), true));
		foreach ($categories as $id => $category)
		{
			if ($category->get_id() != Category::ROOT_CATEGORY)
				$breadcrumb->add($category->get_name(), GalleryUrlBuilder::display_category($category->get_id(), $category->get_rewrited_name()));
		}
		
		return $response;
	}
	
	public static function get_view()
	{
		$object = new self();
		$object->init();
		$object->check_authorizations();
		$object->build_view();
		return $object->tpl;
	}
}
?>