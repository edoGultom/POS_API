<?php

namespace app\controllers;

use app\models\TblMeja;
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

class TableController extends Controller
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
        $model = TblMeja::findOne($id);
        if ($model !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Data Tidak Ditemukan.');
    }
    protected function findAllModel()
    {
        $model = TblMeja::find()->all();
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
            $table = $this->findAllModel();
            if ($table) {
                // echo "<pre>";
                // print_r($table);
                // echo "</pre>";
                // exit();
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
            $table = new TblMeja();
            $data = $request->bodyParams; // Get the body of the request
            $table->load($data, '');
            if ($table->validate() &&  $table->save()) {
                $transaction->commit();
                $res['status'] = true;
                $res['message'] = 'Berhasil menambah data!';
                $res['data'] =  $this->findModel($table->id);
            } else {
                return [
                    'status' => false,
                    // 'message' => $table->getRequiredAttributes()
                    'message' => $table->getErrors(),
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
