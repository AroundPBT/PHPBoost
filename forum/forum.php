<?php
/*##################################################
 *                                forum.php
 *                            -------------------
 *   begin                : October 26, 2005
 *   copyright            : (C) 2005 Viarre Régis / Sautel Benoît
 *   email                : crowkait@phpboost.com / ben.popeye@phpboost.com
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

require_once('../kernel/begin.php');
require_once('../forum/forum_begin.php');
require_once('../forum/forum_tools.php');

$id_get = retrieve(GET, 'id', 0);

//Vérification de l'existance de la catégorie.
if ($id_get != Category::ROOT_CATEGORY && !ForumService::get_categories_manager()->get_categories_cache()->category_exists($id_get))
{
	$controller = PHPBoostErrors::unexisting_category();
    DispatchManager::redirect($controller);
}

//Vérification des autorisations d'accès.
if (!ForumAuthorizationsService::check_authorizations($id_get)->read())
{
	$error_controller = PHPBoostErrors::user_not_authorized();
	DispatchManager::redirect($error_controller);
}

try {
	$category = ForumService::get_categories_manager()->get_categories_cache()->get_category($id_get);
} catch (CategoryNotFoundException $e) {
	$error_controller = PHPBoostErrors::unexisting_page();
	DispatchManager::redirect($error_controller);
}

if ($category->get_url())
{
	$error_controller = PHPBoostErrors::unexisting_page();
	DispatchManager::redirect($error_controller);
}

//Récupération de la barre d'arborescence.
$Bread_crumb->add($config->get_forum_name(), 'index.php');
$categories = array_reverse(ForumService::get_categories_manager()->get_parents($id_get, true));
foreach ($categories as $id => $cat)
{
	if ($cat->get_id() != Category::ROOT_CATEGORY)
	{
		if ($cat->get_type() == ForumCategory::TYPE_FORUM)
			$Bread_crumb->add($cat->get_name(), 'forum' . url('.php?id=' . $cat->get_id(), '-' . $cat->get_id() . '+' . $cat->get_rewrited_name() . '.php'));
		else
			$Bread_crumb->add($cat->get_name(), url('index.php?id=' . $cat->get_id(), 'cat-' . $cat->get_id() . '+' . $cat->get_rewrited_name() . '.php'));
	}
}

if (!empty($id_get))
	define('TITLE', $category->get_name());
else
	define('TITLE', $LANG['title_forum']);
require_once('../kernel/header.php'); 

//Redirection changement de catégorie.
$change_cat = retrieve(POST, 'change_cat', '');
if (!empty($change_cat))
	AppContext::get_response()->redirect('/forum/forum' . url('.php?id=' . $change_cat, '-' . $change_cat . '+' . $category->get_rewrited_name() . '.php', '&'));
	
if (!empty($id_get))
{
	$tpl = new FileTemplate('forum/forum_forum.tpl');

	//Invité?
	$is_guest = AppContext::get_current_user()->get_id() == -1;
	
	//Calcul du temps de péremption, ou de dernière vue des messages par à rapport à la configuration.
	$max_time_msg = forum_limit_time_msg();
	
	//Affichage des sous forums s'il y en a.
	if (ForumService::get_categories_manager()->get_categories_cache()->get_childrens($id_get))
	{
		$tpl->put_all(array(
			'C_FORUM_SUB_CATS' => true
		));
		
		//Vérification des autorisations.
		$authorized_categories = ForumService::get_authorized_categories($id_get);
		
		//On liste les sous-catégories.
		$result = PersistenceContext::get_querier()->select('SELECT @id_cat:= c.id, c.id AS cid, c.id_parent, c.name, c.rewrited_name, c.description as subname, c.url, c.last_topic_id, t.id AS tid, t.idcat, t.title, t.last_timestamp, t.last_user_id, t.last_msg_id, t.nbr_msg AS t_nbr_msg, t.display_msg, t.status, m.user_id, m.display_name, m.level AS user_level, m.groups, v.last_view_id,
		(SELECT COUNT(*) FROM ' . ForumSetup::$forum_topics_table . '
			WHERE idcat IN (
				@id_cat,
				(SELECT GROUP_CONCAT(id SEPARATOR \',\') FROM ' . ForumSetup::$forum_cats_table . ' WHERE id_parent = @id_cat), 
				(SELECT GROUP_CONCAT(childs.id SEPARATOR \',\') FROM ' . ForumSetup::$forum_cats_table . ' parents
				INNER JOIN ' . ForumSetup::$forum_cats_table . ' childs ON parents.id = childs.id_parent
				WHERE parents.id_parent = @id_cat)
			)
		) AS nbr_topic,
		(SELECT COUNT(*) FROM ' . ForumSetup::$forum_message_table . '
			WHERE idtopic IN (
				(SELECT GROUP_CONCAT(id SEPARATOR \',\') FROM ' . ForumSetup::$forum_topics_table . ' WHERE idcat = @id_cat), 
				(SELECT GROUP_CONCAT(t.id SEPARATOR \',\') FROM ' . ForumSetup::$forum_topics_table . ' t LEFT JOIN ' . ForumSetup::$forum_cats_table . ' c ON t.idcat = c.id WHERE id_parent = @id_cat)
			)
		) AS nbr_msg
		FROM ' . ForumSetup::$forum_cats_table . ' c
		LEFT JOIN ' . ForumSetup::$forum_topics_table . ' t ON t.id = c.last_topic_id
		LEFT JOIN ' . ForumSetup::$forum_view_table . ' v ON v.user_id = :user_id AND v.idtopic = t.id
		LEFT JOIN ' . DB_TABLE_MEMBER . ' m ON m.user_id = t.last_user_id
		WHERE c.id_parent = :id_cat AND c.id IN :authorized_categories
		ORDER BY c.id_parent, c.c_order', array(
			'user_id' => AppContext::get_current_user()->get_id(),
			'id_cat' => $category->get_id(),
			'authorized_categories' => $authorized_categories
		));
		
		$categories = array();
		while ($row = $result->fetch())
		{
			$categories[] = $row;
		}
		$result->dispose();
		
		$display_sub_cats = false;
		$is_sub_forum = array();
		foreach ($categories as $row)
		{
			if (in_array($row['id_parent'], $is_sub_forum))
				$is_sub_forum[] = $row['cid'];
			
			if (!in_array($row['cid'], $is_sub_forum))
			{
				if ($row['nbr_msg'] !== '0')
				{
					//Si le dernier message lu est présent on redirige vers lui, sinon on redirige vers le dernier posté.
					if (!empty($row['last_view_id'])) //Calcul de la page du last_view_id réalisé dans topic.php
					{
						$last_msg_id = $row['last_view_id']; 
						$last_page = 'idm=' . $row['last_view_id'] . '&amp;';
						$last_page_rewrite = '-0-' . $row['last_view_id'];
					}
					else
					{
						$last_msg_id = $row['last_msg_id']; 
						$last_page = ceil($row['t_nbr_msg'] / $config->get_number_messages_per_page());
						$last_page_rewrite = ($last_page > 1) ? '-' . $last_page : '';
						$last_page = ($last_page > 1) ? 'pt=' . $last_page . '&amp;' : '';
					}
					
					$last_topic_title = (($config->is_message_before_topic_title_displayed() && $row['display_msg']) ? $config->get_message_before_topic_title() : '') . ' ' . $row['title'];
					$last_topic_title = stripslashes((strlen(TextHelper::html_entity_decode($last_topic_title)) > 20) ? TextHelper::substr_html($last_topic_title, 0, 20) . '...' : $last_topic_title);
					
					$group_color = User::get_group_color($row['groups'], $row['user_level']);
					
					$last = '<a href="topic' . url('.php?id=' . $row['tid'], '-' . $row['tid'] . '+' . Url::encode_rewrite($row['title'])  . '.php') . '" class="small">' . $last_topic_title . '</a><br />
					<a href="topic' . url('.php?' . $last_page .  'id=' . $row['tid'], '-' . $row['tid'] . $last_page_rewrite . '+' . Url::encode_rewrite($row['title'])  . '.php') . '#m' .  $last_msg_id . '" title=""><i class="fa fa-hand-o-right"></i></a> ' . $LANG['on'] . ' ' . Date::to_format($row['last_timestamp'], Date::FORMAT_DAY_MONTH_YEAR_HOUR_MINUTE) . '<br />
					' . $LANG['by'] . (!empty($row['display_name']) ? ' <a href="'. UserUrlBuilder::profile($row['last_user_id'])->rel() .'" class="small '.UserService::get_level_class($row['user_level']).'"' . (!empty($group_color) ? ' style="color:' . $group_color . '"' : '') . '>' . TextHelper::wordwrap_html($row['display_name'], 13) . '</a>' : ' ' . $LANG['guest']);
				}
				else
				{
					$row['last_timestamp'] = '';
					$last = '<br />' . $LANG['no_message'] . '<br /><br />';
				}

				//Vérirication de l'existance de sous forums.
				$subforums = '';
				$children = ForumService::get_categories_manager()->get_categories_cache()->get_childrens($row['cid']);
				if ($children)
				{
					foreach ($children as $id => $child) //Listage des sous forums.
					{
						if ($child->get_id_parent() == $row['cid'] && ForumAuthorizationsService::check_authorizations($child->get_id())->read()) //Sous forum distant d'un niveau au plus.
						{
							$is_sub_forum[] = $child->get_id();
							$link = $child->get_url() ? '<a href="' . $child->get_url() . '" class="small">' : '<a href="forum' . url('.php?id=' . $child->get_id(), '-' . $child->get_id() . '+' . $child->get_rewrited_name() . '.php') . '" class="small">';
							$subforums .= !empty($subforums) ? ', ' . $link . $child->get_name() . '</a>' : $link . $child->get_name() . '</a>';
						}
					}
					$subforums = '<strong>' . $LANG['subforum_s'] . '</strong>: ' . $subforums;
				}
				
				//Vérifications des topics Lu/non Lus.
				$img_announce = 'fa-announce';
				$blink = false;
				if (!$is_guest)
				{
					if ($row['last_view_id'] != $row['last_msg_id'] && $row['last_timestamp'] >= $max_time_msg) //Nouveau message (non lu).
					{
						$img_announce =  $img_announce . '-new'; //Image affiché aux visiteurs.
						$blink = true;
					}
				}
				$img_announce .= ($row['status'] == '0') ? '-lock' : '';
				
				$tpl->assign_block_vars('subcats', array(
					'C_BLINK' => $blink,
					'IMG_ANNOUNCE' => $img_announce,
					'NAME' => $row['name'],
					'DESC' => $row['subname'],
					'SUBFORUMS' => !empty($subforums) && !empty($row['subname']) ? '<br />' . $subforums : $subforums,
					'NBR_TOPIC' => $row['nbr_topic'],
					'NBR_MSG' => $row['nbr_msg'],
					'U_FORUM_URL' => $row['url'],
					'U_FORUM_VARS' => url('.php?id=' . $row['cid'], '-' . $row['cid'] . '+' . $row['rewrited_name'] . '.php'),
					'U_LAST_TOPIC' => $last
				));
			}
		}
	}
	
	//On vérifie si l'utilisateur a les droits d'écritures.
	$check_group_write_auth = ForumAuthorizationsService::check_authorizations($id_get)->write();
	if (!$check_group_write_auth)
	{
		$tpl->assign_block_vars('error_auth_write', array(
			'L_ERROR_AUTH_WRITE' => $LANG['e_cat_write']
		));
	}
	
	$nbr_topic = PersistenceContext::get_querier()->count(PREFIX . 'forum_topics', 'WHERE idcat=:idcat', array('idcat' => $id_get));
	
	//On crée une pagination (si activé) si le nombre de forum est trop important.
	$page = AppContext::get_request()->get_getint('p', 1);
	$pagination = new ModulePagination($page, $nbr_topic, $config->get_number_topics_per_page(), Pagination::LIGHT_PAGINATION);
	$pagination->set_url(new Url('/forum/forum.php?id=' . $id_get . '&amp;p=%d'));

	if ($pagination->current_page_is_empty() && $page > 1)
	{
		$error_controller = PHPBoostErrors::unexisting_page();
		DispatchManager::redirect($error_controller);
	}

	//Affichage de l'arborescence des catégories.
	$i = 0;
	$forum_cats = '';
	foreach ($Bread_crumb->get_links() as $key => $array)
	{
		if ($i == 2)
			$forum_cats .= '<a href="' . $array[1] . '">' . $array[0] . '</a>';
		elseif ($i > 2)
			$forum_cats .= ' &raquo; <a href="' . $array[1] . '">' . $array[0] . '</a>';
		$i++;
	}
	
	//Si l'utilisateur a les droits d'édition.
	$check_group_edit_auth = ForumAuthorizationsService::check_authorizations($id_get)->moderation();

	$vars_tpl = array(
		'C_PAGINATION' => $pagination->has_several_pages(),
		'FORUM_NAME' => $config->get_forum_name(),
		'PAGINATION' => $pagination->display(),
		'IDCAT' => $id_get,
		//'C_MASS_MODO_CHECK' => $check_group_edit_auth ? true : false,
		'C_MASS_MODO_CHECK' => false,
		'C_POST_NEW_SUBJECT' => $check_group_write_auth,
		'U_MSG_SET_VIEW' => '<a class="small" href="' . PATH_TO_ROOT . '/forum/action' . url('.php?read=1&amp;f=' . $id_get, '') . '" title="' . $LANG['mark_as_read'] . '" onclick="javascript:return Confirm_read_topics();">' . $LANG['mark_as_read'] . '</a>',
		'U_CHANGE_CAT'=> 'forum' . url('.php?id=' . $id_get, '-' . $id_get . '+' . $category->get_rewrited_name() . '.php'),
		'U_ONCHANGE' => url(".php?id=' + this.options[this.selectedIndex].value + '", "forum-' + this.options[this.selectedIndex].value + '.php"),
		'U_ONCHANGE_CAT' => url("index.php?id=' + this.options[this.selectedIndex].value + '", "cat-' + this.options[this.selectedIndex].value + '.php"),
		'U_FORUM_CAT' => $forum_cats,
		'U_POST_NEW_SUBJECT' => 'post' . url('.php?new=topic&amp;id=' . $id_get, ''),
		'L_FORUM_INDEX' => $LANG['forum_index'],
		'L_SUBFORUMS' => $LANG['sub_forums'],
		'L_DISPLAY_UNREAD_MSG' => $LANG['show_not_reads'],
		'L_FORUM' => $LANG['forum'],
		'L_AUTHOR' => $LANG['author'],
		'L_TOPIC' => $LANG['topic_s'],
		'L_ANSWERS' => $LANG['replies'],
		'L_MESSAGE' => $LANG['message_s'],
		'L_POLL' => $LANG['poll'],
		'L_VIEW' => $LANG['views'],
		'L_LAST_MESSAGE' => $LANG['last_messages'],
		'L_POST_NEW_SUBJECT' => $LANG['post_new_subject'],
		'L_FOR_SELECTION' => $LANG['for_selection'],
		'L_CHANGE_STATUT_TO' => sprintf($LANG['change_status_to'], $config->get_message_before_topic_title()),
		'L_CHANGE_STATUT_TO_DEFAULT' => $LANG['change_status_to_default'],
		'L_MOVE_TO' => $LANG['move_to'],
		'L_DELETE' => LangLoader::get_message('delete', 'common'),
		'L_LOCK' => $LANG['forum_lock'],
		'L_UNLOCK' => $LANG['forum_unlock'],
		'L_GO' => $LANG['go']
	);

	$nbr_topics_display = 0;
	$result = PersistenceContext::get_querier()->select("SELECT m1.display_name AS login, m1.level AS user_level, m1.groups AS user_groups, m2.display_name AS last_login, m2.level AS last_user_level, m2.groups AS last_user_groups, t.id, t.title, t.subtitle, t.user_id, t.nbr_msg, t.nbr_views, t.last_user_id , t.last_msg_id, t.last_timestamp, t.type, t.status, t.display_msg, v.last_view_id, p.question, tr.id AS idtrack
	FROM " . PREFIX . "forum_topics t
	LEFT JOIN " . PREFIX . "forum_view v ON v.user_id = :user_id AND v.idtopic = t.id
	LEFT JOIN " . DB_TABLE_MEMBER . " m1 ON m1.user_id = t.user_id
	LEFT JOIN " . DB_TABLE_MEMBER . " m2 ON m2.user_id = t.last_user_id
	LEFT JOIN " . PREFIX . "forum_poll p ON p.idtopic = t.id
	LEFT JOIN " . PREFIX . "forum_track tr ON tr.idtopic = t.id AND tr.user_id = :user_id
	WHERE t.idcat = :idcat
	ORDER BY t.type DESC , t.last_timestamp DESC
	LIMIT :number_items_per_page OFFSET :display_from", array(
		'user_id' => AppContext::get_current_user()->get_id(),
		'idcat' => $id_get,
		'number_items_per_page' => $pagination->get_number_items_per_page(),
		'display_from' => $pagination->get_display_from()
	));
	while ($row = $result->fetch())
	{
		//On définit un array pour l'appellation correspondant au type de champ
		$type = array('2' => $LANG['forum_announce'] . ':', '1' => $LANG['forum_postit'] . ':', '0' => '');
		
		//Vérifications des topics Lu/non Lus.
		$img_announce = 'fa-announce';
		$new_msg = false;
		$blink = false;
		if (!$is_guest) //Non visible aux invités.
		{
			if ($row['last_view_id'] != $row['last_msg_id'] && $row['last_timestamp'] >= $max_time_msg) //Nouveau message (non lu).
			{
				$img_announce =  $img_announce . '-new'; //Image affiché aux visiteurs.
				$new_msg = true;
				$blink = true;
			}
		}
		$img_announce .= ($row['type'] == '1') ? '-post' : '';
		$img_announce .= ($row['type'] == '2') ? '-top' : '';
		$img_announce .= ($row['status'] == '0' && $row['type'] == '0') ? '-lock' : '';

		//Si le dernier message lu est présent on redirige vers lui, sinon on redirige vers le dernier posté.
		//Puis calcul de la page du last_msg_id ou du last_view_id.
		if (!empty($row['last_view_id'])) 
		{
			$last_msg_id = $row['last_view_id']; 
			$last_page = 'idm=' . $row['last_view_id'] . '&amp;';
			$last_page_rewrite = '-0-' . $row['last_view_id'];
		}
		else
		{
			$last_msg_id = $row['last_msg_id']; 
			$last_page = ceil( $row['nbr_msg'] / $config->get_number_messages_per_page() );
			$last_page_rewrite = ($last_page > 1) ? '-' . $last_page : '';
			$last_page = ($last_page > 1) ? 'pt=' . $last_page . '&amp;' : '';
		}
		
		//On encode l'url pour un éventuel rewriting, c'est une opération assez gourmande
		$rewrited_title_topic = ServerEnvironmentConfig::load()->is_url_rewriting_enabled() ? '+' . Url::encode_rewrite($row['title']) : '';
		
		//Affichage du dernier message posté.
		$last_group_color = User::get_group_color($row['last_user_groups'], $row['last_user_level']);
		$last_msg = '<a href="topic' . url('.php?' . $last_page . 'id=' . $row['id'], '-' . $row['id'] . $last_page_rewrite . $rewrited_title_topic . '.php') . '#m' . $last_msg_id . '" title=""><i class="fa fa-hand-o-right"></i></a>' . ' ' . $LANG['on'] . ' ' . Date::to_format($row['last_timestamp'], Date::FORMAT_DAY_MONTH_YEAR_HOUR_MINUTE) . '<br /> ' . $LANG['by'] . ' ' . (!empty($row['last_login']) ? '<a class="small '.UserService::get_level_class($row['last_user_level']).'"' . (!empty($last_group_color) ? ' style="color:' . $last_group_color . '"' : '') . ' href="'. UserUrlBuilder::profile($row['last_user_id'])->rel() .'">' . TextHelper::wordwrap_html($row['last_login'], 13) . '</a>' : '<em>' . $LANG['guest'] . '</em>');
		
		//Ancre ajoutée aux messages non lus.	
		$new_ancre = ($new_msg === true && !$is_guest) ? '<a href="topic' . url('.php?' . $last_page . 'id=' . $row['id'], '-' . $row['id'] . $last_page_rewrite . $rewrited_title_topic . '.php') . '#m' . $last_msg_id . '" title=""><i class="fa fa-hand-o-right"></i></a>' : '';
		
		//On crée une pagination (si activé) si le nombre de topics est trop important.
		$page = AppContext::get_request()->get_getint('pt', 1);
		$topic_pagination = new ModulePagination($page, $row['nbr_msg'], $config->get_number_messages_per_page(), Pagination::LIGHT_PAGINATION);
		$topic_pagination->set_url(new Url('/forum/topic.php?id=' . $row['id'] . '&amp;pt=%d'));
		
		$group_color = User::get_group_color($row['user_groups'], $row['user_level']);
		
		$tpl->assign_block_vars('topics', array(
			'C_PAGINATION' => $topic_pagination->has_several_pages(),
			'C_IMG_POLL' => !empty($row['question']),
			'C_IMG_TRACK' => !empty($row['idtrack']),
			'C_DISPLAY_MSG' => ($config->is_message_before_topic_title_displayed() && $config->is_message_before_topic_title_icon_displayed() && $row['display_msg']),
			'C_HOT_TOPIC' => ($row['type'] == '0' && $row['status'] != '0' && ($row['nbr_msg'] > $config->get_number_messages_per_page())),
			'C_BLINK' => $blink,
			'IMG_ANNOUNCE' => $img_announce,
			'ANCRE' => $new_ancre,
			'TYPE' => $type[$row['type']],
			'TITLE' => stripslashes($row['title']),
			'AUTHOR' => !empty($row['login']) ? '<a href="'. UserUrlBuilder::profile($row['user_id'])->rel() .'" class="small '.UserService::get_level_class($row['user_level']).'"' . (!empty($group_color) ? ' style="color:' . $group_color . '"' : '') . '>' . $row['login'] . '</a>' : '<em>' . $LANG['guest'] . '</em>',
			'DESC' => $row['subtitle'],
			'PAGINATION' => $topic_pagination->display(),
			'MSG' => ($row['nbr_msg'] - 1),
			'VUS' => $row['nbr_views'],
			'L_DISPLAY_MSG' => ($config->is_message_before_topic_title_displayed() && $row['display_msg']) ? $config->get_message_before_topic_title() : '', 
			'U_TOPIC_VARS' => url('.php?id=' . $row['id'], '-' . $row['id'] . $rewrited_title_topic . '.php'),
			'U_LAST_MSG' => $last_msg
		));
		$nbr_topics_display++;
	}
	$result->dispose();
		
	//Affichage message aucun topics.
	if ($nbr_topics_display == 0)
	{
		$tpl->put_all(array(
			'C_NO_TOPICS' => true,
			'L_NO_TOPICS' => $LANG['no_topics']
		));
	}

	//Listes les utilisateurs en lignes.
	list($users_list, $total_admin, $total_modo, $total_member, $total_visit, $total_online) = forum_list_user_online("AND s.location_script LIKE '%" . url('/forum/forum.php?id=' . $id_get, '/forum/forum-' . $id_get) . "%'");

	//Liste des catégories.
	$search_category_children_options = new SearchCategoryChildrensOptions();
	$search_category_children_options->add_authorizations_bits(Category::READ_AUTHORIZATIONS);
	$categories_tree = ForumService::get_categories_manager()->get_select_categories_form_field('cats', '', $id_get, $search_category_children_options);
	$method = new ReflectionMethod('AbstractFormFieldChoice', 'get_options');
	$method->setAccessible(true);
	$categories_tree_options = $method->invoke($categories_tree);
	$cat_list = '';
	foreach ($categories_tree_options as $option)
	{
		if ($option->get_raw_value())
		{
			$cat = ForumService::get_categories_manager()->get_categories_cache()->get_category($option->get_raw_value());
			if (!$cat->get_url())
				$cat_list .= $option->display()->render();
		}
	}

	$vars_tpl = array_merge($vars_tpl, array(
		'C_USER_CONNECTED' => AppContext::get_current_user()->check_level(User::MEMBER_LEVEL),
		'TOTAL_ONLINE' => $total_online,
		'USERS_ONLINE' => (($total_online - $total_visit) == 0) ? '<em>' . $LANG['no_member_online'] . '</em>' : $users_list,
		'ADMIN' => $total_admin,
		'MODO' => $total_modo,
		'MEMBER' => $total_member,
		'GUEST' => $total_visit,
		'SELECT_CAT' => $cat_list, //Retourne la liste des catégories, avec les vérifications d'accès qui s'imposent.
		'L_USER' => ($total_online > 1) ? $LANG['user_s'] : $LANG['user'],
		'L_ADMIN' => ($total_admin > 1) ? $LANG['admin_s'] : $LANG['admin'],
		'L_MODO' => ($total_modo > 1) ? $LANG['modo_s'] : $LANG['modo'],
		'L_MEMBER' => ($total_member > 1) ? $LANG['member_s'] : $LANG['member'],
		'L_GUEST' => ($total_visit > 1) ? $LANG['guest_s'] : $LANG['guest'],
		'L_AND' => $LANG['and'],
		'L_ONLINE' => strtolower($LANG['online'])
	));
	
	$tpl->put_all($vars_tpl);
	$tpl_top->put_all($vars_tpl);
	$tpl_bottom->put_all($vars_tpl);
		
	$tpl->put('forum_top', $tpl_top);
	$tpl->put('forum_bottom', $tpl_bottom);
		
	$tpl->display();
}
else
{
	$modulesLoader = AppContext::get_extension_provider_service();
	$module = $modulesLoader->get_provider('forum');
	if ($module->has_extension_point(HomePageExtensionPoint::EXTENSION_POINT))
	{
		echo $module->get_extension_point(HomePageExtensionPoint::EXTENSION_POINT)->get_home_page()->get_view()->display();
	}
}

include('../kernel/footer.php');
?>
