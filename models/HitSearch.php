<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Hit;

/**
 * HitSearch represents the model behind the search form of `app\models\Hit`.
 */
class HitSearch extends Hit
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['link_id'], 'integer'],
            [['datetime', 'ip', 'user_agent', 'country', 'city', 'os', 'os_version', 'browser', 'browser_version'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Hit::find();

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'datetime' => SORT_DESC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // Search by created User
        $query
            ->joinWith('link')
            ->where(['link.created_by' => Yii::$app->user->id]);

        // grid filtering conditions
        $query->andFilterWhere([
            'hit.link_id' => $this->link_id,
            'hit.datetime' => $this->datetime,
            'hit.os_version' => $this->os_version,
            'hit.browser_version' => $this->browser_version,
        ]);

        $query
            ->andFilterWhere(['like', 'hit.ip', $this->ip])
            ->andFilterWhere(['like', 'hit.user_agent', $this->user_agent])
            ->andFilterWhere(['like', 'hit.country', $this->country])
            ->andFilterWhere(['like', 'hit.city', $this->city])
            ->andFilterWhere(['like', 'hit.os', $this->os])
            ->andFilterWhere(['like', 'hit.browser', $this->browser]);

        return $dataProvider;
    }
}
