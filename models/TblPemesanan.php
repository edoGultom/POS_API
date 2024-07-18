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
class TblPemesanan extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_pemesanan';
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_meja', 'id_pelayan'], 'integer'],
            [['status'], 'in', 'range' => ['ordered', 'in_progress', 'ready', 'served', 'paid']],
            ['waktu', 'safe'],
        ];
    }
}
