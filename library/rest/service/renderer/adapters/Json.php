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

class Json implements AdapterInterface
{
    /**
     * @param $data
     * @param bool $return
     * @return string|void
     */
    public function render($data, $return = false)
    {
        $result = self::prettyPrint(json_encode($data));

        if ($return) {
            return $result;
        }

        header('Content-type: application/json');
        echo $result;
    }

    /**
     * Pretty-print JSON string from ZendFramework
     *
     * Use 'indent' option to select indentation string - by default it's a tab
     *
     * @param string $json Original JSON string
     * @param array $options Encoding options
     * @return string
     */
    public static function prettyPrint($json, $options = array())
    {
        $tokens = preg_split('|([\{\}\]\[,])|', $json, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = "";
        $indent = 0;

        $ind = "\t";
        if (isset($options['indent'])) {
            $ind = $options['indent'];
        }

        $inLiteral = false;
        foreach ($tokens as $token) {
            if ($token == "") continue;

            $prefix = str_repeat($ind, $indent);
            if (!$inLiteral && ($token == "{" || $token == "[")) {
                $indent++;
                if ($result != "" && $result[strlen($result)-1] == "\n") {
                    $result .= $prefix;
                }
                $result .= "$token\n";
            } elseif (!$inLiteral && ($token == "}" || $token == "]")) {
                $indent--;
                $prefix = str_repeat($ind, $indent);
                $result .= "\n$prefix$token";
            } elseif (!$inLiteral && $token == ",") {
                $result .= "$token\n";
            } else {
                $result .= ($inLiteral ?  '' : $prefix) . $token;

                // Count # of unescaped double-quotes in token, subtract # of
                // escaped double-quotes and if the result is odd then we are
                // inside a string literal
                if ((substr_count($token, "\"")-substr_count($token, "\\\"")) % 2 != 0) {
                    $inLiteral = !$inLiteral;
                }
            }
        }
        return $result;
   }
}