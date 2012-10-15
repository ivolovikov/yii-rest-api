<?php
/**
 * Yii RESTful API
 *
 * @link      https://github.com/paysio/yii-rest-api
 * @copyright Copyright (c) 2012 Pays I/O Ltd. (http://pays.io)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT license
 * @package   REST_Controller
 */

namespace rest\controller;

class Behavior extends \CBehavior
{
    /**
     * @var string
     */
    public $serviceName = 'restService';

    /**
     * @var string
     */
    public $behaviorName = 'restAPI';

    /**
     * Check POST request
     * @return bool
     */
    public function isPost()
    {
        return \Yii::app()->request->isPostRequest;
    }

    /**
     * Check PUT request
     * @return bool
     */
    public function isPut()
    {
        $request = \Yii::app()->request;
        return $this->isRestService() ? $request->isPutRequest : $request->isPostRequest;
    }

    /**
     * Check DELETE request
     * @return bool
     */
    public function isDelete()
    {
        $request = \Yii::app()->request;
        return $this->isRestService() ? $request->isDeleteRequest : $request->isPostRequest;
    }

    /**
	 * Renders a view with a layout.
	 *
	 * @param string $view name of the view to be rendered. See {@link getViewFile} for details
	 * about how the view script is resolved.
	 * @param array $data data to be extracted into PHP variables and made available to the view script
	 * @param boolean $return whether the rendering result should be returned instead of being displayed to end users.
	 * @param array $fields allowed fields to REST render
	 * @return string the rendering result. Null if the rendering result is not required.
	 * @see renderPartial
	 * @see getLayoutFile
	 */
	public function renderRest($view, $data = null, $return = false, array $fields = null)
	{
        if ($this->hasEventHandler('onBeforeRender')) {
            $this->onBeforeRender(new \CEvent($this, array('view' => &$view, 'data' => &$data, 'return' => &$return, 'fields' => &$fields)));
        }

        if ($this->isRestService() && !$return) {
            $this->getRestService()->sendData($data, $fields);
        }

        $this->getOwner()->disableBehavior($this->behaviorName);
        $result = $this->getOwner()->render($view, $data, $return);
        $this->getOwner()->enableBehavior($this->behaviorName);

        return $result;
	}

    /**
     * @param \CEvent $event
     */
    public function onBeforeRender(\CEvent $event)
    {
        $this->raiseEvent('onBeforeRender', $event);
    }

    /**
     * Redirects the browser to the specified URL or route (controller/action).
     * @param mixed $url the URL to be redirected to. If the parameter is an array,
     * the first element must be a route to a controller action and the rest
     * are GET parameters in name-value pairs.
     * @param boolean|integer $terminate whether to terminate OR REST response status code !!!
	 * @param integer $statusCode the HTTP status code. Defaults to 302. See {@link http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html}
     * for details about HTTP status code.
     */
    public function redirectRest($url, $terminate = true, $statusCode = 302)
    {
        if ($this->hasEventHandler('onBeforeRedirect')) {
            $this->onBeforeRedirect(new \CEvent($this, array('url' => &$url, 'terminate' => &$terminate, 'statusCode' => &$statusCode)));
        }

        $model = $this->replaceModelIdInUrl($url);

        $this->getOwner()->disableBehavior($this->behaviorName);
        if ($this->isRestService()) {
            if ($statusCode == 201) {
                $this->getOwner()->redirect($url, false, 201);
            } else {
                $statusCode = 200;
            }
            $this->getRestService()->sendData($model, null, $statusCode);
        }

        $this->getOwner()->redirect($url, $terminate);
        $this->getOwner()->enableBehavior($this->behaviorName);
    }

    /**
     * @param \CEvent $event
     */
    public function onBeforeRedirect(\CEvent $event)
    {
        $this->raiseEvent('onBeforeRedirect', $event);
    }

    /**
     * @param $url
     * @return null|\CModel
     */
    public function replaceModelIdInUrl(&$url)
    {
        $model = null;
        if (is_array($url) && ($params = array_splice($url, 1))) {
            $route = isset($url[0]) ? $url[0] : '';
            foreach ($params as $id => $param) {
                if ($param instanceof \CModel) {
                    if (isset($param->$id)) {
                        $params[$id] = $param->$id;
                    } else {
                        unset($params[$id]);
                    }
                    $model = $param;
                }
            }
            if (strpos($route, 'http') === false) {
                $url = array_merge(array($route), $params);
            } else {
                $url = $route . ($params ? '?' . http_build_query($params) : '');
            }
        }
        return $model;
    }

    /**
     * Is REST service enabled
     * @return bool
     */
    public function isRestService()
    {
        return $this->getRestService()->isEnabled();
    }

    /**
     * @return \rest\Service
     */
    public function getRestService()
    {
        return \Yii::app()->{$this->serviceName};
    }

    /**
     * @return \CController
     */
    public function getOwner()
    {
        return parent::getOwner();
    }
}