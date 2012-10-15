<?php
/**
 * Yii RESTful API
 *
 * @link      https://github.com/paysio/yii-rest-api
 * @copyright Copyright (c) 2012 Pays I/O Ltd. (http://pays.io)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT license
 * @package   REST_Service
 */

namespace rest\service\renderer\adapters;

use rest\service\renderer\AdapterInterface;

class Xml implements AdapterInterface
{
    /**
     * @param $data
     * @param bool $return
     * @return string|void
     */
    public function render($data, $return = false)
    {
        $xml = new \SimpleXMLElement('<response/>');
        $this->arrayToXml($data, $xml);

        $result = $xml->asXML();

        if ($return) {
            return $result;
        }

        header('Content-type: application/xhtml+xml');
        echo $result;
    }

    public function arrayToXml($data, &$xml)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (isset($value['object'])) {
                    $subnode = $xml->addChild((string)$value['object']);
                    unset($value['object']);
                } else {
                    $subnode = $xml->addChild(is_numeric($key) ? "item_$key" : (string)$key);
                }

                $this->arrayToXml($value, $subnode);
            } else {
                $xml->addChild("$key","$value");
            }
        }
    }
}