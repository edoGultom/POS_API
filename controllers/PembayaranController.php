<?php

namespace app\controllers;

use app\models\TblPembayaran;
use app\models\TblPemesanan;
use app\models\TblPemesananDetail;
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

    public function actionAdd()
    {
        $request = Yii::$app->request;
        $body = $request->bodyParams; // Get the body of the request
        $cartList = $body['CartList'];
        $idPemesanan = $body['id_pemesanan'];
        $metode_pembayaran = $body['metode_pembayaran'];
        $totalBayar = $body['totalBayar'];
        $status = $body['status'];

        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $pembayaran = new TblPembayaran();
            $pembayaran->id_pemesanan = $idPemesanan;
            $pembayaran->jumlah = $totalBayar;
            $pembayaran->tipe_pembayaran = $metode_pembayaran;
            // $pembayaran->waktu_pembayaran = date('Y-m-d H:i:s');
            $pembayaran->id_kasir = Yii::$app->user->identity->id;

            if ($metode_pembayaran === 'qris') {
                $dataArr = (array) $cartList;
                $dataArr = array_map(function ($item) {
                    return (object) $item;
                }, $dataArr);
                $midtransResp = Yii::$app->midtrans->checkout($idPemesanan, $totalBayar, $dataArr);
                if ($midtransResp->status_code == '201') {
                    $pembayaran->id_transaksi_qris = $midtransResp->transaction_id;
                    $actions = $midtransResp->actions[0];
                    $pembayaran->link_qris = $actions->url;
                    if (!$pembayaran->save()) {
                        $transaction->rollBack();
                        throw new Exception('Failed to save pembayaran ');
                    }

                    $transaction->commit();
                    return [
                        'status' => true,
                        'message' => "Successfully saved ",
                        'midtrans' => $midtransResp
                    ];
                }
            } else {
                $cash = (object)$body['cash'];
                $pembayaran->jumlah_diberikan = $cash->jumlah_diberikan;
                $pembayaran->jumlah_kembalian = $cash->jumlah_kembalian;
                if (!$pembayaran->save()) {
                    $transaction->rollBack();
                    throw new Exception('Failed to save pembayaran ');
                }
                $pemesanan = TblPemesanan::findOne(['id' => $idPemesanan]);
                $pemesanan->status = 'paid';
                if (!$pemesanan->save()) {
                    throw new Exception('Data Not found');
                }
                TblPemesananDetail::updateAll(['status' => 'paid'], ['id_pemesanan' => $idPemesanan]);

                $transaction->commit();
                return [
                    'status' => true,
                    'message' => "Successfully saved ",
                    'cash' => $pembayaran
                ];
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            throw new Exception('Failed to save: ' . $e->getMessage());
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
