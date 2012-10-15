<?php
/**
 * Yii RESTful API
 *
 * @link      https://github.com/paysio/yii-rest-api
 * @copyright Copyright (c) 2012 Pays I/O Ltd. (http://pays.io)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT license
 * @package   REST_Service_DEMO
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
            'enable' => isset($_REQUEST['_rest']),
        ),

		'urlManager' => array(
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
	),
);