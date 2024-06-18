<?php

namespace app\models;

use Exception;
use Yii;

/**
 * This is the model class for table "partai".
 *
 * @property int $id
 * @property int|null $no_urut_partai
 * @property string|null $nama_partai
 * @property string|null $keterangan
 */
class TblPenjualanBarang extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_penjualan_barang';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_penjualan', 'id_barang', 'qty', 'harga', 'total'], 'integer'],
            ['temperatur', 'string', 'max' => 50]
        ];
    }
    public function fields()
    {
        $fields = parent::fields();
        $fields['nama_barang']  = function ($model) {
            return $this->barang->nama_barang ?? '-';
        };
        $fields['link']  = function ($model) {
            return $this->barang->path ?? '-';
        };
        $fields['kategori']  = function ($model) {
            return $this->barang->kategori->nama_kategori ?? '-';
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
                $barang = TblBarang::findOne(['id' => $this->id_barang]);
                if (!$barang) {
                    throw new Exception('Menu not found');
                }
                $barang->type = 'sales';
                $barang->updateStok = $this->qty;
                $barang->stok = $barang->stok - $this->qty;

                if (!$barang->save()) {
                    throw new Exception('Failed to save the stock menus: ');
                }
                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
                throw new Exception('Failed to save the stock menus: ' . $e->getMessage());
            }
        }
    }
    public function getBarang()
    {
        return $this->hasOne(TblBarang::class, ['id' => 'id_barang']);
    }
}
