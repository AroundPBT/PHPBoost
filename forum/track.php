<?php
/*##################################################
 *                                track.php
 *                            -------------------
 *   begin                : October 26, 2005
 *   copyright            : (C) 2005 Viarre Régis
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

require_once('../kernel/begin.php');
require_once('../forum/forum_begin.php');
require_once('../forum/forum_tools.php');

$Bread_crumb->add($config->get_forum_name(), 'index.php');
$Bread_crumb->add($LANG['show_topic_track'], '');
define('TITLE', $LANG['show_topic_track']);
require_once('../kernel/header.php');

$page = retrieve(GET, 'p', 1);

$request = AppContext::get_request();

$change_cat = $request->get_postint('change_cat', 0);
$valid = $request->get_postvalue('valid', false);

//Redirection changement de catégorie.
if ($change_cat)
	AppContext::get_response()->redirect('/forum/forum' . url('.php?id=' . $change_cat, '-' . $change_cat . $rewrited_title . '.php', '&'));
if (!AppContext::get_current_user()->check_level(User::MEMBER_LEVEL)) //Réservé aux membres.
	AppContext::get_response()->redirect(UserUrlBuilder::connect());
	
if ($valid)
{
	$result = PersistenceContext::get_querier()->select('SELECT t.id, tr.pm, tr.mail
	FROM ' . PREFIX . 'forum_topics t
	LEFT JOIN ' . PREFIX . 'forum_track tr ON tr.idtopic = t.id
	WHERE tr.user_id =:user_id', array('user_id' => AppContext::get_current_user()->get_id()));
	while ($row = $result->fetch())
	{
		$pm = ($request->has_postparameter('p' . $row['id']) && $request->get_postvalue('p' . $row['id']) == 'on') ? 1 : 0;
		if ($row['pm'] != $pm)
			PersistenceContext::get_querier()->update(PREFIX . 'forum_track', array('pm' => $pm), 'WHERE idtopic =:id', array('id' => $row['id']));
		$mail = ($request->has_postparameter('m' . $row['id']) && $request->get_postvalue('m' . $row['id']) == 'on') ? 1 : 0;
		if ($row['mail'] != $mail)
			PersistenceContext::get_querier()->update(PREFIX . 'forum_track', array('mail' => $mail), 'WHERE idtopic =:id', array('id' => $row['id']));
		$del = ($request->has_postparameter('d' . $row['id']) && $request->get_postvalue('d' . $row['id']) == 'on') ? true : false;
		if ($del)
			PersistenceContext::get_querier()->delete(PREFIX . 'forum_track', 'WHERE idtopic=:id', array('id' => $row['id']));
	}
	$result->dispose();
	
	AppContext::get_response()->redirect('/forum/track.php');
}
elseif (AppContext::get_current_user()->check_level(User::MEMBER_LEVEL)) //Affichage des message()s non lu(s) du membre.
{
	$tpl = new FileTemplate('forum/forum_track.tpl');

	$row = PersistenceContext::get_querier()->select_single_row_query("SELECT COUNT(*) as nbr_topics
	FROM " . PREFIX . "forum_topics t
	LEFT JOIN " . PREFIX . "forum_track tr ON tr.idtopic = t.id
	WHERE tr.user_id = :user_id", array(
		'user_id' => AppContext::get_current_user()->get_id()
	));
	$nbr_topics = $row['nbr_topics'];
	
	$page = AppContext::get_request()->get_getint('p', 1);
	$pagination = new ModulePagination($page, $nbr_topics, $config->get_number_topics_per_page(), Pagination::LIGHT_PAGINATION);
	$pagination->set_url(new Url('/forum/track.php?p=%d'));

	if ($pagination->current_page_is_empty() && $page > 1)
	{
		$error_controller = PHPBoostErrors::unexisting_page();
		DispatchManager::redirect($error_controller);
	}

	//Calcul du temps de péremption, ou de dernière vue des messages par à rapport à la configuration.
	$max_time_msg = forum_limit_time_msg();
	
	$TmpTemplate = new FileTemplate('forum/forum_generic_results.tpl');
	$module_data_path = $TmpTemplate->get_pictures_data_path();

	$nbr_topics_compt = 0;
	$result = PersistenceContext::get_querier()->select("SELECT m1.display_name AS login, m1.level AS user_level, m1.groups AS user_groups, m2.display_name AS last_login, m2.level AS last_user_level, m2.groups AS last_user_groups, t.id , t.title , t.subtitle , t.user_id , t.nbr_msg , t.nbr_views , t.last_user_id , t.last_msg_id , t.last_timestamp , t.type , t.status, t.display_msg, v.last_view_id, p.question, me.last_view_forum, tr.pm, tr.mail, me.last_view_forum
	FROM " . PREFIX . "forum_topics t
	LEFT JOIN " . PREFIX . "forum_view v ON v.user_id = :user_id AND v.idtopic = t.id
	LEFT JOIN " . PREFIX . "forum_track tr ON tr.idtopic = t.id
	LEFT JOIN " . PREFIX . "forum_poll p ON p.idtopic = t.id
	LEFT JOIN " . DB_TABLE_MEMBER . " m1 ON m1.user_id = t.user_id
	LEFT JOIN " . DB_TABLE_MEMBER . " m2 ON m2.user_id = t.last_user_id
	LEFT JOIN " . DB_TABLE_MEMBER_EXTENDED_FIELDS . " me ON me.user_id = :user_id
	WHERE tr.user_id = :user_id
	ORDER BY t.last_timestamp DESC
	LIMIT :number_items_per_page OFFSET :display_from", array(
		'user_id' => AppContext::get_current_user()->get_id(),
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
				$img_announce = $img_announce . '-new'; //Image affiché aux visiteurs.
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
		$rewrited_title = ServerEnvironmentConfig::load()->is_url_rewriting_enabled() ? '+' . Url::encode_rewrite($row['title']) : '';
		
		//Affichage du dernier message posté.
		$last_group_color = User::get_group_color($row['last_user_groups'], $row['last_user_level']);
		$last_msg = '<a href="topic' . url('.php?' . $last_page . 'id=' . $row['id'], '-' . $row['id'] . $last_page_rewrite . $rewrited_title . '.php') . '#m' . $last_msg_id . '" title=""><i class="fa fa-hand-o-right"></i></a>' . ' ' . $LANG['on'] . ' ' . Date::to_format($row['last_timestamp'], Date::FORMAT_DAY_MONTH_YEAR_HOUR_MINUTE) . '<br /> ' . $LANG['by'] . ' ' . (!empty($row['last_login']) ? '<a class="small '.UserService::get_level_class($row['last_user_level']).'"' . (!empty($last_group_color) ? ' style="color:' . $last_group_color . '"' : '') . ' href="'. UserUrlBuilder::profile($row['last_user_id'])->rel() .'">' . TextHelper::wordwrap_html($row['last_login'], 13) . '</a>' : '<em>' . $LANG['guest'] . '</em>');
		
		//Ancre ajoutée aux messages non lus.
		$new_ancre = ($new_msg === true && AppContext::get_current_user()->get_id() !== -1) ? '<a href="topic' . url('.php?' . $last_page . 'id=' . $row['id'], '-' . $row['id'] . $last_page_rewrite . $rewrited_title . '.php') . '#m' . $last_msg_id . '" title=""><i class="fa fa-hand-o-right"></i></a>' : '';
		
		//On crée une pagination (si activé) si le nombre de topics est trop important.
		$page = AppContext::get_request()->get_getint('pt', 1);
		$topic_pagination = new ModulePagination($page, $row['nbr_msg'], $config->get_number_messages_per_page(), Pagination::LIGHT_PAGINATION);
		$topic_pagination->set_url(new Url('/forum/topic.php?id=' . $row['id'] . '&amp;pt=%d'));
		
		$group_color = User::get_group_color($row['user_groups'], $row['user_level']);
		
		$tpl->assign_block_vars('topics', array(
			'C_HOT_TOPIC' => ($row['type'] == '0' && $row['status'] != '0' && ($row['nbr_msg'] > $config->get_number_messages_per_page())),
			'C_POLL' => !empty($row['question']),
			'C_BLINK' => $blink,
			'ID' => $row['id'],
			'INCR' => $nbr_topics_compt,
			'CHECKED_PM' => ($row['pm'] == 1) ? 'checked="checked"' : '',
			'CHECKED_MAIL' => ($row['mail'] == 1) ? 'checked="checked"' : '',
			'IMG_ANNOUNCE' => $img_announce,
			'ANCRE' => $new_ancre,
			'TRACK' => '<i class="fa fa-msg-track"></i>',
			'DISPLAY_MSG' => ($config->is_message_before_topic_title_displayed() && $config->is_message_before_topic_title_icon_displayed() && $row['display_msg']) ? '<i class="fa fa-msg-display"></i>' : '',
			'TYPE' => $type[$row['type']],
			'TITLE' => stripslashes($row['title']),
			'AUTHOR' => !empty($row['login']) ? '<a href="'. UserUrlBuilder::profile($row['user_id'])->rel() .'" class="small '.UserService::get_level_class($row['user_level']).'"' . (!empty($group_color) ? ' style="color:' . $group_color . '"' : '') . '>' . $row['login'] . '</a>' : '<em>' . $LANG['guest'] . '</em>',
			'DESC' => $row['subtitle'],
			'PAGINATION' => $topic_pagination->display(),
			'MSG' => ($row['nbr_msg'] - 1),
			'VUS' => $row['nbr_views'],
			'U_TOPIC_VARS' => url('.php?id=' . $row['id'], '-' . $row['id'] . $rewrited_title . '.php'),
			'U_LAST_MSG' => $last_msg,
			'L_DISPLAY_MSG' => ($config->is_message_before_topic_title_displayed() && $row['display_msg']) ? $config->get_message_before_topic_title() : '',
		));
		$nbr_topics_compt++;
	}
	$result->dispose();
	
	//Le membre a déjà lu tous les messages.
	if ($nbr_topics == 0)
	{
		$tpl->put_all(array(
			'C_NO_TRACKED_TOPICS' => true,
			'L_NO_TRACKED_TOPICS' => '0 ' . $LANG['show_topic_track']
		));
	}

	$l_topic = ($nbr_topics > 1) ? $LANG['topic_s'] : $LANG['topic'];
	
	$vars_tpl = array(
		'C_PAGINATION' => $pagination->has_several_pages(),
		'NBR_TOPICS' => $nbr_topics,
		'FORUM_NAME' => $config->get_forum_name(),
		'PAGINATION' => $pagination->display(),
		'U_MSG_SET_VIEW' => '<a class="small" href="' . PATH_TO_ROOT . '/forum/action' . url('.php?read=1&amp;favorite=1', '') . '" title="' . $LANG['mark_as_read'] . '" onclick="javascript:return Confirm_read_topics();">' . $LANG['mark_as_read'] . '</a>',
		'U_CHANGE_CAT'=> 'track.php' . '&amp;token=' . AppContext::get_session()->get_token(),
		'U_ONCHANGE' => url(".php?id=' + this.options[this.selectedIndex].value + '", "forum-' + this.options[this.selectedIndex].value + '.php"),
		'U_ONCHANGE_CAT' => url("index.php?id=' + this.options[this.selectedIndex].value + '", "cat-' + this.options[this.selectedIndex].value + '.php"),
		'U_FORUM_CAT' => '<a href="' . PATH_TO_ROOT . '/forum/track.php' . '">' . $LANG['show_topic_track'] . '</a>',
		'U_POST_NEW_SUBJECT' => '',
		'U_TRACK_ACTION' => url('.php?p=' . $page . '&amp;token=' . AppContext::get_session()->get_token()),
		'L_FORUM_INDEX' => $LANG['forum_index'],
		'L_AUTHOR' => $LANG['author'],
		'L_FORUM' => $LANG['forum'],
		'L_DELETE' => LangLoader::get_message('delete', 'common'),
		'L_MAIL' => $LANG['mail'],
		'L_PM' => $LANG['pm'],
		'L_EXPLAIN_TRACK' => $LANG['explain_track'],
		'L_TOPIC' => $l_topic,
		'L_MESSAGE' => $LANG['replies'],
		'L_VIEW' => $LANG['views'],
		'L_LAST_MESSAGE' => $LANG['last_message'],
		'L_SUBMIT' => $LANG['submit']
	);
	
	//Listes les utilisateurs en lignes.
	list($users_list, $total_admin, $total_modo, $total_member, $total_visit, $total_online) = forum_list_user_online("AND s.location_script LIKE '%" ."/forum/track.php%'");

	//Liste des catégories.
	$search_category_children_options = new SearchCategoryChildrensOptions();
	$search_category_children_options->add_authorizations_bits(Category::READ_AUTHORIZATIONS);
	$categories_tree = ForumService::get_categories_manager()->get_select_categories_form_field('cats', '', Category::ROOT_CATEGORY, $search_category_children_options);
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
	AppContext::get_response()->redirect('/forum/index.php');

include('../kernel/footer.php');

?>
