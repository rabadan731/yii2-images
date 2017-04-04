<?php

namespace rabadan731\images\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use rabadan731\images\models\query\ImageQuery;

/**
 * This is the model class for table "r731_images".
 *
 * @property integer $image_id
 * @property string $title
 * @property integer $status
 * @property integer $position
 * @property integer $type
 * @property integer $sitemap
 * @property string $object_class
 * @property string $object_table
 * @property integer $object_id
 * @property string $file_name
 * @property string $file_folder
 * @property string $file_url
 * @property string $crop
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $eventDate
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property string $niceEventDate
 */
class Image extends \yii\db\ActiveRecord
{
    CONST IMG_DEFAULT = 0;
    CONST IMG_LOGO = 1;
    CONST IMG_LIST = 5;
    CONST IMG_WYSIWYG = 9;

    CONST STATUS_DRAFT = 0;
    CONST STATUS_CLOSE = 3;
    CONST STATUS_PUBLISH = 7;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            BlameableBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'r731_images';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_table', 'object_id', 'file_name', 'file_url'], 'required'],
            [[
                'status',
                'position',
                'type',
                'sitemap',
                'object_id',
                'created_at',
                'updated_at',
                'eventDate',
                'created_by',
                'updated_by'], 'integer'
            ],
            [['title', 'object_table', 'file_name', 'file_folder', 'crop'], 'string', 'max' => 256],
            [['object_class'], 'string', 'max' => 512],
            [['file_url'], 'string', 'max' => 1024],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'title' => Yii::t('common', 'Title'),
            'status' => Yii::t('common', 'Status'),
            'position' => Yii::t('common', 'Position'),
            'type' => Yii::t('common', 'Type'),
            'sitemap' => Yii::t('common', 'Sitemap'),
            'object_class' => Yii::t('common', 'Object Class'),
            'object_table' => Yii::t('common', 'Object Table'),
            'object_id' => Yii::t('common', 'Object ID'),
            'file_name' => Yii::t('common', 'File Name'),
            'file_url' => Yii::t('common', 'File Url'),
            'crop' => Yii::t('common', 'Crop'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'eventDate' => Yii::t('common', 'Event Date'),
            'created_by' => Yii::t('common', 'Created By'),
            'updated_by' => Yii::t('common', 'Updated By'),
        ];
    }

    public function getUrl()
    {
        return "/".ltrim($this->file_url, "/");
    }

    public function getPath()
    {
        return Yii::getAlias('@root/storage/') . ltrim($this->file_url, "/");
    }


    /******************************************** СОХРАНЕНИЕ **********************************************************/
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord && is_null($this->position)) {
                $this->file_url = trim($this->file_url, "/");
                $this->file_name = trim($this->file_name, "/");
                $this->position = self::find()->max("position") + 30;
            }
            return true;
        } else {
            return false;
        }
    }
    /****************************************  ***************************************************/


    /********************************************** DELETE *************************************************/
    public function beforeDelete()
    {
        if (is_file($this->getPath())) @unlink($this->getPath());

        return parent::beforeDelete();
    }

//    public static function deleteItems($dir, $id)
//    {
//        $items = self::find()->where(['object_table' => $dir, 'object_id' => $id])->all();
//        foreach ($items as $item) {
//            $item->delete();
//        }
//    }

    /**********************************************  ******************************************************/

    public function getLogo($w = 250, $h=250)
    {
        return Yii::$app->image->getImage([
            'path' => $this->file_url,
            'actions' => ['best_fit' => ['w' => $w, 'h' => $h]]
        ]);
    }

    public function getThumb($w = 250, $h=250)
    {
        return Yii::$app->image->getImage([
            'path' => $this->file_url,
            'actions' => ['thumbnail' => ['w' => $w, 'h' => $h]]
        ]);
    }


