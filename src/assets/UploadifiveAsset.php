<?php

namespace rabadan731\images\assets;

use yii\web\AssetBundle;

/**
 * Class CommentAsset
 * @package rabadan731\comments
 */
class UploadifiveAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@common/components/images/assets';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/jquery.uploadifive.min.js'
    ];

    /**
     * @inheritdoc
     */
    public $css = [
        'css/uploadifive.css'
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset'
    ];
}