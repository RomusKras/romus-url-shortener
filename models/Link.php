<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Url;

/**
 * This is the model class for table "link".
 *
 * @property int    $id
 * @property string $url
 * @property string $description
 * @property string $hash
 * @property int    $counter Счетчик
 * @property string $created Дата создания
 * @property int    $created_by Создатель сокращенной ссылки
 *
 * @property Hit[]  $hits
 * @property User   $owner
 */
class Link extends \yii\db\ActiveRecord
{
    public $short_url = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%link}}';
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
                'createdAtAttribute'    => 'created',
                'updatedAtAttribute'    => null,
            ],
            [
                'class'                 => BlameableBehavior::class,
                'createdByAttribute'    => 'created_by',
                'updatedByAttribute'    => null,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url', 'hash'], 'required'],
            [['hash'], 'unique'],
            [['created'], 'safe'],
            [['counter', 'created_by'], 'integer'],
            [['url', 'description', 'hash'], 'string', 'max' => 255],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            ['url', 'url', 'defaultScheme' => ['http', 'https'], 'enableIDN' => true],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => 'ID',
            'url'           => 'Адрес ссылки',
            'description'   => 'Описание',
            'hash'          => 'Hash',
            'short_url'     => 'Короткая ссылка',
            'counter'       => 'Кол-во переходов',
            'created'       => 'Дата создания',
            'created_by'    => 'Кем создана',
        ];
    }

    /**
     * Create short_url
     *
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        if ($this->short_url === false) {
            $this->short_url = Url::base(true) . '/' . $this->hash;
        }
    }

    /**
     * Generate hash for shorten url
     *
     * @param null $timestamp
     * @return string
     */
    public static function generateHash($timestamp = null)
    {
        if ($timestamp === null) {
            $timestamp = strtotime("now");
        }
        return base_convert($timestamp, 10, 36);
    }

    /**
     * @param $ip
     * @param $ua
     * @return bool
     */
    public function generateHit($ip = null, $ua = null, $country = null, $city = null)
    {
        $hit = new Hit();
        $hit->link_id = $this->id;
        $hit->ip = $ip;
        $hit->user_agent = $ua;
        if ($country != null) {
            $hit->country = $country;
        }
        if ($city != null) {
            $hit->city = $city;
        }
        $hitSave = $hit->save();
        if ($hitSave === false) {
            $errToPrint = "";
            $errToPrint .= "MODEL NOT SAVED, ip contains ".strlen($ip)." ";
            $errToPrint .= print_r($hit->getAttributes(), true);
            $errToPrint .= print_r($hit->getErrors(), true);
            Yii::error('Err' . $errToPrint);
            return false;
        } else {
            return true;
        }
    }

    /**
     * Update counter
     */
    public function updateCounter()
    {
        $this->counter++;
        $this->save(false);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHits()
    {
        return $this->hasMany(Hit::class, ['link_id' => 'id']);
    }

    /**
     * @return int
     */
    public function getCountHits()
    {
        return Hit::find()->where(['link_id' => $this->id])->count();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * @inheritdoc
     * @return LinkQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new LinkQuery(get_called_class());
    }

    /**
     * Finds link by hash
     *
     * @param string $hash
     * @return static|null
     */
    public static function findByHash($hash)
    {
        return static::findOne(['hash' => $hash]);
    }

    /**
     * Finds link by url
     *
     * @param string $url Оригинальная ссылка
     * @return static|null
     */
    public static function findByUrl($url)
    {
        return static::findOne(['url' => $url]);
    }
}
