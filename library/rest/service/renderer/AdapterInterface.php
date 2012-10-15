<?php
/**
 * Yii RESTful API
 *
 * @link      https://github.com/paysio/yii-rest-api
 * @copyright Copyright (c) 2012 Pays I/O Ltd. (http://pays.io)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT license
 * @package   REST_Service
 */

namespace rest\service\renderer;

interface AdapterInterface
{
    /**
     * @abstract
     * @param $data
     * @param bool $return
     * @return string|void
     */
    public function render($data, $return = false);
}