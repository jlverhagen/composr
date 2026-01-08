<span class="constrain-field">
	{+START,IF,{INFORM_MAIL_CHECK}}
		<span class="inform-mail-check">{!mail:INFORM_MAIL_CHECK,{$FIND_SCRIPT,mail_check}}</span>
	{+END}
	<input {+START,IF_PASSED,AUTOCOMPLETE} autocomplete="{AUTOCOMPLETE*}"{+END} size="{$?,{$MOBILE},27,40}" tabindex="{TABINDEX*}" class="form-control input-email{REQUIRED*}" maxlength="255" type="email" id="{NAME*}" name="{NAME*}" value="{DEFAULT*}" />
</span>
