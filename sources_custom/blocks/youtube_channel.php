<?php

/**
 * Licensed under Creative Commons Public Domain license (CC0).  You are free to copy, use, distribute, and modify as you wish.
 */

/*EXTRA FUNCTIONS: json_decode|DateTime|DateInterval*/

/**
 * Block class.
 */
class Block_youtube_channel
{
    /**
     * Find details of the block.
     *
     * @return ?array Map of block info (null: block is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Jason Verhagen';
        $info['organisation'] = 'HolleywoodStudio.com';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 11;
        $info['locked'] = false;
        $info['parameters'] = array('name', 'api_key', 'playlist_id', 'title', 'template_main', 'template_style', 'start_video', 'max_videos', 'description_type', 'embed_allowed', 'show_player', 'player_align', 'player_width', 'player_height', 'style', 'nothumbplayer', 'thumbnail', 'formorelead', 'formoretext', 'formoreurl');
        return $info;
    }

    /**
     * Find caching details for the block.
     *
     * @return ?array Map of cache details (cache_on and ttl) (null: block is disabled).
     */
    public function caching_environment()
    {
        $info = array();
        $info['cache_on'] = array('block_youtube_channel__cache_on');
        $info['ttl'] = intval(get_option('youtube_channel_block_update_time'));
        return $info;
    }

