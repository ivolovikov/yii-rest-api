<?php
/**
 * Yii RESTful API
 *
 * @link      https://github.com/paysio/yii-rest-api
 * @copyright Copyright (c) 2012 Pays I/O Ltd. (http://pays.io)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT license
 * @package   REST_Service
 */

namespace rest;

class Service extends \CComponent
{
    const ERR_TYPE_PARAM = 'invalid_param_error',
          ERR_TYPE_REQUEST = 'invalid_request_error',
          ERR_TYPE_API = 'api_error';

    /**
     * @var bool
     */
    protected $_enabled = false;

    /**
     * @var array
     */
    public $authAdapterConfig = array(
        'class' => '\rest\service\auth\adapters\Basic',
    );

    /**
     * @var array
     */
    public $rendererAdapterConfig = array(
        'class' => '\rest\service\renderer\adapters\Json',
    );

    /**
     * @var string
     */
    public $controllerBehaviorName = 'restAPI';

    /**
     * @var string
     */
    public $modelBehaviorName = 'renderModel';

    /**
     * @var \rest\service\auth\AdapterInterface
     */
    protected $_authAdapter;
    /**
     * @var \rest\service\renderer\AdapterInterface
     */
    protected $_rendererAdapter;

    /**
     * Init service
     */
    public function init()
    {
        if (!$this->isEnabled()) {
            return;
        }
        $app = \Yii::app();

        $app->request->enableCsrfValidation = false;
        $app->detachEventHandler('onBeginRequest',array($app->request, 'validateCsrfToken'));

        $app->attachEventHandler('onEndRequest', array($this, 'onEndRequest'));
        $app->attachEventHandler('onBeginRequest', array($this, 'onBeginRequest'));
    }

    /**
     * @param \CEvent $event
     */
    public function onBeginRequest(\CEvent $event)
    {
        $app = \Yii::app();

        $app->attachEventHandler('onException', array($this, 'onException'));
        $app->attachEventHandler('onError', array($this, 'onError'));

        $this->getAuthAdapter()->authenticate();
    }

    /**
     * @param \CEvent $event
     */
    public function onEndRequest(\CEvent $event)
    {

    }

    /**
	 * Raised when an uncaught PHP exception occurs.
	 * @param \CExceptionEvent $event event parameter
	 */
	public function onException(\CExceptionEvent $event)
	{
        $event->handled = true;

        if ($event->exception instanceof \CHttpException) {
            $statusCode = $event->exception->statusCode;
            $message = $event->exception->getMessage();
            $type = self::ERR_TYPE_REQUEST;
        } else {
            $statusCode = 500;
            $message = $event->exception->getMessage();
            $type = self::ERR_TYPE_API;
        }

        $this->_setErrorHandlerError(array(
            'code' => ($event->exception instanceof \CHttpException) ? $event->exception->statusCode : 500,
            'type' => get_class($event->exception),
            'errorCode' => $event->exception->getCode(),
            'message' => $event->exception->getMessage(),
            'file' => $event->exception->getFile(),
            'line' => $event->exception->getLine(),
            'trace' => $event->exception->getTraceAsString(),
            'traces' => $event->exception->getTrace(),
        ));

        $this->sendError($type, defined('YII_DEBUG') && YII_DEBUG ? $message : \Yii::t('ext', 'An error has occurred'), array(), $statusCode);
	}

    /**
     * Set CErrorHandler::_error property
     * @param array $error
     */
    protected function _setErrorHandlerError(array $error)
    {
        $refObject = new \ReflectionObject(\Yii::app()->errorHandler);
        if ($refObject->hasProperty('_error')) {
            $refProperty = $refObject->getProperty('_error');
            $refProperty->setAccessible(true);
            $refProperty->setValue(\Yii::app()->errorHandler, $error);
        }
    }

	/**
	 * Raised when a PHP execution error occurs.
	 * @param \CErrorEvent $event event parameter
	 */
	public function onError(\CErrorEvent $event)
	{
        $event->handled = true;

        $this->_setErrorHandlerError(array(
            'code' => 500,
            'type' => $event->code,
            'message' => $event->message,
            'file' => $event->file,
            'line' => $event->line,
            'trace' => '',
            'traces' => array(),
        ));

        $this->sendError(self::ERR_TYPE_API, defined('YII_DEBUG') && YII_DEBUG ? $event->message : \Yii::t('ext', 'An error has occurred'));
	}

    /**
     * @param $type
     * @param $message
     * @param array $data
     * @param int $statusCode
     */
    public function sendError($type, $message, array $data = array(), $statusCode = 500)
    {
        ob_clean();

        $data['type'] = $type;
        $data['message'] = $message;

        $this->_send(array('error' => $data), $statusCode);
    }

