/*##################################################
 *                                bbcode.js
 *                            -------------------
 *   begin                : August 01, 2005
 *   copyright          : (C) 2005 Viarre R�gis
 *   email                : crowkait@phpboost.com
 *
 *
###################################################
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
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

function textarea_resize(id, px, type)
{
	var textarea = document.getElementById(id);
	if( type == 'height' )
	{
		var current_height = parseInt(textarea.style.height) ? parseInt(textarea.style.height) : 300;
		var new_height = current_height + px;
		
		if( new_height > 40 )
			textarea.style.height = new_height + "px";
	}
	else
	{
		var current_width = parseInt(textarea.style.width) ? parseInt(textarea.style.width) : 150;
		var new_width = current_width + px;
		
		if( new_width > 40 )
			textarea.style.width = new_width + "px";
	}
	
	return false;
}

//Insertion dans le champs.
function simple_insert(open_balise, close_balise, field)
{
	var textarea = document.getElementById(field);
	var scroll = textarea.scrollTop;
	
	if( close_balise != "" && close_balise != "smile" )
		textarea.value += '[' + open_balise + '][/' + close_balise + ']';
	else if( close_balise == "smile" )
		textarea.value += ' ' + open_balise + ' ';
		
	textarea.focus();
	textarea.scrollTop = scroll;
	return;
}

//R�cup�ration de la s�lection sur netscape, ajout des balises autour.
function netscape_sel(target, open_balise, close_balise)
{
	var sel_length = target.textLength;
	var sel_start = target.selectionStart;
	var sel_end = target.selectionEnd;
	var scroll = target.scrollTop; //Position verticale.
	
	if( sel_end == 1 || sel_end == 2 )
	{
		sel_end = sel_length;
	}

	var string_start = (target.value).substring(0, sel_start);
	var selection = (target.value).substring(sel_start, sel_end);
	var string_end = (target.value).substring(sel_end, sel_length);

	if( close_balise != "" && selection == "" && close_balise != "smile" )
	{
		target.value = string_start + open_balise + close_balise + string_end;
		target.setSelectionRange(string_start.length + open_balise.length, target.value.length - string_end.length - close_balise.length);
		target.focus();
	}
	else if( close_balise == "smile" )
	{
		target.value = string_start + selection + ' ' + open_balise + ' ' + string_end;	
		target.setSelectionRange(string_start.length + open_balise.length + 2, target.value.length - string_end.length);	
		target.focus();
	}
	else
	{
		target.value = string_start + open_balise + selection + close_balise + string_end;
		target.setSelectionRange(string_start.length + open_balise.length, target.value.length - string_end.length - close_balise.length);
		target.focus();
	}
	
	target.scrollTop = scroll; //Remet � la bonne position le textarea.

	return;
}

//R�cup�ration de la s�lection sur IE, ajout des balises autour.
function ie_sel(target, open_balise, close_balise)
{
	selText = false;
	var scroll = target.scrollTop; //Position verticale.
	
	selection = document.selection.createRange().text; // S�lection

	if( close_balise != "" && selection == "" && close_balise != "smile" )
		document.selection.createRange().text = open_balise + close_balise;
	else if( close_balise == "smile" )
		document.selection.createRange().text = selection + open_balise + ' ';
	else
		document.selection.createRange().text = open_balise + selection + close_balise;		
	
	target.scrollTop = scroll; //Remet � la bonne position le textarea.
	selText = '';
	
	return;
}

//Fonction d'insertion du BBcode dans le champs, tient compte du navigateur utilis�.
function insertbbcode(open_balise, close_balise, field)
{
	var area = document.getElementById(field);
	var nav = navigator.appName; //Recup�re le nom du navigateur

	area.focus();

	if( nav == 'Microsoft Internet Explorer' ) // Internet Explorer
		ie_sel(area, open_balise, close_balise);
	else if( nav == 'Netscape' || nav == 'Opera' ) //Netscape ou opera
		netscape_sel(area, open_balise, close_balise);
	else //insertion normale (autres navigateurs)
		simple_insert(open_balise, close_balise, field);

	return;
}

//Insertion dans le champs des codes de type select.
function insertbbcode_select(id_select, close_balise, field)
{
	var select = document.getElementById(id_select + field);
	
	if( select.value != '' )
		insertbbcode('[' + id_select + '=' + select.value + ']', close_balise, field);
	
	//On remet la valeur par d�faut.
	select.options[0].selected = true;
	
	return;
}	

//Insertion dans le champs des codes de type select.
function insertbbcode_select2(id_select, field)
{
	var select = document.getElementById(id_select + field);
	
	if( select.value != '' )
		insertbbcode('[' + select.value + ']', '[/' + select.value + ']', field);
	
	//On remet la valeur par d�faut.
	select.options[0].selected = true;
	
	return;
}

//Envoi le cookie au client.
function sendCookie(name, value)
{
	var date = new Date();
	date.setMonth(date.getMonth() + 1); //1 mois de validit�.
	document.cookie = name + '=' + value + '; expires = ' + date.toGMTString() + '; path = "/"';
}

//R�cup�re la valeur du cookie.
function getCookie(name) 
{
	start = document.cookie.indexOf(name + "=")
	if( start >= 0 ) 
	{
		start += name.length + 1;
		end = document.cookie.indexOf(';', start);
		
		if( end < 0 ) 
			end = document.cookie.length;
		
		return document.cookie.substring(start, end);
	}
	return '';
}

//Conserve la configuration de la barre bbcode.
function set_bbcode_preference(divID)
{
	if( getCookie('hide-bbcode') == 0 )
	{
		jQuery('#' + divID).hide();
		var parent = jQuery('#' + divID).closest('.bbcode')[0];
		parent.className = 'bbcode';
	}
}

//Masquage du div.
function show_bbcode_div(divID, hide)
{
	var div = document.getElementById(divID);
	var parent = jQuery('#' + divID).closest('.bbcode')[0];
	if( div.style.display == 'none' )
	{
		parent.className = 'bbcode expand';
		sendCookie('hide-bbcode', 1); //On envoi le cookie pour se souvenir du choix de l'utilisateur.
		jQuery("#" + divID).fadeIn(300);
	}
	else
	{
		jQuery("#" + divID).fadeOut(300, function(){
			parent.className = 'bbcode';
			sendCookie('hide-bbcode', 0); //On envoi le cookie pour se souvenir du choix de l'utilisateur.
		});
	}
}

function bbcode_color(divID, field)
{
	var i;
	var br;
	var contents;
	var color = new Array(
	'#000000', '#433026', '#333300', '#003300', '#003366', '#000080', '#333399', '#333333',
	'#800000', '#FFA500', '#808000', '#008000', '#008080', '#0000FF', '#666699', '#808080',
	'#F04343', '#FF9900', '#99CC00', '#339966', '#33CCCC', '#3366FF', '#800080', '#ACA899',
	'#FFC0CB', '#FFCC00', '#FFFF00', '#00FF00', '#00FFFF', '#00CCFF', '#993366', '#C0C0C0',
	'#FF99CC', '#FFCC99', '#FFFF99', '#CCFFCC', '#CCFFFF', '#CC99FF', '#E3007B', '#FFFFFF');
	
	contents = '<table><tr>';
	for(i = 0; i < 40; i++)
	{
		br = (i+1) % 8;
		br = (br == 0 && i != 0 && i < 39) ? '</tr><tr>' : '';
		contents += '<td><a href="" style="background:' + color[i] + ';" onclick="insertbbcode(\'[color=' + color[i] + ']\', \'[/color]\', \'' + field + '\');bb_hide_block(\'' + divID + '\', \'' + field + '\', 0);return false;"></a></td>' + br;
	}
	document.getElementById("bbcolor" + field).innerHTML = contents + '</tr></table>';
}

function bbcode_table(field, head_name)
{
	var cols = document.getElementById('bb-cols' + field).value;
	var lines = document.getElementById('bb-lines' + field).value;
	var head = document.getElementById('bb-head' + field).checked;
	var code = '';
	
	if( cols >= 0 && lines >= 0 )
	{
		var colspan = cols > 1 ? ' colspan="' + cols + '"' : '';
		var pointor = head ? (59 + colspan.length) : 22;
		code = head ? '[table]\n\t[row]\n\t\t[head' + colspan + ']'+ head_name +'[/head]\n\t[/row]\n' : '[table]\n';
		
		for(var i = 0; i < lines; i++)
		{
			code += '\t[row]\n';
			for(var j = 0; j < cols; j++)
				code += '\t\t[col][/col]\n';
			code += '\t[/row]\n';
		}
		code += '[/table]';
		
		insertbbcode(code.substring(0, pointor), code.substring(pointor, code.length), field);
	}
}

function bbcode_list(field)
{
	var elements = document.getElementById('bb_list' + field).value;
	var ordered_list = document.getElementById('bb_ordered_list' + field).checked;
	if( elements <= 0 )
		elements = 1;
	
	var pointor = ordered_list ? 19 : 11;
	
	code = '[list' + (ordered_list ? '=ordered' : '') + ']\n';
	for(var j = 0; j < elements; j++)
		code += '\t[*]\n';
	code += '[/list]';
	insertbbcode(code.substring(0, pointor), code.substring(pointor, code.length), field);
}

function bbcode_url(field, prompt_text)
{
	var url = prompt(prompt_text);
	if( url != null)
		insertbbcode('[url=' + url + ']', '[/url]', field);
}

function bbcode_lightbox(field, prompt_text)
{
	var url = prompt(prompt_text);
	if( url != null)
		insertbbcode('[lightbox=' + url + '][img style="max-width:150px;"]' + url, '[/img][/lightbox]', field);
}

function bbcode_anchor(field, prompt_text)
{
	var anchor = prompt(prompt_text);
	if( anchor != null)
		insertbbcode('[anchor=' + anchor + ']', '[/anchor]', field);
	else
		insertbbcode('[anchor]', '[/anchor]', field);
}