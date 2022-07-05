<?php

/** @var yii\web\View $this */
use yii\widgets\ActiveForm;
use yii\helpers\Html;

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <!-- <div class="jumbotron text-center bg-transparent">
        <h1 class="display-4">Congratulations!</h1>

        <p class="lead">You have successfully created your Yii-powered application.</p>

        <p><a class="btn btn-lg btn-success" href="http://www.yiiframework.com">Get started with Yii</a></p>
    </div> -->

    <div class="body-content">

    <div class="jumbotron">
        <h1>Сократитель 3000</h1>

        <p class="lead">Сервис для создания коротких ссылок.</p>

        <?php
        if (Yii::$app->user->isGuest) {
            echo \yii\helpers\Html::a('Вход на сайт', ['/site/login'], ['class'=>'btn btn-lg btn-success']);
        } else {
            echo \yii\helpers\Html::a('Создать новую ссылку', ['/link/create'], ['class'=>'btn btn-lg btn-success']);
        }
        ?>

    </div>
        
    </div>
</div>
