<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "partai".
 *
 * @property int $id
 * @property int|null $no_urut_partai
 * @property string|null $nama_partai
 * @property string|null $keterangan
 */
class TblPenjualanBarang extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_stok_barang';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_penjualan', 'id_barang', 'perubahan_stok', 'created_at', 'updated_at'], 'integer'],
            [['tipe'], 'string', 'max' => 100],
            [['tanggal'], 'safe'],
        ];
    }
}
