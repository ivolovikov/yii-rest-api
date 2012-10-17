## Yii RESTful API

This is extension for Yii Framework (http://www.yiiframework.com/), which can easy add RESTful API to existing web application.

### EXAMPLE
#### Request
     curl http://test.local/api/users \
       -u demo:demo \
       -d email="user@test.local" \
       -d password="passwd"
#### Response
    {
        "object":"rest_user",
        "id":"TEST_ID",
        "email":"user@test.local",
        "name":"Test REST User"
    }

### INSTALLATION

All of this code yo can find in *demo* folder.

- Unpack *library* folder to *%YOUR_EXTENSION_PATH%/yii-rest-api*
- Update yours *config/main.php*

Add new path of alias at the beginning

    YiiBase::setPathOfAlias('rest', '%YOUR_EXTENSION_PATH%/yii-rest-api/library/rest');

Add extension service to preload and components sections

    'preload' => array('restService'),  

    'components' => array(  
        'restService' => array(  
            'class'  => '\rest\Service',  
            'enable' => strpos($_SERVER['REQUEST_URI'], '/api/') !== false, // for example
        ),  
    ),

Change routing settings

    'urlManager'=>array(
        'urlFormat'      => 'path',
        'showScriptName' => false,
        'baseUrl'        => '',
        'rules' => array(
            array('%YOUR_CONTROLLER%/index',  'pattern' => 'api/%YOUR_CONTROLLER%', 'verb' => 'GET', 'parsingOnly' => true),
            array('%YOUR_CONTROLLER%/create', 'pattern' => 'api/%YOUR_CONTROLLER%', 'verb' => 'POST', 'parsingOnly' => true),
            array('%YOUR_CONTROLLER%/view',   'pattern' => 'api/%YOUR_CONTROLLER%/<id>', 'verb' => 'GET', 'parsingOnly' => true),
            array('%YOUR_CONTROLLER%/update', 'pattern' => 'api/%YOUR_CONTROLLER%/<id>', 'verb' => 'PUT', 'parsingOnly' => true),
            array('%YOUR_CONTROLLER%/delete', 'pattern' => 'api/%YOUR_CONTROLLER%/<id>', 'verb' => 'DELETE', 'parsingOnly' => true),
        )
    ),

- Update parent or specific Controller

Add behavior

    public function behaviors()
    {
        return array(
            'restAPI' => array('class' => '\rest\controller\Behavior')
        );
    }

Overwrite render method (if need it)

    public function render($view, $data = null, $return = false, array $fields = array())
    {
        if (($behavior = $this->asa('restAPI')) && $behavior->getEnabled()) {
            return $this->renderRest($view, $data, $return, $fields);
        } else {
            return parent::render($view, $data, $return);
        }
    }

Overwrite redirect method (if need it)

    public function redirect($url, $terminate = true, $statusCode = 302)
    {
        if (($behavior = $this->asa('restAPI')) && $behavior->getEnabled()) {
            $this->redirectRest($url, $terminate, $statusCode);
        } else {
            parent::redirect($url, $terminate, $statusCode);
        }
    }

- Upate parent or specific ActiveRecord Model (or any other instance of CModel), if you need render rules.

Add behavior

    public function behaviors()
    {
        return array(
            'renderModel' => array('class' => '\rest\model\Behavior')
        );
    }

Add rule

    public function rules()
    {
        return array(            
            array('field1,field2,field3', 'safe', 'on' => 'render'),
        );
    }

### METHODS

Methods that can be used after behaviors attached

Controller methods

    /**
     * @method bool isPost()
     * @method bool isPut()
     * @method bool isDelete()
     * @method string renderRest(string $view, array $data = null, bool $return = false, array $fields = array())
     * @method void redirectRest(string $url, bool $terminate = true, int $statusCode = 302)
     * @method bool isRestService()
     * @method \rest\Service getRestService()
     */

Model methods

    /**
     * @method array getRenderAttributes(bool $recursive = true)
     * @method string getObjectId()
     */

### REQUIREMENTS

PHP >= 5.3.0  
Yii Framework >= 1.1.8

### LICENSE

Copyright (c) 2012 Pays I/O Ltd. (http://pays.io)

Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php

