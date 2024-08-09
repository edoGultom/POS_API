<?php

namespace app\controllers;

use app\models\TblMeja;
use app\models\TblPembayaran;
use app\models\TblPemesanan;
use app\models\TblPemesananDetail;
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
        $trxId = $body['transaction_id'];
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
        $tblPembayaran = TblPembayaran::findOne(['id_transaksi_qris' => $trxId]);
        $pemesanan = TblPemesanan::findOne(['id' => $tblPembayaran->id_pemesanan]);

        $status = 'pending payment';
        $satle = null;
        if ($body['transaction_status'] == 'settlement') {
            $status = 'paid';
            $tblPembayaran->waktu_pembayaran = date('Y-m-d H:i:s');
            $satle =  $body['settlement_time'];
        } else if ($body['transaction_status'] == 'expired') {
            $status = 'expired payment';
        } else if ($body['transaction_status'] == 'cancel') {
            $status = 'canceled payment';
        }
        $pemesanan->status = $status;

        if ($tblPembayaran) {
            if (!$tblPembayaran->save()) {
                throw new Exception('Failed save Pembayara');
            }
            if (!$pemesanan->save()) {
                throw new Exception('Failed update pemesanan');
            }
            TblMeja::updateAll(['status' => 'Available'], ['id' => $pemesanan->id_meja]);
            TblPemesananDetail::updateAll(['status' => $status], ['id_pemesanan' => $tblPembayaran->id_pemesanan]);
        }
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
    public function actionIsfinish($idTrx)
    {
        $request = Yii::$app->request;
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        $pembayaran = TblPembayaran::findOne(['id_transaksi_qris' => $idTrx]);
        $message = 'Pending Payment';
        if ($pembayaran && $pembayaran->waktu_pembayaran != null) {
            $message = $pembayaran->pemesanan->status ?? '';
        }
        try {
            if ($pembayaran && $pembayaran->waktu_pembayaran != null) {
                return [
                    'status' => true,
                    'message' => $message,
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
