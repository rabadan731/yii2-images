<?php
/**
 * Created by PhpStorm.
 * User: rabadan
 * Date: 11.11.15
 * Time: 13:00
 */

namespace rabadan731\image\widgets;

use Yii;
use rabadan731\enum\helpers\ImageEnum;
use rabadan731\images\models\Image;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

class ListWidget extends Widget {
    /**
     * @var string table attribute
     */
    public $tableAttribute=null;
    /**
     * @var int id attribute
     */
    public $idAttribute=null;

    public $type=ImageEnum::ITEM;

    protected $images;

    public $tumbWidth=250;

    public $imgWidth=1920;
    public $imgHeight=1080;
    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init()
    {
        if ($this->tableAttribute === null) {
            throw new InvalidConfigException('The "tableAttribute" property must be set.');
        }
        if ($this->idAttribute === null) {
            throw new InvalidConfigException('The "idAttribute" property must be set.');
        }

        $this->images = Image::find()->where([
            'object_table'  => $this->tableAttribute,
            'object_id'     => $this->idAttribute,
            'type'          => $this->type
        ])->all();

    }

    /**
     * Executes the widget.
     * @return array the result of widget execution to be outputted.
     */
    public function run()
    {
        $result = [];


        if (count($this->images) == 0) {
            $result[0] = [
                'thumb'     => Yii::$app->image->noimagePath,
                'img'       => Yii::$app->image->noimagePath,
                'original'  => Yii::$app->image->noimagePath
            ];
        }

        foreach ($this->images as $image) {
            $parametrsThumb = [
                'path' => ($image->file_path) . ($image->file_name),
                'actions' => [
                    'thumbnail' => ['w' => $this->tumbWidth]
                ]
            ];
            if ($image->crop !== null) {
                $crop = explode(",", $image->crop);
                $parametrsThumb['actions'] = array_merge(
                    ['crop' => ['x1' => $crop[0], 'y1' => $crop[1], 'x2' => $crop[2], 'y2' => $crop[3]]],
                    $parametrsThumb['actions']
                );
            }
            $parametrsImg = [
                'path' => ($image->file_path) . ($image->file_name),
                'actions' => [
                    'best_fit' => ['w' => $this->imgWidth, 'h' => $this->imgHeight]
                ]
            ];

            $result[$image->id] = [
                'thumb' => Yii::$app->image->getImage($parametrsThumb),
                'img' => Yii::$app->image->getImage($parametrsImg),
                'original' => ($image->file_path) . ($image->file_name)
            ];
        }


        return $result;
    }
} 