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
class TblBahanBaku extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_bahan_baku';
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nama'], 'string'],
            [['quantity', 'id_unit_bahan_baku'], 'integer'],
        ];
    }
}
