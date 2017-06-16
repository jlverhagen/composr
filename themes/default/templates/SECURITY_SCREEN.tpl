{$REQUIRE_JAVASCRIPT,securitylogging}

<div data-tpl="securityScreen">
	{TITLE}

	<h2>{!FAILED_LOGINS}</h2>

	{FAILED_LOGINS}

	<h2>{!SECURITY_ALERTS}</h2>

	<p>
		{!SECURITY_PAGE_CLEANUP}
	</p>

	{ALERTS}

	{+START,IF,{$NEQ,{NUM_ALERTS},0}}
		<form title="{!PRIMARY_PAGE_FORM}" action="{URL*}" method="post" autocomplete="off">
			{$INSERT_SPAMMER_BLACKHOLE}

			<p class="proceed_button">
				<input class="button_screen menu___generic_admin__delete js-click-btn-delete-add-form-marked-posts" type="submit" value="{!DELETE}" />
			</p>
		</form>
	{+END}
</div>
