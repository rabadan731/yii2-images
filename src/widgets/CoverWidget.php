<?php

namespace rabadan731\images\widgets;

use common\models\R731Images;
use Yii;
use rabadan731\enum\helpers\ImageEnum;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Html;

class CoverWidget extends Widget {
    /**
     * @var string table attribute
     */
    public $tableAttribute=null;
    /**
     * @var int id attribute
     */
    public $idAttribute=null;

    public $type=ImageEnum::COVER;

    protected $image;

    public $options;
    public $w=250;
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

        $this->image = R731Images::find()->where([
            'object_table'  => $this->tableAttribute,
            'object_id'     => $this->idAttribute,
            'type'          => $this->type
        ])->one();

        if (is_null($this->image)) {
            $this->image = R731Images::find()->where([
                'object_table'  => $this->tableAttribute,
                'object_id'     => $this->idAttribute,
                'type'          => ImageEnum::ITEM
            ])->one();
        }



    }

    /**
     * Executes the widget.
     * @return string the result of widget execution to be outputted.
     */
    public function run()
    {
        if ($this->image === null) {
            return  Html::img(Yii::$app->image->noimagePath,$this->options);
        }

        if (!isset($this->options['alt'])) {
            $this->options['alt'] = $this->image->title;
        }

        $parametrs = [
            'path'=> $this->image->url,
            'actions'=>[
                'thumbnail'=>['w'=> $this->w]
            ]
        ];
        if ($this->image->crop !== null) {
            $crop = explode(",",$this->image->crop);
            $parametrs['actions'] = array_merge(
                ['crop'=> ['x1'=> $crop[0],'y1'=> $crop[1],'x2'=> $crop[2],'y2'=> $crop[3]]],
                $parametrs['actions']
            );
        }
        return Html::img(Yii::getAlias("@frontendUrl").Yii::$app->image->getImage($parametrs),$this->options);
    }
} 