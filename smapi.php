<?php
/**
 * SERPmetrics PHP-SDK
 */

class SMapi {

    const VERSION = 'v2.1.1';

    public static $apiUrl = 'api.serpmetrics.com';
    public static $userAgent = 'SERPmetrics PHP5 Library';
    public static $serializer = array('json_encode', 'json_decode');
    public static $retries = 3;

    protected $_http_status = null;
    protected $_credentials = array(
        'key' => null,
        'secret' => null
        );


    /**
     * Sets up a new SM instance
     *
     * @param array $credentials
     * @return void
     */
    public function __construct($credentials = array()) {
        $this->_credentials = $credentials;
    }


    /**
     * Adds a new keyword to the queue. $engines should be passed as an array
     * of {engine}_{locale} strings. For example:
     * Array
     * (
     *     [0] => google_en-us
     *     [1] => yahoo_en-us
     * )
     *
     * @param string $keyword
     * @param array $engines
     * @return mixed
     */
    public function add($keyword, $engines, $location = null, $device = 'desktop') {
        if (!is_array($engines) && !empty($engines)) {
            $engines = array($engines);
        }

        $options = array(
            'path' => '/keywords/add',
            'params' => array(
                'keyword' => $keyword,
                'engines' => $engines,
                'location' => $location,
                'device' => $device
                )
            );
        $res = self::rest($options);
        return $res;
    }


    /**
     * Remove a keyword from the queue.
     * Note: this REMOVES a keyword entirely, including ALL engines assigned. To update
     *       a keywords engine list, simply call add() with the new engine list.
     *
     * @param string $keyword_id
     * @return mixed
     */
    public function remove($keyword_id) {
        $options = array(
            'path' => '/keywords/delete',
            'params' => array(
                'keyword_id' => $keyword_id
                )
            );
        $res = self::rest($options);
        return $res;
    }


    /**
     * Adds a new keyword to the delayed queue, usage as per add()
     */
    public function delayed_add($keyword, $engines, $location = null, $device = 'desktop') {
        if (!is_array($engines) && !empty($engines)) {
            $engines = array($engines);
        }

        $options = array(
            'path' => '/delayed/add',
            'params' => array(
                'keyword' => $keyword,
                'engines' => $engines,
                'location' => $location,
                'device' => $device
                )
            );
        $res = self::rest($options);
        return $res;
    }


    /**
     * Gets status for a given $delayed_id
     *
     * @param string $delayed_id
     * @return mixed
     */
    public function delayed_status($delayed_id) {
        $options = array(
            'path' => '/delayed/status',
            'params' => array(
                'delayed_id' => $priority_id
                )
            );
        $res = self::rest($options);
        return $res;
    }


    /**
     * Adds a new keyword to the priority queue, usage as per add()
     */
    public function priority_add($keyword, $engines, $location = null, $device = 'desktop') {
        if (!is_array($engines) && !empty($engines)) {
            $engines = array($engines);
        }

        $options = array(
            'path' => '/priority/add',
            'params' => array(
                'keyword' => $keyword,
                'engines' => $engines,
                'location' => $location,
                'device' => $device
                )
            );
        $res = self::rest($options);
        return $res;
    }

    /**
     * Gets status for a given $priority_id
     *
     * @param string $priority_id
     * @return mixed
     */
    public function priority_status($priority_id) {
        $options = array(
            'path' => '/priority/status',
            'params' => array(
                'priority_id' => $priority_id
                )
            );
        $res = self::rest($options);
        return $res;
    }


    /**
     * Gets last $limit SERP check timestamps/ids for keyword/engine combination. $engine
     * should be in the format {engine}_{locale} (for example google_en-us).
     *
     * @param string $keyword_id
     * @param string $engine
     * @param integer $limit (optional)
     * @return array
     */
    public function check($keyword_id, $engine, $limit = 10) {
        $options = array(
            'path' => '/keywords/check',
            'params' => array(
                'keyword_id' => $keyword_id,
                'engine' => $engine,
                'limit' => $limit
                )
            );
        $res = self::rest($options);
        return $res;
    }


    /**
     * Get SERP data for given id. Restricted to optional specified domain
     *
     * @param string $id
     * @param string $domain
     * @return mixed
     */
    public function serp($check_id, $domain = null) {
        $options = array(
            'path' => '/keywords/serp',
            'params' => array(
                'check_id' => $check_id,
                'domain' => $domain
                )
            );
        $res = self::rest($options);
        return $res;
    }


    /**
     * Get current credit balance
     *
     * @return mixed
     */
    public function credit() {
        $options = array(
            'path' => '/users/credit',
            );
        $res = self::rest($options);
        return $res;
    }

    /**
     * Get trended flux data for a given engine_code
     *
     * @param string $engine_code
     * @param string $type
     * @return mixed
     */
    public function flux($engine_code, $type = 'daily') {
        $options = array(
            'path' => '/flux/trend',
            'params' => array(
                'engine_code' => $engine_code,
                'type' => $type
                )
            );
        $res = self::rest($options);
        return $res;
    }


    /**
     * Generates authentication signature
     *
     * @param array $credentials
     * @return array
     */
    protected static function _generateSignature($credentials = null) {
        $ts = time();
        if (empty($credentials)) {
            $credentials = $this->_credentials;
        }
        $signature = base64_encode(hash_hmac('sha256', $ts, $credentials['secret'], true));

        return array('ts'=>$ts, 'signature'=>$signature);
    }


    /**
     * Generates a REST request to the API with retries and exponential backoff
     *
     * @param array $options
     * @param array $credentials
     * @return mixed
     */
    public function rest($options, $credentials = array()) {
        $defaults = array(
            'method' => 'POST',
            'url' => self::$apiUrl,
            'path' => '/',
            'query' => array()
            );

        $options = $options + $defaults;

        if (empty($credentials)) {
            $credentials = $this->_credentials;
        }

        if (!empty($options['params'])) {
            $params = htmlentities(json_encode($options['params']));
        }

        $auth = self::_generateSignature($credentials);
        $options['query'] = $options['query'] + array(
            'key' => $credentials['key'],
            'auth' => $auth['signature'],
            'ts' => $auth['ts'],
            'params' => (!empty($params)) ? $params : null,
            );

        $attempt = 0;
        while (true) {
            $attempt++;

            $curl = curl_init($options['url'] . $options['path']);
            curl_setopt_array($curl, array(
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => ($defaults['method'] == 'POST') ? true : false,
                CURLOPT_POSTFIELDS => ($defaults['method'] == 'POST') ? $options['query'] : http_build_query($options['query']),
                CURLOPT_USERAGENT => self::$userAgent .' '. self::VERSION
                ));

            $r = curl_exec($curl);
            $this->_http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($error = curl_error($curl)) {
                trigger_error('SMapi: curl error: ' . curl_error($curl), E_USER_WARNING);
                if (!self::_exponentialBackoff($attempt, self::$retries)) {
                    return false;
                }
                continue;
            }
            break;
        }

        return call_user_func_array(self::$serializer[1], array($r, true));
    }

    /**
     * Return the last HTTP status code received. Useful for debugging purposes.
     *
     * @return integer
     */
    public function httpStatus() {
        return $this->_http_status;
    }


    /**
     * Implements exponential backoff
     *
     * @param integer $current
     * @param integer $max
     * @return boolean
     */
    protected static function _exponentialBackoff($current, $max) {
        if ($current <= $max) {
            $delay = (int)(pow(2, $current) * 100000);
            usleep($delay);
            return true;
        }
        return false;
    }

}
