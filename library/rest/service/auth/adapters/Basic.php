<?php
/**
 * Yii RESTful API
 *
 * @link      https://github.com/paysio/yii-rest-api
 * @copyright Copyright (c) 2012 Pays I/O Ltd. (http://pays.io)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT license
 * @package   REST_Service
 */

namespace rest\service\auth\adapters;

use rest\service\auth\AdapterInterface;

class Basic implements AdapterInterface
{
    /**
     * @var string
     */
    public $realm = 'API';

    /**
     * @var string
     */
    public $identityClass = 'application.components.UserIdentity';

    /**
     * @throws \CHttpException
     */
    public function authenticate()
    {
        header('WWW-Authenticate: Basic realm="' . $this->realm . '"');

        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            throw new \CHttpException(401, \Yii::t('ext', 'Undefined auth user'));
        }

        $user = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];

        $identityClass = \Yii::import($this->identityClass);
        $identity = new $identityClass($user, $password);
        if (!$identity->authenticate()) {
            throw new \CHttpException(401, $identity->errorMessage);
        }

        \Yii::app()->user->login($identity);
    }
}