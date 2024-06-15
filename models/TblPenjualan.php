<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use Yii;
use yii\base\Exception;

/**
 * This is the model class for table "partai".
 *
 * @property int $id
 * @property int|null $no_urut_partai
 * @property string|null $nama_partai
 * @property string|null $keterangan
 */
class TblPenjualan extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_penjualan';
    }
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
    /**
     * {@inheritdoc}
     */
    public $id_barang;
    public $qty;
    public $harga;
    public $total;
    public function rules()
    {
        return [
            [['total_transaksi', 'id_pelanggan', 'id_user', 'created_at', 'updated_at'], 'integer'],
            [['status_pembayaran', 'payment_gateway'], 'string', 'max' => 100],
            [['tanggal_pembayaran', 'id_barang', 'qty', 'harga', 'total'], 'safe'],
        ];
    }
    public function getPenjualanBarang()
    {
        return $this->hasMany(TblPenjualanBarang::class, ['id_penjualan' => 'id']);
    }
}
