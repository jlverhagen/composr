{TITLE}

<h2>{!W_SENTRY_QUESTION}</h2>

<form method="post" enctype="multipart/form-data" action="{$PAGE_LINK*,_SELF:_SELF}" autocomplete="off">
	{$INSERT_SPAMMER_BLACKHOLE}

	<input type="hidden" name="dx" value="{DX*}" />
	<input type="hidden" name="dy" value="{DY*}" />
	<input type="hidden" name="type" value="answered" />

	<p>
		<label for="param">{QUESTION*}: <input type="text" value="" id="param" name="param" /></label>
	</p>

	<p class="proceed_button">
		<input class="button_screen buttons__proceed" type="submit" value="{!PROCEED}" />
	</p>
</form>

