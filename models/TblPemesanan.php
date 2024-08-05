<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use Yii;
use yii\db\Exception;
use yii\web\NotFoundHttpException;

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
    public $arrMenu;
    public static function tableName()
    {
        return 'tbl_pemesanan';
    }
    public function rules()
    {
        return [
            [['id_meja', 'id_pelayan'], 'integer'],
            [['status'], 'in', 'range' => ['ordered', 'in_progress', 'ready', 'served', 'paid']],
            ['waktu', 'safe'],
        ];
    }
    public function fields()
    {
        $fields = parent::fields();
        $fields['order_detail']  = function ($model) {
            return $this->orderDetail ?? [];
        };
        return $fields;
    }
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();
            try {
                foreach ($this->arrMenu as $value) {
                    $model = new TblPemesananDetail();
                    $model->id_pemesanan = $this->id;
                    $model->id_menu = $value['id'];
                    $model->quantity = $value['qty'];
                    $model->harga = $value['harga'] + $value['harga_ekstra'];
                    $model->total = $value['totalHarga'];
                    $model->temperatur = $value['temperatur'];
                    $model->status = $this->status;
                    if (!$model->save()) {
                        throw new \Exception('Failed to save data');
                    }
                }

                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
                throw new Exception('Failed to save ordered: ' . $e->getMessage());
            }
        }
    }
    public function getOrderDetail()
    {
        return $this->hasMany(TblPemesananDetail::class, ['id_pemesanan' => 'id'])->orderBy(['id' => SORT_DESC]);
    }
}
