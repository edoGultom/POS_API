<?php

namespace app\controllers;

use app\models\TblMenu;
use app\models\TblTransaksiStok;
use app\models\UploadedFiledb;
use app\models\UploadForm;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;
use filsh\yii2\oauth2server\filters\auth\CompositeAuth;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class StockController extends Controller
{
    public $pesan = '';
    public $data = '';
    public $status = false;

    public function beforeAction($action)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                'class' => CompositeAuth::class,
                'authMethods' => [
                    ['class' => HttpBearerAuth::class],
                    ['class' => QueryParamAuth::class, 'tokenParam' => 'accessToken'],
                ]
            ],
            'exceptionFilter' => [
                'class' => ErrorToExceptionFilter::class
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'add-transaksi-stok'  => ['GET'],
                    'index'  => ['GET'],
                ],
            ],
        ]);
    }
    protected function findModel($id)
    {
        $model = TblTransaksiStok::findOne($id);
        if ($model !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Data Tidak Ditemukan.');
    }
    protected function findAllModel()
    {
        $model = TblTransaksiStok::find()->all();
        if (count($model) > 0) {
            return $model;
        }
        throw new NotFoundHttpException('Data Tidak Ditemukan.');
    }
    public function actionIndex()
    {
        $res = [];
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $model = $this->findAllModel();
            if ($model) {
                $transaction->commit();
                $res['status'] = true;
                $res['data'] = $model;
                $res['message'] = 'Berhasil mengambil data!';
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
        return $res;
    }
    public function actionAdd()
    {
        $request = Yii::$app->request;
        $res = [];
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();

        try {
            $model = new TblTransaksiStok();
            $data = $request->bodyParams; // Get the body of the request
            $model->load($data, '');
            // $model->kode = $model->setKode();
            if ($model->validate() &&  $model->save()) {
                $transaction->commit();
                $res['status'] = true;
                $res['message'] = 'Berhasil menambah data!';
                $res['data'] =  $this->findModel($model->id);
            } else {
                return [
                    'status' => false,
                    'message' => $model->getErrors(),
                ];
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
        return $res;
    }
}