    /**
     * @param $data
     * @param array $filterFields
     * @param int $statusCode
     */
    public function sendData($data, array $filterFields = null, $statusCode = 200)
    {
        if ($filterFields !== null && $data !== null) {
            $filteredData = array('object' => 'list');
            foreach ($filterFields as $field) {
                if (!array_key_exists($field, $data)) {
                    continue;
                }
                $filteredData[$field] = $this->_filterData($data[$field]);
            }
            $data = $filteredData;
        } else {
            $data = $this->_filterData($data);
        }

        $this->_send($data, $statusCode);
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function _filterData($data, $path = null)
    {
        if ($data instanceof \CModel && $data->hasErrors()) {
            $this->_setErrorHandlerError(array(
                'code' => 400,
                'type' => self::ERR_TYPE_PARAM,
                'message' => _('Invalid request params'),
                'file' => __FILE__,
                'line' => __LINE__,
                'trace' => '',
                'traces' => array(),
            ));

            $this->sendError(self::ERR_TYPE_PARAM, \Yii::t('ext', 'Invalid data parameters'), array(
                'params' => $this->generateModelErrorFields($data, $path)
            ), 400);
        }

        if ($data instanceof \CComponent && $data->asa($this->modelBehaviorName)) {
            $data = $data->getRenderAttributes(false);
        }
        if (is_array($data) || ($data instanceof \Traversable && !($data instanceof \CModel))) {
            $filteredData = array();
            foreach ($data as $key => $row) {
                $filteredData[$key] = $this->_filterData($row, $path ? $path . '[' . $key . ']' : $key);
            }
            $data = $filteredData;
        } elseif ($data instanceof \IDataProvider) {
            $data = $this->_filterData($data->getData());
        }

        return $data;
    }

    /**
     * @param \CModel $model
     * @param null $path
     * @return array
     */
    public function generateModelErrorFields(\CModel $model, $path = null)
    {
        $validators = \CValidator::$builtInValidators;

        if ($this->hasEventHandler('onBeforeGenerateError')) {
            $this->onBeforeGenerateError(new \CEvent($this, array('model' => $model)));
        }

        $errors = $model->getErrors();
        $errorFields = array_keys($errors);
        $errorHandled = array();

        $i = 0;
        $result = array();
        foreach ($model->getValidators() as $validator) {
            if (isset($hasError) && $validator->skipOnError || !array_intersect($validator->attributes, $errorFields)) {
                continue;
            }
            $model->clearErrors();
            $validator->validate($model);
            if ($model->hasErrors()) {
                $hasError = true;
                $code = array_search(get_class($validator), $validators);
                if ($validator instanceof \CInlineValidator) {
                    $code = $validator->method;
                }

                foreach ($validator->attributes as $attribute) {
                    if ($model->hasErrors($attribute)) {
                        $result[$i]['code'] = $code;
                        $result[$i]['message'] = $model->getError($attribute);
                        $result[$i]['name'] = $path ? $path . '[' . $attribute . ']' : $attribute;

                        $errorHandled[] = $attribute;
                        $i++;
                    }
                }
            }
        }

        foreach (array_diff($errorFields, $errorHandled) as $attribute) {
            $result[$i]['code'] = 'unknown';
            $result[$i]['message'] = implode(',', $errors[$attribute]);
            $result[$i]['name'] = $path ? $path . '[' . $attribute . ']' : $attribute;
        }

        return $result;
    }

    /**
     * @param \CEvent $event
     */
    public function onBeforeGenerateError(\CEvent $event)
    {
        $this->raiseEvent('onBeforeGenerateError', $event);
    }

    /**
     * @param $data
     * @param int $statusCode
     */
    protected function _send($data, $statusCode = 200)
    {
        if (!$data) {
            $data = new \stdClass();
        }
        $reasonPhrase = self::getStatusPhrase($statusCode);

        header($_SERVER['SERVER_PROTOCOL'] . " {$statusCode} {$reasonPhrase}");

        $this->getRendererAdapter()->render($data);

        \Yii::app()->end();
    }

    /**
     * @static
     * @param $statusCode
     * @return string
     */
    public static function getStatusPhrase($statusCode)
    {
        switch ($statusCode) {
            case 200:
                $reasonPhrase = 'OK';
                break;
            case 201:
                $reasonPhrase = 'Created';
                break;
            case 400:
                $reasonPhrase = 'Bad Request';
                break;
            case 401:
                $reasonPhrase = 'Unauthorized';
                break;
            case 403:
                $reasonPhrase = 'Forbidden';
                break;
            case 404:
                $reasonPhrase = 'Not Found';
                break;
            case 500:
                $reasonPhrase = 'Internal Server Error';
                break;
            default:
                $reasonPhrase = '...';
        }
        return $reasonPhrase;
    }

    /**
     * @return \rest\service\auth\AdapterInterface
     */
    public function getAuthAdapter()
    {
        if ($this->_authAdapter === null) {
            $this->_authAdapter = \Yii::createComponent($this->authAdapterConfig);
        }
        return $this->_authAdapter;
    }

    /**
     * @return \rest\service\renderer\AdapterInterface
     */
    public function getRendererAdapter()
    {
        if ($this->_rendererAdapter === null) {
            $this->_rendererAdapter = \Yii::createComponent($this->rendererAdapterConfig);
        }
        return $this->_rendererAdapter;
    }

    /**
     * @return Service
     */
    public function enable()
    {
        $this->_enabled = true;
        return $this;
    }

    /**
     * @return Service
     */
    public function disable()
    {
        $this->_enabled = false;
        return $this;
    }

    /**
     * @param $value
     */
    public function setEnable($value)
    {
        $this->_enabled = $value;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_enabled;
    }
}
