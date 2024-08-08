<?php

namespace app\controllers;

use app\models\TblMenu;
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

class ReportController extends Controller
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
                    'stok-by-date-range'  => ['POST'],
                    'stok-by-date'  => ['POST'],
                    'penjualan-by-date-range'  => ['POST'],
                    'penjuakan-by-date'  => ['POST'],
                ],
            ],
        ]);
    }
    protected function findStokByDateRange($start, $end)
    {
        $model = Yii::$app->db->createCommand(
            "
            SELECT 
                DATE(tp.waktu) AS tanggal,
                tm.nama AS menu,
                tbb.id AS id_bahan_baku,
                tbb.nama AS bahan_baku,
                tubb.nama AS satuan,
                (
                    SELECT 
                        SUM(CASE WHEN tipe = 'masuk' THEN ttsbb.quantity ELSE 0 END) 
                    FROM 
                        tbl_transaksi_stok tts
                        INNER JOIN tbl_transaksi_stok_bahan_baku ttsbb ON tts.id = ttsbb.id_transaksi_stok
                    WHERE 
                        ttsbb.id_bahan_baku = tbb.id
                        AND  DATE(tts.tanggal) <= DATE(tp.waktu)
                ) AS stok_awal,
                SUM(tpd.quantity * tmbb.quantity) AS total_qty_terpakai,
                    (
                    SELECT 
                        SUM(CASE WHEN tipe = 'masuk' THEN ttsbb.quantity ELSE 0 END) +
                        SUM(CASE WHEN tipe = 'keluar' THEN ttsbb.quantity ELSE 0 END)
                    FROM 
                        tbl_transaksi_stok tts
                        INNER JOIN tbl_transaksi_stok_bahan_baku ttsbb ON tts.id = ttsbb.id_transaksi_stok
                    WHERE 
                        ttsbb.id_bahan_baku = tbb.id
                        AND DATE(tts.tanggal) <= DATE(tp.waktu)
                ) AS stok_akhir
            FROM 
                tbl_pemesanan_detail tpd
            JOIN 
                tbl_pemesanan tp ON tpd.id_pemesanan = tp.id
            JOIN 
                tbl_menu tm ON tpd.id_menu = tm.id
            JOIN 
                tbl_menu_bahan_baku tmbb ON tm.id = tmbb.id_menu
            JOIN 
                tbl_bahan_baku tbb ON tmbb.id_bahan_baku = tbb.id
            JOIN 
                tbl_unit_bahan_baku tubb ON tubb.id = tbb.id_unit_bahan_baku
            WHERE 
                tp.status = 'paid' -- Menghitung hanya pesanan yang sudah dibayar
                and DATE(tp.waktu) BETWEEN '$start' AND '$end'
            GROUP BY 
                DATE(tp.waktu), tm.nama, tbb.nama
            ORDER BY 
                tanggal, menu, raw_material;
        "
        )->queryAll();
        return array_values($model);
    }
    protected function findStokByDate($date)
    {
        $model = Yii::$app->db->createCommand(
            " 
            SELECT 
                DATE(tp.waktu) AS tanggal,
                tm.nama AS menu,
                tbb.id AS id_bahan_baku,
                tbb.nama AS bahan_baku,
                tubb.nama AS satuan,
                (
                    SELECT 
                        SUM(CASE WHEN tipe = 'masuk' THEN ttsbb.quantity ELSE 0 END) 
                    FROM 
                        tbl_transaksi_stok tts
                        INNER JOIN tbl_transaksi_stok_bahan_baku ttsbb ON tts.id = ttsbb.id_transaksi_stok
                    WHERE 
                        ttsbb.id_bahan_baku = tbb.id
                        AND  DATE(tts.tanggal) <= DATE(tp.waktu)
                ) AS stok_awal,
                SUM(tpd.quantity * tmbb.quantity) AS total_qty_terpakai,
                    (
                    SELECT 
                        SUM(CASE WHEN tipe = 'masuk' THEN ttsbb.quantity ELSE 0 END) +
                        SUM(CASE WHEN tipe = 'keluar' THEN ttsbb.quantity ELSE 0 END)
                    FROM 
                        tbl_transaksi_stok tts
                        INNER JOIN tbl_transaksi_stok_bahan_baku ttsbb ON tts.id = ttsbb.id_transaksi_stok
                    WHERE 
                        ttsbb.id_bahan_baku = tbb.id
                        AND DATE(tts.tanggal) <= DATE(tp.waktu)
                ) AS stok_akhir
            FROM 
                tbl_pemesanan_detail tpd
            JOIN 
                tbl_pemesanan tp ON tpd.id_pemesanan = tp.id
            JOIN 
                tbl_menu tm ON tpd.id_menu = tm.id
            JOIN 
                tbl_menu_bahan_baku tmbb ON tm.id = tmbb.id_menu
            JOIN 
                tbl_bahan_baku tbb ON tmbb.id_bahan_baku = tbb.id
            JOIN 
                tbl_unit_bahan_baku tubb ON tubb.id = tbb.id_unit_bahan_baku
            WHERE 
                tp.status = 'paid' -- Menghitung hanya pesanan yang sudah dibayar
                and DATE(tp.waktu)='$date'
            GROUP BY 
                DATE(tp.waktu), tm.nama, tbb.nama
            ORDER BY 
                tanggal, menu, bahan_baku;
            "
        )->queryAll();
        return array_values($model);
    }
    protected function findPenjualanByDateRange($start, $end)
    {
        $model = Yii::$app->db->createCommand(
            "
            SELECT 
                DATE(tp.waktu) AS tanggal,
                tm.nama AS menu,
                tpd.temperatur as temperatur,
                tm.harga as harga,
                SUM(tpd.quantity) AS qty,
                SUM(tpd.total) AS total
            FROM 
                tbl_pemesanan_detail tpd
            JOIN 
                tbl_pemesanan tp ON tpd.id_pemesanan = tp.id
            JOIN 
                tbl_menu tm ON tpd.id_menu = tm.id
            WHERE 
                tp.status = 'paid' -- Menghitung hanya pesanan yang sudah dibayar
            AND
                DATE(tp.waktu)  BETWEEN '$start' AND '$end'
            GROUP BY 
                DATE(tp.waktu), tm.nama
            ORDER BY 
                tanggal, menu; "
        )->queryAll();
        return array_values($model);
    }
    protected function findPenjualanByDate($date)
    {
        $model = Yii::$app->db->createCommand(
            "
            SELECT 
                DATE(tp.waktu) AS tanggal,
                tm.nama AS menu,
                tpd.temperatur as temperatur,
                tm.harga as harga,
                SUM(tpd.quantity) AS qty,
                SUM(tpd.total) AS total
            FROM 
                tbl_pemesanan_detail tpd
            JOIN 
                tbl_pemesanan tp ON tpd.id_pemesanan = tp.id
            JOIN 
                tbl_menu tm ON tpd.id_menu = tm.id
            WHERE 
                tp.status = 'paid' -- Menghitung hanya pesanan yang sudah dibayar
            AND
                DATE(tp.waktu) ='$date' 
            GROUP BY 
                DATE(tp.waktu), tm.nama
            ORDER BY 
                tanggal, menu;"
        )->queryAll();
        return array_values($model);
    }
    protected function findPenjualanByDateMax($date)
    {
        $model = Yii::$app->db->createCommand(
            "
            SELECT 
                DATE(tp.waktu) AS tanggal,
                tm.nama AS menu,
                tpd.temperatur as temperatur,
                tm.harga as harga,
                SUM(tpd.quantity) AS qty,
                SUM(tpd.total) AS total
            FROM 
                tbl_pemesanan_detail tpd
            JOIN 
                tbl_pemesanan tp ON tpd.id_pemesanan = tp.id
            JOIN 
                tbl_menu tm ON tpd.id_menu = tm.id
            WHERE 
                tp.status = 'paid' -- Menghitung hanya pesanan yang sudah dibayar
            AND
                DATE(tp.waktu) <='$date' 
            GROUP BY 
                DATE(tp.waktu), tm.nama
            ORDER BY 
                tanggal, menu;"
        )->queryAll();
        return array_values($model);
    }
    protected function findStokByDateMax($date)
    {
        $model = Yii::$app->db->createCommand(
            "
            SELECT 
                DATE(tp.waktu) AS tanggal,
                tm.nama AS menu,
                tbb.id AS id_bahan_baku,
                tbb.nama AS bahan_baku,
                tubb.nama AS satuan,
                (
                    SELECT 
                        SUM(CASE WHEN tipe = 'masuk' THEN ttsbb.quantity ELSE 0 END) 
                    FROM 
                        tbl_transaksi_stok tts
                        INNER JOIN tbl_transaksi_stok_bahan_baku ttsbb ON tts.id = ttsbb.id_transaksi_stok
                    WHERE 
                        ttsbb.id_bahan_baku = tbb.id
                        AND  DATE(tts.tanggal) <= DATE(tp.waktu)
                ) AS stok_awal,
                SUM(tpd.quantity * tmbb.quantity) AS total_qty_terpakai,
                    (
                    SELECT 
                        SUM(CASE WHEN tipe = 'masuk' THEN ttsbb.quantity ELSE 0 END) +
                        SUM(CASE WHEN tipe = 'keluar' THEN ttsbb.quantity ELSE 0 END)
                    FROM 
                        tbl_transaksi_stok tts
                        INNER JOIN tbl_transaksi_stok_bahan_baku ttsbb ON tts.id = ttsbb.id_transaksi_stok
                    WHERE 
                        ttsbb.id_bahan_baku = tbb.id
                        AND DATE(tts.tanggal) <= DATE(tp.waktu)
                ) AS stok_akhir
            FROM 
                tbl_pemesanan_detail tpd
            JOIN 
                tbl_pemesanan tp ON tpd.id_pemesanan = tp.id
            JOIN 
                tbl_menu tm ON tpd.id_menu = tm.id
            JOIN 
                tbl_menu_bahan_baku tmbb ON tm.id = tmbb.id_menu
            JOIN 
                tbl_bahan_baku tbb ON tmbb.id_bahan_baku = tbb.id
            JOIN 
                tbl_unit_bahan_baku tubb ON tubb.id = tbb.id_unit_bahan_baku
            WHERE 
                tp.status = 'paid' -- Menghitung hanya pesanan yang sudah dibayar
                and DATE(tp.waktu)<='$date'
            GROUP BY 
                DATE(tp.waktu), tm.nama, tbb.nama
            ORDER BY 
                tanggal, menu, bahan_baku;"
        )->queryAll();
        return array_values($model);
    }


    protected function findAllModel()
    {
        $model = TblMenu::find()->all();
        if (count($model) > 0) {
            return $model;
        }
        throw new NotFoundHttpException('Data Tidak Ditemukan.');
    }
    public function actionStokByDateRange()
    {
        $res = [];
        $connection = Yii::$app->db;
        $request = Yii::$app->request;
        $data = $request->bodyParams; // Get the body of the request
        $dateStart = $data['start'];
        $dateEnd = $data['end'];
        $model = $this->findStokByDateRange($dateStart, $dateEnd);

        $transaction = $connection->beginTransaction();
        try {
            if ($model) {
                $transaction->commit();
                $res['status'] = true;
                $res['data'] = $model;
                $res['message'] = 'Berhasil mengambil data!';
            } else {
                $res['status'] = true;
                $res['message'] = 'Data Kosong';
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
    public function actionStokByDate()
    {
        $res = [];
        $connection = Yii::$app->db;
        $request = Yii::$app->request;
        $data = $request->bodyParams; // Get the body of the request
        $date = $data['date'];
        $model = $this->findStokByDate($date);

        if ($data >= date('Y-m-d')) {
            $model = $this->findStokByDateMax($date);
        }

        $transaction = $connection->beginTransaction();
        try {
            if ($model) {
                $transaction->commit();
                $res['status'] = true;
                $res['data'] = $model;
                $res['message'] = 'Berhasil mengambil data!';
            } else {
                $res['status'] = true;
                $res['message'] = 'Data Kosong';
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
    public function actionPenjualanByDateRange()
    {
        $res = [];
        $connection = Yii::$app->db;
        $request = Yii::$app->request;
        $data = $request->bodyParams; // Get the body of the request
        $dateStart = $data['start'];
        $dateEnd = $data['end'];
        $model = $this->findPenjualanByDateRange($dateStart, $dateEnd);

        $transaction = $connection->beginTransaction();
        try {
            if ($model) {
                $transaction->commit();
                $res['status'] = true;
                $res['data'] = $model;
                $res['message'] = 'Berhasil mengambil data!';
            } else {
                $res['status'] = true;
                $res['message'] = 'Data Kosong';
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
    public function actionPenjualanByDate()
    {
        $res = [];
        $connection = Yii::$app->db;
        $request = Yii::$app->request;
        $data = $request->bodyParams; // Get the body of the request
        $date = $data['date'];
        $model = $this->findPenjualanByDate($date);

        if ($data >= date('Y-m-d')) {
            $model = $this->findPenjualanByDateMax($date);
        }

        $transaction = $connection->beginTransaction();
        try {
            if ($model) {
                $transaction->commit();
                $res['status'] = true;
                $res['data'] = $model;
                $res['message'] = 'Berhasil mengambil data!';
            } else {
                $res['status'] = true;
                $res['message'] = 'Data Kosong';
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
