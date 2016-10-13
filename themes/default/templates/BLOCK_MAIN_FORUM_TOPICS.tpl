<section id="tray_{TITLE|}" data-view="ToggleableTray" data-tray-cookie="{TITLE|}" class="box box___block_main_forum_topics">
	<h3 class="toggleable_tray_title js-tray-header">
		<a class="toggleable_tray_button js-btn-tray-toggle" href="#!"><img alt="{!CONTRACT}: {$STRIP_TAGS,{TITLE}}" title="{!CONTRACT}" src="{$IMG*,1x/trays/contract2}" srcset="{$IMG*,2x/trays/contract2} 2x" /></a>

		{+START,IF_NON_EMPTY,{TITLE}}
			<a class="toggleable_tray_button js-btn-tray-toggle" href="#!">{TITLE}</a>
		{+END}
	</h3>

	<div class="toggleable_tray js-tray-content">
		{+START,LOOP,TOPICS}
			<div class="box box___block_main_forum_topics_topic"><div class="box_inner">
				<p class="tiny_paragraph">
					<a title="{$STRIP_TAGS,{TITLE}}" href="{TOPIC_URL*}">{$TRUNCATE_LEFT,{TITLE},30,0,1}</a>
				</p>

				<div role="note">
					<ul class="tiny_paragraph associated_details horizontal_meta_details">
						<li>{!BY_SIMPLE,{$DISPLAYED_USERNAME*,{USERNAME}}}</li>
						<li>{!POST_PLU,{NUM_POSTS*}}</li>
					</ul>
				</div>

				<p class="tiny_paragraph associated_details">
					<span class="field_name">{!LAST_POST}:</span> {DATE*}
				</p>
			</div></div>
		{+END}

		{+START,IF_NON_EMPTY,{SUBMIT_URL}}
			<ul class="horizontal_links associated_links_block_group force_margin">
				<li><a href="{SUBMIT_URL*}">{!ADD_TOPIC}</a></li>
			</ul>
		{+END}
	</div>
</section>