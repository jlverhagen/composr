{$REQUIRE_JAVASCRIPT,cns_forum}

<td id="cell_mark_{ID*}" class="cns_topic_marker_cell" data-tpl="cnsTopicMarker">
	<form class="webstandards_checker_off inline" title="{!MARKER} #{ID*}" method="post" action="index.php" id="form_mark_{ID*}" autocomplete="off">
		{$INSERT_SPAMMER_BLACKHOLE}

		<div class="inline">
			{+START,IF,{$NOT,{$IS_GUEST}}}<div class="accessibility_hidden"><label for="mark_{ID*}">{!MARKER} #{ID*}</label></div>{+END}{$,Guests don't see this so search engines don't; hopefully people with screen-readers are logged in}
			<input value="1" type="checkbox" id="mark_{ID*}" name="mark_{ID*}" class="js-click-checkbox-set-row-mark-class" {+START,IF,{$NOT,{$IS_GUEST}}} title="{!MARKER} #{ID*}"{+END} />
		</div>
	</form>
</td>
