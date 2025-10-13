{+START,INCLUDE,EMAIL_LOG_SCREEN}{+END}

{+START,IF,{$ADDON_INSTALLED,cms_homesite_tracker}}
	{+START,IF,{$ADDON_INSTALLED,cms_homesite}}
		<div>
			<h2>{!cms_homesite:TRACKER_EMAIL_LOG}</h2>

			{TRACKER_RESULTS_TABLE}
		</div>
	{+END}
{+END}
