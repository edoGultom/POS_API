<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

/* @var $model \yii\db\ActiveRecord */

$model = new $generator->modelClass();
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}

echo "<?php\n";
?>
use yii\bootstrap5\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form">
    <?= '<?php if (!Yii::$app->request->isAjax){ ?>' . "\n" ?>
    <?= '<div class="card">' . "\n" ?>
    <?= '<div class="card-body">' . "\n" ?>
    <?= "<?php } ?>\n" ?>
    <?= "<?php " ?>$form = ActiveForm::begin(); ?>

    <?php foreach ($generator->getColumnNames() as $attribute) {
        if (in_array($attribute, $safeAttributes)) {
            echo "      <?= " . $generator->generateActiveField($attribute) . " ?>\n\n";
        }
    } ?>
    <?= '<?php if (!Yii::$app->request->isAjax){ ?>' . "\n" ?>
    <div class="form-group">
        <?= "<?= " ?>Html::submitButton($model->isNewRecord ? <?= $generator->generateString('Create') ?> : <?= $generator->generateString('Update') ?>, ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
    <?= "<?php } ?>\n" ?>

    <?= "<?php " ?>ActiveForm::end(); ?>
    <?= '<?php if (!Yii::$app->request->isAjax){ ?>' . "\n" ?>
</div>
</div>
<?= "<?php } ?>\n" ?>
</div>