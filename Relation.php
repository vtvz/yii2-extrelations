<?php
namespace vtvz\extrelations;


// use yii\base\Object;
use yii\base\InvalidParamException;
use yii\base\InvalidConfigException;
use yii\base\InvalidValueException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;


/**
*
*/
class Relation extends \vtvz\relations\BaseRelation
{
    /* N relation(s) to M owner(s) */
    const TYPE_ONE_TO_ONE   = 'one_to_one'; //one relation to one owner
    const TYPE_ONE_TO_MANY  = 'one_to_many'; //one relation to many owners
    const TYPE_MANY_TO_ONE  = 'many_to_one'; //many relations to one owner
    const TYPE_MANY_TO_MANY = 'many_to_many'; //many to many relation

    protected $types = [
        self::TYPE_ONE_TO_ONE => 'hasOne',
        self::TYPE_ONE_TO_MANY => 'hasOne',
        self::TYPE_MANY_TO_ONE => 'hasMany',
        self::TYPE_MANY_TO_MANY => 'hasMany',
    ];

    public $linkTemplates = [
        self::TYPE_ONE_TO_ONE => ['id' => 'id'],
        self::TYPE_ONE_TO_MANY => ['id' => '{name-v}Id'],
        self::TYPE_MANY_TO_ONE => ['{inverseOf-v}Id' => 'id'],
        self::TYPE_MANY_TO_MANY => ['id' => '{name-vs}Id'],
    ];

    public $viaLinkTemplate = ['{inverseOf-vs}Id' => 'id'];

    public $viaTableTemplate = '{{%mn_{aRel-us}_has_{bRel-us}}}';// u - unserscore; s - singularize

    public $parser = null;

    public $parserConfig = [
        'class' => 'vtvz\bparser\Parser',
    ];

    public function init()
    {
        parent::init();

        if ($this->parser === null) {
            $parts = [];


            $parts['name']      = $this->name;
            $parts['inverseOf'] = $this->inverseOf;

            $rels = [$this->inverseOf, $this->name];
            sort($rels);
            list($parts['aRel'], $parts['bRel']) = $rels;

            $this->parser = \Yii::createObject(array_merge($this->parserConfig, ['parts' => $parts]));
        }

    }

    public function save()
    {
        foreach ($this->populations as $population) {
            if (!$population->save()) {
                throw new InvalidValueException('Can\'t save populated value');
            }

            $this->owner->link($this->name, $population);
        }

        return true;
    }

    public function unlink()
    {
        if (!$this->getUnlink()) {
            return true;
        }

        $this->owner->unlinkAll($this->name, $this->getDelete());

        return true;
    }


    /**
     * Получение ActiveQuery связи
     */
    public function get()
    {
        $type = $this->types[$this->getType()];

        /** @var ActiveQuery $relation */
        $relation = $this->owner->{$type}($this->model, $this->getLink());



        if ($this->getCallable instanceof \Closure) {
            $relation = call_user_func($this->getCallable, $relation);
        }

        if (!empty($this->getVia())) {
            $relation->via($this->via);
        } elseif ($this->getType() === self::TYPE_MANY_TO_MANY) {
            $relation->viaTable($this->getViaTable(), $this->getViaLink());
        }


        if (!empty($this->inverseOf)) {
            $relation->inverseOf($this->inverseOf);
        }



        return $relation;
    }

    public function add($value)
    {
        if (!($value instanceof $this->model)) {
            throw new InvalidParamException('Value shoud be instance of ' . $this->model);
        }

        if (empty($this->inverseOf)) {
            throw new InvalidConfigException('Property "inverseOf" shoudn\'t be empty');

        }

        if ($this->addCallable instanceof \Closure) {
            $value = call_user_func($this->addCallable, $value);
        }

        if (!$value->validate()) {
            throw new InvalidValueException('Value shoud be valid');
        }


        $this->populations[] = $value;

        /*if (!empty($this->getViaTable()) && ($value->getIsNewRecord() || $this->owner->getIsNewRecord())) {
            if (!$value->save(false) || $this->owner->save()) {
                throw new InvalidValueException('Can\'t save value');
            }
        }

        $this->owner->link($this->name, $value);*/

    }

    public function create($params = [])
    {
        return \Yii::createObject($this->model, $params);
    }

    /*

     #####                        #       #
     #                            #
     #      #   #  # ##    ###   ####    ##     ###   # ##    ###
     ####   #   #  ##  #  #   #   #       #    #   #  ##  #  #
     #      #   #  #   #  #       #       #    #   #  #   #   ###
     #      #  ##  #   #  #   #   #  #    #    #   #  #   #      #
     #       ## #  #   #   ###     ##    ###    ###   #   #  ####


    */

    public function parseLink($template)
    {
        $link = [];

        foreach ($template as $key => $value) {
            $link[$this->parseStr($key)] = $this->parseStr($value);
        }

        return $link;
    }

    public function parseStr($value)
    {
        return $this->parser->run($value);
    }

    /*

       ###           #      #
      #   #          #      #
      #       ###   ####   ####    ###   # ##    ###
      #      #   #   #      #     #   #  ##  #  #
      #  ##  #####   #      #     #####  #       ###
      #   #  #       #  #   #  #  #      #          #
       ###    ###     ##     ##    ###   #      ####


    */

    public function getUnlink()
    {
        if ($this->unlink !== null) {
            return (bool) $this->unlink;
        }

        $unlinks = [
            self::TYPE_MANY_TO_ONE,
            self::TYPE_MANY_TO_MANY,
            self::TYPE_ONE_TO_ONE
        ];

        if (in_array($this->getType(), $unlinks)) {

            return true;
        }

        return false;
    }

    public function getDelete()
    {
        if ($this->delete !== null) {
            return (bool) $this->delete;
        }

        $deletes = [
            self::TYPE_MANY_TO_ONE,
            self::TYPE_MANY_TO_MANY,
            self::TYPE_ONE_TO_ONE
        ];

        if (in_array($this->getType(), $deletes)) {

            return true;
        }

        return false;
    }

    public function getType()
    {

        if ($this->type !== null) {
            return $this->type;
        }

        if (!empty($this->viaTable)) {
            return self::TYPE_MANY_TO_MANY;
        }

        if (!empty($this->link)) {
            if (key_exists('id', $this->link)) {
                return self::TYPE_ONE_TO_MANY;
            }

            if (in_array('id', $this->link)) {
                return self::TYPE_MANY_TO_ONE;
            }
        }

        return self::TYPE_ONE_TO_ONE;
    }

    public function getLink()
    {
        if ($this->link !== null) {
            return $this->parseLink($this->link);
        }

        $template = $this->linkTemplates[$this->getType()];

        return $this->parseLink($template);
    }

    public function getViaTable()
    {
        if ($this->viaTable != null) {
            return $this->parseStr($this->viaTable);
        }

        if ($this->getType() != self::TYPE_MANY_TO_MANY) {
            return false;
        }

        return $this->parseStr($this->viaTableTemplate);
    }

    public function getViaLink()
    {
        if ($this->viaLink) {
            return $this->parseLink($this->viaLink);
        }

        return $this->parseLink($this->viaLinkTemplate);

    }
}
