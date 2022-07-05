<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\models\Link */

$this->title = 'Создать короткую ссылку';
$this->params['breadcrumbs'][] = ['label' => 'Короткие ссылки', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="link-create container">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="link-form">

        <?php $form = ActiveForm::begin(); 
        /* ActiveForm::begin([
            'action' => ['index'],
            //'method' => 'get',
            'options' => ['id' => $model -> formName()],
            'enableAjaxValidation' => true,
        ]); */ ?>

        <?= $form->field($model, 'url',[
            'errorOptions' => [
                'encode' => false,
            ]
        ])->textInput(['maxlength' => true])->label("Адрес ссылки"); ?>

        <?= $form->field($model, 'description')->textInput(['maxlength' => true])->label("Описание"); ?>

        <div class="form-group">
            <?= Html::submitButton('Сократить ссылку', ['class' => 'btn btn-success']); ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
   <?php /* $script = <<< JS
    $(document).ready(function () {
        $('body').on('beforeSubmit', 'form#{$model -> formName()}', function () {
            var form = $(this);
            // return false if form still have some validation errors
            if (form.find('.has-error').length) 
            {
                return false;
            }
            // submit form
            $.ajax({
            url    : form.attr('action'),
            type   : 'get',
            data   : form.serialize(),
            success: function (response) 
            {
                var getupdatedata = $(response).find('#filter_id_test');
                // $.pjax.reload('#note_update_id'); for pjax update
                $('#yiiikap').html(getupdatedata);
                //console.log(getupdatedata);
            },
            error  : function () 
            {
                console.log('internal server error');
            }
            });
            return false;
         });
    });
    JS;
    $this->registerJs($script); */?>

</div>
