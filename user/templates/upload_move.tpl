	{HEADER}
	<script>
	<!--
	function close_popup()
	{
		opener=self;
		self.close();
	}
	-->
	</script>
	<section id="module-user-upload-move">
		<header><h1>{L_FILES_MANAGEMENT}</h1></header>
		<div class="content">
			<div class="upload-address-bar">
				<a href="upload.php?root=1{POPUP}"><i class="fa fa-home"></i> {L_ROOT}</a>{URL}
			</div>
			# INCLUDE message_helper #
			<form action="{TARGET}" method="post">
				<div class="upload-elements-container">
					# START folder #
						<div class="upload-elements-move-folder">
							<i class="fa fa-folder"></i> {folder.NAME}
						</div>
					# END folder #
							
					# START file #
						<div class="upload-elements-move-file">
							# IF file.C_DISPLAY_REAL_IMG #
								<img src="{PATH_TO_ROOT}/upload/{file.FILE_ICON}" alt="{file.NAME}"/>
							# ELSE #
								<i class="fa {file.FILE_ICON}"></i>
							# ENDIF #
							<br />{file.NAME}<br />
							<span class="smaller">{file.FILETYPE}</span><br />
							<span class="smaller">{file.SIZE}</span><br />
						</div>
					# END file #
					
					<div class="upload-elements-move-to">
						<strong>{L_MOVE_TO}</strong>
						<br />
						<i class="fa fa-arrow-right"></i>
					</div>
					<div class="upload-elements-move-to-cat">
							<script src="{PATH_TO_ROOT}/kernel/lib/js/phpboost/upload.js"></script>
							<script>
							<!--
								var path = '{PATH_TO_ROOT}/templates/{THEME}';
								var selected_cat = {SELECTED_CAT};
							-->
							</script>
							<span class="upload-root-cat"><a href="javascript:select_cat(0);"><i class="fa fa-home"></i> <span id="class_0" class="{CAT_0}">{L_ROOT}</span></a></span>
							<br />
							{FOLDERS}
					</div>
				</div>
				<div class="spacer"></div>
				<fieldset class="fieldset-submit">
					<input type="hidden" name="new_cat" id="id_cat" value="{SELECTED_CAT}">
					<input type="hidden" name="token" value="{TOKEN}">
					<button type="submit" value="true" name="valid" class="submit">{L_SUBMIT}</button>
				</fieldset>
			</form>
		</div>
		<footer></footer>
	</section>
	{FOOTER}