<?php
/*##################################################
 *                            install.php
 *                            -------------------
 *   begin                : Ferbruary 21, 2013
 *   copyright            : (C) 2013 Patrick DUBEAU
 *   email                : daaxwizeman@gmail.com
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


#####################################################
#                      French			    #
####################################################

$lang = array();

$lang['default.category.name'] = 'Cat�gorie de test';
$lang['default.category.description'] = 'Articles de d�monstration';
$lang['default.article.title'] = 'D�buter avec le module Articles';
$lang['default.article.description'] = '';
$lang['default.article.contents'] = 'Ce bref article va vous donner quelques conseils simples pour prendre en main ce module.<br /> 
<br /> 
<ul class="formatter-ul">
<li class="formatter-li">Pour configurer votre module, <a href="' . ArticlesUrlBuilder::configuration()->relative() . '">cliquez ici</a> 
</li><li class="formatter-li">Pour ajouter des cat�gories : <a href="' . ArticlesUrlBuilder::add_category()->relative() . '">cliquez ici</a> (les cat�gories et sous cat�gories sont � l\'infini) 
</li><li class="formatter-li">Pour ajouter un article : <a href="' . ArticlesUrlBuilder::add_article()->relative() . '">cliquez ici</a>
</li></ul> 
<ul class="formatter-ul">
<li class="formatter-li">Pour mettre en page vos articles, vous pouvez utiliser le langage bbcode ou l\'�diteur WYSIWYG (cf cet <a href="http://www.phpboost.com/articles/articles-6-61+mise-en-page-du-contenu.php">article</a>)<br /> 
</li></ul><br /> 
<br /> 
Pour en savoir plus, n\'h�sitez pas � consulter la documentation du module sur le site de <a href="http://www.phpboost.com">PHPBoost</a>.<br /> 
<br /> 
<br /> 
Bonne utilisation de ce module.';

?>
