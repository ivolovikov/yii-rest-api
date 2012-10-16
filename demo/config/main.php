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
                array('rest/index',  'pattern' => 'api/rest', 'verb' => 'GET'),
                array('rest/create', 'pattern' => 'api/rest', 'verb' => 'POST', 'parsingOnly' => true),
                array('rest/view',   'pattern' => 'api/rest/<id>', 'verb' => 'GET'),
                array('rest/update', 'pattern' => 'api/rest/<id>', 'verb' => 'PUT', 'parsingOnly' => true),
                array('rest/delete', 'pattern' => 'api/rest/<id>', 'verb' => 'DELETE', 'parsingOnly' => true),
            )
		),
	),
);