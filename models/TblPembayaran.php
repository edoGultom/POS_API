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
class TblPembayaran extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_pembayaran';
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_pemesanan', 'jumlah', 'jumlah_diberikan', 'jumlah_kembalian', 'id_kasir'], 'integer'],
            [['tipe_pembayaran', 'id_transaksi_qris', 'link_qris'], 'string'],
            ['waktu_pembayaran', 'safe'],
        ];
    }
    public function fields()
    {
        $fields = parent::fields();
        // Add extra field
        $fields['detail']  = function ($model) {
            return $this->pemesananDetail ?? [];
        };
        $fields['kasir']  = function ($model) {
            return $this->user->name ?? [];
        };
        return $fields;
    }
    public function getPemesananDetail()
    {
        return $this->hasMany(TblPemesananDetail::class, ['id_pemesanan' => 'id_pemesanan'])->orderBy(['id' => SORT_DESC]);
    }
    public function getPemesanan()
    {
        return $this->hasOne(TblPemesanan::class, ['id' => 'id_pemesanan'])->orderBy(['id' => SORT_DESC]);
    }
    public function getKasir()
    {
      return User::findOne([
            'id' => $this->id_kasir,
        ]);

    }
}
