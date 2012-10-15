<?php
/**
 * Yii RESTful API
 *
 * @link      https://github.com/paysio/yii-rest-api
 * @copyright Copyright (c) 2012 Pays I/O Ltd. (http://pays.io)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT license
 * @package   REST_Service_TEST
 */

/*
 * Set error reporting to the level to which Zend Framework code must comply.
 */
error_reporting(E_ALL | E_STRICT);

defined('RESTAPI_TESTS_PATH') or define('RESTAPI_TESTS_PATH', __DIR__);

defined('TEST_BASE_URL') or define('TEST_BASE_URL', 'http://test.local/');

require_once __DIR__ . '/_autoload.php';