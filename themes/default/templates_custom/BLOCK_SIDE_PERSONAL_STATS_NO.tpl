{+START,IF,{$NOR,{$GET,login_screen},{$MATCH_KEY_MATCH,_WILD:login}}}
	<section class="box box___block_side_personal_stats_no"><div class="box_inner">
		{+START,IF_NON_EMPTY,{TITLE}}<h3>{TITLE}</h3>{+END}

		<form title="{!_LOGIN}" onsubmit="if ($cms.form.checkFieldForBlankness(this.elements['login_username'])) { $cms.ui.disableFormButtons(this); return true; } return false;" action="{LOGIN_URL*}" method="post" autocomplete="on">
			{$INSERT_SPAMMER_BLACKHOLE}

			<div>
				<div class="constrain_field">
					<div class="accessibility_hidden"><label for="ps_login_username">{$LOGIN_LABEL}</label></div>
					<input maxlength="80" class="wide_field login_block_username" type="text" placeholder="{!USERNAME}" id="ps_login_username" name="login_username" />
					<div class="accessibility_hidden"><label for="ps_password">{!PASSWORD}</label></div>
					<input maxlength="255" class="wide_field" type="password" placeholder="{!PASSWORD}" value="" name="password" id="ps_password" />
				</div>

				{+START,IF,{$CONFIG_OPTION,password_cookies}}
					<div class="login_block_cookies">
						<div class="float_surrounder">
							<label for="ps_remember">{!REMEMBER_ME}</label>
							<input {+START,IF,{$CONFIG_OPTION,remember_me_by_default}} checked="checked"{+END}{+START,IF,{$NOT,{$CONFIG_OPTION,remember_me_by_default}}} onclick="if (this.checked) { var t=this; $cms.ui.confirm('{!REMEMBER_ME_COOKIE;}',function(answer) { if (!answer) { t.checked=false; } }); }"{+END} type="checkbox" value="1" id="ps_remember" name="remember" />
						</div>
						{+START,IF,{$CONFIG_OPTION,is_on_invisibility}}
							<div class="float_surrounder">
								<label for="login_invisible">{!INVISIBLE}</label>
								<input type="checkbox" value="1" id="login_invisible" name="login_invisible" />
							</div>
						{+END}
					</div>
				{+END}

				<p class="proceed_button">
					<input class="button_screen_item menu__site_meta__user_actions__login" type="submit" value="{!_LOGIN}" />
				</p>
			</div>
		</form>

		<ul class="horizontal_links associated_links_block_group force_margin">
			{+START,IF_NON_EMPTY,{JOIN_URL}}<li><a href="{JOIN_URL*}">{!_JOIN}</a></li>{+END}
			<li><a data-open-as-overlay="1" rel="nofollow" href="{FULL_LOGIN_URL*}" title="{!MORE}: {!_LOGIN}">{!MORE}</a></li>
		</ul>

		{+START,IF_NON_EMPTY,{$CONFIG_OPTION,facebook_appid}}{+START,IF,{$CONFIG_OPTION,facebook_allow_signups}}
			{+START,IF_EMPTY,{$FB_CONNECT_UID}}
				<div style="margin-top: 0.4em; text-align: center"><div class="fb-login-button" data-scope="email{$,Asking for this stuff is now a big hassle as it needs a screencast(s) making: user_birthday,user_about_me,user_hometown,user_location,user_website}{+START,IF,{$CONFIG_OPTION,facebook_auto_syndicate}},publish_actions,publish_pages{+END}"></div></div>
			{+END}
		{+END}{+END}
	</div></section>
{+END}
