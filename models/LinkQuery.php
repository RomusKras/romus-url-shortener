<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Link]].
 *
 * @see Link
 */
class LinkQuery extends \yii\db\ActiveQuery
{
    /**
     * Find active link
     *
     * @return $this
     */
    // public function active()
    // {
    //     return $this->andWhere(['or',
    //         ['expiration' => null],
    //         ['>=', 'expiration', gmdate("Y-m-d H:i:s")]
    //     ]);
    // }

    /**
     * @inheritdoc
     * @return Link[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Link|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
