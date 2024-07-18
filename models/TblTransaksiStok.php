<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use Yii;

/**
 * This is the model class for table "partai".
 *
 * @property int $id
 * @property int|null $no_urut_partai
 * @property string|null $nama_partai
 * @property string|null $keterangan
 */
class TblTransaksiStok extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_transaksi_stok';
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_bahan_baku', 'quantity'], 'integer'],
            [['tipe'], 'in', 'range' => ['Masuk', 'Keluar']],
            ['waktu', 'safe']
        ];
    }
}