    /**
     * Execute the block.
     *
     * @param  array $map A map of parameters.
     * @return Tempcode The result of execution.
     */
    public function run($map)
    {
        i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

        // Set up some arrays for dealing with thumbnails
        $thumb = array('default', 'medium', 'high', 'start', 'middle', 'end', 'standard', 'maxres');
        $thumbalt = array('default', 'mqdefault', 'hqdefault', '1', '2', '3', 'sddefault', 'maxresdefault');

        // Set up variables from parameters
        $channel_name = array_key_exists('name', $map) ? trim($map['name']) : '';
        $channel_title = array_key_exists('title', $map) ? $map['title'] : 'YouTube';
        $channel_tempmain = array_key_exists('template_main', $map) ? $map['template_main'] : '';
        if ($channel_tempmain) {
            $channel_tempmain = '_' . $channel_tempmain;
        }
        $channel_templatemain = 'BLOCK_YOUTUBE_CHANNEL' . $channel_tempmain;
        $channel_tempstyle = array_key_exists('template_style', $map) ? $map['template_style'] : '';
        if ($channel_tempstyle) {
            $channel_tempstyle = '_' . $channel_tempstyle;
        }
        $channel_templatestyle = 'BLOCK_YOUTUBE_CHANNEL_STYLE' . $channel_tempstyle;
        $channel_startvideo = array_key_exists('start_video', $map) ? $map['start_video'] : '1';
        $channel_maxvideos = array_key_exists('max_videos', $map) ? $map['max_videos'] : '25';
        $channel_showplayer = array_key_exists('show_player', $map) ? $map['show_player'] : '1';
        $channel_embedallowed = array_key_exists('embed_allowed', $map) ? $map['embed_allowed'] : '1';
        $channel_playeralign = strtolower(array_key_exists('player_align', $map) ? $map['player_align'] : 'center');
        $channel_playerwidth = array_key_exists('player_width', $map) ? $map['player_width'] : '480';
        $channel_playerheight = array_key_exists('player_height', $map) ? $map['player_height'] : '270';
        $channel_style = array_key_exists('style', $map) ? $map['style'] : '1';
        $channel_nothumbplayer = array_key_exists('nothumbplayer', $map) ? $map['nothumbplayer'] : '0';
        $channel_formorelead = array_key_exists('formorelead', $map) ? $map['formorelead'] : '';
        $channel_formoretext = array_key_exists('formoretext', $map) ? $map['formoretext'] : '';
        $channel_formoreurl = array_key_exists('formoreurl', $map) ? $map['formoreurl'] : '';
        $channel_thumbnail = array_key_exists('thumbnail', $map) ? $map['thumbnail'] : '0';
        $channel_descriptiontype = strtolower(array_key_exists('description_type', $map) ? $map['description_type'] : 'long');
        if ($channel_name == '') {
            $playlist_id = array_key_exists('playlist_id', $map) ? trim($map['playlist_id']) : '';
        } else {
            $playlist_id = '';
        }
        $channel_url = 'https://www.youtube.com/';

        // Set blank variable that can be set to an error message.
        $channel_error = '';

        // Get API Key config setting. If block config API Key setting is set, use that. If api_key block parameter is set, use that instead.
        $youtube_api_key = array_key_exists('api_key', $map) ? $map['api_key'] : get_option('youtube_channel_block_api_key');

        // Generate error if no YouTube API key is configured or specified
        if (empty($youtube_api_key)) {
            $channel_error .= 'Error: No YouTube API Key has been specified.<br />';
        }

        // Sanity checks on some critical input - if out of range or unknown values are detected, set them to a default value
        if ($channel_startvideo > 1) {
            $channel_maxvideos = $channel_maxvideos + ($channel_startvideo - 1);
        }
        if ($channel_maxvideos < 1 || $channel_maxvideos > 50) {
            $channel_maxvideos = 50;
        }
        if ($channel_startvideo < 1 || $channel_startvideo > 50) {
            $channel_startvideo = 1;
        }
        if ($channel_showplayer < 0 || $channel_showplayer + $channel_maxvideos > 50) {
            $channel_showplayer = 50;
        }
        if (!in_array($channel_playeralign, array('center', 'left', 'right'))) {
            $channel_playeralign = 'center';
        }
        if ($channel_nothumbplayer < 0 || $channel_nothumbplayer > 1) {
            $channel_nothumbplayer = 0;
        }
        if ($channel_thumbnail < 0 || $channel_thumbnail > 7) {
            $channel_thumbnail = 0;
        }
        if (!in_array($channel_descriptiontype, array('long', 'short'))) {
            $channel_descriptiontype = 'long';
        }

        // Create some working variables for working in foreach loop
        if (($channel_showplayer + $channel_startvideo - 1) >= $channel_maxvideos) {
            $temp_showplayer = $channel_maxvideos;
        } else {
            $temp_showplayer = $channel_showplayer + $channel_startvideo - 1;
        }
        $temp_nothumbplayer = $channel_nothumbplayer;

        // Get playlist ID from YouTube API v3 using user name so we can get the uploads playlist, or use the specified playlist ID if no user name is specified.
        if (!empty($channel_name)) {
            $channel = json_decode(@file_get_contents("https://www.googleapis.com/youtube/v3/channels?part=contentDetails&forUsername=$channel_name&fields=items(contentDetails(relatedPlaylists(uploads)))&key=$youtube_api_key"));

            // Check if we got a user upload playlist and assign it to a variable. If not, set an error.
            if (isset($channel->items[0]->contentDetails->relatedPlaylists->uploads)) {
                $playlist_id = $channel->items[0]->contentDetails->relatedPlaylists->uploads;
            } else {
                $channel_error .= 'Error: Unable to get uploads playlist for <b>' . $channel_name . '</b>. Verify you have the correct user name and API key.<br />';
            }

            // Set channel url using user name
            $channel_url = 'http://www.youtube.com/user/' . $channel_name;
        }
        if (empty($playlist_id)) {
            $channel_error .= 'Error: No channel user name or playlist ID specified.<br />';
        }

        // Get playlist video list from YouTube API v3
        $playlist_items = json_decode(@file_get_contents("https://www.googleapis.com/youtube/v3/playlistItems?part=snippet%2Cstatus&maxResults=$channel_maxvideos&playlistId=$playlist_id&fields=items(snippet(title%2CchannelId%2CchannelTitle%2Cdescription%2Cthumbnails%2CpublishedAt%2CresourceId(videoId))%2Cstatus(privacyStatus))%2CpageInfo(totalResults)&key=$youtube_api_key"));
        if (isset($playlist_items->pageInfo->totalResults)) {
            $total_playlist_items = $playlist_items->pageInfo->totalResults;
        } else {
            $total_playlist_items = 0;
        }
        if ($channel_startvideo > $total_playlist_items) {
            $channel_error .= 'Error: start_video parameter is beyond the end of the playlist.<br />';
        }

        // Set channel name and url if user name parameter was not used
        if (empty($channel_name) && isset($playlist_items->items[0]->snippet->channelTitle)) {
            $channel_name = $playlist_items->items[0]->snippet->channelTitle;
            $channel_url = 'https://www.youtube.com/channel/' . $playlist_items->items[0]->snippet->channelId;
        }

        $content = new Tempcode();

        // $i will count the iterations through the foreach loop
        $i = 1;

        // If we get playlist data, parse out the meta information for each video and pass it to the style template. Else, send an error to template.
        if (isset($playlist_items->items) && $channel_startvideo <= $total_playlist_items) {
            foreach ($playlist_items->items as $entry) {
                // Basic meta information for the video from 'playlistItems' YouTube API v3 call
                //get private/public status of video
                $is_public = $entry->status->privacyStatus;

                // We will omit non-public videos and all videos before the start_video block parameter setting.
                if (($is_public == 'public') && ($i >= $channel_startvideo) && ($i <= $channel_maxvideos)) {
                    //get video title
                    $title = (isset($entry->snippet->title) ? $entry->snippet->title : '');

                    //get video description
                    $description_long = (isset($entry->snippet->description) ? $entry->snippet->description : '');

                    //get video upload date/time
                    $uploaded = $entry->snippet->publishedAt;

                    //get video id
                    $video_id = $entry->snippet->resourceId->videoId;

                    //get video thumbnails
                    $thumbnails = $entry->snippet->thumbnails;

                    //generate video url
                    $video_url = 'https://www.youtube.com/watch?v=' . $video_id;

                    // Generate shortened description
                    $description_shortened = 0;
                    if (strlen($description_long) <= 250) {
                        $description_short = $description_long;
                    } else {
                        $description_short = substr($description_long, 0, strrpos(substr($description_long, 0, 250), ' '));
                        $description_shortened = 1;
                    }

                    // Get more detailed metadata for each individual video from 'videos' YouTube API v3 call
                    $video_metadata = json_decode(@file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=contentDetails%2Cstatistics%2Cstatus&id=$video_id&fields=items(contentDetails(duration)%2Cstatistics(viewCount%2CfavoriteCount%2ClikeCount%2CdislikeCount)%2Cstatus(embeddable))&key=$youtube_api_key"));

                    // Check if we got a result. If not, set error and move on.
                    if (!isset($video_metadata->items[0]->contentDetails->duration)) {
                        $channel_error .= 'Error: Failed to get data for video #' . strval($i) . '<br />';
                        continue;
                    }
                    //get video view count
                    $views = 0;
                    if (isset($video_metadata->items[0]->statistics->viewCount)) {
                        $views = $video_metadata->items[0]->statistics->viewCount;
                    }

                    //used to find out if video can be embedded
                    $embeddable = $video_metadata->items[0]->status->embeddable;

                    //get video favorite count
                    $favoritecount = 0;
                    if (isset($video_metadata->items[0]->statistics->favoriteCount)) {
                        $favoritecount = $video_metadata->items[0]->statistics->favoriteCount;
                    }

                    //get video likes count
                    $likes = 0;
                    if (isset($video_metadata->items[0]->statistics->likeCount)) {
                        $likes = $video_metadata->items[0]->statistics->likeCount;
                    }

                    //get video dislikes count
                    $dislikes = 0;
                    if (isset($video_metadata->items[0]->statistics->dislikeCount)) {
                        $dislikes = $video_metadata->items[0]->statistics->dislikeCount;
                    }

                    //generate total number of likes and dislikes
                    $ratingstotal = $likes + $dislikes;

                    //generate percentage of likes. if no likes or dislikes, set percentage of likes to 0
                    if ($ratingstotal > 0) {
                        $likespercent = intval(round(($likes / $ratingstotal) * 100));
                    } else {
                        $likespercent = 0;
                    }

                    //if the video has likes or dislikes, get the average rating (can be used for half star ratings of 1 to 5 stars)
                    //if no likes or dislikes, node will not be available - manually set rating is set to 0
                    if ($ratingstotal > 0) {
                        $rating = round($likespercent / 20, 2);
                    } else {
                        $rating = 0.0;
                    }

                    //generate full star rating of 1 to 5 stars (no half stars)
                    //if no likes or dislikes, set full star rating to 0
                    if ($ratingstotal > 0) {
                        $ratingstars = intval(round($rating));
                    } else {
                        $ratingstars = 0;
                    }

                    //initialise hours, minutes, and seconds to 0
                    $hours = 0;
                    $minutes = 0;
                    $seconds = 0;

                    //initialise duration_text (blanks the variable each run through the loop)
                    $duration_text = '';

                    //get video duration from YouTube API
                    $video_duration = $video_metadata->items[0]->contentDetails->duration;
                    $parts = array();
                    preg_match_all('/(\d+)/', $video_duration, $parts);

                    // YouTube duration may not include all parts, i.e. may send hours, minutes, and seconds, may send minutes and seconds, or possibly only send seconds.
                    // We will 0 out the duration parts that aren't sent
                    if (count($parts[0]) == 1) {
                        array_unshift($parts[0], '0', '0');
                    } elseif (count($parts[0]) == 2) {
                        array_unshift($parts[0], '0');
                    }

                    // Get seconds
                    $sec_init = $parts[0][2];
                    $seconds = ($sec_init) % 60;
                    $seconds_overflow = floor($sec_init / 60);

                    // Get minutes
                    $min_init = $parts[0][1] + $seconds_overflow;
                    $minutes = ($min_init) % 60;
                    $minutes_overflow = floor(($min_init) / 60);

                    // Get hours
                    $hours = $parts[0][0] + $minutes_overflow;

                    if ($seconds == 0 || $seconds > 1) {
                        //if more than one second or 0, use plural - seconds
                        $seconds_text = strval($seconds) . ' seconds';
                    } else {
                        //if only one second, use singular - second
                        $seconds_text = strval($seconds) . ' second';
                    }

                    if ($minutes > 1) {
                        //if more than one minute, use plural - minutes
                        $minutes_text = strval($minutes) . ' minutes';
                    } elseif ($minutes == 1) {
                        //if only one minute, use singular - minute
                        $minutes_text = strval($minutes) . ' minute';
                    } else {
                        //if 0 minutes, we will omit text
                        $minutes_text = null;
                    }

                    if ($hours > 1) {
                        //if more than one hour, use plural - hours
                        $hours_text = strval($hours) . ' hours';
                    } elseif ($hours == 1) {
                        //if only one hour, use singular - hour
                        $hours_text = strval($hours) . ' hour';
                    } else {
                        //if 0 hours, we will omit text
                        $hours_text = null;
                    }

                    if (!empty($hours_text)) {
                        $duration_text = $hours_text;
                    }
                    if (!empty($duration_text) && !empty($minutes_text)) {
                        $duration_text = $duration_text . ', ' . $minutes_text;
                    } else {
                        $duration_text = $minutes_text;
                    }
                    if (!empty($duration_text) && !empty($seconds_text)) {
                        $duration_text = $duration_text . ', ' . $seconds_text;
                    } else {
                        $duration_text = $seconds_text;
                    }

                    // Generate hh:mm:ss as-is if hours are less than 24, else add hours manually if greater than 23
                    if ($hours < 24) {
                        $numeric = new DateTime('@0');
                        $numeric->add(new DateInterval($video_duration));
                        //generate numeric time format hh:mm:ss
                        $duration_numeric = $numeric->format('H:i:s');
                    } else {
                        $numeric = new DateTime('@0');
                        $numeric->add(new DateInterval('PT' . strval($minutes) . 'M' . strval($seconds) . 'S'));
                        //generate numeric time format mm:ss
                        $duration_numeric = sprintf('%02d', $hours) . ':' . $numeric->format('i:s:');
                    }

                    //generate embedded video player url
                    $embedvideo = 'https://www.youtube.com/embed/' . $video_id;

                    // Generate the iframe YouTube Player embed code
                    $videoplayer = "<iframe width=\"$channel_playerwidth\" height=\"$channel_playerheight\" src=\"$embedvideo\" frameborder=\"0\" allowfullscreen></iframe>";

                    // Define a few different thumbnail image choice here:
                    //  0 => default image, low res 120x90 - "default" 1
                    //  1 => default image, medium res 320x180 - "mqdefault"
                    //  2 => default image, high res 480x360 - "hqdefault"
                    //  3 => low res, start frame - "0"
                    //  4 => low res, middle frame - "1"
                    //  5 => low res, last frame - "2"
                    //  6 => default image, standard res 640x480 - "sddefault"
                    //  7 => middle of vid, max res - 1280x720 - "maxresdefault"
                    //set base URL for thumbnails to use for thumbnails that are no longer returned by API call
                    $base_thumb_url = dirname($thumbnails->default->url) . '/';
                    // Pre-define thumbimg array first, then set elements and ignore errors for thumbnails that don't exist
                    $thumbimg = array(array('url' => '', 'width' => '120', 'height' => '90'),
                                      array('url' => '', 'width' => '120', 'height' => '90'),
                                      array('url' => '', 'width' => '120', 'height' => '90'),
                                      array('url' => '', 'width' => '120', 'height' => '90'),
                                      array('url' => '', 'width' => '120', 'height' => '90'),
                                      array('url' => '', 'width' => '120', 'height' => '90'),
                                      array('url' => '', 'width' => '120', 'height' => '90'),
                                      array('url' => '', 'width' => '120', 'height' => '90'));
                    $thumbimg[0] = @array('url' => $thumbnails->default->url, 'width' => $thumbnails->default->width, 'height' => $thumbnails->default->height);
                    $thumbimg[1] = @array('url' => $thumbnails->medium->url, 'width' => $thumbnails->medium->width, 'height' => $thumbnails->medium->height);
                    $thumbimg[2] = @array('url' => $thumbnails->high->url, 'width' => $thumbnails->high->width, 'height' => $thumbnails->high->height);
                    $thumbimg[3] = @array('url' => $base_thumb_url . $thumbalt[3] . '.jpg', 'width' => $thumbnails->default->width, 'height' => $thumbnails->default->height);
                    $thumbimg[4] = @array('url' => $base_thumb_url . $thumbalt[4] . '.jpg', 'width' => $thumbnails->default->width, 'height' => $thumbnails->default->height);
                    $thumbimg[5] = @array('url' => $base_thumb_url . $thumbalt[5] . '.jpg', 'width' => $thumbnails->default->width, 'height' => $thumbnails->default->height);
                    $thumbimg[6] = @array('url' => $thumbnails->standard->url, 'width' => $thumbnails->standard->width, 'height' => $thumbnails->standard->height);
                    $thumbimg[7] = @array('url' => $thumbnails->maxres->url, 'width' => $thumbnails->maxres->width, 'height' => $thumbnails->maxres->height);
                    $thumb_img = $thumbimg[$channel_thumbnail];
                    $thumb_alt = $thumbalt[$channel_thumbnail];
                    $t = 0;
                    // Loop through and add a url to thumbimg for which thumbnails weren't available
                    foreach ($thumbimg as $thumburl) {
                        if (is_null($thumburl['url'])) {
                            $thumbimg[$t]['url'] = $base_thumb_url . $thumbalt[$t] . '.jpg';
                        }
                        $t++;
                    }

                    // If show player parameter is greater than 0, pass SHOWPLAYER set to 1 to template.
                    if ($temp_showplayer > 0 && $i <= $temp_showplayer) {
                        $channel_showplayer = '1';
                    } else {
                        $channel_showplayer = '0';
                    }

                    // Disable thumbnail when player is enabled, enable thumbnail when player is disabled.
                    if ($temp_nothumbplayer == 0 && $channel_showplayer == 1) {
                        $channel_nothumbplayer = '0';
                    }
                    if ($temp_nothumbplayer == 0 && $channel_showplayer == 0) {
                        $channel_nothumbplayer = '1';
                    }

                    // Style all of the meta info using the Style template and store it all in the content variable which is passed to the main template
                    $content->attach(do_template("$channel_templatestyle", array(
                        'EMBED_ALLOWED' => $channel_embedallowed,
                        'EMBEDPLAYER_ALLOWED' => $embeddable,
                        'VIDEO_ID' => $video_id,
                        'MAX_VIDEOS' => strval($channel_maxvideos),
                        'COUNT' => strval($i),
                        'VIDEO_URL' => $video_url,
                        'CHANNEL_TITLE' => $channel_title,
                        'VIDEO_TITLE' => $title,
                        'RATING_STARS' => strval($ratingstars),
                        'RATING_LIKE_PERCENT' => strval($likespercent),
                        'RATING_NUM_RATES' => strval($ratingstotal),
                        'RATING_DISLIKES' => strval($dislikes),
                        'RATING_LIKES' => strval($likes),
                        'RATING_NUMERIC' => float_to_raw_string($rating),
                        'FAVORITE_COUNT' => strval($favoritecount),
                        'CHANNEL_URL' => $channel_url,
                        'CHANNEL_NAME' => $channel_name,
                        'UPLOAD_DATE' => $uploaded,
                        'DESCRIPTION_TYPE' => $channel_descriptiontype,
                        'DESCRIPTION' => $description_long,
                        'DESCRIPTION_SHORT' => $description_short,
                        'DESCRIPTION_SHORTENED' => strval($description_shortened),
                        'VIEWS' => $views,
                        'DURATION_TEXT' => $duration_text,
                        'DURATION_NUMERIC' => $duration_numeric,
                        'THUMBNAIL' => $thumb_img['url'],
                        'THUMBWIDTH' => strval($thumb_img['width']),
                        'THUMBHEIGHT' => strval($thumb_img['height']),
                        'THUMBALT' => $thumb_alt,
                        'THUMBNAIL_0' => $thumbimg[0]['url'],
                        'THUMBWIDTH_0' => strval($thumbimg[0]['width']),
                        'THUMBHEIGHT_0' => strval($thumbimg[0]['height']),
                        'THUMBALT_0' => $thumbalt[0],
                        'THUMBNAIL_1' => $thumbimg[1]['url'],
                        'THUMBWIDTH_1' => strval($thumbimg[1]['width']),
                        'THUMBHEIGHT_1' => strval($thumbimg[1]['height']),
                        'THUMBALT_1' => $thumbalt[1],
                        'THUMBNAIL_2' => $thumbimg[2]['url'],
                        'THUMBWIDTH_2' => strval($thumbimg[2]['width']),
                        'THUMBHEIGHT_2' => strval($thumbimg[2]['height']),
                        'THUMBALT_2' => $thumbalt[2],
                        'THUMBNAIL_3' => $thumbimg[3]['url'],
                        'THUMBWIDTH_3' => strval($thumbimg[3]['width']),
                        'THUMBHEIGHT_3' => strval($thumbimg[3]['height']),
                        'THUMBALT_3' => $thumbalt[3],
                        'THUMBNAIL_4' => $thumbimg[4]['url'],
                        'THUMBWIDTH_4' => strval($thumbimg[4]['width']),
                        'THUMBHEIGHT_4' => strval($thumbimg[4]['height']),
                        'THUMBALT_4' => $thumbalt[4],
                        'THUMBNAIL_5' => $thumbimg[5]['url'],
                        'THUMBWIDTH_5' => strval($thumbimg[5]['width']),
                        'THUMBHEIGHT_5' => strval($thumbimg[5]['height']),
                        'THUMBALT_5' => $thumbalt[5],
                        'THUMBNAIL_6' => $thumbimg[6]['url'],
                        'THUMBWIDTH_6' => strval($thumbimg[6]['width']),
                        'THUMBHEIGHT_6' => strval($thumbimg[6]['height']),
                        'THUMBALT_6' => $thumbalt[6],
                        'THUMBNAIL_7' => $thumbimg[7]['url'],
                        'THUMBWIDTH_7' => strval($thumbimg[7]['width']),
                        'THUMBHEIGHT_7' => strval($thumbimg[7]['height']),
                        'THUMBALT_7' => $thumbalt[7],
                        'PLAYERALIGN' => $channel_playeralign,
                        'PLAYERHEIGHT' => $channel_playerheight,
                        'PLAYERWIDTH' => $channel_playerwidth,
                        'EMBEDVIDEO' => $embedvideo,
                        'VIDEO_PLAYER' => $videoplayer,
                        'SHOWPLAYER' => $channel_showplayer,
                        'STYLE' => strval($channel_style),
                        'NOTHUMBPLAYER' => $channel_nothumbplayer,
                        'FOR_MORE_LEAD' => $channel_formorelead,
                        'FOR_MORE_TEXT' => $channel_formoretext,
                        'FOR_MORE_URL' => $channel_formoreurl
                    )));
                }
                $i++;
            }
        } else {
            //set error if channel request doesn't return a channel result
            $channel_error .= 'Error: Invalid playlist ID, no data returned, or possibly the YouTube API request limit is exceeded.<br />';
        }

        // Send styled content to the main template
        return do_template("$channel_templatemain", array(
            'CHANNEL_ERROR' => $channel_error,
            'CHANNEL_TITLE' => $channel_title,
            'CHANNEL_NAME' => $channel_name,
            'CHANNEL_URL' => $channel_url,
            'CONTENT' => $content));
    }
}

/**
 * Find the cache signature for the block.
 *
 * @param  array                    The block parameters.
 * @return array                    The cache signature.
 */
function block_youtube_channel__cache_on($map)
{
    return array(array_key_exists('max_videos', $map) ? intval($map['max_videos']) : 25,
                 array_key_exists('start_video', $map) ? intval($map['start_video']) : 1,
                 array_key_exists('embed_player', $map) ? intval($map['embed_player']) : 1,
                 array_key_exists('show_player', $map) ? intval($map['show_player']) : 1,
                 array_key_exists('style', $map) ? intval($map['style']) : 1,
                 array_key_exists('nothumbplayer', $map) ? intval($map['nothumbplayer']) : 0,
                 array_key_exists('thumbnail', $map) ? intval($map['thumbnail']) : 0,
                 array_key_exists('player_width', $map) ? intval($map['player_width']) : 480,
                 array_key_exists('player_height', $map) ? intval($map['player_height']) : 270,
                 array_key_exists('title', $map) ? $map['title'] : '',
                 array_key_exists('player_align', $map) ? $map['player_align'] : 'center',
                 array_key_exists('formorelead', $map) ? $map['formorelead'] : '',
                 array_key_exists('formoretext', $map) ? $map['formoretext'] : '',
                 array_key_exists('formoreurl', $map) ? $map['formoreurl'] : '',
                 array_key_exists('name', $map) ? $map['name'] : '',
                 array_key_exists('template_main', $map) ? $map['template_main'] : '',
                 array_key_exists('api_key', $map) ? $map['api_key'] : '',
                 array_key_exists('description_type', $map) ? $map['description_type'] : 'long',
                 array_key_exists('playlist_id', $map) ? $map['playlist_id'] : ''
    );
}
