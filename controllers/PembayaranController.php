<?php

namespace app\controllers;

use app\models\TblPembayaran;
use app\models\TblPenjualan;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;
use filsh\yii2\oauth2server\filters\auth\CompositeAuth;
use yii\base\Exception;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

class PembayaranController extends Controller
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
                    'update'  => ['PUT'],
                    'delete'  => ['DELETE'],
                ],
            ],
        ]);
    }
    protected function findModel($id)
    {
        $model = TblPembayaran::findOne($id);
        if ($model !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Data Tidak Ditemukan.');
    }
    protected function findAllModel()
    {
        $model = TblPembayaran::find()->all();
        if (count($model) > 0) {
            return $model;
        }
        throw new NotFoundHttpException('Data Tidak Ditemukan.');
    }
    private function savePenjualan($data, $totalBayar, $status)
    {
        // $midtransResp = Yii::$app->midtrans->checkout();
        $res = [];
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            foreach ($data as $value) {
                try {
                    $model = new TblPenjualan();
                    $model->id_user = Yii::$app->user->identity->id;
                    $model->total_transaksi = $totalBayar;
                    $model->status_pembayaran = $status;
                    $model->payment_gateway = 'midtrans';
                    $model->id_barang = $value['id'];
                    $model->qty = $value['qty'];
                    $model->harga = $value['harga'];
                    $model->total = $value['totalHarga'];
                    try {
                        if (!$model->save()) {
                            return [
                                'status' => false,
                                'message' => "Failed to save model for data: " . $model->getErrors(),
                            ];
                        }
                    } catch (Exception $e) {
                        return [
                            'status' => false,
                            'message' => "Exception occurred while saving model for with error: " . $e->getMessage(),
                        ];
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    return $e->getMessage();
                }
            }
            $transaction->commit();
            return [
                'status' => true,
                'message' => "Successfully saved ",
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return $e->getMessage();
        }
    }

    public function actionAdd()
    {
        $request = Yii::$app->request;
        $body = $request->bodyParams; // Get the body of the request
        $data = $body['CartList'];
        $pembayaran = $body['pembayaran'];
        $totalBayar = $body['totalBayar'];
        $status = $body['status'];
        // return $data;
        return $this->savePenjualan($data, $totalBayar, $status);
        // $res = [];
        // $connection = Yii::$app->db;
        // $transaction = $connection->beginTransaction();

        // try {
        //     $model = new TblPembayaran();
        //     $data = $request->bodyParams; // Get the body of the request
        //     $model->load($data, '');
        //     if ($model->validate() &&  $model->save()) {
        //         $transaction->commit();
        //         $res['status'] = true;
        //         $res['message'] = 'Berhasil menambah data!';
        //     } else {
        //         return [
        //             'status' => false,
        //             // 'message' => $model->getRequiredAttributes()
        //             'message' => $model->getErrors(),
        //         ];
        //     }
        // } catch (\Exception $e) {
        //     $transaction->rollBack();
        //     return [
        //         'status' => false,
        //         'message' => $e->getMessage(),
        //     ];
        // }
        // return $res;
    }
    public function actionUpdate($id)
    {
        $res = [];
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        $data =  Yii::$app->request->getBodyParams();
        try {
            $model =  $this->findModel($id);
            if ($model) {
                $model->setAttributes($data, ''); // Set the attributes manually
                if ($model->validate() &&  $model->save()) {
                    $transaction->commit();
                    $res['status'] = true;
                    $res['message'] = 'Berhasil merubah data!';
                } else {
                    return [
                        'status' => false,
                        'message' => $model->getErrors(),
                        // 'message' => $model->getRequiredAttributes()
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
            $model =  $this->findModel($id);
            if ($model->delete()) {
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
