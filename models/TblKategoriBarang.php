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
class TblKategoriBarang extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_kategori_barang';
    }
    public function behaviors()
    {
        return [
            TimestampBehavior::class
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'integer'],
            [['nama_kategori'], 'string', 'max' => 255],
        ];
    }
}
