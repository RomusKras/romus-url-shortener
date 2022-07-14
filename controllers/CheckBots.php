<?php
namespace app\controllers;
use Yii;
use linslin\yii2\curl;
use yii\base\BaseObject;
use app\models\Link;
use app\controllers\LinkController;
use Exception;

class CheckBots extends BaseObject implements \yii\queue\JobInterface
{
    public $userAgent;
    public $ip;
    public $hash;
    public $country;
    public $city;
    
    public function execute($queue)
    {
        $userAgent = $this->userAgent;
        $ip = $this->ip;
        $hash = $this->hash;
        $country = $this->country;
        $city = $this->city;

        // Проверяем не бот ли это
        $curl = new curl\Curl();
        $response = $curl->setGetParams([
            'userAgent' => $userAgent,
         ])
         ->get('https://qnits.net/api/checkUserAgent');
         // List of status codes here http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
        switch ($curl->responseCode) {
            case 'timeout':
                //timeout error logic here
                break;
            case 200:
                // Обрабатываем ответ 
                $response = json_decode($response, true);
                // Если не бот
                if ($response['isBot'] === false) {
                    $model = Link::findByHash($hash);
                    if ($model !== null) {
                        if ($model->generateHit($ip, $userAgent, $country, $city)) {
                            //Yii::error('Агент = '.$userAgent.' и ip = '.$ip);
                            $model->updateCounter();
                        }
                    }
                } else {
                    //throw new HttpException(404 ,'Ботам не позволительно это!');
                    //return $this->redirect('site/error');
                }
                break;
            case 404:
                //404 Error logic here
                break;
        }
    }
}