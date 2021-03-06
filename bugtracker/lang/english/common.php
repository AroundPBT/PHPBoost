<?php
/*##################################################
 *                              common.php
 *                            -------------------
 *   begin                : November 09, 2012
 *   copyright            : (C) 2012 Julien BRISWALTER
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

####################################################
# English                                          #
####################################################
 
//Module title
$lang['module_title'] = 'Bugtracker';

//Notice
$lang['notice.no_one'] = 'No one';
$lang['notice.none'] = 'None';
$lang['notice.none_e'] = 'None';
$lang['notice.no_bug'] = 'No bug declared';
$lang['notice.no_bug_solved'] = 'No bug solved';
$lang['notice.no_bug_fixed'] = 'No bug fixed in this version';
$lang['notice.no_bug_in_progress'] = 'No bug being corrected in this version';
$lang['notice.no_bug_matching_filter'] = 'No bug matching the selected filter';
$lang['notice.no_bug_matching_filters'] = 'No bug matching the selected filters';
$lang['notice.no_version_roadmap'] = 'Please add at least one version in the configuration to display the roadmap.';
$lang['notice.no_history'] = 'This bug has no history';
$lang['notice.contents_update'] = 'Contents update';
$lang['notice.new_comment'] = 'New comment';
$lang['notice.reproduction_method_update'] = 'Reproduction method update';
$lang['notice.not_defined'] = 'Not defined';
$lang['notice.not_defined_e_date'] = 'Date not defined';

//Actions
$lang['actions'] = 'Actions';
$lang['actions.add'] = 'New bug';
$lang['actions.history'] = 'History';
$lang['actions.change_status'] = 'Change bug status';
$lang['actions.confirm.del_default_value'] = 'Delete this default value?';
$lang['actions.confirm.del_filter'] = 'Delete this filter?';

//Titles
$lang['titles.add'] = 'New bug';
$lang['titles.add_version'] = 'Add a new version';
$lang['titles.add_type'] = 'Add a new bug type';
$lang['titles.add_category'] = 'Add a new category';
$lang['titles.edit'] = 'Bug edition';
$lang['titles.change_status'] = 'Status modification of bug';
$lang['titles.delete'] = 'Bug suppression';
$lang['titles.history'] = 'History of bug';
$lang['titles.detail'] = 'Bug';
$lang['titles.roadmap'] = 'Roadmap';
$lang['titles.bugs_infos'] = 'Bug\'s informations';
$lang['titles.stats'] = 'Statistics';
$lang['titles.bugs_treatment_state'] = 'Bug\'s treatment state';
$lang['titles.versions'] = 'Versions';
$lang['titles.types'] = 'Types';
$lang['titles.categories'] = 'Categories';
$lang['titles.priorities'] = 'Priorities';
$lang['titles.severities'] = 'Severities';
$lang['titles.admin.authorizations.manage'] = 'Manage authorizations';
$lang['titles.admin.module_config'] = 'Bugtracker module configuration';
$lang['titles.admin.module_authorizations'] = 'Bugtracker module authorizations configuration';
$lang['titles.choose_version'] = 'Version to display';
$lang['titles.solved'] = 'Fixed bugs';
$lang['titles.unsolved'] = 'Unsolved bugs';
$lang['titles.contents_value_title'] = 'Request default description';
$lang['titles.contents_value'] = 'Default description';
$lang['titles.filter'] = 'Filter';
$lang['titles.filters'] = 'Filters';
$lang['titles.informations'] = 'Informations';
$lang['titles.version_informations'] = 'Version Informations';

//Labels
$lang['labels.fields.id'] = 'ID';
$lang['labels.fields.submit_date'] = 'Detected on';
$lang['labels.fields.fix_date'] = 'Fixed on';
$lang['labels.fields.status'] = 'Status';
$lang['labels.fields.type'] = 'Type';
$lang['labels.fields.category'] = 'Category';
$lang['labels.fields.reproductible'] = 'Reproductible';
$lang['labels.fields.reproduction_method'] = 'Reproduction method';
$lang['labels.fields.severity'] = 'Level';
$lang['labels.fields.priority'] = 'Priority';
$lang['labels.fields.progress'] = 'Progress';
$lang['labels.fields.detected_in'] = 'Detected in version';
$lang['labels.fields.fixed_in'] = 'Fixed in version';
$lang['labels.fields.assigned_to_id'] = 'Assigned to';
$lang['labels.fields.updater_id'] = 'Updated by';
$lang['labels.fields.update_date'] = 'Updated on';
$lang['labels.fields.updated_field'] = 'Updated field';
$lang['labels.fields.old_value'] = 'Old value';
$lang['labels.fields.new_value'] = 'New value';
$lang['labels.fields.change_comment'] = 'Comment';
$lang['labels.fields.version'] = 'Version';
$lang['labels.fields.version_detected'] = 'Detected version';
$lang['labels.fields.version_fixed'] = 'Fixed version';
$lang['labels.fields.version_release_date'] = 'Release date';
$lang['labels.page'] = 'Page';
$lang['labels.color'] = 'Color';
$lang['labels.number_fixed'] = 'Corrected bugs number';
$lang['labels.number_in_progress'] = 'Bugs being corrected number';
$lang['labels.top_posters'] = 'Top posters';
$lang['labels.login'] = 'Login';
$lang['labels.default'] = 'Default';
$lang['labels.default_value'] = 'Default value';
$lang['labels.del_default_value'] = 'Delete default value';
$lang['labels.type_mandatory'] = 'Section <b>Type</b> mandatory?';
$lang['labels.category_mandatory'] = 'Section <b>Category</b> mandatory?';
$lang['labels.severity_mandatory'] = 'Section <b>Severity</b> mandatory?';
$lang['labels.priority_mandatory'] = 'Section <b>Priority</b> mandatory?';
$lang['labels.detected_in_mandatory'] = 'Section <b>Detected in version</b> mandatory?';
$lang['labels.detected'] = 'Detected';
$lang['labels.detected_in'] = 'Detected in';
$lang['labels.fixed'] = 'Fixed';
$lang['labels.fix_bugs_per_version'] = 'Fixed bugs number per version';
$lang['labels.not_yet_fixed'] = 'Not yet fixed';
$lang['labels.alert_fix'] = 'Fix alert';
$lang['labels.alert_delete'] = 'Delete alert';
$lang['labels.save_filters'] = 'Save filters';
$lang['labels.version_name'] = 'Version name';

//Status
$lang['status.new'] = 'New';
$lang['status.pending'] = 'Pending';
$lang['status.assigned'] = 'Assigned';
$lang['status.in_progress'] = 'In progress';
$lang['status.fixed'] = 'Fixed';
$lang['status.reopen'] = 'Reopen';
$lang['status.rejected'] = 'Rejected';

//Explainations
$lang['explain.contents'] = 'Useful details to treat the bug';
$lang['explain.roadmap'] = 'Displays the fixed bug list for each version. Displayed if there is at least one version in the list.';
$lang['explain.type'] = 'Demands type. Examples : Anomaly, Evolution...';
$lang['explain.category'] = 'Demands category. Examples : Kernel, Module...';
$lang['explain.severity'] = 'Demands severity. Examples : Minor, Major, Critical...';
$lang['explain.priority'] = 'Demands priority. Examples : Low, Normal, High...';
$lang['explain.version'] = 'Product version list.';
$lang['explain.remarks'] = 'Remarks : <br />
- If the table is empty, this option will not be visible on the post bug page<br />
- If the table contains only one value, this option will not be visible too and will automatically be assigned to the bug<br /><br />';
$lang['explain.contents_value'] = 'Enter the default description to display for a new bug below. Leave empty if you don\'t want to fill the description.';
$lang['explain.delete_comment'] = 'Optional. Permits to add a comment in the Private Message of bug deleting.';
$lang['explain.change_status_select_fix_version'] = 'You can select a version to put the bug in the roadmap.';
$lang['explain.change_status_comments_message'] = 'Optional. Permits to comment the bug and add it in the Private Message if sending is enabled.';

//PM
$lang['pm.with_comment'] = '<br />
<br />
Comment:<br />
:comment';
$lang['pm.edit_fields'] = '<br />
<br />
:fields';
$lang['pm.bug_link'] = '<br />
<br />
<a href=":link">Bug link</a>';

$lang['pm.assigned.title'] = '[Bugtracker] The bug #:id has been assigned to you';
$lang['pm.assigned.contents'] = ':author assigned you the bug #:id.';

$lang['pm.comment.title'] = '[Bugtracker] A new comment has been posted for the bug #:id';
$lang['pm.comment.contents'] = ':author add a comment to the bug #:id.';

$lang['pm.edit.title'] = '[Bugtracker] The bug #:id has been updated';
$lang['pm.edit.contents'] = ':author has updated the following fields in the bug #:id :';

$lang['pm.fixed.title'] = '[Bugtracker] The bug #:id has been fixed';
$lang['pm.fixed.contents'] = ':author has fixed the bug #:id.';

$lang['pm.reopen.title'] = '[Bugtracker] The bug #:id has been reopen';
$lang['pm.reopen.contents'] = ':author has reopened the bug #:id.';

$lang['pm.rejected.title'] = '[Bugtracker] The bug #:id has been rejected';
$lang['pm.rejected.contents'] = ':author has rejected the bug #:id.';

$lang['pm.pending.title'] = '[Bugtracker] The bug #:id has been put on hold';
$lang['pm.pending.contents'] = ':author has put on hold the bug #:id.';

$lang['pm.in_progress.title'] = '[Bugtracker] The bug #:id is in progress';
$lang['pm.in_progress.contents'] = ':author has put the bug #:id in progress.';

$lang['pm.delete.title'] = '[Bugtracker] The bug #:id has been deleted';
$lang['pm.delete.contents'] = ':author delete the bug #:id.';

//Configuration
$lang['config.rejected_bug_color_label'] = 'Rejected bug line color';
$lang['config.fixed_bug_color_label'] = 'Fixed bug line color';
$lang['config.enable_roadmap'] = 'Enable roadmap';
$lang['config.enable_stats'] = 'Enable statistics';
$lang['config.enable_stats_top_posters'] = 'Display top posters list';
$lang['config.stats_top_posters_number'] = 'Number user displayed';
$lang['config.progress_bar'] = 'Progress bar';
$lang['config.enable_progress_bar'] = 'Enable progress bar';
$lang['config.status.new'] = 'Percent of a New bug';
$lang['config.status.pending'] = 'Percent of a Pending bug';
$lang['config.status.assigned'] = 'Percent of an Assigned bug';
$lang['config.status.in_progress'] = 'Percent of a bug In progress';
$lang['config.status.fixed'] = 'Percent of a bug Fixed';
$lang['config.status.reopen'] = 'Percent of a bug Reopen';
$lang['config.status.rejected'] = 'Percent of a bug Rejected';
$lang['config.admin_alerts'] = 'Admin alerts';
$lang['config.enable_admin_alerts'] = 'Enable admin alerts';
$lang['config.admin_alerts_levels'] = 'Bug severity to send the alert';
$lang['config.admin_alerts_fix_action'] = 'Action when fixing a bug';
$lang['config.pm'] = 'Private Messages';
$lang['config.enable_pm'] = 'Enable Private Messages (PM) send';
$lang['config.enable_pm.comment'] = 'Send a PM when a new comment is posted';
$lang['config.enable_pm.in_progress'] = 'Send a PM when the status become In progress';
$lang['config.enable_pm.fix'] = 'Send a PM when a bug is fixed';
$lang['config.enable_pm.pending'] = 'Send a PM when a bug is pending';
$lang['config.enable_pm.assign'] = 'Send a PM when a bug is assigned';
$lang['config.enable_pm.edit'] = 'Send a PM when a bug is edited';
$lang['config.enable_pm.reject'] = 'Send a PM when a bug is rejected';
$lang['config.enable_pm.reopen'] = 'Send a PM when a bug is reopen';
$lang['config.enable_pm.delete'] = 'Send a PM when a bug is deleted';
$lang['config.delete_parameter.type'] = 'Deleting a type';
$lang['config.delete_parameter.category'] = 'Deleting a category';
$lang['config.delete_parameter.version'] = 'Deleting a version';
$lang['config.delete_parameter.description.type'] = 'You are about to delete a type of bug. Two solutions are available to you. You can either assign another type to all the bugs associated with this type, or remove all bugs associated with this type. If no action is selected on this page, the type of bug will be removed and the bugs stored (by removing their type). <strong>Note that this action is irreversible!</ strong>';
$lang['config.delete_parameter.description.category'] = 'You are about to delete a category. Two solutions are available to you. You can either assign another category to all the bugs associated with this category, or remove all bugs associated with this category. If no action is selected on this page, the category of bug will be removed and the bugs stored (by removing their category). <strong>Note that this action is irreversible!</ strong>';
$lang['config.delete_parameter.description.version'] = 'You are about to delete a version. Two solutions are available to you. You can either assign another version to all the bugs associated with this version, or remove all bugs associated with this version. If no action is selected on this page, the version of bug will be removed and the bugs stored (by removing their version). <strong>Note that this action is irreversible!</ strong>';
$lang['config.delete_parameter.move_into_another'] = 'Move associated bugs in:';
$lang['config.delete_parameter.parameter_and_content.type'] = 'Delete type of bug and all associated bugs';
$lang['config.delete_parameter.parameter_and_content.category'] = 'Delete category and all associated bugs';
$lang['config.delete_parameter.parameter_and_content.version'] = 'Delete version and all associated bugs';
$lang['config.display_type_column'] = 'Display <b>Type</b> column in tables';
$lang['config.display_category_column'] = 'Display <b>Category</b> column in tables';
$lang['config.display_priority_column'] = 'Display <b>Priority</b> column in tables';
$lang['config.display_detected_in_column'] = 'Display <b>Detected in</b> column in tables';

//Authorizations
$lang['config.auth.read'] = 'Permission to display the bugs list';
$lang['config.auth.create'] = 'Permission to post a bug';
$lang['config.auth.create_advanced'] = 'Advanced permission to post a bug';
$lang['config.auth.create_advanced_explain'] = 'Permits to choose the severity and the priority of the bug';
$lang['config.auth.moderate'] = 'Permission to moderate the Bugtracker';

//Errors
$lang['error.e_unexist_bug'] = 'This bug does not exist';
$lang['error.e_unexist_parameter'] = 'This parameter does not exist';
$lang['error.e_unexist_type'] = 'This type does not exist';
$lang['error.e_unexist_category'] = 'This category does not exist';
$lang['error.e_unexist_severity'] = 'This severity does not exist';
$lang['error.e_unexist_priority'] = 'This priority does not exist';
$lang['error.e_unexist_version'] = 'This version does not exist';
$lang['error.e_already_rejected_bug'] = 'This bug is already rejected';
$lang['error.e_already_reopen_bug'] = 'This bug is already reopen';
$lang['error.e_already_fixed_bug'] = 'This bug is already fixed';
$lang['error.e_already_pending_bug'] = 'This bug is already pending';
$lang['error.e_status_not_changed'] = 'The bug status has not changed';

//Success
$lang['success.add'] = 'The bug #:id has been committed';
$lang['success.edit'] = 'The bug #:id has been updated';
$lang['success.new'] = 'The bug has been set to <b>New</b>';
$lang['success.fixed'] = 'The bug has been fixed';
$lang['success.in_progress'] = 'The bug resolution is in progress';
$lang['success.delete'] = 'The bug #:id has been deleted';
$lang['success.rejected'] = 'The bug has been rejected';
$lang['success.reopen'] = 'The bug has been reopen';
$lang['success.assigned'] = 'The bug has been assigned';
$lang['success.pending'] = 'The bug has been put on hold';
$lang['success.add.filter'] = 'The filter has been added';
?>
