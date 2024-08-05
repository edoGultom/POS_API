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
        $fields['pelayan']  = function ($model) {
            return $this->user->name ?? '';
        };
        $fields['order_detail']  = function ($model) {
            return $this->orderDetail ?? [];
        };
        $fields['meja']  = function ($model) {
            return $this->meja ?? '';
        };
        $fields['total']  = function ($model) {
            return $this->sumOrderDetail ?? 0;
        };
        $fields['quantity']  = function ($model) {
            return $this->sumQty ?? 0;
        };
        $fields['waktu']  = function ($model) {
            return Yii::$app->formatter->asDateTime($this->waktu, 'php:d-m-Y H:i:s');
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
                $table = TblMeja::findOne(['id' => $this->id_meja]);
                if ($table) {
                    $table->status = 'Occupied';
                    $table->save();
                }
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
    public function getSumOrderDetail()
    {
        return $this->hasMany(TblPemesananDetail::class, ['id_pemesanan' => 'id'])->sum('total');
    }
    public function getSumQty()
    {
        return $this->hasMany(TblPemesananDetail::class, ['id_pemesanan' => 'id'])->sum('quantity');
    }
    public function getMeja()
    {
        return $this->hasOne(TblMeja::class, ['id' => 'id_meja'])->orderBy(['id' => SORT_DESC]);
    }
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'id_pelayan']);
    }
}
