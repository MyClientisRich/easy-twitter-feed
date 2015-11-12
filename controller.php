<?php
    require_once("lib/twitteroauth/twitteroauth.php"); //Path to twitteroauth library


    // FILL IN !!

    $twitteruser = "myclientisrich"; // string, no @
    $notweets = ; // int
    $consumerkey = ""; // string
    $consumersecret = ""; // string
    $accesstoken = ""; // string
    $accesstokensecret = ""; // string
    $include_rts = true; // bool
    $exclude_replies = false; // bool

    $just_now_string = "À l'instant";

    function getConnectionWithAccessToken($cons_key, $cons_secret, $oauth_token, $oauth_token_secret) {
        $connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
        return $connection;
    }

    /*
    ** CACHE SYSTEM : THROUGH twitter_result.data
    */

    if (file_exists('twitter_result.data')) {
        $data = unserialize(file_get_contents('twitter_result.data'));
        if ($data['timestamp'] > time() - 10 * 60) {
            $tweet = $data['twitter_result'];
        }
    }

    if (!$tweet) { // cache doesn't exist or is older than 10 mins
        $connection = getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);
        $tweet = $connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$twitteruser."&count=".$notweets."&include_rts=".$include_rts."&exclude_replies=".$exclude_replies);

        $data = array ('twitter_result' => $twitter_result, 'timestamp' => time());
        file_put_contents('twitter_result.data', serialize($data));
    }

    /*
    ** END CACHE SYSTEM
    */

    $tweet = json_decode(json_encode($tweet));

    /*
    ** Convert #, @ and links to actual HTML links
    */

    function tweet_html_text($tweet) {
        $text = $tweet->text;

        // hastags
        $linkified = array();
        foreach ($tweet->entities->hashtags as $hashtag) {
            $hash = $hashtag->text;

            if (in_array($hash, $linkified)) {
                continue; // do not process same hash twice or more
            }
            $linkified[] = $hash;

            // replace single words only, so looking for #Google we wont linkify >#Google<Reader
            $text = preg_replace('/#\b' . $hash . '\b/', sprintf('<a target="_blank" href="https://twitter.com/search?q=%%23%2$s&src=hash">#%1$s</a>', $hash, urlencode($hash)), $text);
        }

        // user_mentions
        $linkified = array();
        foreach ($tweet->entities->user_mentions as $userMention) {
            $name = $userMention->name;
            $screenName = $userMention->screen_name;

            if (in_array($screenName, $linkified)) {
                continue; // do not process same user mention twice or more
            }
            $linkified[] = $screenName;

            // replace single words only, so looking for @John we wont linkify >@John<Snow
            $text = preg_replace('/@\b' . $screenName . '\b/', sprintf('<a target="_blank" href="https://www.twitter.com/%1$s" title="%2$s">@%1$s</a>', $screenName, $name), $text);
        }

        // urls
        $linkified = array();
        foreach ($tweet->entities->urls as $url) {
            $url = $url->url;

            if (in_array($url, $linkified)) {
                continue; // do not process same url twice or more
            }
            $linkified[] = $url;

            $text = str_replace($url, sprintf('<a target="_blank" href="%1$s">%1$s</a>', $url), $text);
        }

        return $text;
    }

    /*
    ** Get time elapsed from twitter's created_at variable.
    */

    function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'an',
            'm' => 'mois',
            'w' => 'semaine',
            'd' => 'jour',
            'h' => 'heure',
            'i' => 'minute',
            's' => 'seconde',
        );

        $string_en = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );

        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) : $just_now_string;
    }
?>