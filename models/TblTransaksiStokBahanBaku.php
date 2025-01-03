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
class TblTransaksiStokBahanBaku extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_transaksi_stok_bahan_baku';
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_transaksi_stok', 'id_bahan_baku', 'quantity'], 'integer'],
        ];
    }
    public function fields()
    {
        $fields = parent::fields();

        $fields['list_bahan_baku']  = function ($model) {
            return $this->bahanBaku ?? '';
        };
        return $fields;
    }
    public function getBahanBaku()
    {
        return $this->hasOne(TblBahanBaku::class, ['id' => 'id_bahan_baku'])->orderBy(['id' => SORT_DESC]);
    }
}
