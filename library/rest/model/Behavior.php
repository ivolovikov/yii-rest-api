<?php
/**
 * Yii RESTful API
 *
 * @link      https://github.com/paysio/yii-rest-api
 * @copyright Copyright (c) 2012 Pays I/O Ltd. (http://pays.io)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT license
 * @package   REST_Model
 */

namespace rest\model;

class Behavior extends \CBehavior
{
    /**
     * @var string
     */
    public $scenarioName = 'render';

    /**
     * @var string
     */
    public $behaviorName = 'renderModel';

    /**
     * @return array
     */
    public function getAttributeNames()
    {
        $attributes = array();
        foreach ($this->getOwner()->rules() as $rule) {
            if (!isset($rule['on']) || $rule['on'] != $this->scenarioName) {
                continue;
            }
            $attr = explode(',', $rule[0]);
            $attributes = array_merge($attributes, array_map('trim', $attr));
        }
        return array_unique($attributes);
    }

    /**
     * @param bool $recursive
     * @return array
     */
    public function getRenderAttributes($recursive = true)
    {
        $model = $this->getOwner();
        $attrs = array('object' => $this->getObjectId());
        foreach ($this->getAttributeNames() as $name) {
            $attr = $model->$name;
            if ($recursive) {
                if ($attr instanceof \CComponent && $attr->asa($this->behaviorName)) {
                    $attr = $attr->getRenderAttributes($recursive);
                } elseif (is_array($attr) || $attr instanceof \Traversable) {
                    $renderedAttr = array();
                    foreach ($attr as $key => $row) {
                        if ($row instanceof \CComponent && $row->asa($this->behaviorName)) {
                            $renderedAttr[$key] = $row->getRenderAttributes($recursive);
                        } else {
                            $renderedAttr[$key] = $row;
                        }
                    }
                    $attr = $renderedAttr;
                }
            }
            $attrs[$name] = $attr;
        }
        return $attrs;
    }

    /**
     * @return string
     */
    public function getObjectId()
    {
        $model = $this->getOwner();
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', get_class($model)));
    }

    /**
     * @return \CModel
     */
    public function getOwner()
    {
        return parent::getOwner();
    }
}