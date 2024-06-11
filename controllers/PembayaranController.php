<?php

namespace app\controllers;

use app\models\TblPembayaran;
use app\models\TblPenjualan;
use app\models\TblPenjualanBarang;
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
    private function savePenjualan($data, $idPenjualan)
    {
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            foreach ($data as $value) {
                try {
                    $model = new TblPenjualanBarang();
                    $model->id_penjualan = $idPenjualan;
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
                    return [
                        'status' => false,
                        'message' => "Failed " . $e->getMessage(),
                    ];
                }
            }
            $transaction->commit();
            return [
                'status' => true,
                'message' => "Successfully saved ",
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'status' => false,
                'message' => "Failed " . $e->getMessage(),
            ];
        }
    }

    public function actionAdd()
    {
        $request = Yii::$app->request;
        $body = $request->bodyParams; // Get the body of the request
        $data = $body['CartList'];
        $metode_pembayaran = $body['metode_pembayaran'];
        $totalBayar = $body['totalBayar'];
        $status = $body['status'];

        $model = new TblPenjualan();
        $model->id_user = Yii::$app->user->identity->id;
        $model->total_transaksi = $totalBayar;
        $model->status_pembayaran = $status;
        if ($metode_pembayaran === 'qris') {
            $model->payment_gateway = 'midtrans';
        }
        if (!$model->save()) {
            return [
                'status' => false,
                'message' => "Failed Saved!",
            ];
        }
        $this->savePenjualan($data, $model->id);
        //    PEMMBAYARAN MIDTRANS
        $midtransResp = Yii::$app->midtrans->checkout($model);
        // return $midtransResp;
        // END PEMBAYARA MIDTRANS
        if ($midtransResp->status_code == '201') {
            $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();
            try {
                $pembayaran = new TblPembayaran();
                $pembayaran->id_penjualan = $model->id;
                $pembayaran->payment_method = strtoupper($metode_pembayaran);
                if ($metode_pembayaran === 'qris') {
                    $pembayaran->payment_gateway = 'midtrans';
                }
                $pembayaran->jumlah = $totalBayar;
                $pembayaran->tanggal_pembayaran = date('Y-m-d H:i:s');
                $pembayaran->payment_status = $status;
                $pembayaran->id_transaksi = $midtransResp->transaction_id;
                if (!$pembayaran->save()) {
                    return [
                        'status' => false,
                        'message' => "Failed Saved to pembayaran!",
                    ];
                }
                $transaction->commit();
                return [
                    'status' => true,
                    'message' => "Successfully saved ",
                    'midtrans' => $midtransResp
                ];
            } catch (Exception $e) {
                return [
                    'status' => false,
                    'message' => "Exception occurred while saving model for with error: " . $e->getMessage(),
                ];
            }
        }
    }
    public function actionCancel()
    {
        $request = Yii::$app->request;
        $body = $request->bodyParams; // Get the body of the request
        $orderId = $body['order_id'];
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        $midtransResp = Yii::$app->midtrans->cancel($orderId);
        try {
            if ($midtransResp->status_code == '200') {
                $transaction->commit();
                return [
                    'status' => true,
                    'message' => "Berhasil membatalkan",
                    'data' => $midtransResp
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => "Exception occurred while saving model for with error: " . $e->getMessage(),
            ];
        }
        //    PEMMBAYARAN MIDTRANS
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
