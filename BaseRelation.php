<?php
namespace vtvz\relations;

use yii\base\Component;

/**
*
*/
abstract class BaseRelation extends Component
{
    public $name;
    public $owner;

    public $model;
    public $callable = null;

    protected $link;
    protected $inverseOf;
    protected $via;
    protected $viaTable;
    protected $viaLink;

    protected $delete = null;
    protected $unlink = null;
    protected $type   = null;

    protected $types  = [];

    abstract public function get();
    abstract public function add($value);
    abstract public function create();

    public function getLink()
    {
        return $this->link;
    }

    public function setLink($link)
    {
        $this->link = $link;
    }

    public function getInverseOf()
    {
        return $this->inverseOf;
    }

    public function setInverseOf($inverseOf)
    {
        $this->inverseOf = $inverseOf;
    }

    public function getVia()
    {
        return $this->via;
    }

    public function setVia($via)
    {
        $this->via = $via;
    }

    public function getViaTable()
    {
        return $this->viaTable;
    }

    public function setViaTable($viaTable)
    {
        $this->viaTable = $viaTable;
    }

    public function getViaLink()
    {
        return $this->viaLink;
    }

    public function setViaLink($viaLink)
    {
        $this->viaLink = $viaLink;
    }

    public function getDelete()
    {
        return $this->delete;
    }

    public function setDelete($delete)
    {
        $this->delete = $delete;
    }

    public function getUnlink()
    {
        return $this->unlink;
    }

    public function setUnlink($unlink)
    {
        $this->unlink = $unlink;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        if (!key_exists($type, $this->types)) {
            throw new InvalidParamException('Param "type" is invalid');
        }

        $this->type = $type;
    }

    public function getTypes()
    {
        return $this->types;
    }
}
