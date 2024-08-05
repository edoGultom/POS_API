<?php

namespace app\controllers;

use app\models\TblMeja;
use app\models\TblPemesanan;
use app\models\TblPemesananDetail;
use app\models\UploadedFiledb;
use app\models\UploadForm;
use DateTime;
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
use yii\db\Exception;

class OrderController extends Controller
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
                    'index'  => ['GET'],
                    'add'  => ['POST'],
                    'update'  => ['POST'],
                    'delete'  => ['DELETE'],
                    'test'  => ['GET'],

                ],
            ],
        ]);
    }

    protected function findModel($id)
    {
        $model = TblPemesanan::findOne($id);
        if ($model !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Data Tidak Ditemukan.');
    }
    protected function findAllModel()
    {
        $model = TblPemesanan::find()->all();
        if (count($model) > 0) {
            return $model;
        }
        throw new NotFoundHttpException('Data Tidak Ditemukan.');
    }
    protected function findAllModelOrder($status)
    {
        $model = TblPemesanan::find();
        if ($status === 'in_progress') {
            $model->where(['status' => 'in_progress']);
        } else if ($status === 'ordered') {
            $model->where(['status' => 'ordered']);
        } else if ($status === 'ready') {
            $model->where(['status' => 'ready']);
        } else if ($status === 'paid') {
            $model->where(['status' => 'paid']);
        }
        $data = $model->orderBy(['waktu' => SORT_ASC])->all();
        if (count($data) > 0) {
            return $data;
        }
        throw new NotFoundHttpException('Data Tidak Ditemukan.');
    }
    public function actionIndex()
    {
        $res = [];
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $table = $this->findAllModel();
            if ($table) {
                $transaction->commit();
                $res['status'] = true;
                $res['data'] = $table;
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
    public function actionGetOrders($status)
    {
        $res = [];
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $table = $this->findAllModelOrder($status);
            if ($table) {
                $transaction->commit();
                $res['status'] = true;
                $res['data'] = $table;
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
            $model = new TblPemesanan();
            $rawData = $request->getRawBody();
            $data = json_decode($rawData, true);
            $orderMenus = $data['ordered'];
            $table = (object)$data['table'];
            $status = $data['status'];
            $model->id_meja = $table->id;
            $model->id_pelayan = Yii::$app->user->identity->id;
            $model->status = $status;
            $model->waktu =  Yii::$app->formatter->asDateTime(new DateTime(), 'php:Y-m-d H:i:s');
            $model->arrMenu = $orderMenus;
            if (!$model->save()) {
                throw new \Exception('Failed to save data');
            }
            $transaction->commit();
            $res['status'] = true;
            $res['message'] = 'Berhasil menambah data!';
            $res['data'] =  $this->findModel($model->id);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'status' => false,
                'message' =>  $e->getMessage(),
            ];
        }
        return $res;
    }
    public function actionUpdate($id)
    {
        $res = [];
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        $data =  Yii::$app->request->getBodyParams();
        $files = UploadedFile::getInstancesByName("file");
        try {
            $table =  $this->findModel($id);;
            if ($table) {
                $table->setAttributes($data); // Set the attributes manually
                if ($table->validate() && $table->save()) {
                    if (!empty($files)) {
                        UploadedFiledb::find()->where(['filename' =>  $table->path])->one()->delete();
                        unlink(Yii::getAlias('@' . $table->path));
                        $upload = new UploadForm();
                        $upload->imageFilestable = $files;
                        $resp = $upload->uploadFiletable($table->id);
                        if (!$resp) {
                            $this->status = false;
                            $this->pesan = $resp;
                        }
                        $table->path = $resp;
                        if (!$table->save()) {
                            return [
                                'status' => false,
                                'message' => $table->getErrors(),
                            ];
                        }
                    }
                    $transaction->commit();
                    $res['status'] = true;
                    $res['message'] = 'Berhasil merubah data!';
                    $res['data'] = $this->findModel($id);
                } else {
                    return [
                        'status' => false,
                        'message' => $table->getErrors(),
                    ];
                }
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
    public function actionDelete($id)
    {
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $table =  $this->findModel($id);
            if ($table->delete()) {
                $res['status'] = true;
                $res['message'] = 'Berhasil menghapus data!';
                // $res['data'] =  $this->findModel($id);
                $transaction->commit();
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
