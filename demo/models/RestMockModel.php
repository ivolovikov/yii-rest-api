<?php
/**
 * Yii RESTful API
 *
 * @link      https://github.com/paysio/yii-rest-api
 * @copyright Copyright (c) 2012 Pays I/O Ltd. (http://pays.io)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT license
 * @package   REST_Service_DEMO
 */

/**
 * @method array getRenderAttributes(bool $recursive = true)
 */
class RestMockModel extends CModel
{
    public $id = 'TEST_ID';

    public $version = 0.1;

    public $name = 'Yii REST API';

    public $hidden;

    public function __construct()
	{
		$this->attachBehaviors($this->behaviors());
	}

    /**
     * @return array
     */
    public function attributeNames()
    {
        return array('id', 'version', 'name', 'hidden');
    }

    /**
     * @return array
     */
    public function rules()
    {
        return array(
            array('version', 'numerical'),
            array('name', 'length', 'max' => 244),

            array('id,version,name', 'safe', 'on' => 'render'),
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
}