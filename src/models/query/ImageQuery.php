<?php

namespace rabadan731\images\models\query;

/**
 * This is the ActiveQuery class for [[\common\modules\monitoring\models\Monitoring]].
 *
 * @see \common\modules\monitoring\models\Monitoring
 */
class ImageQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \rabadan731\images\models\Image[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \rabadan731\images\models\Image|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }


    public function table($objectTable)
    {
        $this->andWhere([
            'object_table' => $objectTable
        ]);
        return $this;
    }

    public function tableID($objectId)
    {
        $this->andWhere([
            'object_id' => $objectId
        ]);
        return $this;
    }


}
