<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use UAParser\Parser;

/**
 * This is the model class for table "hit".
 *
 * @property int $id
 * @property int $link_id
 * @property string $datetime
 * @property string $ip
 * @property string $country
 * @property string $city
 * @property string $user_agent
 * @property string $os
 * @property string $os_version
 * @property string $browser
 * @property string $browser_version
 *
 * @property Link $link
 */
class Hit extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%hit}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class'                 => TimestampBehavior::class,
                'value'                 => function () { return gmdate("Y-m-d H:i:s"); },
                'createdAtAttribute'    => 'datetime',
                'updatedAtAttribute'    => null,
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['link_id', 'ip', 'user_agent'], 'required'],
            [['link_id'], 'integer'],
            [['datetime', ], 'safe'],
            [['country', 'city', 'os', 'os_version', 'browser', 'browser_version'], 'string'],
            [['ip'], 'string', 'max' => 15],
            [['user_agent'], 'string', 'max' => 255],
            [['link_id'], 'exist', 'skipOnError' => true, 'targetClass' => Link::class, 'targetAttribute' => ['link_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => 'ID',
            'link_id'       => 'Ссылка',
            'datetime'      => 'Время',
            'ip'            => 'IP',
            'country'       => 'Страна',
            'city'          => 'Город',
            'user_agent'    => 'User Agent',
            'os'            => 'OC',
            'os_version'    => 'Версия ОС',
            'browser'       => 'Браузер',
            'browser_version' => 'Версия браузера',
        ];
    }

    public function beforeSave($insert)
    {
        // Only for new records
        if ($this->isNewRecord) {
            // Country and City by IP
            try {
                $ip = Yii::$app->geoip->ip($this->ip);
                $this->country   = $ip->country ? $ip->country  : 'Неизвестно';
                $this->city      = $ip->city    ? $ip->city     : 'Неизвестно';
            } catch (\Exception $e) {
                $this->country   = 'Неизвестно';
                $this->city      = 'Неизвестно';
            }
            // OS and Browser by UA
            try {
                $parser = Parser::create();
                $ua = $parser->parse($this->user_agent);
                $this->os                = $ua->os->family;
                $this->os_version        = $ua->os->major;
                $this->browser           = $ua->ua->family;
                $this->browser_version   = $ua->ua->major;
            } catch (\Exception $e) {
                $this->os                = 'Неизвестно';
                $this->os_version        = 'Неизвестно';
                $this->browser           = 'Неизвестно';
                $this->browser_version   = 'Неизвестно';
            }
        }
        return parent::beforeSave($insert);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLink()
    {
        return $this->hasOne(Link::class, ['id' => 'link_id']);
    }

    /**
     * @inheritdoc
     * @return HitQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new HitQuery(get_called_class());
    }

}
