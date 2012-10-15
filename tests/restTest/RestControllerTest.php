<?php
/**
 * Yii RESTful API
 *
 * @link      https://github.com/paysio/yii-rest-api
 * @copyright Copyright (c) 2012 Pays I/O Ltd. (http://pays.io)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT license
 * @package   REST_Service_TEST
 */

namespace restTest;

class RestControllerTest extends AbstractTest
{
    public function testCreate()
    {
        $result = $this->post('/api/rest', array('version' => 0.2));
        $model = json_decode($result['body']);

        $this->assertEquals($model->id, 'TEST_ID');
        $this->assertEquals($model->version, 0.2);

        $this->assertEquals($result['code'], 201);

        $this->assertContains('api/rest/TEST_ID', $result['location']);
    }

    public function testView()
    {
        $result = $this->get('/api/rest/TEST_ID');
        $model = json_decode($result['body']);
        $model = $model->model;

        $this->assertEquals($model->id, 'TEST_ID');
        $this->assertEquals($model->version, 0.1);

        $this->assertEquals($result['code'], 200);
    }

    public function testIndex()
    {
        $result = $this->get('/api/rest');
        $data = json_decode($result['body']);

        $this->assertEquals($data->count, 100);
        $this->assertCount(3, $data->data);
        $this->assertEquals($data->data[0]->id, 'TEST_ID');

        $this->assertEquals($result['code'], 200);
    }

    public function testUpdate()
    {
        $result = $this->put('/api/rest/TEST_ID', array('version' => '0.3'));
        $model = json_decode($result['body']);

        $this->assertEquals($model->id, 'TEST_ID');
        $this->assertEquals($model->version, 0.3);

        $this->assertEquals($result['code'], 200);
    }

    public function testError()
    {
        $result = $this->put('/api/rest/TEST_ID', array('version' => 'wrong_version'));
        $model = json_decode($result['body']);

        $this->assertEquals($model->error->type, 'invalid_param_error');
        $this->assertCount(1, $model->error->params);
        $this->assertEquals($model->error->params[0]->name, 'version');

        $this->assertEquals($result['code'], 400);
    }

    public function testDelete()
    {
        $result = $this->delete('/api/rest/TEST_ID');
        $model = json_decode($result['body']);

        $this->assertEquals($model->id, 'TEST_ID');

        $this->assertEquals($result['code'], 200);
    }
}