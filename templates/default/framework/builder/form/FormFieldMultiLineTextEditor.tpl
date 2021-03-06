<div id="${escape(HTML_ID)}_field" # IF C_HIDDEN # style="display:none;" # ENDIF # class="form-element-textarea # IF C_REQUIRED_AND_HAS_VALUE # constraint-status-right # ENDIF #">
	<label for="${escape(HTML_ID)}">
		{LABEL}
		# IF C_DESCRIPTION #<span class="field-description">{DESCRIPTION}</span># ENDIF #
	</label>

	# IF C_EDITOR_ENABLED #
	{EDITOR}
	# ENDIF #

	<div id="onblurContainerResponse${escape(HTML_ID)}" class="form-field-textarea picture-status-constraint# IF C_REQUIRED # field-required # ENDIF #">
		<textarea id="${escape(HTML_ID)}" name="${escape(HTML_ID)}" rows="{ROWS}" cols="{COLS}" class="# IF C_READONLY #low-opacity # ENDIF #${escape(CLASS)} " onblur="{ONBLUR}" # IF C_DISABLED # disabled="disabled" # ENDIF # # IF C_READONLY # readonly="readonly" # ENDIF #>{VALUE}</textarea>
		<span class="text-status-constraint" style="display:none" id="onblurMessageResponse${escape(HTML_ID)}"></span>
	</div>

	# IF C_EDITOR_ENABLED #
	<div class="form-element-preview">{PREVIEW_BUTTON}</div>
	# ENDIF #

</div>

# INCLUDE ADD_FIELD_JS #