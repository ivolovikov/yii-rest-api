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
 * @method bool isPost()
 * @method bool isPut()
 * @method bool isDelete()
 * @method string renderRest(string $view, array $data = null, bool $return = false, array $fields = array())
 * @method void redirectRest(string $url, bool $terminate = true, int $statusCode = 302)
 * @method bool isRestService()
 * @method \rest\Service getRestService()
 */
class RestController extends Controller
{
    public function init()
	{
	    Yii::app()->restService->enable();
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array(
            'restAPI' => array('class' => '\rest\controller\Behavior')
        );
    }

    public function actionIndex()
    {
        $model = new RestMockModel();
        $data = array(
            'count' => 100,
            'data' => array($model, $model, $model)
        );
        $this->render('empty', $data, false, array('count', 'data'));
    }

    public function actionView()
    {
        $model = $this->loadModel();
        $this->render('empty', array('model' => $model), false, array('model'));
    }

    public function actionCreate()
    {
        $model =  new RestMockModel();

        if ($this->isPost() && ($data = $_POST)) {
            $model->attributes = $data;
            if ($model->validate()) {
                $this->redirect(array('view', 'id' => $model), true, 201);
            }
        }
        $this->render('empty', array('model' => $model), false, array('model'));
    }

    public function actionUpdate()
    {
        $model = $this->loadModel();
        $data = array(
            'version' => Yii::app()->request->getPut('version'),
            'name' => Yii::app()->request->getPut('name'),
        );

        if ($this->isPut() && $data) {
            $model->attributes = $data;
            if ($model->validate()) {
                $this->redirect(array('view', 'id' => $model));
            }
        }
        $this->render('empty', array('model' => $model), false, array('model'));
    }

    public function actionDelete()
    {
        if ($this->isDelete()) {
            $model = $this->loadModel();
            $this->redirect(array('index', $model));
        } else {
            throw new \CHttpException(400, Yii::t('app', 'Invalid delete request'));
        }
    }

    /**
     * @return RestMockModel
     * @throws CHttpException
     */
    public function loadModel()
    {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $object = new RestMockModel();
        if ($id != $object->id) {
            throw new CHttpException(404, Yii::t('app', 'Object not found'));
        }
        return $object;
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
	public function render($view, $data = null, $return = false, array $fields = array())
	{
        if (($behavior = $this->asa('restAPI')) && $behavior->getEnabled()) {
            return $this->renderRest($view, $data, $return, $fields);
        } else {
            return parent::render($view, $data, $return);
        }
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
    public function redirect($url, $terminate = true, $statusCode = 302)
    {
        if (($behavior = $this->asa('restAPI')) && $behavior->getEnabled()) {
            $this->redirectRest($url, $terminate, $statusCode);
        } else {
            parent::redirect($url, $terminate, $statusCode);
        }
    }
}