			<br />
		</div>
		<div id="admin_footer">
			<span>
				<!-- This mention must figured on the website ! -->
				<!-- Cette mention dois figurer sur le site ! -->
				{L_POWERED_BY} <a style="font-size:10px" href="http://www.phpboost.com" title="PHPBoost">PHPBoost 2</a> {L_PHPBOOST_RIGHT}
			</span>	
			# IF C_DISPLAY_BENCH #
			<br />
			<span>
				{L_ACHIEVED} {BENCH}{L_UNIT_SECOND} - {REQ} {L_REQ}
			</span>	
			# ENDIF #
			# IF C_DISPLAY_AUTHOR_THEME #
			<br />
			<span>
				{L_THEME} {L_THEME_NAME} {L_BY} <a href="{U_THEME_AUTHOR_LINK}" style="font-size:10px;">{L_THEME_AUTHOR}</a>
			</span>
			# ENDIF #
		</div>
	</body>
</html>