<?php
/**
 * Yii RESTful API
 *
 * @link      https://github.com/paysio/yii-rest-api
 * @copyright Copyright (c) 2012 Pays I/O Ltd. (http://pays.io)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT license
 * @package   REST_Service_Demo
 */

YiiBase::setPathOfAlias('rest', realpath(__DIR__ . '/../extensions/yii-rest-api/library/rest'));

return array(
	'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
	'name' => 'My Web Application',

	'preload' => array('restService'),

	'import' => array(
		'application.models.*',
		'application.components.*',
	),

	'components' => array(
        'restService' => array(
            'class'  => '\rest\Service',
            'enable' => isset($_SERVER['REQUEST_URI']) && (strpos($_SERVER['REQUEST_URI'], '/api/') !== false), //for example
        ),

		'urlManager' => array(
			'urlFormat'      => 'path',
			'showScriptName' => false,
            'baseUrl'        => '',
            'rules'          => array(
                array('restUser/index',  'pattern' => 'api/users', 'verb' => 'GET', 'parsingOnly' => true),
                array('restUser/create', 'pattern' => 'api/users', 'verb' => 'POST', 'parsingOnly' => true),
                array('restUser/view',   'pattern' => 'api/users/<id>', 'verb' => 'GET', 'parsingOnly' => true),
                array('restUser/update', 'pattern' => 'api/users/<id>', 'verb' => 'PUT', 'parsingOnly' => true),
                array('restUser/delete', 'pattern' => 'api/users/<id>', 'verb' => 'DELETE', 'parsingOnly' => true),
            )
		),
	),
);