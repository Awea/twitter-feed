# TwitterFeed
A simple wrapper to get Twitter user timeline with cached results.

## Installation

`composer require awea/twitter-feed`

## Usage

```php
use \Awea\TwitterFeed\TwitterFeed;

// key, secret and tokens accessible on https://apps.twitter.com/ 
$twitter_feed = new TwitterFeed([
  'screen_name'         => 'user name',
  'consumer_key'        => 'consumer_key',
  'consumer_secret'     => 'consumer_secret',
  'access_token'        => 'access_token',
  'access_token_secret' => 'access_token_secret'
]);

$tweets = $twitter_feed->getUserTL();
```
