<?php

namespace app\controllers;

use app\models\TblPembayaran;
use app\models\TblPenjualan;
use yii\rest\Controller;
use yii\web\Response;
use Yii;
use yii\base\Exception;
use yii\web\NotFoundHttpException;

class VerifyController extends Controller
{
    public function beforeAction($action)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $body = $request->bodyParams; // Get the body of the request
        $orderId = $body['order_id'];
        $statusCode = $body['status_code'];
        $grossAmount = $body['gross_amount'];
        $serverKey = 'SB-Mid-server-vb9mGwshjOGUYK5IV8M6peGo';
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($signature !== $body['signature_key']) {
            return [
                'status' => false,
                'message' => 'invalid signature'
            ];
        }
        $status = 'PENDING';
        $satle = null;
        if ($body['transaction_status'] == 'settlement') {
            $status = 'PAID';
            $satle =  $body['settlement_time'];
        } else if ($body['transaction_status'] == 'expired') {
            $status = 'EXPIRED';
        } else if ($body['transaction_status'] == 'cancel') {
            $status = 'CANCELED';
        }
        TblPenjualan::updateAll([
            'status_pembayaran' => $status,
        ], ['id' => intval($orderId)]);
        TblPembayaran::updateAll([
            'payment_status' => $status,
            'tanggal_pembayaran' => $satle
        ], ['id_penjualan' => intval($orderId)]);

        return [
            'status' => true,
            'message' => 'Success Update Payment'
        ];
    }
    protected function findModel($id)
    {
        $model = TblPembayaran::findOne($id);
        if ($model !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Data Tidak Ditemukan.');
    }
    public function actionIsfinish($orderId)
    {
        $request = Yii::$app->request;
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        $pembayaran = TblPembayaran::findOne(['id_penjualan' => $orderId]);
        try {
            if ($pembayaran) {
                return [
                    'status' => true,
                    'message' => $pembayaran->payment_status,
                ];
            } else {
                return [
                    'status' => false,
                    'message' => "Not Found",
                ];
            }
            $transaction->commit();
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => "Exception occurred while saving model for with error: " . $e->getMessage(),
            ];
        }
    }
}
