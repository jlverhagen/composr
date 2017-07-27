<div class="wide_table_wrap">
    <table class="columned_table results_table wide_table">
        <thead>
        <tr>
            <th>Avatar</th>
            <th>Member</th>
            <th>Average post length</th>
            <th>Number of posts</th>
        </tr>
        </thead>
        <?php
        i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

        $max = array_key_exists('max', $map) ? intval($map['max']) : 10;

        if (multi_lang_content()) {
            $sql = 'SELECT m.id,AVG(' . db_function('LENGTH', array('text_original')) . ') AS avg,COUNT(*) AS cnt FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_members m LEFT JOIN ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_posts p ON p.p_poster=m.id LEFT JOIN ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'translate t ON t.id=p.p_post WHERE m.id<>' . strval($GLOBALS['FORUM_DRIVER']->get_guest_id()) . ' GROUP BY m.id ORDER BY avg DESC';
        } else {
            $sql = 'SELECT m.id,AVG(' . db_function('LENGTH', array('p_post')) . ') AS avg,COUNT(*) AS cnt FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_members m LEFT JOIN ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_posts p ON p.p_poster=m.id WHERE m.id<>' . strval($GLOBALS['FORUM_DRIVER']->get_guest_id()) . ' GROUP BY m.id ORDER BY avg DESC';
        }
        $members = $GLOBALS['FORUM_DB']->query($sql, $max);

        foreach ($members as $_member) {
            $member = $_member['id'];
            $av_post_length = $_member['avg'];

            $_avatar_url = escape_html($GLOBALS['FORUM_DRIVER']->get_member_avatar_url($member));
            $link = $GLOBALS['FORUM_DRIVER']->member_profile_url($member, false, true);
            if (is_object($link)) {
                $link = $link->evaluate();
            }
            $_link = escape_html($link);
            $_avatar = ($_avatar_url != '') ? ('<img alt="Avatar" src="' . $_avatar_url . '" />') : '';
            $_username = escape_html($GLOBALS['FORUM_DRIVER']->get_username($member, true));
            $_av_post_length = escape_html(integer_format(intval($av_post_length)));
            $_num_posts = escape_html(integer_format($_member['cnt']));

            echo <<<END
            <tr>
                    <td>{$_avatar}</td>
                    <td><a href="{$_link}">{$_username}</a></td>
                    <td>{$_av_post_length} letters</td>
                    <td>{$_num_posts} posts</td>
            </tr>
END;
        }
        ?>
    </table>
</div>
