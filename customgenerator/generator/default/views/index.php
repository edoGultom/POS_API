<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\bootstrap4\Modal;
use yii\helpers\Url;
use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();
foreach ($generator->getColumnNames() as $name) {
    $var_cari = $name;
    break;
}

echo "<?php\n";
?>
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap5\Modal;
use yii2ajaxcrud\ajaxcrud\CrudAsset;
use yii2ajaxcrud\ajaxcrud\BulkButtonWidget;
use kartik\form\ActiveForm;
use yii\widgets\Pjax;



/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>;
$this->params['breadcrumbs'][] = $this->title;
$this->registerCssFile("@web/css/myfield.css");

CrudAsset::register($this);
if (Yii::$app->request->isAjax) {
$this->registerJsFile(
'@web//js-custom/form-reload.js',
['depends' => [\yii\web\JqueryAsset::className()]]
);
}
?>
<div class="row">
    <div class="col-sm-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="row title-table panel-custom">
                    <div class="col-xxl-10">List <?= "<?= " ?> $this->title <?= " ?>" ?></div>
                    <div class="col-xxl-2 d-flex">
                        <?= '<?=' . "\n" ?>
                        Html::a(
                        'Tambah Data <span class="btn-label"><i class="ri-add-circle-line"></i></span>',
                        ['create'],
                        [
                        'role'=>'modal-remote',
                        'title'=> Yii::t('yii2-ajaxcrud', 'Create New').'create',
                        'class'=>'btn btn-tambah-data px-1'
                        ])
                        <?= "?>\n" ?>
                    </div>
                </div>
            </div>
            <div class="card-body custom-card-body">
                <?= '<?php' ?> Pjax::begin(['id' => 'search-form-wrap', 'enablePushState' => false]) <?= "?>\n" ?>
                <?= '<?php' ?>
                if (Yii::$app->request->isAjax) {
                $form = ActiveForm::begin([
                // 'action' => ['index'],
                'method' => 'GET',
                'id' => 'formReload',
                ]) ;
                }else{
                $form = ActiveForm::begin([
                // 'action' => ['index'],
                'method' => 'GET',
                ]) ;
                }
                <?= "?>\n" ?>
                <div class="d-grid form-search">
                    <?= '<?php' ?>
                    if (Yii::$app->request->isAjax) {
                    echo '<input type="hidden" name="url" value="'.Url::current() .'">';
                    echo $form->field($searchModel, 'cari', ['addon' => ['prepend' => ['content' => '<i class=" ri-search-line"></i>']]])->textInput(['id' => 'textSearch','maxlength' => true, 'class' => 'table-input-search item1', 'placeholder' => 'Cari berdasarkan'])->label(false);
                    }
                    else{
                    echo $form->field($searchModel, 'cari', ['addon' => ['prepend' => ['content' => '<i class=" ri-search-line"></i>']]])->textInput(['maxlength' => true, 'class' => 'table-input-search item1', 'placeholder' => 'Cari berdasarkan'])->label(false);
                    }
                    <?= "?>\n" ?>
                    <?= '<?=' ?> Html::button('<i class=" ri-search-line"></i> Cari', ['type' => 'submit', 'class' => 'btn btn-search']) <?= "?>\n" ?>
                </div>
                <?= '<?php' ?> ActiveForm::end() <?= "?>\n" ?>
                <div id="ajaxCrudDatatable">
                    <?= "<?=" ?>GridView::widget([
                    'id' => 'crud-datatable',
                    'dataProvider' => $dataProvider,
                    'filterModel' => null,
                    'pjax' => true,
                    'columns' => require(__DIR__ . '/_columns.php'),
                    'toolbar' => [],
                    'striped' => false,
                    'condensed' => true,
                    'layout' => '<div>{items}<br />
                        <div class="d-flex justify-content-between px-4 py-2 align-items-center">
                            <div class="text-muted">{summary}</div>
                            <div class="px-2">{pager}</div>
                        </div>
                    </div>',
                    'summary' => 'Menampilkan {end} dari {totalCount} Hasil',
                    'pager' => [
                    'prevPageLabel' => 'Previous',
                    'nextPageLabel' => 'Next',

                    ],
                    'responsive' => true,
                    'panel' => [
                    // 'heading'=>'<h3 class="panel-title custom-panel-title"><i class="fas fa-globe"></i> List '. $this->title .'</h3>',
                    // 'type'=>'success',
                    // 'before'=> Html::button('<img src="images/icons/filters.svg">Filters', ['class' => 'btn btn-filters']),
                    // 'footer'=>false,
                    // 'clearfix' => false,
                    ],
                    'tableOptions' => ['class' => 'table align-middle table-nowrap table-striped-columns mb-0'],
                    ])<?= "?>\n" ?>
                </div>
                <?= '<?php' ?> Pjax::end() <?= "?>\n" ?>
            </div>
        </div>
    </div>
</div>
<?= '<?php Modal::begin([
    "id" => "ajaxCrudModal",
    "footer" => "", // always need it for jquery plugin
    "clientOptions" => [
        "tabindex" => false,
        // "backdrop" => "static",
        "keyboard" => false,
    ],
    "options" => [
        "tabindex" => false
    ]
])?>' . "\n" ?>
<?= '<?php Modal::end(); ?>' ?>