## Yii RESTful API

This is extension for Yii Framework (http://www.yiiframework.com/), which can easy add RESTful API to existing web application.

### INSTALLATION

All of this code yo can see in *demo* folder.

- Unpack *library* folder to *YOUR_EXTENSION_PATH/yii-rest-api*
- Update yours *config/main.php*

Add new path of alias at the beginning

    YiiBase::setPathOfAlias('rest', realpath(__DIR__ . 'YOUR_EXTENSION_PATH/yii-rest-api/library/rest'));

Add extension service to preload and components sections

    'preload' => array('restService'),  

    'components' => array(  
        'restService' => array(  
            'class'  => '\rest\Service',  
            'enable' => isset($_REQUEST['_rest']), // for example  
        ),  
    ),

Change routing settings

    'urlManager'=>array(
        'urlFormat'      => 'path',
        'showScriptName' => false,
        'baseUrl'        => '',
        'rules' => array(
            array('<controller>/index',  'pattern' => 'api/<controller:\w+>', 'verb' => 'GET'),
            array('<controller>/create', 'pattern' => 'api/<controller:\w+>', 'verb' => 'POST'),
            array('<controller>/view',   'pattern' => 'api/<controller:\w+>/<id>', 'verb' => 'GET'),
            array('<controller>/update', 'pattern' => 'api/<controller:\w+>/<id>', 'verb' => 'PUT'),
            array('<controller>/delete', 'pattern' => 'api/<controller:\w+>/<id>', 'verb' => 'DELETE'),
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

    public function render($view, $data = null, $return = false, array $fields = null)
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

### REQUIREMENTS

PHP >= 5.3.0  
Yii Framework >= 1.1.8

### LICENSE

Copyright (c) 2012 Pays I/O Ltd. (http://pays.io)

Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php

