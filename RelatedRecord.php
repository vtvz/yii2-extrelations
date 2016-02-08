<?php
namespace vtvz\extrelations;

use yii\db\ActiveRecord;

class RelatedRecord extends ActiveRecord
{
    use RelatedRecordTrait;
}
