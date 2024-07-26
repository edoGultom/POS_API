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
            [['kode'], 'string'],
            ['waktu', 'safe']
        ];
    }
    public function setKode()
    {
        $lastId = TblTransaksiStok::find()->max('id');
        return 'TS-' + sprintf("%04d", $lastId->id + 1);
    }
    public function fields()
    {
        $fields = parent::fields();
        $fields['bahan_baku']  = function ($model) {
            return $this->bahanBaku->nama ?? '';
        };
        $fields['unit']  = function ($model) {
            return $this->bahanBaku->unit->nama ?? '';
        };
        return $fields;
    }
    public function getBahanBaku()
    {
        return $this->hasOne(TblBahanBaku::class, ['id' => 'id_bahan_baku'])->orderBy(['id' => SORT_DESC]);
    }
}
