<?php
namespace rabadan731\images\components;

use Yii;
use yii\base\Component;
use yii\helpers\FileHelper;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

/**
 * Created by PhpStorm.
 * User: rabadan
 * Date: 08.11.15
 * Time: 13:22
 */
class Image extends Component
{

    public $sourcePath;
    public $filesPath;
    public $cachePath;

    public $qualiti = 80;

    public $noimagePath = "noimage.png";

    public function getPathRoot()
    {
        return Yii::getAlias("@root/storage");
    }

    /**
     * @param array $parametrs
     * @return string
     * @throws HttpException
     */
    public function getImage($params)
    {
        $imageUrl = $params['path'];
        if ($imageUrl === false) {
            return "/images/{$this->noimagePath}";
        }
        $newPathCache = $this->createCachePath($params);

        if (!file_exists($this->getPathRoot().$newPathCache)) {

            try {
                $img = new SimpleImage($this->getPathRoot()."/".ltrim($imageUrl, "/"));
            } catch (\Exception $e) {
                return "/images/{$this->noimagePath}";
            }
            try {
                foreach ($params['actions'] as $action => $action_param) {
                    $this->{$action}($img, $action_param);
                }
            } catch (\Exception $e) {
                throw new HttpException("create image bad parametrs");
            }

            //сохранение
            $this->createDir($this->getPathRoot()."{$newPathCache}");
          //   echo Yii::getAlias("@root")."{$newPathCache}"; die;
            $img->save(
                $this->getPathRoot().$newPathCache,
                ($img->get_original_info()['exif']['FileSize'] > 100000) ? 80 : null
            );
        }

        return $newPathCache;

//Загрузкака изображение
//        $fullPath = $this->getFullPath($imageUrl);
//        if ($fullPath === false) {
//            return $this->noimagePath;
//        }
//        $oldFileName = $this->getFileName($imageUrl);
//        $newfileName = $this->getNewName($imageUrl, $oldFileName, $params['actions']);
////        echo var_dump($this->getFullPathNewFile($newfileName)); echo "<br />";
////        die;
//        $newPath = $this->getFullPathNewFile($newfileName);
//        if (!$newPath) {
//            $img = new SimpleImage($fullPath);
//
//            try {
//                foreach ($params['actions'] as $action => $action_param) {
//                    $this->{$action}($img, $action_param);
//                }
//            } catch (\Exception $e) {
//                throw new HttpException("create image bad parametrs");
//            }
//
//            //сохранение
//            $this->createDir($newPath);
//
//            $img->save($newPath, ($img->get_original_info()['exif']['FileSize'] > 100000) ? 80 : null);
//            unset($img);
//        }
//        echo $this->filesPath; echo "<br />";
//        echo $this->cachePath;echo "<br />";
//        echo $newfileName;echo "<br />";
//        echo $this->ltrimPath($newfileName);echo "<br />";
//        echo $this->getPublicPathCache($newfileName); echo "<br />";
//        die;
        //возврат ссылки
//        return $this->getPublicPathCache($newfileName);
    }

    public function createCachePath($params)
    {
        $imageUrl = $params['path'];
        //unset($params['path']);
        foreach ($params['actions'] as $action => $actionParam) {
            $options[] = $action;
            $options = array_merge($options, $actionParam);
        }
        $fileName = $this->getFileName($imageUrl);
        $options[] = $fileName;

        $imageUrl = str_replace(
            $fileName,
            implode("_", $options),
            $imageUrl
        );

        return "/".str_replace(
            ((trim($this->filesPath, "/")) . "/"),
            ((trim($this->cachePath, "/")) . "/"),
            $imageUrl
        );
    }


//    public function ltrimPath($path)
//    {
//        $path = ltrim($path, "/");
//        $path = str_replace(((trim($this->filesPath, "/")) . "/"), "", $path);
//        $path = str_replace(((trim($this->cachePath, "/")) . "/"), "", $path);
//        return $path;
//    }

//    public function getPublicPathCache($path)
//    {
//
//        return implode("/", [rtrim($this->cachePath, "/"), $this->ltrimPath($path)]);
//    }
//
//    public function getNewName($link, $fileName, $parameters)
//    {
//
//        foreach ($parameters as $action => $actionParam) {
//            $options[] = $action;
//            $options = array_merge($options, $actionParam);
//        }
//        $options[] = $fileName;
//
//        return str_replace(
//            $fileName,
//            implode("_", $options),
//            $link
//        );
//    }
//
//
//    public function getFullPathNewFile($link)
//    {
//
//        $url = implode("/", [
//            rtrim($this->cachePath, "/"),
//            $this->ltrimPath($link)
//        ]);
//        if (is_file($url)) {
//            return false;
//        }
//
//        return $url;
//    }
//
//
//    public function getFullPath($link)
//    {
//        $url = implode("/", [
//            rtrim(Yii::getAlias("@root"), "/"),
//            ltrim($link, "/")
//        ]);
//
//        if (is_file($url)) {
//            return $url;
//        } else {
//            return false;
//        }
//    }
//
    public function getFileName($path)
    {
        $rPos = strrpos($path, "/");
        if ($rPos === false) {
            return $path;
        }
        return substr($path, $rPos + 1);
    }

    public function createDir($path)
    {
        FileHelper::createDirectory(substr($path, 0, strrpos($path, "/")));
    }

    protected function best_fit($img, $param)
    {
        return $img->best_fit($param['w'], isset($param['h']) ? ($param['h']) : ($param['w']));
    }


    protected function thumbnail($img, $param)
    {
        return $img->thumbnail($param['w'], isset($param['h']) ? ($param['h']) : null);
    }


    protected function resize($img, $param)
    {
        return $img->resize($param['w'], isset($param['h']) ? ($param['h']) : ($param['w']));
    }


    protected function fit_to_width($img, $param)
    {
        return $img->fit_to_width($param['w']);
    }

    protected function fit_to_height($img, $param)
    {
        return $img->fit_to_height(isset($param['h']) ? ($param['h']) : ($param['w']));
    }

    protected function crop($img, $param)
    {
        return $img->crop($param['x1'], $param['y1'], $param['x2'], $param['y2']);
    }


}