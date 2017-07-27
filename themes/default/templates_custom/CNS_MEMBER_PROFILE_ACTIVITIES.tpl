<div class="float_surrounder">
	{+START,IF,{$EQ,{MEMBER_ID},{$MEMBER}}}
		{$BLOCK,block=main_activities_state,member={MEMBER_ID},mode=some_members,param=}
	{+END}

	{$BLOCK,block=main_activities,member={MEMBER_ID},mode=some_members,param=,max=10,grow=1}

	<hr class="spaced_rule" />

	<div class="right">
		{+START,INCLUDE,NOTIFICATION_BUTTONS}
			NOTIFICATIONS_TYPE=activity
			NOTIFICATIONS_ID={MEMBER_ID}
		{+END}
	</div>
</div>

{+START,IF_NON_EMPTY,{SYNDICATIONS}}
	<p>{!CREATE_SYNDICATION_LINK}</p>

	<form action="{$PAGE_LINK*,_SEARCH:members:view:{MEMBER_ID}}#tab__activities" method="post" autocomplete="off">
		{$INSERT_SPAMMER_BLACKHOLE}

		<p>
			{+START,LOOP,SYNDICATIONS}
				{+START,IF,{SYNDICATION_IS_SET}}
					<input class="button_screen_item buttons__cancel" onclick="disable_button_just_clicked(this);" type="submit" id="syndicate_stop__{_loop_key*}" name="syndicate_stop__{_loop_key*}" value="{!STOP_SYNDICATING_TO,{SYNDICATION_SERVICE_NAME*}}" />
				{+END}
				{+START,IF,{$NOT,{SYNDICATION_IS_SET}}}
					<input class="button_screen_item buttons__proceed" onclick="disable_button_just_clicked(this);" type="submit" id="syndicate_start__{_loop_key*}" name="syndicate_start__{_loop_key*}" value="{!START_SYNDICATING_TO,{SYNDICATION_SERVICE_NAME*}}" />
				{+END}
			{+END}
		</p>
	</form>

	{+START,LOOP,SYNDICATIONS}
		{+START,IF_NON_EMPTY,{SYNDICATION_JAVASCRIPT}}
			<script>// <![CDATA[
				{SYNDICATION_JAVASCRIPT/}
			//]]></script>
		{+END}
	{+END}
{+END}
