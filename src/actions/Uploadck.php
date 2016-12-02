<?php

namespace rabadan731\image\actions;

use Yii;
use yii\base\Action;
use yii\helpers\Inflector;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class UploadCk extends Action
{
    public $imageDir;

    public function init()
    {
        if (is_null($this->imageDir)) {
            $this->imageDir = "images/article";
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        //Создаем объект загрузки
        $uploadedFile = UploadedFile::getInstanceByName('upload');

        //Тип изображения
        $mime = FileHelper::getMimeType($uploadedFile->tempName);

        //Генерируем имя изображения
        $fileName = "{${Inflector::slug($uploadedFile->baseName)}}{${time()}}{$uploadedFile->extension}";

        // системный урл
        $fileFolder = "{${Yii::getAlias('@webroot')}}{${DIRECTORY_SEPARATOR}}{$this->imageDir}";

        //публичный урл
        $publicImageUrl = "{${Yii::getAlias('@web')}}/{$this->imageDir}/{$fileName}";

        //создаем папку, если таковой нет
        FileHelper::createDirectory($fileFolder);

        //полный путь нового файла
        $uploadPath = "{$fileFolder}{${DIRECTORY_SEPARATOR}}{$fileName}";

        //обширная проверка пригодности, прежде чем делать что-нибудь с файлом ...
        if ($uploadedFile == null) {
            $message = Yii::t("common", "No file uploaded.");
        } else if ($uploadedFile->size == 0) {
            $message = Yii::t("common", "The file is of zero length.");
        } else if ($mime != "image/jpeg" && $mime != "image/png") {
            $message = Yii::t(
                "common",
                "The image must be in either JPG or PNG format. Please upload a JPG or PNG instead."
            );
        } else if ($uploadedFile->tempName == null) {
            $message = Yii::t(
                "common",
                "You may be attempting to hack our server. We're on to you; 
                expect a knock on the door sometime soon."
            );
        } else {
            //если все ок, то грузим файл
            $message = "";
            $move = $uploadedFile->saveAs($uploadPath);
            if (!$move) {
                $message = Yii::t(
                    "common",
                    "Error moving uploaded file. Check the script is granted Read/Write/Modify permissions."
                );
            }
        }
        $funcNum = $_GET['CKEditorFuncNum'];
        echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction({$funcNum},
             '$publicImageUrl', '{$message}');</script>";
    }
}
