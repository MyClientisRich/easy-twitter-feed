<?php
/**
 * Created by PhpStorm.
 * User: thomas
 * Date: 12/11/2015
 * Time: 13:35
 */

namespace ETF;


use Abraham\TwitterOAuth\TwitterOAuth;

class EasyTwitterFeed {

    public $_user;

    public $_consumerKey;
    public $_consumerSecret; // string
    public $_accessToken; // string
    public $_accessTokenSecret;

    private $_connexion;

    private $_cacheName = "tweets.ETF";
    private $_folder = "./";

    public $_cacheTime = 0;

    /**
     * EasyTwitterFeed constructor.
     * @param string $_user
     * @param $_consumerKey
     * @param $_consumerSecret
     * @param $_accessToken
     * @param $_accessTokenSecret
     */
    public function __construct($_user, $_consumerKey, $_consumerSecret, $_accessToken, $_accessTokenSecret)
    {
        $this->_user = $_user;
        $this->_consumerKey = $_consumerKey;
        $this->_consumerSecret = $_consumerSecret;
        $this->_accessToken = $_accessToken;
        $this->_accessTokenSecret = $_accessTokenSecret;

        $this->_connexion = new TwitterOAuth($this->_consumerKey, $this->_consumerSecret, $this->_accessToken, $this->_accessTokenSecret);
    }

    private function storeTweet($_nbTweet = 2, $_includeRT = true, $_excludeReplies = false) {

        $tweets = $this->_connexion->get(
            "statuses/user_timeline",
            [
                'screen_name' => $this->_user,
                'count' => $_nbTweet,
                'include_rts' => $_includeRT,
                'exclude_replies' => $_excludeReplies
            ]
        );

        $data = array('twitter_result' => $tweets, 'timestamp' => time());
        file_put_contents($this->_folder . $this->_cacheName, serialize($data));

        return $tweets;
    }

    public function getTweets($_nbTweet = 2, $_includeRT = true, $excludeReplies = false) {

        if(file_exists($this->_folder . $this->_cacheName)) {
            $data = unserialize(file_get_contents($this->_folder . $this->_cacheName));

            if($data['timestamp'] > time() - ($this->_cacheTime * 60)) {
                return $data['twitter_result'];
            } else {
                return $this->storeTweet($_nbTweet = 2, $_includeRT = true, $excludeReplies = false);
            }
        } else {
            return $this->storeTweet($_nbTweet = 2, $_includeRT = true, $excludeReplies = false);
        }

    }
}