<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Hit]].
 *
 * @see Hit
 */
class HitQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return Hit[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Hit|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
