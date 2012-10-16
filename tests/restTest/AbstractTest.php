<?php
/**
 * Yii RESTful API
 *
 * @link      https://github.com/paysio/yii-rest-api
 * @copyright Copyright (c) 2012 Pays I/O Ltd. (http://pays.io)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT license
 * @package   REST_Service_TEST
 */

namespace restTest;

abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $url
     * @param array $params
     * @param array $options
     * @return array
     */
    public function get($url, array $params = null, array $options = null)
    {
        $options[CURLOPT_HTTPGET] = 1;
        if (count($params) > 0) {
            $url .= '?' . $this->_encode($params);
        }
        return $this->_request($url, $options);
    }

    /**
     * @param $url
     * @param array $params
     * @param array $options
     * @return array
     */
    public function post($url, array $params, array $options = null)
    {
        $options[CURLOPT_POST] = 1;
        $options[CURLOPT_POSTFIELDS] = $params;
        return $this->_request($url, $options);
    }

    /**
     * @param $url
     * @param array $params
     * @param array $options
     * @return array
     */
    public function put($url, array $params, array $options = null)
    {
        $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
        $options[CURLOPT_POSTFIELDS] = $params;
        return $this->_request($url, $options);
    }

    /**
     * @param $url
     * @param array $params
     * @param array $options
     * @return array
     */
    public function delete($url, array $params = null, array $options = null)
    {
        $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        if (count($params) > 0) {
            $options[CURLOPT_POSTFIELDS] = $params;
        }
        return $this->_request($url, $options);
    }

    /**
     * @param $url
     * @param array $options
     * @param array $headers
     * @return array
     * @throws \CException
     */
    protected function _request($url, array $options, array $headers = array())
    {
        $ch = curl_init();

        $defaultOptions = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => 1,
            CURLOPT_USERPWD => 'demo:demo',
        );

        if (isset($options[CURLOPT_POSTFIELDS])) {
            $options[CURLOPT_POSTFIELDS] = $this->_encode($options[CURLOPT_POSTFIELDS]);
        }

        $options = $defaultOptions + $options;

        $options[CURLOPT_URL] = rtrim(TEST_BASE_URL, '/') . '/' . utf8_encode(trim($url, '/'));

        if ($headers !== null) {
            $options[CURLOPT_HTTPHEADER] = $headers;
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        if ($response === false) {
            throw new \CException(curl_error($ch), curl_errno($ch));
        }

        $info = curl_getinfo($ch);

        $header = substr($response, 0, $info['header_size']);
        $body = substr($response, $info['header_size']);

        preg_match("/Location: (.*?)\n/", $header, $matches);
        $location = isset($matches[1]) ? $matches[1] : null;

        return array('body' => $body, 'code' => $info['http_code'], 'location' => $location);
    }

    /**
     * @param array $params
     * @return string
     */
    protected function _encode(array $params)
    {
        return http_build_query($params, null, '&');
    }
}