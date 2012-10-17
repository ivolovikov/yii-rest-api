<?php
/**
 * Yii RESTful API
 *
 * @link      https://github.com/paysio/yii-rest-api
 * @copyright Copyright (c) 2012 Pays I/O Ltd. (http://pays.io)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT license
 * @package   REST_Service_Demo
 */

/**
 * @method array getRenderAttributes(bool $recursive = true)
 * @method string getObjectId()
 */
class RestUser extends CModel
{
    public $id = 'TEST_ID';

    public $email = 'user@test.local';

    public $name = 'Test REST User';

    public $password;

    public function __construct($scenario = null)
	{
		if ($scenario === null) {
            $scenario = Yii::app()->controller->getAction()->getId();
        }
        $this->scenario = $scenario;
        $this->attachBehaviors($this->behaviors());
	}

    /**
     * @return array
     */
    public function attributeNames()
    {
        return array('id', 'email', 'name', 'password');
    }

    /**
     * @return array
     */
    public function rules()
    {
        return array(
            array('email, password', 'required', 'on' => 'create'),
            array('email', 'email'),
            array('name, password', 'length', 'max' => 244),

            array('id, email, name', 'safe', 'on' => 'render'),
        );
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array(
            'renderModel' => array('class' => '\rest\model\Behavior')
        );
    }

    /**
     * @return bool
     */
    public function save()
    {
        // does nothing
        return $this->validate();
    }
}