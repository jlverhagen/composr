<span class="credits_bar">
	{$SET,show_whats_this,{$AND,{$IS_NON_EMPTY,{WHATS_THIS}},{$NOT,{$MATCH_KEY_MATCH,site:professional_support}}}}
	{+START,IF,{$NOT,{$IS_GUEST}}}
		<span class="gb_credits_available"{+START,IF,{$LT,{_CREDITS},0}} style="color: red"{+END}>{CREDITS_MSG*}</span>{+START,IF,{$NOT,{$GET,show_whats_this}}}{NO_CREDITS_LINK*}{+END}{+START,IF,{$NEQ,{TICKETS_OPEN},0}}{TICKETS_OPEN_MSG}{+END}
	{+END}
	{+START,IF,{$GET,show_whats_this}}
		<span class="gb_help">{WHATS_THIS*}</span>
	{+END}
</span>
