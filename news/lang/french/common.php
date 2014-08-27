<?php
/*##################################################
 *		                         common.php
 *                            -------------------
 *   begin                : February 20, 2013
 *   copyright            : (C) 2013 Kevin MASSY
 *   email                : kevin.massy@phpboost.com
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
 #                     French                       #
 ####################################################

$lang['module_config_title'] = 'Configuration des news';

$lang['news'] = 'News';
$lang['news.add'] = 'Ajouter une news';
$lang['news.edit'] = 'Modifier une news';
$lang['news.pending'] = 'News en attente';
$lang['news.manage'] = 'G�rer les news';
$lang['news.management'] = 'Gestion des news';

$lang['news.message.no_items'] = 'Aucune news n\'est disponible pour le moment';

$lang['news.seo.description.root'] = 'Toutes les news du site :site.';
$lang['news.seo.description.tag'] = 'Toutes les news sur le sujet :subject.';
$lang['news.seo.description.pending'] = 'Toutes les news en attente.';

$lang['news.form.name'] = 'Nom de la news';
$lang['news.form.rewrited_name'] = 'Nom de votre news dans l\'url';
$lang['news.form.rewrited_name.description'] = 'Contient uniquement des lettres minuscules, des chiffres et des traits d\'union.';
$lang['news.form.rewrited_name.personalize'] = 'Personnaliser le nom de la news dans l\'url';
$lang['news.form.short_contents'] = 'Condens� de la news';
$lang['news.form.short_contents.description'] = 'Pour que le condens� de la news soit affich�, veuillez activer l\'option dans la configuration du module';
$lang['news.form.short_contents.enabled'] = 'Personnaliser le condens� de la news';
$lang['news.form.short_contents.enabled.description'] = 'Si non coch�, la news est automatiquement coup�e � :number caract�res et le formatage du texte supprim�.';
$lang['news.form.approved.not'] = 'Gard�e en brouillon';
$lang['news.form.approved.now'] = 'Publi�e';
$lang['news.form.approved.date'] = 'Publi�e en diff�r�';
$lang['news.form.top_list'] = 'Placer la news en t�te de liste';
$lang['news.form.keywords.description'] = 'Vous permet d\'ajouter des mots cl�s � votre news';
$lang['news.form.picture'] = 'Image de la news';
$lang['news.form.contribution.explain'] = 'Vous n\'�tes pas autoris� � cr�er une news, cependant vous pouvez en proposer une.';

//Administration
$lang['admin.config.number_news_per_page'] = 'Nombre de news par page';
$lang['admin.config.number_columns_display_news'] = 'Nombre de colonnes pour afficher les news';
$lang['admin.config.display_condensed'] = 'Afficher le condens� de la news et non la news enti�re';
$lang['admin.config.number_character_to_cut'] = 'Nombre de caract�res pour couper la news';
$lang['admin.config.news_suggestions_enabled'] = 'Activer l\'affichage des suggestions';
$lang['admin.config.display_author'] = 'Activer l\'affichage de l\'auteur';
$lang['admin.config.display_type'] = 'Type d\'affichage des news';
$lang['admin.config.display_type.block'] = 'Affichage en bloc';
$lang['admin.config.display_type.list'] = 'Affichage en liste';

//Feed name
$lang['feed.name'] = 'Actualit�s';
?>