<?php

namespace app\controllers;

use app\models\forms\CreateUrlForm;
use app\controllers\CheckBots;
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

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post()) && $link = $model->createLink()) {
            // Send JSON answer - link & confirmed = true
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $link;
        } else if ( $model->load(Yii::$app->request->post()) && $link = $model->createLink()) {
            return $this->redirect(['view', 'id' => $link->id]);
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post()))  {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $return_array = [
                'confirmed' => false,
                ];
            return $return_array;
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Validate model from AJAX Post
     * @return mixed
     */
    public function actionValidate()
    {
        $model = new CreateUrlForm();
        // AJAX
        if ( $model->load(Yii::$app->request->post()) && Yii::$app->request->isAjax ) { 
            $return_array = [
            'errors' => [],
            'confirmed' => false,
            ];

            Yii::$app->response->format = Response::FORMAT_JSON;
            //return ActiveForm::validate($model);
            $return_array['errors'] = ActiveForm::validate($model);

            // if ($model->createLink() == 1) {
            //     $return_array['confirmed'] = true;
            // }

            return $this->asJson($return_array);
        }
        throw new \yii\web\BadRequestHttpException('Bad request!');

        return $this->render('validate', [
            'model' => $model,
        ]);
    }

    /**
     * Process model from AJAX Post
     * @return mixed
     */
    public function actionProcess()
    {
        $model = new CreateUrlForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            $return_array = [
                'errors' => [],
                'confirmed' => false,
            ];

            Yii::$app->response->format = Response::FORMAT_JSON;
            $return_array['errors'] = ActiveForm::validate($model);

            if ($model->createLink() == 1) {
                $return_array['confirmed'] = true;
            }

            return $this->asJson($return_array);
        }

        if ( $model->load(Yii::$app->request->post()) && $link = $model->save()) {
            return $this->redirect(['view', 'id' => $link->id]);
        }

        return $this->render('process', [
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

        $country = null;
        $city = null;
        try {
            $ip2 = Yii::$app->geoip->ip($ip);
            $country   = $ip2->country ? $ip2->country  : 'Неизвестно';
            $city      = $ip2->city    ? $ip2->city     : 'Неизвестно';
        } catch (\Exception $e) {
            Yii::error('1 GeoIp2 Error = ' . $e);
        }
        
        // Кидаем в очередь проверку на бота и запись в историю посещений
        $id = Yii::$app->queue->push(new CheckBots([
            'userAgent' => $user_agent,
            'ip' => $ip,
            'hash' => $hash,
            'country' => $country,
            'city' => $city,
        ]));

        // Yii::error('Задание ждет в очереди: '.Yii::$app->queue->isWaiting($id));
        // $isDoneMarker = 0;
        // if (Yii::$app->queue->isDone($id)) {
        //     $isDoneMarker = 1;
        // }
        // Yii::error('Задание выполнено в очереди: '.$isDoneMarker);
        // $isReservedMarker = 0;
        // if (Yii::$app->queue->isReserved($id)) {
        //     $isReservedMarker = 1;
        // }
        // Yii::error('Задание выполняется в очереди: '.$isReservedMarker);


        //return $this->redirect('site/error');
        return $this->redirect($link->url);
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

}
