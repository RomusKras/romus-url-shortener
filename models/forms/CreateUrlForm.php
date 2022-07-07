<?php

namespace app\models\forms;

use Yii;
use app\models\Link;
use yii\base\Model;
use yii\helpers\Html;
use yii\web\Response;

/**
 * Class CreateUrlForm
 *
 * @package app\models\forms
 */
class CreateUrlForm extends Model
{
    public $url;
    public $description;

    private $hash = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['url', 'trim'],
            ['url', 'required'],
            [['url', 'description'], 'string', 'max' => 255],
            ['url', 'validateHash'],
            ['url', 'validateUrl'],
        ];
    }

    /**
     * Generate and validate hash.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateHash($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $this->hash = Link::generateHash();
            if (Link::findByHash($this->hash)) {
                $this->addError($attribute, 'Не удалось создать короткую ссылку, попробуйте еще раз.');
            }
        }
    }

    /**
     * Validate url.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateUrl($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $sslFlag = false;
            $tempUrl = $this->url;
            // Если в конце ссылки нет /, то добавим
            if (!str_ends_with($tempUrl, '/')) {
                $tempUrl .= '/';
            }
            // Если в начале ссылки нет http, то добавим
            if (!str_starts_with($tempUrl, 'http://') && !str_starts_with($tempUrl, 'https://')) {
                $tempUrl = 'http://'.$tempUrl;
            } else if (str_starts_with($tempUrl, 'https://')) {
                $sslFlag = true;
            }
            $findedLink = Link::findByUrl($tempUrl);
            if ($findedLink) {
                $this->addError($attribute, 'Ссылка была сокращена ранее '.Html::a('Перейти', ['view', 'id' => $findedLink->id], ['class' => 'btn btn-primary btn-sm']) );
                //Html::a('Перейти', ['view', 'id' => $findedLink->id], ['class' => 'btn btn-primary'])
            }
            // Проверяем дубль ссылки без SSL
            if ($sslFlag) {
                $tempUrl = str_replace("https://", "http://", $tempUrl);
                $findedLink = Link::findByUrl($tempUrl);
                if ($findedLink) {
                    $this->addError($attribute, 'Ссылка была сокращена ранее '.Html::a('Перейти', ['view', 'id' => $findedLink->id], ['class' => 'btn btn-primary btn-sm']) );
                }
            } else {
                $tempUrl = str_replace("http://", "https://", $tempUrl);
                $findedLink = Link::findByUrl($tempUrl);
                if ($findedLink) {
                    $this->addError($attribute, 'Ссылка была сокращена ранее '.Html::a('Перейти', ['view', 'id' => $findedLink->id], ['class' => 'btn btn-primary btn-sm']) );
                }
            }
        }
    }

    /**
     * Create shorten link.
     *
     * @return Link|null the saved model or null if saving fails
     */
    public function createLink()
    {
        if (!$this->validate()) {
            return null;
        }
        $link = new Link();

        // URL
        $tempUrl = $this->url;
        // Если в конце ссылки нет /, то добавим
        if (!str_ends_with($tempUrl, '/')) {
            $tempUrl .= '/';
        }
        // Если в начале ссылки нет http, то добавим
        if (!str_starts_with($tempUrl, 'http://') && !str_starts_with($tempUrl, 'https://')) {
            $tempUrl = 'http://'.$tempUrl;
        }
        $link->url = $tempUrl;

        $link->description = $this->description;
        $link->hash = $this->hash;

        // AJAX answer
        if (Yii::$app->request->isAjax && Yii::$app->request->post()) {
            // Send JSON answer to controller - link & confirmed = true
            return $link->save() ? ['confirmed' => true, 'link' => $link] : ['confirmed' => false];
        }

        return $link->save() ? $link : null;
        
    }
}
