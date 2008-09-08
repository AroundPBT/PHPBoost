		<script type="text/javascript">
		<!--
			function check_form_rank()
			{
				if(document.getElementById('vname').value == "") {
					alert("{L_REQUIRE_RANK_NAME}");
					return false;
			    }
				if(document.getElementById('vmsg').value == "") {
					alert("{L_REQUIRE_NBR_MSG_RANK}");
					return false;
			    }
			}
			
			function Confirm() {
				return confirm("{L_CONFIRM_DEL_RANK}");
			}
			
			function img_change(id, url)
			{
				if( document.images )
					document.images[id].src = "../templates/{THEME}/images/ranks/" + url;
			}
		-->
		</script>
		
		<div id="admin_quick_menu">
			<ul>
				<li class="title_menu">{L_RANKS_MANAGEMENT}</li>
				<li>
					<a href="admin_ranks.php"><img src="../templates/{THEME}/images/admin/ranks.png" alt="" /></a>
					<br />
					<a href="admin_ranks.php" class="quick_link">{L_RANKS_MANAGEMENT}</a>
				</li>
				<li>
					<a href="admin_ranks_add.php"><img src="../templates/{THEME}/images/admin/ranks.png" alt="" /></a>
					<br />
					<a href="admin_ranks_add.php" class="quick_link">{L_ADD_RANKS}</a>
				</li>
			</ul>
		</div>		
		
		<div id="admin_contents">
			<form action="admin_ranks.php" method="post" onsubmit="return check_form_rank();">
				<fieldset>
					<legend>{L_RANKS_MANAGEMENT}</legend>
					<table class="module_table">
						<tr style="text-align:center;"> 
							<th>
								{L_RANK_NAME}
							</th>
							<th>
								{L_NBR_MSG}
							</th>
							<th>
								{L_IMG_ASSOC}
							</th>
							<th>
								{L_DELETE}
							</th>
						</tr>
						# START rank #
						<tr>
							<td class="row2" style="text-align:center;"> 
								<input type="text" maxlength="30" size="20" name="{rank.ID}name" id="vname" value="{rank.RANK}" class="text" />
							</td>
							<td class="row2" style="text-align:center;">
								{rank.MSG}
							</td>
							<td class="row2" style="text-align:center;"> 						
								<select name="{rank.ID}icon" onChange="img_change('icon{rank.ID}', this.options[selectedIndex].value)">
									{rank.RANK_OPTIONS}
								</select>
								<br />
								<img src="../templates/{THEME}/images/ranks/{rank.IMG_RANK}" name="icon{rank.ID}" alt="" />
							</td>
							<td class="row2" style="text-align:center;">
								{rank.DELETE}
							</td>
						</tr>
						# END rank #
					</table>					
				</fieldset>
				<fieldset class="fieldset_submit">
					<legend>{L_UPDATE}</legend>
					<input type="submit" name="valid" value="{L_UPDATE}" class="submit" />
					&nbsp;&nbsp; 
					<input type="reset" value="{L_RESET}" class="reset" />
				</fieldset>
			</form>
		</div>
		