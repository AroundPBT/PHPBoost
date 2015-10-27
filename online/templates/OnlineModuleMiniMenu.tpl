<div class="module-mini-container">
	<div class="module-mini-top">
		<h5 class="sub-title">{@online}</h5>
	</div>
	<div class="module-mini-contents">
		<span class="smaller">{TOTAL_VISITOR_CONNECTED} {L_VISITOR}, {TOTAL_MEMBER_CONNECTED} {L_MEMBER}, {TOTAL_MODERATOR_CONNECTED} {L_MODO}, {TOTAL_ADMINISTRATOR_CONNECTED} {L_ADMIN} {L_USERS_ONLINE}.</span>
		<br /><br />
		# START users #
			<a href="{users.U_PROFILE}" class="{users.LEVEL_CLASS}" # IF users.C_GROUP_COLOR # style="color:{users.GROUP_COLOR}" # ENDIF #>{users.PSEUDO}</a><br/>
		# END users #
		
		# IF C_MORE_USERS #
		<br />
		<a href="${relative_url(OnlineUrlBuilder::home())}">{TOTAL_USERS_CONNECTED} {L_USERS_ONLINE}</a>
		# ENDIF #
		
		<div class="smaller" style="margin-top:10px;">
			{L_TOTAL} : {TOTAL_USERS_CONNECTED}
		</div>
	</div>
	<div class="module-mini-bottom">
	</div>
</div>