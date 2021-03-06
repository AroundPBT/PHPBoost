<script>
<!--
var ContactFormFieldObjectPossibleValues = function(){
	this.integer = {NBR_FIELDS};
	this.id_input = ${escapejs(HTML_ID)};
	this.max_input = {MAX_INPUT};
};

ContactFormFieldObjectPossibleValues.prototype = {
	add : function () {
		if (this.integer <= this.max_input) {
			var id = this.id_input + '_' + this.integer;
			
			jQuery('<div/>', {'id' : id}).appendTo('#input_fields_' + this.id_input);

			jQuery('<input/> ', {type : 'checkbox', id : 'field_is_default_' + id, name : 'field_is_default_' + id, value : '1', 'class' : 'per-default'}).appendTo('#' + id);
			jQuery('#' + id).append(' ');
			
			jQuery('<input/> ', {type : 'text', id : 'field_name_' + id, name : 'field_name_' + id, required : "required", placeholder : '{@field.possible_values.subject}'}).appendTo('#' + id);
			jQuery('#' + id).append(' ');
			
			jQuery('<select/> ', {'id' : 'field_recipient_' + id, 'name' : 'field_recipient_' + id}).appendTo('#' + id);
			jQuery('#' + id).append(' ');
			
			# START recipients_list #
			jQuery('<option/> ', {'value' : ${escapejs(recipients_list.ID)}}).text(${escapejs(recipients_list.NAME)}).appendTo('#field_recipient_' + id);
			# END recipients_list #

			jQuery('<a/> ', {href : 'javascript:ContactFormFieldObjectPossibleValues.delete('+ this.integer +');', title : "${LangLoader::get_message('delete', 'common')}"}).html('<i class="fa fa-delete"></i>').appendTo('#' + id);
			
			this.integer++;
		}
		if (this.integer == this.max_input) {
			jQuery('#add-' + this.id_input).hide();
		}
	},
	delete : function (id) {
		var id = this.id_input + '_' + id;
		jQuery('#' + id).remove();
		this.integer--;
		jQuery('#add-' + this.id_input).show();
	},
};

var ContactFormFieldObjectPossibleValues = new ContactFormFieldObjectPossibleValues();
-->
</script>

<div id="input_fields_${escape(HTML_ID)}">
<div class="text-strong"><span class="is_default_title">${LangLoader::get_message('field.possible_values.is_default', 'admin-user-common')}</span><span class="name_title">{@field.possible_values.subject}</span><span>${LangLoader::get_message('field.possible_values.recipient', 'common', 'contact')}</span></div>
# START fieldelements #
	<div id="${escape(HTML_ID)}_{fieldelements.ID}">
		<input type="checkbox" name="field_is_default_${escape(HTML_ID)}_{fieldelements.ID}" id="field_is_default_${escape(HTML_ID)}_{fieldelements.ID}" value="1"# IF fieldelements.IS_DEFAULT # checked="checked"# ENDIF # class="per-default">
		<input type="text" name="field_name_${escape(HTML_ID)}_{fieldelements.ID}" id="field_name_${escape(HTML_ID)}_{fieldelements.ID}" value="{fieldelements.NAME}" required="required" placeholder="{@field.possible_values.subject}">
		<select id="field_recipient_${escape(HTML_ID)}_{fieldelements.ID}" name="field_recipient_${escape(HTML_ID)}_{fieldelements.ID}">
			# START fieldelements.recipients_list #
			<option value="{fieldelements.recipients_list.ID}" # IF fieldelements.recipients_list.C_RECIPIENT_SELECTED #selected="selected"# ENDIF #>{fieldelements.recipients_list.NAME}</option>
			# END fieldelements.recipients_list #
		</select>
		<a href="javascript:ContactFormFieldObjectPossibleValues.delete({fieldelements.ID});" title="${LangLoader::get_message('delete', 'common')}" data-confirmation="delete-element"><i class="fa fa-delete"></i></a>
	</div>
# END fieldelements #
</div>
<a href="javascript:ContactFormFieldObjectPossibleValues.add();" id="add-${escape(HTML_ID)}"><i class="fa fa-plus"></i></a>
