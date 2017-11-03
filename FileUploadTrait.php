<?php
/**
 * Created by PhpStorm.
 * User: samarth
 * Date: 18/7/17
 * Time: 12:56 PM
 */

namespace App\Traits;

use Image;
use Exception;
use Webpatser\Uuid\Uuid;

trait FileUploadTrait
{
    private $trait_file = null;

    protected function file_trait_path()
    {
        return null;
    }

    protected function getUniqueId()
    {
        return null;
    }

    public function getFile()
    {
        return $this->trait_file;
    }

    public function setFile($value)
    {
        $this->trait_file = $value;
    }

    public static function makeDirectory($destination_path)
    {
        if (!is_dir($destination_path)) {
            mkdir($destination_path, 0777, true);
        }
    }

    private function base_directory()
    {
        return array_first([$this->file_trait_path(), public_path()."/".env('FILE_REPO'), public_path().'/file_trait'], function ($value) {return $value != null;});
    }

    private function getFileUniqueId()
    {
        return array_first([$this->getUniqueId(), Uuid::generate()->string], function ($value) {return $value != null;});
    }

    private function getPath()
    {
        $class = explode('\\', get_class($this));
        return $this->base_directory() ."/".str_plural(strtolower($class[count($class) - 1]))."/";
    }

    public function getFilePath()
    {
        return glob($this->getPath().$this->getFileUniqueId()."*");
    }

    public function uploadFile()
    {
        if (strstr($this->trait_file->getMimeType(), 'image'))
        {
            if (!$this->saveImage($this->trait_file))
            {
                return false;
            }
        }
        else
        {
            self::makeDirectory($this->getPath());
            $extension = $this->trait_file->getClientOriginalExtension();
            $this->trait_file->move($this->getPath(), $this->getFileUniqueId()."_".strstr($this->trait_file->getMimeType(), '/', true).".".$extension);
        }
        return true;
    }

    //---------------functions used only for images-----------------
    protected static function imageTraitProperties()
    {
        return [];
    }

    private function saveImagePath($value = 'original', $extension)
    {
        return $this->getPath().$this->getFileUniqueId()."_".$value.".".$extension;
    }

    public function getImagePath($value = 'original')
    {
        return glob($this->getPath().$this->getFileUniqueId()."_".$value.".*");
    }

    public function getImageRelativePath($identifier = 'original')
    {
        return str_replace(public_path(), "", $this->getImagePath($identifier)[0]);
    }

    public function getImageUrl($identifier = 'original')
    {
        return env('APP_URL').$this->getImageRelativePath($identifier);
    }

    public function saveImage($data)
    {
        try
        {
            $img = Image::make($data);
            $img->backup();
            self::makeDirectory($this->getPath());
            $img->save($this->saveImagePath('original', ltrim($img->mime(), 'image/')), 100);
            foreach ($this->imageTraitProperties() as $version_identifier => $version_identifier_options)
            {
                if ($version_identifier_options['dimension'] != null)
                {
                    $img->resize($version_identifier_options['dimension'][0], $version_identifier_options['dimension'][1]);
                }
                $img->save($this->saveImagePath($version_identifier, ltrim($img->mime(), 'image/')), ($version_identifier_options['quality'] * 100));
                $img->reset();
            }
        }
        catch (Exception $e)
        {
            $this->addError(['file' => [$e->getMessage()]]);
            self::logs("Intervention Image Exception: ". $e->getMessage());
            self::logs("Intervention Image Exception Line: ". $e->getLine());
            self::logs("Intervention Image Exception File: ". $e->getFile());
            $this->slack("Intervention Image Exception: ". $e->getMessage(). "\nIntervention Image Exception Line: ". $e->getLine(). "Intervention Image Exception File: ". $e->getFile());
            return false;
        }
        return true;
    }
}
