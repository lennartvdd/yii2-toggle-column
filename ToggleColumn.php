<?php
/**
 * @link http://phe.me
 * @copyright Copyright (c) 2014 Pheme
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace pheme\grid;

use yii\grid\DataColumn;
use yii\helpers\Html;
use yii\web\View;
use Yii;

/**
 * @author Aris Karageorgos <aris@phe.me>
 */
class ToggleColumn extends DataColumn
{
    /**
     * Toggle action that will be used as the toggle action in your controller
     * @var string
     */
    public $action = 'toggle';


    /**
     * @var string pk field name
     */
    public $primaryKey = 'primaryKey';

    /** 
     * Build a custom URL (primaryKey and action values will be ignored if this is specified.)
     * @var closure 
     */
    public $url;

    /**
     * Whether to use ajax or not
     * @var bool
     */
    public $enableAjax = true;

    public function init()
    {
        if ($this->enableAjax) {
            $this->registerJs();
        }
    }

    protected function buildUrl($model, $key, $index)
    {
        if(isset($this->url) && is_callable($this->url)) {
            return call_user_func($this->url, $model, $key, $index);
        } else {
            return [$this->action, 'id' => $model->{$this->primaryKey}];
        }
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $url = $this->buildUrl($model, $key, $index);

        $attribute = $this->attribute;
        $value = $model->$attribute;

        if ($value === null || $value == true) {
            $icon = 'ok';
            $title = Yii::t('yii', 'Off');
        } else {
            $icon = 'remove';
            $title = Yii::t('yii', 'On');
        }
        return Html::a(
            '<span class="glyphicon glyphicon-' . $icon . '"></span>',
            $url,
            [
                'title' => $title,
                'class' => 'toggle-column',
                'data-method' => 'post',
                'data-pjax' => '0',
            ]
        );
    }

    /**
     * Registers the ajax JS
     */
    public function registerJs()
    {
        $js = <<<'JS'
$("a.toggle-column").on("click", function(e) {
    e.preventDefault();
    $.post($(this).attr("href"), function(data) {
        var pjaxId = $(e.target).closest(".grid-view").parent().attr("id");
        $.pjax.reload({container:"#" + pjaxId});
    });
    return false;
});
JS;
        $this->grid->view->registerJs($js, View::POS_READY, 'pheme-toggle-column');
    }
}
