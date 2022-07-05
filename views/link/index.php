<?php


use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\LinkSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Список ссылок';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="link-index">

    <div class="row">
        <div class="col-xs-6">
            <h1><?= $this->title ?></h1>
        </div>
        <div class="col-xs-6">
            <p class="text-right ml-4 mt-2">
                <?= Html::a('Создать новую ссылку', ['create'], ['class' => 'btn btn-success']) ?>
            </p>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'url',
            'description',
            'short_url',
            'counter',
            [
                'class' => 'yii\grid\ActionColumn',
                'contentOptions' => [
                    'class' => 'text-right'
                ],
                'buttonOptions' => [
                    'class' => 'action-button'
                ]
            ]
        ]
    ]); ?>
</div>
