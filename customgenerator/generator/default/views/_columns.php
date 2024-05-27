<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap4\Modal;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;


/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$modelClass = StringHelper::basename($generator->modelClass);
$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();
$actionParams = $generator->generateActionParams();

echo "<?php\n";

?>
use yii\helpers\Url;
use yii\helpers\Html;

return [
    //[
    //    'class' => 'kartik\grid\CheckboxColumn',
    //    'width' => '20px',
    //],
    //[
    //    'class' => 'kartik\grid\SerialColumn',
    //    'width' => '30px',
    //],
    <?php
    $count = 0;
    foreach ($generator->getColumnNames() as $name) {   
        if ($name=='id'||$name=='created_at'||$name=='updated_at'){
            echo "    // [\n";
            echo "        // 'class'=>'\kartik\grid\DataColumn',\n";
            echo "        // 'attribute'=>'" . $name . "',\n";
            echo "    // ],\n";
        } else if (++$count < 6) {
            echo "    [\n";
            echo "        'class'=>'\kartik\grid\DataColumn',\n";
            echo "        'attribute'=>'" . $name . "',\n";
            echo "    ],\n";
        } else {
            echo "    // [\n";
            echo "        // 'class'=>'\kartik\grid\DataColumn',\n";
            echo "        // 'attribute'=>'" . $name . "',\n";
            echo "    // ],\n";
        }
    }
    ?>
    [
        'class' => 'kartik\grid\ActionColumn',
        'dropdown' => false,
        'noWrap' => 'true',
        // 'template' => '{view} {update} {delete}',
        'template' => '{update} {delete}',
        'buttons'=>[
            'update' => function ($url, $model, $key) {
                return Html::a('<i class="fas fa-pen"></i>', ['update', 'id'=>$model->id],['class' => 'btn btn-lg link-primary', 'role' => 'modal-remote']);
            },
            'delete' => function ($url, $model, $key) {
                return Html::a('<i class="fas fa-trash"></i>', ['delete', 'id'=>$model->id],[
                    'role' => 'modal-remote',
                    'class' => 'btn btn-lg link-danger',
                    'data-confirm' => false,
                    'data-method' => false,// for overide yii data api
                    'data-request-method' => 'post',
                    'data-toggle' => 'tooltip',
                    'data-confirm-title' => Yii::t('yii2-ajaxcrud', 'Delete'),
                    'data-confirm-message' => Yii::t('yii2-ajaxcrud', 'Delete Confirm') 
                ]);
            },
        ], 
        'vAlign' => 'middle',
        'urlCreator' => function($action, $model, $key, $index) { 
                return Url::to([$action,'<?=substr($actionParams,1)?>'=>$key]);
        },
        // 'viewOptions' => ['role' => 'modal-remote', 'title' => Yii::t('yii2-ajaxcrud', 'View'), 'data-toggle' => 'tooltip', 'class' => 'btn btn-sm btn-outline-success'],
        // 'updateOptions' => ['role' => 'modal-remote', 'title' => Yii::t('yii2-ajaxcrud', 'Update'), 'data-toggle' => 'tooltip', 'class' => 'btn btn-sm btn-outline-primary'],
        // 'deleteOptions' => ['role' => 'modal-remote', 'title' => Yii::t('yii2-ajaxcrud', 'Delete'), 'class' => 'btn btn-sm btn-outline-danger', 
        //     'data-confirm' => false,
        //     'data-method' => false,// for overide yii data api
        //     'data-request-method' => 'post',
        //     'data-toggle' => 'tooltip',
        //     'data-confirm-title' => Yii::t('yii2-ajaxcrud', 'Delete'),
        //     'data-confirm-message' => Yii::t('yii2-ajaxcrud', 'Delete Confirm') ],
    ],

];   