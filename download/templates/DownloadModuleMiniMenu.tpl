<div id="module-mini-download" class="module-mini-container">
	<div class="module-mini-top">
		<h5 class="sub-title"># IF C_SORT_BY_DATE #{@last_download_files}# ELSE #{@most_downloaded_files}# ENDIF #</h5>
	</div>
	<div class="module-mini-contents">
		<table>
			<thead>
				<tr>
					<th><i class="fa # IF C_SORT_BY_DATE #fa-calendar# ELSE #fa-trophy# ENDIF #"></i></th>
					<th>${LangLoader::get_message('form.name', 'common')}</th>
					# IF NOT C_SORT_BY_DATE #
					<th><i class="fa # IF C_SORT_BY_NUMBER_DOWNLOADS #fa-download# ELSE #fa-star-o# ENDIF #"></i></th>
					# ENDIF #
				</tr>
			</thead>
			<tbody>
			# IF C_FILES #
				# START downloadfiles #
				<tr>
					<td># IF C_SORT_BY_DATE #<time datetime="{downloadfiles.DATE_ISO8601}">{downloadfiles.DATE_DAY_MONTH}</time># ELSE #{downloadfiles.DISPLAYED_POSITION}# ENDIF #</td>
					<td # IF C_SORT_BY_NOTATION #class="mini-download-table-name"# ENDIF #>
						<a href="{downloadfiles.U_LINK}" title="{downloadfiles.NAME}">
							{downloadfiles.NAME}
						</a>
					</td>
					# IF NOT C_SORT_BY_DATE #
					<td># IF C_SORT_BY_NUMBER_DOWNLOADS #{downloadfiles.NUMBER_DOWNLOADS}# ELSE #{downloadfiles.STATIC_NOTATION}# ENDIF #</td>
					# ENDIF #
				</tr>
				# END downloadfiles #
			# ELSE #
				<tr>
					<td colspan="# IF C_SORT_BY_DATE #2# ELSE #3# ENDIF #">${LangLoader::get_message('no_item_now', 'common')}</td>
				</tr>
			# ENDIF #
			</tbody>
		</table>
	</div>
	<div class="module-mini-bottom">
	</div>
</div>