<section id="module-web">
	# IF NOT C_VISIBLE #
		# INCLUDE NOT_VISIBLE_MESSAGE #
	# ENDIF #
	<article itemscope="itemscope" itemtype="http://schema.org/CreativeWork">
		<header>
			<h1>
				<a href="{U_SYNDICATION}" title="${LangLoader::get_message('syndication', 'common')}" class="fa fa-syndication"></a>
				<span id="name" itemprop="name">{NAME}</span>
				<span class="actions">
					# IF C_EDIT #
						<a href="{U_EDIT}" title="${LangLoader::get_message('edit', 'common')}"><i class="fa fa-edit"></i></a>
					# ENDIF #
					# IF C_DELETE #
						<a href="{U_DELETE}" title="${LangLoader::get_message('delete', 'common')}" data-confirmation="delete-element"><i class="fa fa-delete"></i></a>
					# ENDIF #
				</span>
			</h1>
			
			<meta itemprop="url" content="{U_LINK}">
			<meta itemprop="description" content="${escape(DESCRIPTION)}" />
			# IF C_COMMENTS_ENABLED #
			<meta itemprop="discussionUrl" content="{U_COMMENTS}">
			<meta itemprop="interactionCount" content="{NUMBER_COMMENTS} UserComments">
			# ENDIF #
			
		</header>
		<div class="content">
			<div class="options infos">
				<div class="center">
					# IF C_HAS_PARTNER_PICTURE #
						<img src="{U_PARTNER_PICTURE}" alt="" itemprop="image" />
						<div class="spacer"></div>
					# ENDIF #
					# IF C_VISIBLE #
						<a href="{U_VISIT}" class="basic-button">
							<i class="fa fa-globe"></i> {@visit}
						</a>
						# IF IS_USER_CONNECTED #
						<a href="{U_DEADLINK}" class="basic-button alt" title="${LangLoader::get_message('deadlink', 'common')}">
							<i class="fa fa-unlink"></i>
						</a>
						# ENDIF #
					# ENDIF #
				</div>
				<h6>{@link_infos}</h6>
				<span class="text-strong">{@visits_number} : </span><span>{NUMBER_VIEWS}</span><br/>
				<span class="text-strong">${LangLoader::get_message('category', 'categories-common')} : </span><span><a itemprop="about" class="small" href="{U_CATEGORY}">{CATEGORY_NAME}</a></span><br/>
				# IF C_KEYWORDS #
					<span class="text-strong">${LangLoader::get_message('form.keywords', 'common')} : </span>
					<span>
						# START keywords #
							<a itemprop="keywords" class="small" href="{keywords.URL}">{keywords.NAME}</a># IF keywords.C_SEPARATOR #, # ENDIF #
						# END keywords #
					</span><br/>
				# ENDIF #
				# IF C_COMMENTS_ENABLED #
					<span># IF C_COMMENTS # {NUMBER_COMMENTS} # ENDIF # {L_COMMENTS}</span>
				# ENDIF #
				# IF C_VISIBLE #
					# IF C_NOTATION_ENABLED #
						<div class="spacer"></div>
						<div class="center">{NOTATION}</div>
					# ENDIF #
				# ENDIF #
			</div>
			
			<div itemprop="text">{CONTENTS}</div>
		</div>
		<aside>
			# INCLUDE COMMENTS #
		</aside>
		<footer></footer>
	</article>
</section>