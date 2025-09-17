<div data-view="AddonUpgradeConfirmScreen">
{TITLE}

{+START,IF_PASSED,PING_URL}
	{+START,INCLUDE,HANDLE_CONFLICT_RESOLUTION}{+END}
{+END}
{+START,IF_PASSED,WARNING_DETAILS}
	{WARNING_DETAILS}
{+END}

{WARNINGS}

<p>{TEXT}</p>

<form title="{!PRIMARY_PAGE_FORM}" action="{URL*}" method="post" data-submit-modsecurity-workaround="1">
	{$INSERT_FORM_POST_SECURITY}

	<p class="proceed-button">
		<button class="btn btn-primary btn-scr buttons--back" data-cms-btn-go-back="1" type="button">{+START,INCLUDE,ICON}NAME=buttons/back{+END} <span>{!GO_BACK}</span></button>
		<button data-disable-on-click="1" class="btn btn-primary btn-scr buttons--proceed" type="submit">{+START,INCLUDE,ICON}NAME=buttons/proceed{+END} {!PROCEED}</button>
	</p>

	<input type="hidden" name="name" value="{NAME*}" />
</form>
</div>
