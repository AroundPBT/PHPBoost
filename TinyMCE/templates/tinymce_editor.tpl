<script>
<!--
var displayed = new Array();
displayed[${escapejs(FIELD)}] = false;
function XMLHttpRequest_preview(field)
{
	if( XMLHttpRequest_preview.arguments.length == 0 )
		field = ${escapejs(FIELD)};

	var contents = tinymce.activeEditor.getContent();
	var preview_field = 'xmlhttprequest-preview' + field;
	
	if( contents == "" )
		contents = jQuery('#' + field).val();
	
	if( contents != "" )
	{
		if(!displayed[field])
			jQuery("#" + preview_field).slideDown(500);

		jQuery('#loading-preview-' + field).show();

		displayed[field] = true;

		jQuery.ajax({
			url: PATH_TO_ROOT + "/kernel/framework/ajax/content_xmlhttprequest.php",
			type: "post",
			data: {
				token: '{TOKEN}',
				path_to_root: '{PHP_PATH_TO_ROOT}',
				editor: 'TinyMCE',
				page_path: '{PAGE_PATH}',
				contents: contents,
				ftags: '{FORBIDDEN_TAGS}'
			},
			success: function(returnData){
				jQuery('#' + preview_field).html(returnData);

				jQuery('#loading-preview-' + field).hide();
			}
		});
	}
	else
		alert("{L_REQUIRE_TEXT}");
}

function insertTinyMceContent(content)
{
	var ed = tinymce.activeEditor;
	ed.insertContent(content);
	ed.windowManager.close();
}

function setTinyMceContent(content)
{
	tinymce.activeEditor.setContent(content);
}

-->
</script>
<div id="loading-preview-{FIELD}" class="loading-preview-container" style="display:none;">
	<div class="loading-preview">
		<i class="fa fa-spinner fa-2x fa-spin"></i>
	</div>
</div>
<div id="xmlhttprequest-preview{FIELD}" class="xmlhttprequest-preview" style="display:none;"></div>

# IF NOT C_NOT_JS_INCLUDED #
	<script src="{PATH_TO_ROOT}/TinyMCE/templates/js/tinymce/tinymce.min.js"></script>
# ENDIF #

<script>
<!--
tinymce.init({
	selector : "textarea\#{FIELD}",
	language : "{LANGUAGE}",
	plugins: [
		"advlist autolink autoresize autosave link image lists charmap hr anchor",
		"searchreplace wordcount visualblocks visualchars fullscreen insertdatetime media",
		"table contextmenu directionality smileys paste textcolor colorpicker textpattern imagetools"
	],
	external_plugins: {"nanospell": '{PATH_TO_ROOT}/TinyMCE/templates/js/tinymce/plugins/nanospell/plugin.js'},
	nanospell_server: "php",
	nanospell_dictionary: "fr, en",
	
	# IF C_TOOLBAR1 #toolbar1: "{TOOLBAR1}",# ENDIF #
	# IF C_TOOLBAR2 #toolbar2: "{TOOLBAR2}",# ENDIF #
	# IF C_TOOLBAR3 #toolbar3: "{TOOLBAR3}",# ENDIF #

	menubar: false,
	imagetools_proxy: '{PATH_TO_ROOT}/TinyMCE/lib/TinyMCEPicturesProxy.php',
	autoresize_max_height: '500px',
	advlist_number_styles: 'default',
	advlist_bullet_styles: 'default',
	fontsize_formats: '5pt 10pt 15pt 20pt 25pt 30pt 35pt 40pt 45pt',
	convert_urls: false,
	media_alt_source: false,
	media_poster: false,
	content_css: [
		"{PATH_TO_ROOT}/kernel/lib/css/font-awesome/css/font-awesome.css",
		"{PATH_TO_ROOT}/templates/{THEME}/theme/global.css"
	],
	style_formats: [
		{title: ${escapejs(LangLoader::get_message('success', 'status-messages-common'))}, inline: 'span', classes: 'success'},
		{title: ${escapejs(LangLoader::get_message('error.question', 'status-messages-common'))}, inline: 'span', classes: 'question'},
		{title: ${escapejs(LangLoader::get_message('error.notice', 'status-messages-common'))}, inline: 'span', classes: 'notice'},
		{title: ${escapejs(LangLoader::get_message('error.warning', 'status-messages-common'))}, inline: 'span', classes: 'warning'},
		{title: ${escapejs(LangLoader::get_message('error', 'status-messages-common'))}, inline: 'span', classes: 'error'}
	],
	setup : function(ed) {
		ed.addButton('insertfile', {
			icon: 'browse',
			onclick: function (field_name) {
				ed.windowManager.open({ 
					title: '',
					url: '{PATH_TO_ROOT}/user/upload.php?popup=1&amp;close_button=0&amp;fd=' + field_name + '&amp;edt=TinyMCE',
					width: 720,
					height: 500,
				}); 
			},
			tooltip: 'Insert file'
		});
		ed.on('blur', function(){
			jQuery("\#{FIELD}").val(ed.getContent());
			# IF C_HTMLFORM #
			HTMLForms.get("{FORM_NAME}").getField("{FIELD_NAME}").enableValidationMessage();
			HTMLForms.get("{FORM_NAME}").getField("{FIELD_NAME}").liveValidate();
			# ENDIF #
		})
	},

	smileys: [
		# START smiley #
			# IF smiley.C_NEW_ROW #[# ENDIF #
				{ shortcut: '{smiley.CODE}', url: '{smiley.URL}', title: '{smiley.CODE}' }# IF NOT smiley.C_LAST_OF_THE_ROW #,# ENDIF #
			# IF smiley.C_END_ROW #]# IF NOT smiley.C_LAST_ROW #,# ENDIF ## ENDIF #
		# END smiley #
	]
});
-->
</script>