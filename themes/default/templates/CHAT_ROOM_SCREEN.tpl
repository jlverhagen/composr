{$REQUIRE_JAVASCRIPT,jquery}
{$REQUIRE_JAVASCRIPT,widget_color}
{$REQUIRE_JAVASCRIPT,chat}
{$REQUIRE_CSS,widget_color}

<div data-view="ChatRoomScreen" data-view-params="{+START,PARAMS_JSON,CHATROOM_ID}{_*}{+END}">
	{TITLE}

	{+START,IF_NON_EMPTY,{INTRODUCTION}}<p>{INTRODUCTION}</p>{+END}

	{CHAT_SOUND}

	<div class="chat_posting_area">
		<div class="float_surrounder">
			<div class="left">
				<form title="{!MESSAGE}" action="{MESSAGES_PHP*}?action=post&amp;room_id={CHATROOM_ID*}" method="post" class="inline" autocomplete="off">
					{$INSERT_SPAMMER_BLACKHOLE}

					<div style="display: inline;">
						<p class="accessibility_hidden"><label for="post">{!MESSAGE}</label></p>
						<textarea style="font-family: '{FONT_NAME_DEFAULT;*}'" class="input_text_required js-keypress-enter-post-chat" data-textarea-auto-height="" id="post" name="message" cols="{$?,{$MOBILE},37,39}" rows="1"></textarea>
						<input type="hidden" name="font" id="font" value="{FONT_NAME_DEFAULT*}" />
						<input type="hidden" name="colour" id="colour" value="{TEXT_COLOUR_DEFAULT*}" />
					</div>
				</form>
			</div>
			<div class="left">
				<form title="{SUBMIT_VALUE*}" action="{MESSAGES_PHP*}?action=post&amp;room_id={CHATROOM_ID*}" method="post" class="inline" autocomplete="off">
					{$INSERT_SPAMMER_BLACKHOLE}

					<input type="button" class="button_micro buttons__send js-click-post-chat-message" value="{SUBMIT_VALUE*}" />
				</form>
				{+START,IF,{$NOT,{$MOBILE}}}
					{MICRO_BUTTONS}
					{+START,IF,{$CNS}}
						<a rel="nofollow" class="horiz_field_sep js-click-open-emoticon-chooser-window" tabindex="6" href="#!" title="{!EMOTICONS_POPUP}"><img alt="" src="{$IMG*,icons/16x16/editor/insert_emoticons}" srcset="{$IMG*,icons/32x32/editor/insert_emoticons} 2x" /></a>
					{+END}
				{+END}
			</div>
			<div class="right">
				<a class="toggleable_tray_button js-btn-toggle-chat-comcode-panel" href="#!"><img id="e_chat_comcode_panel" src="{$IMG*,1x/trays/expand}" srcset="{$IMG*,2x/trays/expand} 2x" alt="{!CHAT_TOGGLE_COMCODE_BOX}" title="{!CHAT_TOGGLE_COMCODE_BOX}" /></a>
			</div>
		</div>

		<div style="display: none" id="chat_comcode_panel">
			{BUTTONS}

			{+START,IF_NON_EMPTY,{COMCODE_HELP}{CHATCODE_HELP}}
				<ul class="horizontal_links horiz_field_sep associated_links_block_group">
					{+START,IF_NON_EMPTY,{COMCODE_HELP}}
						<li><a data-open-as-overlay="1" class="link_exempt" title="{!COMCODE_MESSAGE,Comcode} {!LINK_NEW_WINDOW}" target="_blank" href="{COMCODE_HELP*}"><img src="{$IMG*,icons/16x16/editor/comcode}" srcset="{$IMG*,icons/32x32/editor/comcode} 2x" class="vertical_alignment" alt="" /></a></li>
					{+END}
					{+START,IF_NON_EMPTY,{CHATCODE_HELP}}
						<li><a data-open-as-overlay="1" class="link_exempt" title="{$STRIP_TAGS,{!CHATCODE_HELP}} {!LINK_NEW_WINDOW}" target="_blank" href="{CHATCODE_HELP*}">{!CHATCODE_HELP}</a></li>
					{+END}
				</ul>
			{+END}
		</div>
	</div>

	<div class="messages_window"><div role="marquee" class="messages_window_full_chat" id="messages_window"></div></div>

	<div class="box box___chat_screen_chatters"><p class="box_inner">
		{!USERS_IN_CHATROOM} <span id="chat_members_update">{CHATTERS}</span>
	</p></div>

	<form title="{$STRIP_TAGS,{!CHAT_OPTIONS_DESCRIPTION}}" class="below_main_chat_window js-form-submit-check-chat-options" method="post" action="{OPTIONS_URL*}" autocomplete="off">
		{$INSERT_SPAMMER_BLACKHOLE}

		<div class="box box___chat_screen_options box_prominent"><div class="box_inner">
			<h2>{!OPTIONS}</h2>

			<div class="chat_room_options">
				<p class="chat_options_title">
					{!CHAT_OPTIONS_DESCRIPTION}
				</p>

				<div class="float_surrounder">
					<div class="chat_colour_option">
						<p>
							<label for="text_colour">{!CHAT_OPTIONS_COLOUR_NAME}:</label>
						</p>
						<p>
							<input size="10" maxlength="7" class="input_line_required js-change-input-text-color" type="color" id="text_colour" name="text_colour" value="{+START,IF,{$NEQ,{TEXT_COLOUR_DEFAULT},inherit}}#{TEXT_COLOUR_DEFAULT*}{+END}" />
						</p>
					</div>

					<div class="chat_font_option">
						<p>
							<label for="font_name">{!CHAT_OPTIONS_TEXT_NAME}:</label>
						</p>
						<p>
							<select class="js-select-click-font-change js-select-change-font-chage" id="font_name" name="font_name">
								{+START,LOOP,Arial\,Courier\,Georgia\,Impact\,Times\,Trebuchet\,Verdana\,Tahoma\,Geneva\,Helvetica}
									<option {$?,{$EQ,{FONT_NAME_DEFAULT},{_loop_var}},selected="selected" ,}value="{_loop_var*}" style="font-family: '{_loop_var;*}'">{_loop_var*}</option>
								{+END}
							</select>
						</p>
					</div>
				</div>

				<p>
					<label for="play_sound">{!SOUND_EFFECTS}:</label> <input type="checkbox" id="play_sound" name="play_sound" checked="checked" />
				</p>

				<p>
					<input class="button_screen_item buttons__save" data-cms-confirm-click="{!SAVE_COMPUTER_USING_COOKIE*}" type="submit" value="{$STRIP_TAGS,{!CHAT_CHANGE_OPTIONS}}" />
				</p>
			</div>

			<div class="chat_room_actions">
				<p class="lonely_label">{!ACTIONS}:</p>
				<nav>
					<ul class="actions_list">
						{+START,LOOP,LINKS}
							{+START,IF_NON_EMPTY,{_loop_var}}
								<li class="icon_14_{_loop_key*}">{_loop_var}</li>
							{+END}
						{+END}
					</ul>
				</nav>
			</div>
		</div></div>
	</form>

	<div class="force_margin">
		{+START,INCLUDE,NOTIFICATION_BUTTONS}
			NOTIFICATIONS_TYPE=member_entered_chatroom
			NOTIFICATIONS_ID={CHATROOM_ID}
			BREAK=1
		{+END}
	</div>

	{+START,INCLUDE,STAFF_ACTIONS}
		{+START,IF,{$ADDON_INSTALLED,tickets}}
			1_URL={$PAGE_LINK,_SEARCH:report_content:content_type=chat:content_id={CHATROOM_ID}:redirect={$SELF_URL&}}
			1_TITLE={!report_content:REPORT_THIS}
			1_ICON=buttons/report
			1_REL=report
		{+END}
	{+END}

	{$REVIEW_STATUS,chat,{CHATROOM_ID}}
</div>
