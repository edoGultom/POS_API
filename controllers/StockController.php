<?php

namespace app\controllers;

use app\models\TblMenu;
use app\models\TblTransaksiStok;
use app\models\TblTransaksiStokBahanBaku;
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
                    'add-transaction'  => ['POST'],
                    'add-stock'  => ['POST'],
                    'update-transaction'  => ['POST'],
                    'update-stock'  => ['POST'],
                    'detail-stock'  => ['GET'],
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
    protected function findModelStokBahanBaku($id)
    {
        $model = TblTransaksiStokBahanBaku::findOne($id);
        if ($model !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Data Tidak Ditemukan.');
    }
    protected function findAllModelStokBahanBaku($id)
    {
        $model = TblTransaksiStokBahanBaku::find()->where(['id_transaksi_stok' => $id])->all();
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

    public function actionAddTransaction()
    {
        $request = Yii::$app->request;
        $res = [];
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();

        try {
            $model = new TblTransaksiStok();
            $data = $request->bodyParams; // Get the body of the request
            $model->load($data, '');
            $model->kode = $model->setKode();
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
    public function actionUpdateTransaction($id)
    {
        $res = [];
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        $data =  Yii::$app->request->getBodyParams();
        try {
            $table =  $this->findModel($id);;
            if ($table) {
                $table->setAttributes($data); // Set the attributes manually
                if ($table->validate() && $table->save()) {
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
    public function actionDeleteTransaction($id)
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
    // STOCK
    public function actionDetailStock($id)
    {
        $res = [];
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $model = $this->findAllModelStokBahanBaku($id);
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
    public function actionAddStock()
    {
        $request = Yii::$app->request;
        $res = [];
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();

        try {
            $model = new TblTransaksiStokBahanBaku();
            $data = $request->bodyParams; // Get the body of the request
            $model->load($data, '');
            if ($model->validate() &&  $model->save()) {
                $transaction->commit();
                $res['status'] = true;
                $res['message'] = 'Berhasil menambah data!';
                $res['data'] =  $this->findModelStokBahanBaku($model->id);
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
    public function actionUpdateStock($id)
    {
        $res = [];
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        $data =  Yii::$app->request->getBodyParams();
        try {
            $table =  $this->findModelStokBahanBaku($id);;
            if ($table) {
                $table->setAttributes($data); // Set the attributes manually
                if ($table->validate() && $table->save()) {
                    $transaction->commit();
                    $res['status'] = true;
                    $res['message'] = 'Berhasil merubah data!';
                    $res['data'] = $this->findModelStokBahanBaku($id);
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
    public function actionDeleteStock($id)
    {
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $table =  $this->findModelStokBahanBaku($id);
            if ($table->delete()) {
                $res['status'] = true;
                $res['message'] = 'Berhasil menghapus data!';
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
