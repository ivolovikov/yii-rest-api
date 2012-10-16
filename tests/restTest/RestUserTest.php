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

class RestUserTest extends AbstractTest
{
    public function testCreate()
    {
        $result = $this->post('/api/users', array(
            'email' => 'test@test.local',
            'password' => 'hidden_password',
        ));
        $model = json_decode($result['body']);

        $this->assertEquals($model->id, 'TEST_ID');
        $this->assertEquals($model->email, 'test@test.local');
        $this->assertFalse(isset($model->password));

        $this->assertEquals($result['code'], 201);

        $this->assertNotEmpty($result['location']);
    }

    public function testView()
    {
        $result = $this->get('/api/users/TEST_ID');
        $model = json_decode($result['body']);

        $this->assertEquals($model->id, 'TEST_ID');
        $this->assertEquals($model->email, 'user@test.local');
        $this->assertEquals($model->name, 'Test REST User');

        $this->assertEquals($result['code'], 200);
    }

    public function testIndex()
    {
        $result = $this->get('/api/users');
        $data = json_decode($result['body']);

        $this->assertEquals($data->count, 100);
        $this->assertCount(3, $data->data);
        $this->assertEquals($data->data[0]->id, 'TEST_ID');

        $this->assertEquals($result['code'], 200);
    }

    public function testUpdate()
    {
        $result = $this->put('/api/users/TEST_ID', array(
            'email' => 'newmail@test.local',
            'name'  => 'Updated Name'
        ));
        $model = json_decode($result['body']);

        $this->assertEquals($model->id, 'TEST_ID');
        $this->assertEquals($model->email, 'newmail@test.local');
        $this->assertEquals($model->name, 'Updated Name');

        $this->assertEquals($result['code'], 200);
    }

    public function testError()
    {
        $result = $this->put('/api/users/TEST_ID', array('email' => 'wrong_email'));
        $model = json_decode($result['body']);

        $this->assertEquals($model->error->type, 'invalid_param_error');
        $this->assertCount(1, $model->error->params);
        $this->assertEquals($model->error->params[0]->name, 'email');

        $this->assertEquals($result['code'], 400);
    }

    public function testDelete()
    {
        $result = $this->delete('/api/users/TEST_ID');
        $model = json_decode($result['body']);

        $this->assertEquals($model->id, 'TEST_ID');

        $this->assertEquals($result['code'], 200);
    }
}