//    public static function getList(
//        $tableAttribute = null,
//        $idAttribute = null,
//        $type = self::IMG_LIST,
//        $tumbWidth = 250,
//        $imgWidth = 1920,
//        $imgHeight = 1080)
//    {
//
//        if ($tableAttribute === null) {
//            throw new InvalidConfigException('The "tableAttribute" property must be set.');
//        }
//        if ($idAttribute === null) {
//            throw new InvalidConfigException('The "idAttribute" property must be set.');
//        }
//
//        $images = self::find()->where([
//            'object_table' => $tableAttribute,
//            'object_id' => $idAttribute,
//            'type' => $type
//        ])->all();
//
//        $result = [];
//
//
//        if (count($images)) {
//
//            foreach ($images as $image) {
//                $parametrsThumb = [
//                    'path' => ($image->file_url) . ($image->file_name),
//                    'actions' => [
//                        'thumbnail' => ['w' => $tumbWidth, 'h' => $tumbWidth]
//                    ]
//                ];
//
//                if ($image->crop !== null) {
//                    $crop = explode(",", $image->crop);
//                    $parametrsThumb['actions'] = array_merge(
//                        ['crop' => ['x1' => $crop[0], 'y1' => $crop[1], 'x2' => $crop[2], 'y2' => $crop[3]]],
//                        $parametrsThumb['actions']
//                    );
//                }
//                $parametrsImg = [
//                    'path' => ($image->file_url) . ($image->file_name),
//                    'actions' => [
//                        'best_fit' => ['w' => $imgWidth, 'h' => $imgHeight]
//                    ]
//                ];
//                $result[$image->id] = [
//                    'thumb' => Yii::getAlias("@frontendUrl") . Yii::$app->image->getImage($parametrsThumb),
//                    'img' => Yii::getAlias("@frontendUrl") . Yii::$app->image->getImage($parametrsImg),
//                    'original' => Yii::getAlias("@frontendUrl") . ($image->file_url) . ($image->file_name),
//                    'title' => $image->title
//                ];
//            }
//        } else {
//            $result[0] = [
//                'thumb' => Yii::getAlias("@frontendUrl") . Yii::$app->image->noimagePath,
//                'img' => Yii::getAlias("@frontendUrl") . Yii::$app->image->noimagePath,
//                'original' => Yii::getAlias("@frontendUrl") . Yii::$app->image->noimagePath,
//                'title' => 'No image'
//            ];
//        }
//
//        return $result;
//    }


    public static function updateList($post)
    {

        foreach ($post as $id => $data) {
            if (($model = self::findOne($id)) !== null) {


                if (isset($data['title'])) {
                    $model->title = $data['title'];
                }
//                if (isset($data['type'])) {
//                    $model->type = $data['type'];
//                }
                if (isset($data['position'])) {
                    $model->position = $data['position'];
                }


//                if (isset($data['file']) && ($data['file'] == 1)) {
//                    $old_full_url = Yii::getAlias('@frontend/web/') . $model->file_url . DIRECTORY_SEPARATOR . $model->file_name;
//                    if (is_file($old_full_url)) {
//                        $new_file_name = Inflector::slug($data['title'], '_') . "_" . uniqid() . substr(strrchr($model->file_name, '.'), 0);
//                        $new_full_url = Yii::getAlias('@frontend/web/') . ($model->file_url) . DIRECTORY_SEPARATOR . $new_file_name;
//                        if (rename($old_full_url, $new_full_url)) {
//                            $model->file_name = $new_file_name;
//                        } else {
//                            echo 'no rename file';
//                            die;
//                        }
//                    } else {
//                        echo "<pre>" . print_r($old_full_url, true) . "</pre>";
//                        echo 'no find file';
//                        die;
//                    }
//                }

                $model->save();
            } else {
                echo 'no model';
                die;
            }
        }
    }


    /**
     * @inheritdoc
     * @return ImageQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ImageQuery(get_called_class());
    }

    public function getNiceEventDate()
    {
        return Yii::$app->formatter->asDatetime($this->eventDate);
    }
}
