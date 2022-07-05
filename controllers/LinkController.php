<?php

namespace app\controllers;

use app\models\forms\CreateUrlForm;
use app\models\Hit;
use app\models\HitSearch;
use Yii;
use app\models\Link;
use app\models\LinkSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\HttpException;
use linslin\yii2\curl;
use yii\helpers\BaseJson;
use yii\widgets\ActiveForm;
use yii\web\Response;

/**
 * LinkController implements actions for Link model.
 */
class LinkController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'view', 'create', 'update', 'delete'],
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Link models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new LinkSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Link model.
     *
     * @param integer $id
     * @return mixed
     * @throws MethodNotAllowedHttpException
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $searchModel = new HitSearch();
        $searchModel->link_id = $id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('view', [
            'model' => $this->findModel($id),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Link model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CreateUrlForm();

        if ($model->load(Yii::$app->request->post()) && $link = $model->createLink()) {
            return $this->redirect(['view', 'id' => $link->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Link model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     * @return mixed
     * @throws MethodNotAllowedHttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Link model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * @param $hash
     * @return \yii\web\Response
     * @throws MethodNotAllowedHttpException
     * @throws NotFoundHttpException
     */
    public function actionRedirect($hash)
    {
        $link = $this->findModelByHash($hash);
        $ip = Yii::$app->request->userIP;
        $user_agent = Yii::$app->request->userAgent;

        // Проверяем не бот ли это
        $curl = new curl\Curl();
        $response = $curl->setGetParams([
            'userAgent' => $user_agent,
         ])
         ->get('https://qnits.net/api/checkUserAgent');
         // List of status codes here http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
        switch ($curl->responseCode) {
            case 'timeout':
                //timeout error logic here
                break;
                
            case 200:
                //success logic here
                // Обрабатываем ответ 
                //$responseArray = print_r($response, true);
                //Yii::error('Debug array answer ' . $responseArray);
                $response = json_decode($response, true);
                if ($response['isBot'] === false) {

                } else {
                    throw new HttpException(404 ,'Ботам не позволительно это!');
                    //return $this->redirect('site/error');
                }
                break;

            case 404:
                //404 Error logic here
                break;
        }

        // Если не бот
        if ($link->generateHit($ip, $user_agent)) {
            $link->updateCounter();
            return $this->redirect($link->url);
        } 
        return $this->redirect('site/error');
    }

    /**
     * Finds the Link model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param $id
     * @return Link the loaded model
     * @throws MethodNotAllowedHttpException
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Link::findOne($id)) !== null) {
            if (Yii::$app->user->id === $model->created_by) {
                return $model;
            } else {
                throw new MethodNotAllowedHttpException('У Вас нет доступа к этой ссылке.');
            }
        } else {
            throw new NotFoundHttpException('Запрашиваемая страница не существует.');
        }
    }

    /**
     * Finds the Link model based on its hash code value.
     *
     * @param $hash
     * @return Link
     * @throws MethodNotAllowedHttpException
     * @throws NotFoundHttpException
     */
    protected function findModelByHash($hash)
    {
        if (($model = Link::findByHash($hash)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Запрашиваемая ссылка не существует.');
    }

    public function actionValidate()
    {
        $model = new Link();
        $request = \Yii::$app->getRequest();
        if ($request->isGet && $model->load($request->get())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
    }

}
