<?php

class Gravatile
{

    const GRAVATAR_BASE_PATH = 'http://www.gravatar.com/avatar/';

    private $emails = array();

    private $size;

    private $cache;

    private $default;

    /**
     * Create tiles
     *
     * @param int $count how many item is available?
     * @param string $orientation orientation, vertical or horizontal
     *
     * @return resource
     */
    public function buildTile(&$count, $orientation = 'vertical')
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

        return $image;
    }

    /**
     * @param array $emails
     * @param int $size
     * @param string $default
     * @param string $cache
     */
    public function __construct(array $emails, $size = 64, $default = 'monsterid', $cache = './cache')
    {

        foreach ($emails as $email) {
            if (strpos($email, '@') > 0) {
                echo strlen(strtolower(trim($email))) . ' ' . strtolower(trim($email)) . PHP_EOL;
                $this->emails[] = md5(strtolower(trim($email)));
            } else {
                // This is the real md5
                $this->emails[] = $email;
            }
        }
        $this->size = (int)$size;
        $this->cache = $cache;
        $this->default = $default;
    }

    private function getImage($hash)
    {
        $file = $this->cache . '/' . $this->size . $this->default . $hash;
        if ($this->cache) {
            $files = glob($file . '.*');
            if ($realFile = array_shift($files)) {
                return $this->createImageBaseOnFile($realFile);
            }
        }

        $data = null;
        while (!$data) { // I don't know why but some time data is null :/
            $url = 'http://www.gravatar.com/avatar/' .
                $hash . '.png?s=' . $this->size . '&d=' . $this->default;

            $curl = curl_init($url);

            //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            //curl_setopt($curl, CURLOPT_HEADER, 1);

            $data = curl_exec($curl);

            if (!is_dir(dirname($file))) {
                mkdir(dirname($file), 0777, true);
            }
            if ($data) {
                $file = $this->writeFile($data, $file);
            }
        }
        return $this->createImageBaseOnFile($file);
    }

    /**
     * Craeteimage base on request
     *
     * @param $file
     *
     * @return resource
     * @throws Exception
     */
    private function createImageBaseOnFile($file)
    {
        $info = pathinfo($file);
        $ext = $info['extension'];

        switch (strtolower($ext)) {
        case 'png' :
            $im = imagecreatefrompng($file);
            break;
        case 'jpeg':
        case 'jpg':
            $im = imagecreatefromjpeg($file);
            break;
        default:
            $im = null;
        }

        return $im;
    }

    private function writeFile($data, $file)
    {
        // FUCK GRAVATAR!!!!! image headers are not correct!!!
        //list($header, $data) = explode("\r\n\r\n", $resp, 2);
        //if (preg_match('/filename="[^.]+\.([^"]+)/', $header, $matches)) {
        //    $ext = $matches[1];
        //}
        file_put_contents($file, $data);
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $fileInfoArray = explode('/', finfo_file($finfo, $file));
            $ext = end($fileInfoArray);
            finfo_close($finfo);
        } else {
            if (trim(substr($data, 1, 3)) == 'PNG') {
                $ext = 'png';
            } else {
                $ext = 'jpg';
            }
        }

        rename($file, $file . '.' . $ext);

        return $file . '.' . $ext;
    }
}



