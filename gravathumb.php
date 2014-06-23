<?php

class Gravathumb
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

    public function buildList($orientation = 'vertical')
    {
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
            imagecopy($image, $im, $hTarget, $wTarget, 0, 0, $this->size, $this->size);
            imagedestroy($im);
            $hTarget += $hStep;
            $wTarget += $wStep;
        }

        imagejpeg($image, "./$orientation.jpeg");
        imagedestroy($image);
    }

    private function getImage($email)
    {
        $hash = md5(strtolower($email));
        $file = $this->cache . '/' . $this->size . $this->default . $hash . '.jpeg';
        if ($this->cache) {
            if (file_exists($file)) {
                return imagecreatefromjpeg($file);
            }
        }

        $curl = curl_init('http://www.gravatar.com/avatar/' .
            $hash . '.jpg?s=' . $this->size . '&d=' . $this->default);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);

        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }
        file_put_contents($file, $data);
        return imagecreatefromjpeg($file);
    }
}

$tmp = new Gravathumb(
    [
        'fzerorubigd@gmail.com',
        'kahzad@gmail.com',
        'fzerorubigd@gmail.com',
        'reza@gmail.com',
        'fzerorubigd@gmail.com',
        'hasan@gmail.com',
        'fzerorubigd@gmail.com',
        'hosein@gmail.com',
        'fzerorubigd@gmail.com',
        'chalist@gmail.com',
        'fzerorubigd@gmail.com',
        'alireza@gmail.com',
    ]
);

$tmp->buildList();
$tmp->buildList('horizontal');


