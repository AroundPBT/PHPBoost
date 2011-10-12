<?php
/*##################################################
 *                          OnlineModuleMiniMenu.class.php
 *                            -------------------
 *   begin                : October 08, 2011
 *   copyright            : (C) 2011 K�vin MASSY
 *   email                : soldier.weasel@gmail.com
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

class OnlineModuleMiniMenu extends ModuleMiniMenu
{    
    public function get_default_block()
    {
    	return self::BLOCK_POSITION__RIGHT;
    }

	public function display($tpl = false)
    {
    	if (strpos(SCRIPT, '/online/online.php') === false)
	    {
	        global $LANG, $Sql;
	
	    	//Chargement de la langue du module.
	    	load_module_lang('online');
	
	    	$tpl = new FileTemplate('online/online_mini.tpl');
		    MenuService::assign_positions_conditions($tpl, $this->get_block());
	    
		    //On compte les visiteurs en ligne dans la bdd, en prenant en compte le temps max de connexion.
	    	list($count_visit, $count_member, $count_modo, $count_admin) = array(0, 0, 0, 0);
	
	    	$i = 0;
	    	$array_class = array('member', 'modo', 'admin');
	    	$result = $Sql->query_while("SELECT s.user_id, s.level, s.session_time, m.user_groups, m.login
	    	FROM " . DB_TABLE_SESSIONS . " s
	    	LEFT JOIN " . DB_TABLE_MEMBER . " m ON m.user_id = s.user_id
	    	WHERE s.session_time > '" . (time() - SessionsConfig::load()->get_active_session_duration()) . "'
	    	ORDER BY " . OnlineConfig::load()->get_display_order(), __LINE__, __FILE__); //4 Membres enregistr�s max.
	    	while ($row = $Sql->fetch_assoc($result))
	    	{
	    		if ($i < OnlineConfig::load()->get_number_member_displayed())
	    		{
	    			//Visiteurs non pris en compte.
	    			if ($row['level'] !== '-1')
	    			{
	    				$group_color = User::get_group_color($row['user_groups'], $row['level']);
						$tpl->assign_block_vars('online', array(
	    					'USER' => '<a href="'. UserUrlBuilder::profile($row['user_id'])->absolute() .'" class="' . $array_class[$row['level']] . '"' . (!empty($group_color) ? ' style="color:' . $group_color . '"' : '') . '>' . TextHelper::wordwrap_html($row['login'], 19) . '</a><br />'
	    				));
	    				$i++;
	    			}
	    		}
	
	    		switch ($row['level'])
	    		{
	    			case '-1':
	    			$count_visit++;
	    			break;
	    			case '0':
	    			$count_member++;
	    			break;
	    			case '1':
	    			$count_modo++;
	    			break;
	    			case '2':
	    			$count_admin++;
	    			break;
	    		}
	    	}
	    	$Sql->query_close($result);
	
	
	    	$count_visit = (empty($count_visit) && empty($count_member) && empty($count_modo) && empty($count_admin)) ? '1' : $count_visit;
	
	    	$total = $count_visit + $count_member + $count_modo + $count_admin;
	    	$total_member = $count_member + $count_modo + $count_admin;
	
	    	$member_online = $LANG['member_s'] . ' ' . strtolower($LANG['online']);
	    	$more = '<br /><a href="../online/online.php' . SID . '" title="' . $member_online . '">' . $member_online . '</a><br />';
	    	$more = ($total_member > OnlineConfig::load()->get_number_member_displayed()) ? $more : ''; //Plus de 4 membres connect�s.
	
	    	$l_guest = ($count_visit > 1) ? $LANG['guest_s'] : $LANG['guest'];
	    	$l_member = ($count_member > 1) ? $LANG['member_s'] : $LANG['member'];
	    	$l_modo = ($count_modo > 1) ? $LANG['modo_s'] : $LANG['modo'];
	    	$l_admin = ($count_admin > 1) ? $LANG['admin_s'] : $LANG['admin'];
	
	    	$tpl->put_all(array(
	    		'VISIT' => $count_visit,
	    		'USER' => $count_member,
	    		'MODO' => $count_modo,
	    		'ADMIN' => $count_admin,
	    		'MORE' => $more,
	    		'TOTAL' => $total,
	    		'L_VISITOR' => $l_guest,
	    		'L_USER' => $l_member,
	    		'L_MODO' => $l_modo,
	    		'L_ADMIN' => $l_admin,
	    		'L_ONLINE' => $LANG['online'],
	    		'L_TOTAL' => $LANG['total']
	    	));
			return $tpl->render();
	    }
	
	    return '';
    }
}
?>