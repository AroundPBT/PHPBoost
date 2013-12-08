# INCLUDE UPLOAD_FORM #
<form action="{REWRITED_SCRIPT}" method="post">
	# INCLUDE MSG #	
	{@modules.modules_available}
	<table>
		# IF C_MODULES_AVAILABLE #
		<thead>
			<tr> 
				<th>
					{@modules.name}
				</th>
				<th>
					{@modules.description}
				</th>
				<th>
					{@modules.activate_module}
				</th>
			</tr>
		</thead>
		<tbody>
		# ELSE #
		<tbody>
			<tr>
				<td colspan="4">
					<span class="text-strong">{@modules.no_module_to_install}</span>
				</td>
			</tr>
		# ENDIF #
				
		# START available #
		<tr> 	
			<td>					
				<img src="{PATH_TO_ROOT}/{available.ICON}/{available.ICON}.png" alt="" /> <span class="text-strong">{available.NAME}</span> <em>({available.VERSION})</em>
			</td>
			<td>	
				<span class="text-strong">{@modules.author}:</span> {available.AUTHOR} {available.AUTHOR_WEBSITE}<br />
				<span class="text-strong">{@modules.description}:</span> {available.DESCRIPTION}<br />
				<span class="text-strong">{@modules.compatibility}:</span> PHPBoost {available.COMPATIBILITY}<br />
			</td>
			<td>	
				<input type="radio" name="activated-{available.ID}" value="1" checked="checked"> {@modules.yes}
				<input type="radio" name="activated-{available.ID}" value="0"> {@modules.no}
				<br /><br />
				<input type="hidden" name="token" value="{TOKEN}">
				<button type="submit" name="add-{available.ID}" value="true">{@modules.install_module}</button>
			</td>
		</tr>						
		# END available #
		</tbody>
	</table>			
</form>