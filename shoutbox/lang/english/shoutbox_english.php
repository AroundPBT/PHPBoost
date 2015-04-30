<?php
/*##################################################
 *                              shoutobx_english.php
 *                            -------------------
 *   begin                : July 29, 2005
 *   copyright            : (C) 2005 Viarre R�gis
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


 ####################################################
#                                                           English                                                                             #
 ####################################################

//Admin
$LANG['shoutbox_max_msg'] = 'Maximum number of message to keep';
$LANG['shoutbox_max_msg_explain'] = 'Delete each days, set to -1 to deactivate';
$LANG['shoutbox_config'] = 'Shoutbox configuration';
$LANG['admin.authorizations'] = 'Permissions';
$LANG['auth_read'] = 'Permission to display the shoutbox';
$LANG['auth_write'] = 'Write permission';
$LANG['auth_moderation'] = 'Moderation permission';
$LANG['shoutbox_refresh_delay'] = 'Delay between discussion automatic refresh';
$LANG['shoutbox_refresh_delay_explain'] = 'Set 0 to disable';

$LANG['title_shoutbox'] = 'Shoutbox';
$LANG['archives'] = 'Archives';

$LANG['e_unauthorized'] = 'You aren\'t authorized to post !';
$LANG['e_flood'] = 'You can\'t post yet, retry in a few moments';
$LANG['e_l_flood'] = 'You can\'t post more than %d link(s) in your message';
$LANG['e_link_pseudo'] = 'Your login can\'t contain weblinks';
$LANG['e_incomplete'] = 'All the required fields must be filled !';
$LANG['e_readonly'] = 'You can\'t perform this action because you have been set in read only status !';
?>