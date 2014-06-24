<?php

class Gravatile
{

    const GRAVATAR_BASE_PATH = 'http://www.gravatar.com/avatar/';

    private $emails = array();

    private $size;

    private $cache;

    private $default;

    /**
     * @param array $emails
     * @param int $size
     * @param string $default
     * @param string $cache
     */
    public function __construct(array $emails, $size = 64, $default = 'mm', $cache = './cache')
    {
        $this->emails = $emails;
        $this->size = (int)$size;
        $this->cache = $cache;
        $this->default = $default;
    }

    /**
     * Create tiles
     *
     * @param string $orientation orientation, vertical or horizontal
     * @param string $target the target
     *
     * @return int the real count
     */
    public function buildTile($orientation = 'vertical', $target = './tile.jpeg')
    {
        $count = 0;
        $width = $height = count($this->emails) * $this->size;
        $hStep = $wStep = 0;
        if ($orientation == 'vertical') {
            $height = $this->size;
            $hStep = $this->size;
        } else {
            $width = $this->size;
            $wStep = $this->size;
        }
        $image = imagecreatetruecolor($width, $height);
        $hTarget = $wTarget = 0;
        foreach ($this->emails as $email) {
            $im = $this->getImage($email);
            if ($im) {
                imagecopy($image, $im, $hTarget, $wTarget, 0, 0, $this->size, $this->size);
                imagedestroy($im);
                $hTarget += $hStep;
                $wTarget += $wStep;
                $count++;
            }
        }

        imagejpeg($image, $target);
        imagedestroy($image);

        return $count;
    }

    private function getImage($email)
    {
        $hash = md5(strtolower($email));
        $file = $this->cache . '/' . $this->size . $this->default . $hash;
        if ($this->cache) {
            $files = glob($file . '.*');
            if ($realFile = array_shift($files)) {
                return $this->createImageBaseOnFile($realFile);
            }

        }

        $data = null;
        $ext = 'jpg';
        while (!$data) {
            $url = 'http://www.gravatar.com/avatar/' .
                $hash . '.png?s=' . $this->size . '&d=' . $this->default;

            $curl = curl_init($url);

            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            //curl_setopt($curl, CURLOPT_HEADER, 1);

            $data = curl_exec($curl);

            // FUCK GRAVATAR!!!!! image headers are not correct!!!
            //list($header, $data) = explode("\r\n\r\n", $resp, 2);
            //if (preg_match('/filename="[^.]+\.([^"]+)/', $header, $matches)) {
            //    $ext = $matches[1];
            //}

            if (trim(substr($data, 1,3)) == 'PNG') {
                $ext = 'png';
            }

            if (!is_dir(dirname($file . '.' . $ext))) {
                mkdir(dirname($file . '.' . $ext), 0777, true);
            }
            if ($data) {
                file_put_contents($file . '.' . $ext, $data);
            }
        }
        return $this->createImageBaseOnFile($file . '.' . $ext);
    }

    /**
     * Craeteimage base on request
     *
     * @param $file
     *
     * @return resource
     * @throws Exception
     */
    private function createImageBaseOnFile($file) {
        $info = pathinfo($file);
        $ext = $info['extension'];

        switch (strtolower($ext)) {
            case 'png' :
                $im = imagecreatefrompng($file);
                break;
            case 'jpeg':
            case 'jpg':
                $im  = imagecreatefromjpeg($file);
                break;
            default:
                $im = null;
        }

        return $im;
    }
}



