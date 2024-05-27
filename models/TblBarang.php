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
class TblBarang extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_barang';
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
    public function rules()
    {
        return [
            [['nama_barang', 'harga', 'stok'], 'required'],
            [['created_at', 'updated_at', 'id_satuan', 'id_kategori', 'harga', 'stok'], 'integer'],
            [['nama_barang'], 'string', 'max' => 255],
        ];
    }
    public function getRequiredAttributes()
    {
        $requiredAttributes = [];
        foreach ($this->getActiveValidators() as $validator) {
            if ($validator instanceof \yii\validators\RequiredValidator) {
                $requiredAttributes = array_merge($requiredAttributes, $validator->attributes);
            }
        }
        return $requiredAttributes;
    }
}
