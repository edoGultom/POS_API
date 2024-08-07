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
                    'by-date-range'  => ['POST'],
                    'by-date'  => ['POST'],
                ],
            ],
        ]);
    }
    protected function findByDateRange($start, $end)
    {
        $model = Yii::$app->db->createCommand(
            "
            SELECT 
                DATE_FORMAT(FROM_UNIXTIME(s.created_at), '%Y-%m-%d') AS date, -- Format timestamp ke YYYY-MM-DD
                p.id AS product_id, -- ID produk
                p.nama_barang AS product_name, -- Nama produk
                SUM(si.qty) AS total_quantity_sold, -- Total kuantitas yang terjual
                SUM(si.harga * si.qty) AS total_sales_amount, -- Total penjualan dalam nilai uang
                SUM(CASE WHEN py.payment_method = 'CASH' THEN si.harga * si.qty ELSE 0 END) AS cash_sales, -- Total penjualan tunai
                SUM(CASE WHEN py.payment_method = 'QRIS' THEN si.harga * si.qty ELSE 0 END) AS qris_sales -- Total penjualan via QRIS
            FROM 
                tbl_penjualan s
            JOIN 
                tbl_penjualan_barang si ON s.id = si.id_penjualan -- Gabung tabel Sales dan Sale_Items
            JOIN 
                tbl_menu p ON si.id_barang = p.id -- Gabung tabel Sale_Items dan Products
            LEFT JOIN 
                tbl_pembayaran py ON s.id = py.id_penjualan -- Gabung tabel Sales dan Payments
            WHERE 
                DATE(FROM_UNIXTIME(s.created_at)) BETWEEN '$start' AND '$end' -- Filter berdasarkan tanggal
            GROUP BY 
                DATE_FORMAT(FROM_UNIXTIME(s.created_at), '%Y-%m-%d'), p.id, p.nama_barang -- Kelompokkan berdasarkan tanggal, ID produk, dan nama produk
            ORDER BY 
                DATE_FORMAT(FROM_UNIXTIME(s.created_at), '%Y-%m-%d'), p.id; -- Urutkan berdasarkan tanggal dan ID produk
            "
        )->queryAll();
        return array_values($model);
    }
    protected function findByDate($date)
    {
        $model = Yii::$app->db->createCommand(
            " SELECT 
                DATE(tts.tanggal) AS tanggal,
                tbb.nama AS nama_bahan_baku,
                	tubb.nama satuan,
                SUM(CASE WHEN tts.tipe = 'masuk' THEN ttsbb.quantity ELSE 0 END) AS total_masuk,
                SUM(CASE WHEN tts.tipe = 'keluar' THEN ttsbb.quantity ELSE 0 END) AS total_keluar,
                (
                    SUM(CASE WHEN tts.tipe = 'masuk' THEN ttsbb.quantity ELSE 0 END) +
                    SUM(CASE WHEN tts.tipe = 'keluar' THEN ttsbb.quantity ELSE 0 END)
                ) AS saldo_akhir
            FROM 
                tbl_transaksi_stok_bahan_baku ttsbb
            INNER JOIN
                tbl_transaksi_stok tts on tts.id=ttsbb.id_transaksi_stok
            INNER JOIN 
                tbl_menu_bahan_baku tmbb ON tmbb.id_bahan_baku = ttsbb.id_bahan_baku
            INNER JOIN 
                tbl_bahan_baku tbb ON ttsbb.id_bahan_baku = tbb.id
            INNER JOIN 
                tbl_menu tm ON tm.id = tmbb.id_menu
            INNER JOIN 
		        tbl_unit_bahan_baku tubb on tubb.id = tbb.id_unit_bahan_baku
            WHERE tts.tanggal ='$date' 
            GROUP BY 
                DATE(tts.tanggal),
                    ttsbb.id_bahan_baku
            "
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
    public function actionByDateRange()
    {
        $res = [];
        $connection = Yii::$app->db;
        $request = Yii::$app->request;
        $data = $request->bodyParams; // Get the body of the request
        $dateStart = $data['start'];
        $dateEnd = $data['end'];
        $model = $this->findByDateRange($dateStart, $dateEnd);

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
    public function actionByDate()
    {
        $res = [];
        $connection = Yii::$app->db;
        $request = Yii::$app->request;
        $data = $request->bodyParams; // Get the body of the request
        $date = $data['date'];
        $model = $this->findByDate($date);

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
