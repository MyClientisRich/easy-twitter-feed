<?php

namespace ETF;


class Tweet
{

    private $_tweet;
    public $_text;

    public $_lang = 'fr';

    public $_langDate = [

        'fr' => [
            'y' => 'an',
            'm' => 'mois',
            'w' => 'semaine',
            'd' => 'jour',
            'h' => 'heure',
            'i' => 'minute',
            's' => 'seconde',
            'now' => 'À l\'instant'
        ],
        'en' => [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
            'now' => 'Few second ago'
        ]

    ];

    /**
     * Tweet constructor.
     * @param $_tweet
     */
    public function __construct($_tweet)
    {
        $this->_tweet = $_tweet;

        $this->_text = $this->_tweet->text;
    }


    public function toHTML() {

        $this->hashTags();
        $this->mentions();
        $this->urls();

        return $this->_text;
    }

    private function hashTags() {
        $linkified = array();
        foreach ($this->_tweet->entities->hashtags as $hashtag) {
            $hash = $hashtag->text;
            if (in_array($hash, $linkified)) {
                continue; // do not process same hash twice or more
            }
            $linkified[] = $hash;
            // replace single words only, so looking for #Google we wont linkify >#Google<Reader
            $this->_text = preg_replace('/#\b' . $hash . '\b/', sprintf('<a target="_blank" class="hashtag" href="https://twitter.com/search?q=%%23%2$s&src=hash">#%1$s</a>', $hash, urlencode($hash)), $this->_text);
        }
    }

    private function mentions() {
        $linkified = array();
        foreach ($this->_tweet->entities->user_mentions as $userMention) {
            $name = $userMention->name;
            $screenName = $userMention->screen_name;
            if (in_array($screenName, $linkified)) {
                continue; // do not process same user mention twice or more
            }
            $linkified[] = $screenName;
            // replace single words only, so looking for @John we wont linkify >@John<Snow
            $this->_text = preg_replace('/@\b' . $screenName . '\b/', sprintf('<a target="_blank" class="mention" href="https://www.twitter.com/%1$s" title="%2$s">@%1$s</a>', $screenName, $name), $this->_text);
        }
    }

    private function urls() {
        $linkified = array();
        foreach ($this->_tweet->entities->urls as $url) {
            $url = $url->url;
            if (in_array($url, $linkified)) {
                continue; // do not process same url twice or more
            }
            $linkified[] = $url;
            $this->_text = str_replace($url, sprintf('<a target="_blank" class="url" href="%1$s">%1$s</a>', $url), $this->_text);
        }
    }

    public function getTime($full = false) {
        $now = new \DateTime();
        $ago = new \DateTime($this->_tweet->created_at);

        $diff = $now->diff($ago);
        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = $this->_langDate[$this->_lang];

        foreach ($string as $k => &$v) {

            if($k == "now") { continue; }

            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }
        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) : $this->_langDate[$this->_lang]['now'];
    }

    /**
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->_lang = $lang;
    }

}