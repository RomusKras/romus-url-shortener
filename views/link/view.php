<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\Link */
/* @var $searchModel app\models\HitSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $model->hash;
$this->params['breadcrumbs'][] = ['label' => 'Короткие ссылки', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="link-view">

    <div class="row">
        <div class="col-xs-6">
            <h1><?= $this->title ?></h1>
        </div>
        <div class="col-xs-6 mt-2 ml-2">
            <p class="text-right">
                <?php //Html::a('Статистика переходов', ['hit/index', 'link_id' => $model->id], ['class' => 'btn btn-default']) ?>
                <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Уверены, что хотите удалить ссылку?',
                        'method' => 'post',
                    ],
                ]) ?>
            </p>
        </div>
    </div>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'url:url',
            'description',
            'short_url:url',
            //'created:datetime',
            [
                'attribute' => 'created',
                'format' => 'raw',
                'value' => function ($model) {
                    /** @var $model \app\models\Hit  */
                    return Yii::$app->formatter->asDatetime($model->created, 'dd-MM-yyyy HH:mm:ss');
                },
                'filter' => false,
            ],
            'counter',
        ],
    ]) ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'ip',
            [
                'label' => 'Местоположение',
                'format' => 'raw',
                'value' => function ($model) {
                    /** @var $model \app\models\Hit  */
                    return $model->country . ' (' . $model->city . ')';
                },
                'filter' => false,
            ],
            [
                'label' => 'ОС',
                'format' => 'raw',
                'value' => function ($model) {
                    /** @var $model \app\models\Hit  */
                    return $model->os . ' ' . $model->os_version;
                },
                'filter' => false,
            ],
            [
                'label' => 'Браузер',
                'format' => 'raw',
                'value' => function ($model) {
                    /** @var $model \app\models\Hit  */
                    return $model->browser . ' ' . $model->browser_version;
                },
                'filter' => false,
            ],
            [
                'attribute' => 'datetime',
                'format' => 'raw',
                'value' => function ($model) {
                    /** @var $model \app\models\Hit  */
                    return Yii::$app->formatter->asDatetime($model->datetime, 'dd-MM-yyyy HH:mm:ss');
                },
                'filter' => false,
            ]
        ]
    ]); ?>

</div>
