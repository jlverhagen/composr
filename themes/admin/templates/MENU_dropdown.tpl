{$REQUIRE_CSS,menu__dropdown}
{$REQUIRE_JAVASCRIPT,core_menus}

{+START,IF_NON_EMPTY,{CONTENT}}

{$SET,menu_id,r_{MENU|}_d}
{$SET,RAND,{$RAND}}
{$SET,HAS_CHILDREN,{$HAS_ACTUAL_PAGE_ACCESS,admin,adminzone}}

<nav class="menu_type__dropdown" data-view="DropdownMenu" data-view-params="{+START,PARAMS_JSON,MENU,JAVASCRIPT_HIGHLIGHTING,menu_id}{_*}{+END}">
	<ul class="nl js-mouseout-unset-active-menu" id="{$GET*,menu_id}">
		{CONTENT}

		<li class="non_current last toplevel {+START,IF,{$GET,HAS_CHILDREN}}{+START,IF,{$NOT,{$MOBILE}}}js-mousemove-admin-timer-pop-up-menu js-mouseout-admin-clear-pop-up-timer{+END}{+END}" data-vw-rand="{$GET*,RAND}">
			<a href="{$TUTORIAL_URL*,tutorials}" class="toplevel_link last {$?,{$GET,HAS_CHILDREN},js-click-unset-active-menu} {$?,{$HAS_ACTUAL_PAGE_ACCESS,admin,adminzone},js-focus-pop-up-menu}" title="{!menus:MM_TOOLTIP_DOCS}">
				<img width="32" height="32" alt="" src="{$IMG*,icons/32x32/menu/adminzone/help}" srcset="{$IMG*,icons/64x64/menu/adminzone/help} 2x" /> <span>{!HELP}</span>
			</a>
			{+START,IF,{$GET,HAS_CHILDREN}}
				<div aria-haspopup="true" class="nlevel menu_help_section js-mouseover-set-active-menu js-mouseout-unset-active-menu" id="{MENU|*}_dexpand_{$GET*,RAND}" style="display: none">
					{+START,INCLUDE,ADMIN_ZONE_SEARCH}{+END}
				</div>
			{+END}
		</li>
	</ul>
</nav>
{+END}
