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
class TblMetodePembayaran extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_metode_pembayran';
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mtode'], 'string', 'max' => 255],
        ];
    }
}
