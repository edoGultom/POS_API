<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "partai".
 *
 * @property int $id
 * @property int|null $no_urut_partai
 * @property string|null $nama_partai
 * @property string|null $keterangan
 */
class TblPembayaran extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_pembayaran';
    }

    public function rules()
    {
        return [
            [['id_penjualan', 'jumlah', 'jumlah_diberikan', 'jumlah_kembalian'], 'integer'],
            [['payment_method', 'payment_gateway', 'payment_status'], 'string', 'max' => 100],
            [['tanggal_pembayaran'], 'safe'],
            [['id_transaksi'], 'string'],
        ];
    }
    public function fields()
    {
        $fields = parent::fields();
        // Add extra field
        $fields['detail']  = function ($model) {
            return $this->penjualan->penjualanBarang ?? [];
        };
        return $fields;
    }
    public function getPenjualan()
    {
        return $this->hasOne(TblPenjualan::class, ['id' => 'id_penjualan'])->orderBy(['id' => SORT_DESC]);
    }
}
