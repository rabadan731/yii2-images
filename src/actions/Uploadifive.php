<?php

namespace rabadan731\images\actions;

use rabadan731\images\components\SimpleImage;
use rabadan731\images\models\Image;
use Yii;

use yii\base\Exception;
use yii\helpers\FileHelper;

use yii\base\Action;
use yii\base\InvalidConfigException;

use yii\helpers\Inflector;
use yii\web\UploadedFile; 
use yii\web\Response;


class Uploadifive extends Action
{
    /**
     * @var string Path to directory where files will be uploaded
     */
    public $systemPath;
    public $folderPath;

    private $newFileName;

    public $objectTable;
    public $objectId;
    public $objectClass;

    public $fileExtension = [
        'jpg', 'png', 'jpeg', 'gif'
    ];

    /**
     * @var string Validator name
     */
    public $uploadOnlyImage = true;

    /**
     * @var string Variable's name that Imperavi Redactor sent upon image/file upload.
     */
    public $uploadParam = 'file_upload';

    public $uploadType = Image::IMG_LIST;

    /**
     * @var boolean If `true` unique filename will be generated automatically
     */
    public $unique = true;

    public $watermark;
    public $cachePath;

    public $ifSavedBase = false;

    private $fileTitle;


    public function getPathPublish()
    {
        $list = [
            $this->folderPath
        ];

        if (!is_null($this->objectTable)) {
            $list[] = $this->objectTable;
        }
        if (!is_null($this->objectId)) {
            $list[] = $this->objectId;
        }

        return implode("/", $list);
    }

    public function getPathSystem()
    {
        $list = [
            $this->systemPath
        ];

        if (!is_null($this->objectTable)) {
            $list[] = $this->objectTable;
        }
        if (!is_null($this->objectId)) {
            $list[] = $this->objectId;
        }

        return implode("/", $list);
    }


    /**
     * @inheritdoc
     */
    public function init()
    {
        if (is_null($this->systemPath)) {
            throw new InvalidConfigException('The "pathSystem" attribute must be set.');
        } else {

            //Получаем параметры
            if (is_null($this->objectTable)) {
                $this->objectTable = Yii::$app->request->post("object_table");
            }
            if (is_null($this->objectId)) {
                $this->objectId = Yii::$app->request->post("object_id");
            }
            if (is_null($this->uploadType)) {
                $this->uploadType = Yii::$app->request->post("upload_type");
            }

            if ($this->ifSavedBase) {
                if (empty($this->objectTable) || empty($this->objectId)) {
                    throw new InvalidConfigException(
                        "не указан объекет и его ID для записи в базу данных"
                    );
                }
            }

            if (!file_exists($this->getPathSystem())) {
                if (!FileHelper::createDirectory($this->getPathSystem())) {
                    throw new InvalidConfigException(
                        "Directory {$this->getPathSystem()} attribute doesn't exist or cannot be created."
                    );
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!Yii::$app->request->isPost) {
            return 'Only POST is allowed';
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        $file = UploadedFile::getInstanceByName('file_upload');

        if (array_search($file->extension, $this->fileExtension) === false) {
            return "файл с расширением {$file->extension} загружать нельзя";
        }
        //$file->extension

        $this->fileTitle = $file->baseName;
        $this->newFileName = (Inflector::slug($file->baseName, '_')).
            "_".(uniqid())."."."{$file->extension}";

        if (!$file->saveAs(implode("/", [$this->getPathSystem(), $this->newFileName]))) {
            return "Error save file";
        }

        if (!is_null($this->watermark)) {
            $this->setWaterMark();
        }

        if ($this->ifSavedBase) {
            if (!$this->saveBase()) {
                return "Error save field in base";
            }
        }

        return 1;
    }




    private function saveBase()
    {
        $newModel                   = new Image();
        $newModel->title            = $this->fileTitle;
        $newModel->status           = Image::STATUS_DRAFT;
        $newModel->type             = $this->uploadType;
        $newModel->sitemap          = 1;
        $newModel->object_class     = $this->objectClass;
        $newModel->object_table     = $this->objectTable;
        $newModel->object_id        = $this->objectId;
        $newModel->file_name        = $this->newFileName;
        $newModel->file_folder      = ltrim($this->getPathPublish(), "/");
        $newModel->file_url         = implode("/", [
            $this->getPathPublish(),
            $this->newFileName
        ]);
        return $newModel->save();
    }

    public function getFilePath() {
        return implode("/", [
            $this->getPathSystem(),
            $this->newFileName
        ]);
    }

    public function setWaterMark($right = true)
    {
        if (!file_exists(Yii::getAlias($this->getFilePath()))) {
            throw new Exception('Image Path not detected!');
        }

        if (!file_exists(Yii::getAlias($this->watermark))) {
            throw new Exception('WaterMark not detected!');
        }

        $image = new SimpleImage($this->getFilePath());

        $wmMaxWidth = intval($image->get_width() * 0.4);
        $wmMaxHeight = intval($image->get_height() * 0.4);

        $waterMarkPath = Yii::getAlias($this->watermark);

        $waterMark = new SimpleImage($waterMarkPath);

        if (
            $waterMark->get_height() > $wmMaxHeight
            or
            $waterMark->get_width() > $wmMaxWidth
        ) {
            $waterMarkPathInfo = pathinfo($waterMark);

            $waterMarkPath = "{$this->cachePath}/{$waterMarkPathInfo['filename']}
                {$wmMaxWidth}x{$wmMaxHeight}.{$waterMarkPathInfo['extension']}";

            //throw new Exception($waterMarkPath);
            if (!file_exists($waterMarkPath)) {
                $waterMark->fit_to_width($wmMaxWidth);
                $waterMark->save($waterMarkPath, 100);
                if (!file_exists($waterMarkPath)) {
                    throw new Exception("Cant save watermark to {$waterMarkPath}!!!");
                }
            }
        }

        if ($right) {
            $image->overlay($waterMarkPath, "bottom right", .5, -10, -10);
        } else {
            $image->overlay($waterMarkPath, "bottom left", .5, 10, 10);
        }

        $image->save($this->getFilePath(), 100);
    }


}
