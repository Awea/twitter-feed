<?php
/**
 * TwitterFeed 
 *
 * @link      https://github.com/Awea/twitter_feed
 * @license   https://github.com/Awea/twitter_feed/blob/master/LICENSE (LGPL License)
 */
namespace Awea\TwitterFeed;

use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterFeed 
{
    // @var Assoc array used to merge with constructor $opts
    private $default_opts = [
        'cache_folder'     => 'api/cache',
        'cache_expiration' => 3600
    ];

    // @var String path to cache file
    private $cache_file;

    // @var Integer to define cache expiration
    private $cache_expiration;

    // @var String Twitter username
    private $screen_name;

    // @var Instance of TwitterOAuth
    private $twitter_client;

    /**
     * Constructor
     *
     * @param assoc array $opts with keys 'screen_name', 'consumer_key',
     * 'consumer_secret', 'access_token', 'access_token_secret',
     * 'cache_folder', 'cache_expiration'
     */
    public function __construct($opts) 
    {
        $opts                   = $this->mergeDefaults($opts);
        $this->cache_expiration = $opts['cache_expiration'];
        $this->screen_name      = $opts['screen_name'];
        $this->twitter_client   = new TwitterOAuth(
            $opts['consumer_key'], $opts['consumer_secret'], 
            $opts['access_token'], $opts['access_token_secret']
        );
        $this->cache_folder     = $this->getFullPath($opts['cache_folder']);
        $this->cache_file       = $this->cache_folder.$this->screen_name.'.json';
    }

    /**
     * Get User Timeline
     *
     * @param integer $count specifies the number of Tweets to try and retrieve
     * @param boolean $exclude_replies prevent replies from appearing
     * @param boolean $include_rts strip any native retweets 
     *
     * @return array
     */
    public function getUserTL($count = 5, $exclude_replies = true, $include_rts = false) {
        // Check if cache file doesn't exist or needs to be updated
        if(!is_file($this->cache_file) || (date("U")-date("U", filemtime($this->cache_file))) > $this->cache_expiration) {
            $response = $this->twitter_client->get('statuses/user_timeline', [
                'screen_name'     => $this->screen_name,
                'count'           => $count, 
                'exclude_replies' => $exclude_replies,
                'include_rts'     => $include_rts
            ]);

            $this->updateCache($response);
        }

        return $this->readCache();
    }

    /**
     * Merge constructor opts with default opts
     *
     * @param  array $opts Assoc array of arguments (see default_opts)
     * @return array merged with defaults
     */
    private function mergeDefaults($opts){
        return array_merge($this->default_opts, $opts);
    }

    /**
     * Read cache 
     *
     * @return array
     */
    private function readCache() {
        return json_decode(file_get_contents($this->cache_file));
    }

    /**
     * Update Cache
     *
     * @return file_put_contents result (FALSE or number of bytes written)
     */
    private function updateCache($response) {
        if(is_dir($this->cache_folder)){
            if(is_writable($this->cache_folder)){
                return file_put_contents($this->cache_file, json_encode($response));
            }
            else {
                throw new TwitterFeedException("Error, the folder you have specified is not writable.");
            }
        }
        else {
            throw new TwitterFeedException("Error, the folder you have specified does not exist.");
        }
    }

    /**
     * Return full path to cache folder
     *
     * @param string $cache_folder relative location of the cache folder
     * @return string 
     */
    private function getFullPath($cache_folder){
        $doc_root = $_SERVER['DOCUMENT_ROOT'];
        
        if (substr($doc_root, -1) != '/'){
          $doc_root = $doc_root.'/';
        }

        return $doc_root.$cache_folder . '/';
    }
}