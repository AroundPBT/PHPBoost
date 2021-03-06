<?php
/*##################################################
 *                                post.php
 *                            -------------------
 *   begin                : October 27, 2005
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

$id_get = retrieve(GET, 'id', 0);

//Existance de la catégorie.
if ($id_get != Category::ROOT_CATEGORY && !ForumService::get_categories_manager()->get_categories_cache()->category_exists($id_get))
{
	$controller = PHPBoostErrors::unexisting_category();
	DispatchManager::redirect($controller);
}

if (AppContext::get_current_user()->get_delay_readonly() > time()) //Lecture seule.
{
	$controller = PHPBoostErrors::user_in_read_only();
	DispatchManager::redirect($controller);
}

try {
	$category = ForumService::get_categories_manager()->get_categories_cache()->get_category($id_get);
} catch (CategoryNotFoundException $e) {
	$error_controller = PHPBoostErrors::unexisting_page();
	DispatchManager::redirect($error_controller);
}

//Récupération de la barre d'arborescence.
$Bread_crumb->add($config->get_forum_name(), 'index.php');
$categories = array_reverse(ForumService::get_categories_manager()->get_parents($id_get, true));
foreach ($categories as $id => $cat)
{
	if ($cat->get_id() != Category::ROOT_CATEGORY)
		$Bread_crumb->add($cat->get_name(), 'forum' . url('.php?id=' . $cat->get_id(), '-' . $cat->get_id() . '+' . $cat->get_rewrited_name() . '.php'));
}
$Bread_crumb->add($LANG['title_post'], '');
define('TITLE', $LANG['title_forum']);
require_once('../kernel/header.php');

$new_get = retrieve(GET, 'new', '');
$idt_get = retrieve(GET, 'idt', '');
$error_get = retrieve(GET, 'error', '');
$previs = retrieve(POST, 'prw', false); //Prévisualisation des messages.
$post_topic = retrieve(POST, 'post_topic', false);
$preview_topic = retrieve(POST, 'prw_t', '');

$editor = AppContext::get_content_formatting_service()->get_default_editor();
$editor->set_identifier('contents');
	
//Niveau d'autorisation de la catégorie
if (ForumAuthorizationsService::check_authorizations($id_get)->read())
{
	$Forumfct = new Forum();

	//Mod anti-flood
	$check_time = (ContentManagementConfig::load()->is_anti_flood_enabled() && AppContext::get_current_user()->get_id() != -1) ? PersistenceContext::get_querier()->get_column_value(PREFIX . "forum_msg", 'MAX(timestamp) as timestamp', 'WHERE user_id = :user_id', array('user_id' => AppContext::get_current_user()->get_id())) : false;

	//Affichage de l'arborescence des catégories.
	$i = 0;
	$forum_cats = '';
	$Bread_crumb->remove_last();
	foreach ($Bread_crumb->get_links() as $key => $array)
	{
		if ($i == 2)
			$forum_cats .= '<a href="' . $array[1] . '">' . $array[0] . '</a>';
		elseif ($i > 2)
			$forum_cats .= ' &raquo; <a href="' . $array[1] . '">' . $array[0] . '</a>';
		$i++;
	}

	if ($previs) //Prévisualisation des messages
	{
		if (!ForumAuthorizationsService::check_authorizations($id_get)->write())
			AppContext::get_response()->redirect(url(HOST . SCRIPT . '?error=c_write&id=' . $id_get, '', '&') . '#message_helper');

		try {
			$topic = PersistenceContext::get_querier()->select_single_row_query('SELECT idcat, title, subtitle
			FROM ' . PREFIX . 'forum_topics
			WHERE id=:id', array(
				'id' => $idt_get
			));
		} catch (RowNotFoundException $e) {
			$controller = new UserErrorController(LangLoader::get_message('error', 'status-messages-common'), $LANG['e_unexist_topic_forum']);
			DispatchManager::redirect($controller);
		}

		$tpl = new FileTemplate('forum/forum_edit_msg.tpl');

		$contents = retrieve(POST, 'contents', '', TSTRING);
		$post_update = retrieve(POST, 'p_update', '', TSTRING_UNCHANGE);

		$update = !empty($post_update) ? $post_update : url('?new=n_msg&amp;idt=' . $idt_get . '&amp;id=' . $id_get . '&amp;token=' . AppContext::get_session()->get_token());
		$submit = !empty($post_update) ? $LANG['update'] : $LANG['submit'];

		$vars_tpl = array(
			'P_UPDATE' => $post_update,
			'FORUM_NAME' => $config->get_forum_name(),
			'KERNEL_EDITOR' => $editor->display(),
			'DESC' => $topic['subtitle'],
			'CONTENTS' => $contents,
			'DATE' => $LANG['on'] . ' ' . Date::to_format(Date::DATE_NOW, Date::FORMAT_DAY_MONTH_YEAR_HOUR_MINUTE),
			'CONTENTS_PREVIEW' => FormatingHelper::second_parse(stripslashes(FormatingHelper::strparse($contents))),
			'C_FORUM_PREVIEW_MSG' => true,
			'U_ACTION' => 'post.php' . $update . '&amp;token=' . AppContext::get_session()->get_token(),
			'U_FORUM_CAT' => $forum_cats,
			'U_TITLE_T' => '<a href="topic' . url('.php?id=' . $idt_get, '-' . $idt_get . '.php') . '">' . stripslashes($topic['title']) . '</a>',
			'L_REQUIRE' => LangLoader::get_message('form.explain_required_fields', 'status-messages-common'),
			'L_REQUIRE_TEXT' => $LANG['require_text'],
			'L_REQUIRE_TITLE' => $LANG['require_title'],
			'L_FORUM_INDEX' => $LANG['forum_index'],
			'L_EDIT_MESSAGE' => $LANG['preview'],
			'L_MESSAGE' => $LANG['message'],
			'L_SUBMIT' => $submit,
			'L_PREVIEW' => $LANG['preview'],
			'L_RESET' => $LANG['reset']
		);

		$tpl->put_all($vars_tpl);
		
		$tpl->put('forum_top', $tpl_top->display());
		$tpl->display();
		$tpl->put('forum_bottom', $tpl_bottom->display());
	}
	elseif ($new_get === 'topic' && empty($error_get)) //Nouveau topic.
	{
		if ($post_topic && !empty($id_get))
		{
			$is_modo = ForumAuthorizationsService::check_authorizations($id_get)->moderation();
			if (!ForumAuthorizationsService::check_authorizations($id_get)->write())
				AppContext::get_response()->redirect(url(HOST . SCRIPT . '?error=c_write&id=' . $id_get, '', '&') . '#message_helper');

			if ($is_modo)
				$type = retrieve(POST, 'type', 0);
			else
				$type = 0;

			$contents = retrieve(POST, 'contents', '', TSTRING_UNCHANGE);
			$title = retrieve(POST, 'title', '');
			$subtitle = retrieve(POST, 'desc', '');

			//Mod anti Flood
			if ($check_time !== false)
			{
				$delay_flood = ContentManagementConfig::load()->get_anti_flood_duration(); //On recupère le delai de flood.
				$delay_expire = time() - $delay_flood; //On calcul la fin du delai.

				//Droit de flooder?.
				if ($check_time >= $delay_expire && !ForumAuthorizationsService::check_authorizations()->flood()) //Flood
					AppContext::get_response()->redirect(url(HOST . SCRIPT . '?error=flood_t&id=' . $id_get, '', '&') . '#message_helper');
			}

			if (!empty($contents) && !empty($title)) //Insertion nouveau topic.
			{
				list($last_topic_id, $last_msg_id) = $Forumfct->Add_topic($id_get, $title, $subtitle, $contents, $type); //Insertion nouveau topic.

				//Ajout d'un sondage en plus du topic.
				$question = retrieve(POST, 'question', '');
				if (!empty($question))
				{
					$poll_type = retrieve(POST, 'poll_type', 0);
					$poll_type = ($poll_type == 0 || $poll_type == 1) ? $poll_type : 0;

					$answers = array();
					$nbr_votes = 0;
					for ($i = 0; $i < 20; $i++)
					{
						$answer = str_replace('|', '', retrieve(POST, 'a'.$i, ''));
						if (!empty($answer))
						{
							$answers[$i] = $answer;
							$nbr_votes++;
						}
					}
					$Forumfct->Add_poll($last_topic_id, $question, $answers, $nbr_votes, $poll_type); //Ajout du sondage.
				}

				AppContext::get_response()->redirect('/forum/topic' . url('.php?id=' . $last_topic_id, '-' . $last_topic_id . '.php', '&') . '#m' . $last_msg_id);
			}
			else
				AppContext::get_response()->redirect(url(HOST . SCRIPT . '?error=incomplete_t&id=' . $id_get, '', '&') . '#message_helper');
		}
		elseif (!empty($preview_topic) && !empty($id_get))
		{
			if (!ForumAuthorizationsService::check_authorizations($id_get)->write())
				AppContext::get_response()->redirect(url(HOST . SCRIPT . '?error=c_write&id=' . $id_get, '', '&') . '#message_helper');

			$tpl = new FileTemplate('forum/forum_post.tpl');

			$title = retrieve(POST, 'title', '', TSTRING_UNCHANGE);
			$subtitle = retrieve(POST, 'desc', '', TSTRING_UNCHANGE);
			$contents = retrieve(POST, 'contents', '', TSTRING_UNCHANGE);
			$question = retrieve(POST, 'question', '', TSTRING_UNCHANGE);

			$is_modo = ForumAuthorizationsService::check_authorizations($id_get)->moderation();
			$type = retrieve(POST, 'type', 0);

			if (!$is_modo)
				$type = ( $type == 1 || $type == 0 ) ? $type : 0;
			else
			{
				$tpl->put_all(array(
					'C_FORUM_POST_TYPE' => true,
					'CHECKED_NORMAL' => (($type == '0') ? 'checked="ckecked"' : ''),
					'CHECKED_POSTIT' => (($type == '1') ? 'checked="ckecked"' : ''),
					'CHECKED_ANNONCE' => (($type == '2') ? 'checked="ckecked"' : ''),
					'L_TYPE' => '* ' . $LANG['type'],
					'L_DEFAULT' => $LANG['default'],
					'L_POST_IT' => $LANG['forum_postit'],
					'L_ANOUNCE' => $LANG['forum_announce']
				));
			}

			//Liste des choix des sondages => 20 maxi
			$nbr_poll_field = 0;
			for ($i = 0; $i < 20; $i++)
			{
				$answer = retrieve(POST, 'a'.$i, '');
				if (!empty($answer))
				{
					$tpl->assign_block_vars('answers_poll', array(
						'ID' => $i,
						'ANSWER' => stripslashes($answer)
					));
					$nbr_poll_field++;
				}
			}
			for ($i = $nbr_poll_field; $i < 5; $i++) //On complète s'il y a moins de 5 réponses.
			{
				$tpl->assign_block_vars('answers_poll', array(
					'ID' => $i,
					'ANSWER' => ''
				));
				$nbr_poll_field++;
			}

			//Type de réponses du sondage.
			$poll_type = retrieve(POST, 'poll_type', 0);

			$vars_tpl = array(
				'FORUM_NAME' => $config->get_forum_name(),
				'TITLE' => $title,
				'DESC' => $subtitle,
				'CONTENTS' => $contents,
				'KERNEL_EDITOR' => $editor->display(),
				'POLL_QUESTION' => $question,
				'IDTOPIC' => 0,
				'SELECTED_SIMPLE' => ($poll_type == 0) ? 'checked="ckecked"' : '',
				'SELECTED_MULTIPLE' => ($poll_type == 1) ? 'checked="ckecked"' : '',
				'NO_DISPLAY_POLL' => 'true',
				'NBR_POLL_FIELD' => $nbr_poll_field,
				'DATE' => $LANG['on'] . ' ' . Date::to_format(Date::DATE_NOW, Date::FORMAT_DAY_MONTH_YEAR_HOUR_MINUTE),
				'CONTENTS_PREVIEW' => FormatingHelper::second_parse(stripslashes(FormatingHelper::strparse($contents))),
				'C_FORUM_PREVIEW_MSG' => true,
				'C_ADD_POLL_FIELD' => ($nbr_poll_field <= 19) ? true : false,
				'U_ACTION' => 'post.php' . url('?new=topic&amp;id=' . $id_get . '&amp;token=' . AppContext::get_session()->get_token()),
				'U_FORUM_CAT' => $forum_cats,
				'U_TITLE_T' => '<a href="post' . url('.php?new=topic&amp;id=' . $id_get) . '">' . $title . '</a>',
				'L_ACTION' => $LANG['forum_edit_subject'],
				'L_REQUIRE' => LangLoader::get_message('form.explain_required_fields', 'status-messages-common'),
				'L_REQUIRE_TEXT' => $LANG['require_text'],
				'L_REQUIRE_TITLE' => $LANG['require_title'],
				'L_REQUIRE_TITLE_POLL' => $LANG['require_title_poll'],
				'L_FORUM_INDEX' => $LANG['forum_index'],
				'L_TITLE' => $LANG['title'],
				'L_DESC' => $LANG['description'],
				'L_MESSAGE' => $LANG['message'],
				'L_SUBMIT' => $LANG['submit'],
				'L_PREVIEW' => $LANG['preview'],
				'L_RESET' => $LANG['reset'],
				'L_POLL' => $LANG['poll'],
				'L_OPEN_MENU_POLL' => $LANG['open_menu_poll'],
				'L_QUESTION' => $LANG['question'],
				'L_POLL_TYPE' => $LANG['poll_type'],
				'L_ANSWERS' => $LANG['answers'],
				'L_SINGLE' => $LANG['simple_answer'],
				'L_MULTIPLE' => $LANG['multiple_answer']
			);

			$tpl->put_all($vars_tpl);
			
			$tpl->put('forum_top', $tpl_top->display());
			$tpl->display();
			$tpl->put('forum_bottom', $tpl_bottom->display());
		}
		else
		{
			if (!ForumAuthorizationsService::check_authorizations($id_get)->write())
				AppContext::get_response()->redirect(url(HOST . SCRIPT . '?error=c_write&id=' . $id_get, '', '&') . '#message_helper');

			$tpl = new FileTemplate('forum/forum_post.tpl');

			if (ForumAuthorizationsService::check_authorizations($id_get)->moderation())
			{
				$tpl->put_all(array(
					'C_FORUM_POST_TYPE' => true,
					'CHECKED_NORMAL' => 'checked="ckecked"',
					'L_TYPE' => '* ' . $LANG['type'],
					'L_DEFAULT' => $LANG['default'],
					'L_POST_IT' => $LANG['forum_postit'],
					'L_ANOUNCE' => $LANG['forum_announce']
				));
			}

			//Liste des choix des sondages => 20 maxi
			$nbr_poll_field = 0;
			for ($i = 0; $i < 5; $i++)
			{
				$tpl->assign_block_vars('answers_poll', array(
					'ID' => $i,
					'ANSWER' => ''
				));
				$nbr_poll_field++;
			}

			$vars_tpl = array(
				'FORUM_NAME' => $config->get_forum_name(),
				'TITLE' => '',
				'DESC' => '',
				'SELECTED_SIMPLE' => 'checked="ckecked"',
				'IDTOPIC' => 0,
				'KERNEL_EDITOR' => $editor->display(),
				'NO_DISPLAY_POLL' => 'true',
				'NBR_POLL_FIELD' => $nbr_poll_field,
				'C_ADD_POLL_FIELD' => true,
				'U_ACTION' => 'post.php' . url('?new=topic&amp;id=' . $id_get . '&amp;token=' . AppContext::get_session()->get_token()),
				'U_FORUM_CAT' => $forum_cats,
				'U_TITLE_T' => '<a href="post' . url('.php?new=topic&amp;id=' . $id_get) . '" class="basic-button">' . $LANG['post_new_subject'] . '</a>',
				'L_ACTION' => $LANG['forum_new_subject'],
				'L_REQUIRE' => LangLoader::get_message('form.explain_required_fields', 'status-messages-common'),
				'L_REQUIRE_TEXT' => $LANG['require_text'],
				'L_REQUIRE_TITLE' => $LANG['require_title'],
				'L_REQUIRE_TITLE_POLL' => $LANG['require_title_poll'],
				'L_FORUM_INDEX' => $LANG['forum_index'],
				'L_TITLE' => $LANG['title'],
				'L_DESC' => $LANG['description'],
				'L_MESSAGE' => $LANG['message'],
				'L_SUBMIT' => $LANG['submit'],
				'L_PREVIEW' => $LANG['preview'],
				'L_RESET' => $LANG['reset'],
				'L_POLL' => $LANG['poll'],
				'L_OPEN_MENU_POLL' => $LANG['open_menu_poll'],
				'L_QUESTION' => $LANG['question'],
				'L_POLL_TYPE' => $LANG['poll_type'],
				'L_ANSWERS' => $LANG['answers'],
				'L_SINGLE' => $LANG['simple_answer'],
				'L_MULTIPLE' => $LANG['multiple_answer']
			);

			$tpl->put_all($vars_tpl);
			
			$tpl->put('forum_top', $tpl_top->display());
			$tpl->display();
			$tpl->put('forum_bottom', $tpl_bottom->display());
		}
	}
	elseif ($new_get === 'n_msg' && empty($error_get)) //Nouveau message
	{
		if (!ForumAuthorizationsService::check_authorizations($id_get)->write())
			AppContext::get_response()->redirect(url(HOST . SCRIPT . '?error=c_write&id=' . $id_get, '', '&') . '#message_helper');
		
		try {
			$topic = PersistenceContext::get_querier()->select_single_row_query('SELECT idcat, title, nbr_msg, last_user_id, last_msg_id, status
			FROM ' . PREFIX . 'forum_topics
			WHERE id=:id', array(
				'id' => $idt_get
			));
		} catch (RowNotFoundException $e) {
			$controller = new UserErrorController(LangLoader::get_message('error', 'status-messages-common'), $LANG['e_topic_lock_forum']);
			DispatchManager::redirect($controller);
		}

		$is_modo = ForumAuthorizationsService::check_authorizations($id_get)->moderation();

		//Mod anti Flood
		if ($check_time !== false)
		{
			$delay_expire = time() - ContentManagementConfig::load()->get_anti_flood_duration(); //On calcul la fin du delai.
			//Droit de flooder?
			if ($check_time >= $delay_expire && !ForumAuthorizationsService::check_authorizations()->flood()) //Ok
				AppContext::get_response()->redirect( url(HOST . SCRIPT . '?error=flood&id=' . $id_get . '&idt=' . $idt_get, '', '&') . '#message_helper');
		}

		$contents = retrieve(POST, 'contents', '', TSTRING_UNCHANGE);

		//Si le topic n'est pas vérrouilé on ajoute le message.
		if ($topic['status'] != 0 || $is_modo)
		{
			if (!empty($contents) && !empty($idt_get) && empty($update)) //Nouveau message.
			{
				$last_page = ceil( ($topic['nbr_msg'] + 1) / $config->get_number_messages_per_page() );
				$last_page_rewrite = ($last_page > 1) ? '-' . $last_page : '';
				$last_page = ($last_page > 1) ? '&pt=' . $last_page : '';

				if (!$config->are_multiple_posts_allowed() && $topic['last_user_id'] == AppContext::get_current_user()->get_id())
				{
					$last_page = ceil( $topic['nbr_msg'] / $config->get_number_messages_per_page() );
					$last_page_rewrite = ($last_page > 1) ? '-' . $last_page : '';
					$last_page = ($last_page > 1) ? '&pt=' . $last_page : '';
					
					$last_message_content = PersistenceContext::get_querier()->get_column_value(PREFIX . 'forum_msg', 'contents', 'WHERE id = :id', array('id' => $topic['last_msg_id']));
					
					$now = new Date();
					
					if (AppContext::get_current_user()->get_editor() == 'TinyMCE')
					{
						$new_content = FormatingHelper::second_parse($last_message_content) . '<br /><br />-------------------------------------------<br /><em>' . $LANG['edit_on'] . ' ' . $now->format(Date::FORMAT_DAY_MONTH_YEAR_HOUR_MINUTE_TEXT) . '</em><br /><br />' . FormatingHelper::second_parse($contents);
					}
					else
					{
						$new_content = FormatingHelper::second_parse($last_message_content) . '

-------------------------------------------
<em>' . $LANG['edit_on'] . ' ' . $now->format(Date::FORMAT_DAY_MONTH_YEAR_HOUR_MINUTE_TEXT) . '</em>

' . FormatingHelper::second_parse($contents);
					}
					
					$Forumfct->Update_msg($idt_get, $topic['last_msg_id'], FormatingHelper::unparse($new_content), $topic['last_user_id']); //Mise à jour du topic.
					$last_msg_id = $topic['last_msg_id'];
				}
				else
					$last_msg_id = $Forumfct->Add_msg($idt_get, $topic['idcat'], $contents, $topic['title'], $last_page, $last_page_rewrite);

				//Redirection après post.
				AppContext::get_response()->redirect('/forum/topic' . url('.php?id=' . $idt_get . $last_page, '-' . $idt_get . $last_page_rewrite . '.php', '&') . '#m' . $last_msg_id);
			}
			else
				AppContext::get_response()->redirect(url(HOST . SCRIPT . '?error=incomplete&id=' . $id_get . '&idt=' . $idt_get, '', '&') . '#message_helper');
		}
		else
			AppContext::get_response()->redirect(url(HOST . SCRIPT . '?error=locked&id=' . $id_get . '&idt=' . $idt_get, '', '&') . '#message_helper');
	}
	elseif ($new_get === 'msg' && empty($error_get)) //Edition d'un message/topic.
	{
		if (!ForumAuthorizationsService::check_authorizations($id_get)->write())
			AppContext::get_response()->redirect(url(HOST . SCRIPT . '?error=c_write&id=' . $id_get, '', '&') . '#message_helper');

		$id_m = retrieve(GET, 'idm', 0);
		$update = retrieve(GET, 'update', false);
		
		$id_first = PersistenceContext::get_querier()->get_column_value(PREFIX . "forum_msg", 'MIN(id)', 'WHERE idtopic = :idtopic', array('idtopic' => $idt_get));
		
		if (empty($id_get) || empty($id_first)) //Topic/message inexistant.
		{
			$controller = new UserErrorController(LangLoader::get_message('error', 'status-messages-common'), $LANG['e_unexist_topic_forum']);
			DispatchManager::redirect($controller);
		}
		
		try {
			$topic = PersistenceContext::get_querier()->select_single_row(PREFIX . 'forum_topics', array('title', 'subtitle', 'type', 'user_id', 'display_msg'), 'WHERE id=:id', array('id' => $idt_get));
		} catch (RowNotFoundException $e) {
			$error_controller = PHPBoostErrors::unexisting_element();
			DispatchManager::redirect($error_controller);
		}

		$is_modo = ForumAuthorizationsService::check_authorizations($id_get)->moderation();

		//Edition du topic complet
		if ($id_first == $id_m)
		{
			//User_id du message correspondant à l'utilisateur connecté => autorisation.
			$user_id_msg = PersistenceContext::get_querier()->get_column_value(PREFIX . "forum_msg", 'user_id', 'WHERE id = :id', array('id' => $id_m));
			$check_auth = false;
			if ($user_id_msg == AppContext::get_current_user()->get_id())
				$check_auth = true;
			elseif ($is_modo)
				$check_auth = true;

			if (!$check_auth)
			{
				$error_controller = PHPBoostErrors::user_not_authorized();
				DispatchManager::redirect($error_controller);
			}

			if ($update && $post_topic)
			{
				$title = retrieve(POST, 'title', '');
				$subtitle = retrieve(POST, 'desc', '');
				$contents = retrieve(POST, 'contents', '', TSTRING_UNCHANGE);
				$type = $is_modo ? retrieve(POST, 'type', 0) : 0;

				if (!empty($title) && !empty($contents))
				{
					$Forumfct->Update_topic($idt_get, $id_m, $title, $subtitle, $contents, $type, $user_id_msg); //Mise à jour du topic.

					//Mise à jour du sondage en plus du topic.
					$del_poll = retrieve(POST, 'del_poll', false);
					$question = retrieve(POST, 'question', '');
					if (!empty($question) && !$del_poll) //Enregistrement du sondage.
					{
						//Mise à jour si le sondage existe, sinon création.
						$check_poll = PersistenceContext::get_querier()->count(PREFIX . 'forum_poll', 'WHERE idtopic=:idtopic', array('idtopic' => $idt_get));

						$poll_type = retrieve(POST, 'poll_type', 0);
						$poll_type = ($poll_type == 0 || $poll_type == 1) ? $poll_type : 0;

						$answers = array();
						$nbr_votes = 0;
						for ($i = 0; $i < 20; $i++)
						{
							$answer = str_replace('|', '', retrieve(POST, 'a'.$i, ''));
							if (!empty($answer))
							{
								$answers[$i] = $answer;
								$nbr_votes++;
							}
						}

						if ($check_poll == 1) //Mise à jour.
							$Forumfct->Update_poll($idt_get, $question, $answers, $poll_type);
						elseif ($check_poll == 0) //Ajout du sondage.
							$Forumfct->Add_poll($idt_get, $question, $answers, $nbr_votes, $poll_type);
					}
					elseif ($del_poll && ForumAuthorizationsService::check_authorizations($id_get)->moderation()) //Suppression du sondage, admin et modo seulement biensûr...
						$Forumfct->Del_poll($idt_get);

					//Redirection après post.
					AppContext::get_response()->redirect('/forum/topic' . url('.php?id=' . $idt_get, '-' . $idt_get . '.php', '&'));
				}
				else
					AppContext::get_response()->redirect('/forum/post' . url('.php?new=msg&idm=' . $id_m . '&id=' . $id_get . '&idt=' . $idt_get . '&errore=incomplete_t', '', '&') . '#message_helper');
			}
			elseif (!empty($preview_topic))
			{
				$tpl = new FileTemplate('forum/forum_post.tpl');
				
				$title = retrieve(POST, 'title', '', TSTRING_UNCHANGE);
				$subtitle = retrieve(POST, 'desc', '', TSTRING_UNCHANGE);
				$contents = retrieve(POST, 'contents', '', TSTRING_UNCHANGE);
				$question = retrieve(POST, 'question', '', TSTRING_UNCHANGE);

				$type = retrieve(POST, 'type', 0);
				if (!$is_modo)
					$type = ($type == 1 || $type == 0) ? $type : 0;
				else
				{
					$tpl->put_all(array(
						'C_FORUM_POST_TYPE' => true,
						'CHECKED_NORMAL' => (($type == 0) ? 'checked="ckecked"' : ''),
						'CHECKED_POSTIT' => (($type == 1) ? 'checked="ckecked"' : ''),
						'CHECKED_ANNONCE' => (($type == 2) ? 'checked="ckecked"' : ''),
						'L_TYPE' => '* ' . $LANG['type'],
						'L_DEFAULT' => $LANG['default'],
						'L_POST_IT' => $LANG['forum_postit'],
						'L_ANOUNCE' => $LANG['forum_announce']
					));
				}

				//Liste des choix des sondages => 20 maxi
				$nbr_poll_field = 0;
				for ($i = 0; $i < 20; $i++)
				{
					$answer = retrieve(POST, 'a'.$i, '');
					if (!empty($anwser))
					{
						$tpl->assign_block_vars('answers_poll', array(
							'ID' => $i,
							'ANSWER' => stripslashes($anwser)
						));
						$nbr_poll_field++;
					}
				}
				for ($i = $nbr_poll_field; $i < 5; $i++) //On complète s'il y a moins de 5 réponses.
				{
					$tpl->assign_block_vars('answers_poll', array(
						'ID' => $i,
						'ANSWER' => ''
					));
					$nbr_poll_field++;
				}

				//Type de réponses du sondage.
				$poll_type = retrieve(POST, 'poll_type', 0);

				$vars_tpl = array(
					'FORUM_NAME' => $config->get_forum_name(),
					'TITLE' => $title,
					'DESC' => $subtitle,
					'CONTENTS' => $contents,
					'KERNEL_EDITOR' => $editor->display(),
					'POLL_QUESTION' => $question,
					'IDTOPIC' => 0,
					'SELECTED_SIMPLE' => 'checked="ckecked"',
					'NO_DISPLAY_POLL' => !empty($question) ? 'false' : 'true',
					'NBR_POLL_FIELD' => $nbr_poll_field,
					'SELECTED_SIMPLE' => ($poll_type == 0) ? 'checked="ckecked"' : '',
					'SELECTED_MULTIPLE' => ($poll_type == 1) ? 'checked="ckecked"' : '',
					'DATE' => $LANG['on'] . ' ' . Date::to_format(Date::DATE_NOW, Date::FORMAT_DAY_MONTH_YEAR_HOUR_MINUTE),
					'CONTENTS_PREVIEW' => FormatingHelper::second_parse(stripslashes(FormatingHelper::strparse($contents))),
					'C_FORUM_PREVIEW_MSG' => true,
					'C_DELETE_POLL' => ($is_modo) ? true : false, //Suppression d'un sondage => modo uniquement.
					'C_ADD_POLL_FIELD' => ($nbr_poll_field <= 19) ? true : false,
					'U_ACTION' => 'post.php' . url('?update=1&amp;new=msg&amp;id=' . $id_get . '&amp;idt=' . $idt_get . '&amp;idm=' . $id_m . '&amp;token=' . AppContext::get_session()->get_token()),
					'U_FORUM_CAT' => '<a href="forum' . url('.php?id=' . $id_get, '-' . $id_get . '.php') . '">' . $category->get_name() . '</a>',
					'U_TITLE_T' => '<a href="topic' . url('.php?id=' . $idt_get, '-' . $idt_get . '.php') . '">' . $title . '</a>',
					'L_ACTION' => $LANG['forum_edit_subject'],
					'L_REQUIRE' => LangLoader::get_message('form.explain_required_fields', 'status-messages-common'),
					'L_REQUIRE_TEXT' => $LANG['require_text'],
					'L_REQUIRE_TITLE' => $LANG['require_title'],
					'L_REQUIRE_TITLE_POLL' => $LANG['require_title_poll'],
					'L_FORUM_INDEX' => $LANG['forum_index'],
					'L_TITLE' => $LANG['title'],
					'L_DESC' => $LANG['description'],
					'L_MESSAGE' => $LANG['message'],
					'L_SUBMIT' => $LANG['update'],
					'L_PREVIEW' => $LANG['preview'],
					'L_RESET' => $LANG['reset'],
					'L_POLL' => $LANG['poll'],
					'L_OPEN_MENU_POLL' => $LANG['open_menu_poll'],
					'L_QUESTION' => $LANG['question'],
					'L_POLL_TYPE' => $LANG['poll_type'],
					'L_ANSWERS' => $LANG['answers'],
					'L_SINGLE' => $LANG['simple_answer'],
					'L_MULTIPLE' => $LANG['multiple_answer'],
					'L_DELETE_POLL' => $LANG['delete_poll']
				);

				$tpl->put_all($vars_tpl);
				
				$tpl->put('forum_top', $tpl_top->display());
				$tpl->display();
				$tpl->put('forum_bottom', $tpl_bottom->display());
			}
			else
			{
				$tpl = new FileTemplate('forum/forum_post.tpl');

				$contents = PersistenceContext::get_querier()->get_column_value(PREFIX . "forum_msg", 'contents', 'WHERE id = :id', array('id' => $id_first));

				//Gestion des erreurs à l'édition.
				$get_error_e = retrieve(GET, 'errore', '');
				if ($get_error_e == 'incomplete_t')
					$tpl->put('message_helper', MessageHelper::display($LANG['e_incomplete'], MessageHelper::NOTICE));

				if ($is_modo)
				{
					$tpl->put_all(array(
						'C_FORUM_POST_TYPE' => true,
						'CHECKED_NORMAL' => (($topic['type'] == '0') ? 'checked="ckecked"' : ''),
						'CHECKED_POSTIT' => (($topic['type'] == '1') ? 'checked="ckecked"' : ''),
						'CHECKED_ANNONCE' => (($topic['type'] == '2') ? 'checked="ckecked"' : ''),
						'L_TYPE' => '* ' . $LANG['type'],
						'L_DEFAULT' => $LANG['default'],
						'L_POST_IT' => $LANG['forum_postit'],
						'L_ANOUNCE' => $LANG['forum_announce']
					));
				}

				//Récupération des infos du sondage associé si il existe
				$poll = array('question' => '', 'answers' => '', 'votes' => '', 'type' => '');
				try {
					$poll = PersistenceContext::get_querier()->select_single_row(PREFIX . 'forum_poll', array('question', 'answers', 'votes', 'type'), 'WHERE idtopic=:id', array('id' => $idt_get));
				} catch (RowNotFoundException $e) {}
				
				$array_answer = explode('|', $poll['answers']);
				$array_votes = explode('|', $poll['votes']);

				$TmpTemplate = new FileTemplate('forum/forum_generic_results.tpl');
				$module_data_path = $TmpTemplate->get_pictures_data_path();
				
				//Affichage du lien pour changer le display_msg du topic et autorisation d'édition.
				if ($config->is_message_before_topic_title_displayed() && ($is_modo || AppContext::get_current_user()->get_id() == $topic['user_id']))
				{
					$img_display = $topic['display_msg'] ? 'fa-msg-not-display' : 'fa-msg-display';
					$tpl_bottom->put_all(array(
						'C_DISPLAY_MSG' => true,
						'ICON_DISPLAY_MSG' => $config->is_message_before_topic_title_icon_displayed() ? '<i class="fa ' . $img_display . '"></i>' : '',
						'L_EXPLAIN_DISPLAY_MSG_DEFAULT' => $topic['display_msg'] ? $config->get_message_when_topic_is_solved() : $config->get_message_when_topic_is_unsolved(),
						'L_EXPLAIN_DISPLAY_MSG' => $config->get_message_when_topic_is_unsolved(),
						'L_EXPLAIN_DISPLAY_MSG_BIS' => $config->get_message_when_topic_is_solved(),
						'U_ACTION_MSG_DISPLAY' => url('.php?msg_d=1&amp;id=' . $id_get . '&amp;token=' . AppContext::get_session()->get_token())
					));
				}

				//Liste des choix des sondages => 20 maxi
				$nbr_poll_field = 0;
				foreach ($array_answer as $key => $answer)
				{
					if (!empty($answer))
					{
						$nbr_votes = isset($array_votes[$key]) ? $array_votes[$key] : 0;
						$tpl->assign_block_vars('answers_poll', array(
							'ID' => $nbr_poll_field,
							'ANSWER' => stripslashes($answer),
							'NBR_VOTES' => $nbr_votes,
							'L_VOTES' => ($nbr_votes > 1) ? $LANG['votes'] : $LANG['vote']
						));
						$nbr_poll_field++;
					}
				}
				for ($i = $nbr_poll_field; $i < 5; $i++) //On complète s'il y a moins de 5 réponses.
				{
					$tpl->assign_block_vars('answers_poll', array(
						'ID' => $i,
						'ANSWER' => ''
					));
					$nbr_poll_field++;
				}
				
				$vars_tpl = array(
					'FORUM_NAME' => $config->get_forum_name(),
					'TITLE' => stripslashes($topic['title']),
					'DESC' => $topic['subtitle'],
					'CONTENTS' => FormatingHelper::unparse(stripslashes($contents)),
					'POLL_QUESTION' => !empty($poll['question']) ? stripslashes($poll['question']) : '',
					'SELECTED_SIMPLE' => 'checked="ckecked"',
					'MODULE_DATA_PATH' => $module_data_path,
					'IDTOPIC' => $idt_get,
					'KERNEL_EDITOR' => $editor->display(),
					'NBR_POLL_FIELD' => $nbr_poll_field,
					'NO_DISPLAY_POLL' => !empty($poll['question']) ? 'false' : 'true',
					'C_DELETE_POLL' => $is_modo, //Suppression d'un sondage => modo uniquement.
					'C_ADD_POLL_FIELD' => ($nbr_poll_field <= 19),
					'U_ACTION' => 'post.php' . url('?update=1&amp;new=msg&amp;id=' . $id_get . '&amp;idt=' . $idt_get . '&amp;idm=' . $id_m . '&amp;token=' . AppContext::get_session()->get_token()),
					'U_FORUM_CAT' => '<a href="forum' . url('.php?id=' . $id_get, '-' . $id_get . '.php') . '">' . $category->get_name() . '</a>',
					'U_TITLE_T' => '<a href="topic' . url('.php?id=' . $idt_get, '-' . $idt_get . '.php') . '">' . stripslashes($topic['title']) . '</a>',
					'L_ACTION' => $LANG['forum_edit_subject'],
					'L_REQUIRE' => LangLoader::get_message('form.explain_required_fields', 'status-messages-common'),
					'L_REQUIRE_TEXT' => $LANG['require_text'],
					'L_REQUIRE_TITLE' => $LANG['require_title'],
					'L_REQUIRE_TITLE_POLL' => $LANG['require_title_poll'],
					'L_FORUM_INDEX' => $LANG['forum_index'],
					'L_TITLE' => $LANG['title'],
					'L_DESC' => $LANG['description'],
					'L_MESSAGE' => $LANG['message'],
					'L_SUBMIT' => $LANG['update'],
					'L_PREVIEW' => $LANG['preview'],
					'L_RESET' => $LANG['reset'],
					'L_POLL' => $LANG['poll'],
					'L_OPEN_MENU_POLL' => $LANG['open_menu_poll'],
					'L_QUESTION' => $LANG['question'],
					'L_POLL_TYPE' => $LANG['poll_type'],
					'L_ANSWERS' => $LANG['answers'],
					'L_SINGLE' => $LANG['simple_answer'],
					'L_MULTIPLE' => $LANG['multiple_answer'],
					'L_DELETE_POLL' => $LANG['delete_poll']
				);

				//Type de réponses du sondage.
				if (isset($poll['type']) && $poll['type'] == '0')
				{
					$tpl->put_all(array(
						'SELECTED_SIMPLE' => 'checked="ckecked"'
					));
				}
				elseif (isset($poll['type']) && $poll['type'] == '1')
				{
					$tpl->put_all(array(
						'SELECTED_MULTIPLE' => 'checked="ckecked"'
					));
				}

				$tpl->put_all($vars_tpl);
				
				$tpl->put('forum_top', $tpl_top->display());
				$tpl->display();
				$tpl->put('forum_bottom', $tpl_bottom->display());
			}
		}
		//Sinon on édite simplement le message
		elseif ($id_m > $id_first)
		{
			//User_id du message correspondant à l'utilisateur connecté => autorisation.
			$user_id_msg = PersistenceContext::get_querier()->get_column_value(PREFIX . "forum_msg", 'user_id', 'WHERE id = :id', array('id' => $id_m));
			$check_auth = false;
			if ($user_id_msg == AppContext::get_current_user()->get_id())
				$check_auth = true;
			elseif ($is_modo)
				$check_auth = true;

			if (!$check_auth) //Non autorisé!
			{
				$error_controller = PHPBoostErrors::user_not_authorized();
				DispatchManager::redirect($error_controller);
			}

			if ($update && retrieve(POST, 'edit_msg', false))
			{
				$contents = retrieve(POST, 'contents', '', TSTRING_UNCHANGE);
				if (!empty($contents))
				{
					$nbr_msg_before = $Forumfct->Update_msg($idt_get, $id_m, $contents, $user_id_msg);

					//Calcul de la page sur laquelle se situe le message.
					$msg_page = ceil( ($nbr_msg_before + 1) / $config->get_number_messages_per_page() );
					$msg_page_rewrite = ($msg_page > 1) ? '-' . $msg_page : '';
					$msg_page = ($msg_page > 1) ? '&pt=' . $msg_page : '';

					//Redirection après édition.
					AppContext::get_response()->redirect('/forum/topic' . url('.php?id=' . $idt_get . $msg_page, '-' . $idt_get .  $msg_page_rewrite . '.php', '&') . '#m' . $id_m);
				}
				else
					AppContext::get_response()->redirect('/forum/post' . url('.php?new=msg&idm=' . $id_m . '&id=' . $id_get . '&idt=' . $idt_get . '&errore=incomplete', '', '&') . '#message_helper');
			}
			else
			{
				$tpl = new FileTemplate('forum/forum_edit_msg.tpl');
				

				$contents = PersistenceContext::get_querier()->get_column_value(PREFIX . "forum_msg", 'contents', 'WHERE id = :id', array('id' => $id_m));
				//Gestion des erreurs à l'édition.
				$get_error_e = retrieve(GET, 'errore', '');
				if ($get_error_e == 'incomplete')
					$tpl->put('message_helper', MessageHelper::display($LANG['e_incomplete'], MessageHelper::NOTICE));

				$vars_tpl = array(
					'P_UPDATE' => url('?update=1&amp;new=msg&amp;id=' . $id_get . '&amp;idt=' . $idt_get . '&amp;idm=' . $id_m),
					'FORUM_NAME' => $config->get_forum_name(),
					'DESC' => $topic['subtitle'],
					'CONTENTS' => FormatingHelper::unparse(stripslashes($contents)),
					'KERNEL_EDITOR' => $editor->display(),
					'U_ACTION' => 'post.php' . url('?update=1&amp;new=msg&amp;id=' . $id_get . '&amp;idt=' . $idt_get . '&amp;idm=' . $id_m . '&amp;token=' . AppContext::get_session()->get_token()),
					'U_FORUM_CAT' => '<a href="forum' . url('.php?id=' . $id_get, '-' . $id_get . '.php') . '">' . $category->get_name() . '</a>',
					'U_TITLE_T' => '<a href="topic' . url('.php?id=' . $idt_get, '-' . $idt_get . '.php') . '">' . stripslashes($topic['title']) . '</a>',
					'L_REQUIRE' => LangLoader::get_message('form.explain_required_fields', 'status-messages-common'),
					'L_REQUIRE_TEXT' => $LANG['require_text'],
					'L_FORUM_INDEX' => $LANG['forum_index'],
					'L_EDIT_MESSAGE' => $LANG['edit_message'],
					'L_MESSAGE' => $LANG['message'],
					'L_SUBMIT' => $LANG['update'],
					'L_PREVIEW' => $LANG['preview'],
					'L_RESET' => $LANG['reset'],
				);

				$tpl->put_all($vars_tpl);
				
				$tpl->put('forum_top', $tpl_top->display());
				$tpl->display();
				$tpl->put('forum_bottom', $tpl_bottom->display());
			}
		}
	}
	elseif (!empty($error_get) && (!empty($idt_get) || !empty($id_get)))
	{
		if (!empty($id_get) && !empty($idt_get) && ($error_get === 'flood' || $error_get === 'incomplete' || $error_get === 'locked'))
		{
			try {
				$topic = PersistenceContext::get_querier()->select_single_row(PREFIX . 'forum_topics', array('idcat', 'title', 'subtitle'), 'WHERE id=:id', array('id' => $idt_get));
			} catch (RowNotFoundException $e) {
				$error_controller = PHPBoostErrors::unexisting_element();
				DispatchManager::redirect($error_controller);
			}
			if (empty($topic['idcat'])) //Topic inexistant.
			{
				$controller = new UserErrorController(LangLoader::get_message('error', 'status-messages-common'), 
                    $LANG['e_unexist_topic_forum']);
                DispatchManager::redirect($controller);
			}

			$tpl = new FileTemplate('forum/forum_edit_msg.tpl');
			

			//Gestion erreur.
			switch ($error_get)
			{
				case 'flood':
				$errstr = $LANG['e_flood'];
				$type = MessageHelper::WARNING;
				break;
				case 'incomplete':
				$errstr = $LANG['e_incomplete'];
				$type = MessageHelper::NOTICE;
				break;
				case 'locked':
				$errstr = $LANG['e_topic_lock_forum'];
				$type = MessageHelper::WARNING;
				break;
				default:
				$errstr = '';
			}
			if (!empty($errstr))
				$tpl->put('message_helper', MessageHelper::display($errstr, $type));

			$vars_tpl = array(
				'P_UPDATE' => '',
				'FORUM_NAME' => $config->get_forum_name(),
				'DESC' => $topic['subtitle'],
				'KERNEL_EDITOR' => $editor->display(),
				'U_ACTION' => 'post.php' . url('?new=n_msg&amp;idt=' . $idt_get . '&amp;id=' . $id_get . '&amp;token=' . AppContext::get_session()->get_token()),
				'U_FORUM_CAT' => '<a href="forum' . url('.php?id=' . $id_get, '-' . $id_get . '.php') . '">' . $category->get_name() . '</a>',
				'U_TITLE_T' => '<a href="topic' . url('.php?id=' . $idt_get, '-' . $idt_get . '.php') . '">' . stripslashes($topic['title']) . '</a>',
				'L_ACTION' => $LANG['respond'],
				'L_REQUIRE' => LangLoader::get_message('form.explain_required_fields', 'status-messages-common'),
				'L_REQUIRE_TEXT' => $LANG['require_text'],
				'L_FORUM_INDEX' => $LANG['forum_index'],
				'L_EDIT_MESSAGE' => $LANG['respond'],
				'L_MESSAGE' => $LANG['message'],
				'L_SUBMIT' => $LANG['submit'],
				'L_PREVIEW' => $LANG['preview'],
				'L_RESET' => $LANG['reset']
			);
		}
		elseif (!empty($id_get) && ($error_get === 'c_locked' || $error_get === 'c_write' || $error_get === 'incomplete_t' || $error_get === 'false_t'))
		{
			$tpl = new FileTemplate('forum/forum_post.tpl');
			

			if (ForumAuthorizationsService::check_authorizations($id_get)->moderation())
			{
				$tpl->put_all(array(
					'C_FORUM_POST_TYPE' => true,
					'CHECKED_NORMAL' => 'checked="ckecked"',
					'L_TYPE' => '* ' . $LANG['type'],
					'L_DEFAULT' => $LANG['default'],
					'L_POST_IT' => $LANG['forum_postit'],
					'L_ANOUNCE' => $LANG['forum_announce']
				));
			}

			//Gestion erreur.
			switch ($error_get)
			{
				case 'flood_t':
				$errstr = $LANG['e_flood'];
				$type = MessageHelper::WARNING;
				break;
				case 'incomplete_t':
				$errstr = $LANG['e_incomplete'];
				$type = MessageHelper::NOTICE;
				break;
				case 'c_locked':
				$errstr = $LANG['e_cat_lock_forum'];
				$type = MessageHelper::WARNING;
				break;
				case 'c_write':
				$errstr = $LANG['e_cat_write'];
				$type = MessageHelper::WARNING;
				break;
				default:
				$errstr = '';
			}
			if (!empty($errstr))
				$tpl->put('message_helper', MessageHelper::display($errstr, $type));

			//Liste des choix des sondages => 20 maxi
			$nbr_poll_field = 0;
			for ($i = 0; $i < 5; $i++)
			{
				$tpl->assign_block_vars('answers_poll', array(
					'ID' => $i,
					'ANSWER' => ''
				));
				$nbr_poll_field++;
			}

			$vars_tpl = array(
				'FORUM_NAME' => $config->get_forum_name(),
				'TITLE' => '',
				'SELECTED_SIMPLE' => 'checked="checked"',
				'IDTOPIC' => 0,
				'KERNEL_EDITOR' => $editor->display(),
				'NO_DISPLAY_POLL' => 'true',
				'NBR_POLL_FIELD' => $nbr_poll_field,
				'C_ADD_POLL_FIELD' => true,
				'U_ACTION' => 'post.php' . url('?new=topic&amp;id=' . $id_get . '&amp;token=' . AppContext::get_session()->get_token()),
				'U_FORUM_CAT' => '<a href="forum' . url('.php?id=' . $id_get, '-' . $id_get . '.php') . '">' . $category->get_name() . '</a>',
				'U_TITLE_T' => '<a href="post' . url('.php?new=topic&amp;id=' . $id_get) . '" class="basic-button">' . $LANG['post_new_subject'] . '</a>',
				'L_ACTION' => $LANG['forum_new_subject'],
				'L_REQUIRE' => LangLoader::get_message('form.explain_required_fields', 'status-messages-common'),
				'L_REQUIRE_TEXT' => $LANG['require_text'],
				'L_REQUIRE_TITLE' => $LANG['require_title'],
				'L_REQUIRE_TITLE_POLL' => $LANG['require_title_poll'],
				'L_FORUM_INDEX' => $LANG['forum_index'],
				'L_TITLE' => $LANG['title'],
				'L_DESC' => $LANG['description'],
				'L_MESSAGE' => $LANG['message'],
				'L_SUBMIT' => $LANG['submit'],
				'L_PREVIEW' => $LANG['preview'],
				'L_RESET' => $LANG['reset'],
				'L_POLL' => $LANG['poll'],
				'L_OPEN_MENU_POLL' => $LANG['open_menu_poll'],
				'L_QUESTION' => $LANG['question'],
				'L_POLL_TYPE' => $LANG['poll_type'],
				'L_ANSWERS' => $LANG['answers'],
				'L_SINGLE' => $LANG['simple_answer'],
				'L_MULTIPLE' => $LANG['multiple_answer']
			);
		}
		else
		{
			$controller = PHPBoostErrors::unknow();
            DispatchManager::redirect($controller);
		}

		$tpl->put_all($vars_tpl);
		
		$tpl->put('forum_top', $tpl_top->display());
		$tpl->display();
		$tpl->put('forum_bottom', $tpl_bottom->display());
	}
	else
	{
		$controller = PHPBoostErrors::unknow();
        DispatchManager::redirect($controller);
	}
}
else
{
	$error_controller = PHPBoostErrors::user_not_authorized();
	DispatchManager::redirect($error_controller);
}

include('../kernel/footer.php');

?>
