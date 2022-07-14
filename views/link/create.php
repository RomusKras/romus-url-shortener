<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Link */

$this->title = 'Создать короткую ссылку';
$this->params['breadcrumbs'][] = ['label' => 'Короткие ссылки', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="link-create container">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="link-form">

        <?php //$form = ActiveForm::begin(); 
         $form = ActiveForm::begin([
            'action' => ['create'],
            'options' => ['id' => $model -> formName()],
            'enableAjaxValidation' => true,
            'validationUrl' => Url::toRoute('link/validate'),   
            'fieldConfig' => [
                'errorOptions'         => [
                    'tag'   => 'div',
                    'class' => 'form-control-feedback-error',
                ],
            ],
        ]);  
        ?>
        

        <?= $form->field($model, 'url',[
            'errorOptions' => [
                'encode' => false,
            ],
        ])->textInput(['maxlength' => true])->label("Адрес ссылки")->error(); ?>

        <?= $form->field($model, 'description')->textInput(['maxlength' => true])->label("Описание"); ?>

        <div class="form-group">
            <?= Html::submitButton('Сократить ссылку', ['class' => 'btn btn-success']); ?>
        </div>
        <?php ActiveForm::end(); ?>

    </div>
   <?php  
    $script = <<< JS
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
            type   : 'post',
            data   : form.serialize(),
            success: function (result) 
            {
                if (result.confirmed == false) {
                    // Форма не принята

                } else {
                    // Форма принята, показываем полученную сокр. ссылку
                    if ($('div.short-link-wrap.container').length) {
                        $('div.short-link-wrap.container').empty();
                    } else {
                        $('div.link-create.container').after($('<div>', {
                            class: 'short-link-wrap container'
                        }));
                    }
                    $('div.short-link-wrap.container').append($('<h1>', {
                        text: 'Ссылка сокращена.'
                    }));       
                    $('div.short-link-wrap.container').append($('<div>', {
                        'class': 'short-link',
                        text: 'Сокращенная ссылка: '
                    }));     
                    $('div.short-link-wrap.container div.short-link').append($('<a>', {
                        'class': 'short-link',
                        text: window.location.origin+'/'+result.link.hash,
                        href: window.location.origin+'/'+result.link.hash
                    })); 
                    form.each(function(){
                        this.reset();
                    });
                }
            },
            error: function () 
            {
                console.log('internal server error');
            }
            });
            return false;
        });
        $('form#{$model -> formName()}').on('afterValidate', function (event) {
            var form = $(this);
            var url = ['validate'];
            var type = 'post';
            var data = form.serialize();

            $.ajax({
                url: url,
                type: type,
                data: data,
                success: function (result) {
                    form.yiiActiveForm('updateMessages', result.errors, true);
                    if (result.errors.length != 0) {
                        
                    }
                    else if (result.confirmed == true) {
                        //$('.confirm-panel').show();
                    }
                    else {
                        //form.submit();
                    }
            },
            error: function() {
                alert('Error');
            }
            });

            // prevent default form submission
            return false;            
        });
    });
    JS;
     $this->registerJs($script); 
    ?>

</div>
