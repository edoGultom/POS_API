<?php

namespace app\controllers;

use app\models\TblPembayaran;
use app\models\TblPenjualan;
use yii\rest\Controller;
use yii\web\Response;
use Yii;

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
        } else  if ($body['transaction_status'] == 'expired') {
            $status = 'EXPIRED';
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
}
