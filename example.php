<?php
require 'vendor/autoload.php';

use \ETF\EasyTwitterFeed;

try {

    $ETF = new EasyTwitterFeed(
        'TWITTER_NAME',
        'CONSUMER KEY',
        'CONSUMER SECRET',
        'ACCESS TOKEN',
        'ACCESS TOKEN SECRET'
    );

    $tweets = $ETF->getTweets(3, false, true);

    foreach($tweets as $tweet) {
        ?>
        <div>
            <?=$tweet->toHTML()?>
            <div>Il y a <?=$tweet->getTime();?></div>
        </div>
        <br><br>
        <?php
    }

} catch (Exception $e) {

    var_dump($e);

}