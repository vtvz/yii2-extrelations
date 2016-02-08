<?php
namespace vtvz\extrelations;

trait RelatedRecordTrait
{
    use \vtvz\relations\RelatedRecordTrait;

    public function init()
    {
        $this->setRelationConfig([
            'class' => Relations::className(),
        ]);

        parent::init();

        $this->ensureRelations();
    }
}
