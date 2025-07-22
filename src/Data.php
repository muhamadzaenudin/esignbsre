<?php

namespace Muhamadzaenudin\Esignbsre;

use GuzzleHttp\Psr7;

class Data
{
    private $type = 'invisible';
    private $mandatory = ['file', 'nik', 'passphrase'];
    private $imagettd = ['imageTTD', 'page', 'xAxis', 'yAxis', 'width', 'height', 'reason', 'location'];
    private $qr = ['imageTTD', 'page', 'linkQR', 'xAxis', 'yAxis', 'width', 'height', 'reason', 'location'];
    private $tag_koordinat = ['imageTTD', 'width', 'height', 'tag_koordinat', 'reason', 'location'];
    private $data = [];
    private $multipart = [];

    public function __construct($type, $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    public function getData()
    {
        foreach ($this->mandatory as $key) {
            array_push($this->multipart, [
                'name' => $key,
                'contents' => $this->getContent($this->data[$key]),
            ]);
        }

        switch ($this->type) {
            case 'imagettd':
                foreach ($this->imagettd as $key) {
                    if (isset($this->data[$key])) {
                        $this->multipart[] = [
                            'name'    => $key,
                            'contents' => $this->getContent($this->data[$key])
                        ];
                    }
                }
                array_push($this->multipart, [
                    'name' => 'image',
                    'contents' => 'true'
                ]);
                break;
            case 'qr':
                foreach ($this->qr as $key) {
                    if (isset($this->data[$key])) {
                        $this->multipart[] = [
                            'name'    => $key,
                            'contents' => $this->getContent($this->data[$key])
                        ];
                    }
                }
                array_push($this->multipart, [
                    'name' => 'image',
                    'contents' => 'false'
                ]);
                break;
            case 'tagkoordinat':
                foreach ($this->tag_koordinat as $key) {
                    if (isset($this->data[$key])) {
                        $this->multipart[] = [
                            'name'    => $key,
                            'contents' => $this->getContent($this->data[$key])
                        ];
                    }
                }
                array_push($this->multipart, [
                    'name' => 'image',
                    'contents' => 'true'
                ]);
                break;
        }

        array_push($this->multipart, [
            'name' => 'tampilan',
            'contents' => $this->getType()
        ]);

        return $this->multipart;
    }

    private function getContent($data)
    {
        if (is_string($data) && is_file($data)) {
            return Psr7\Utils::tryFopen($data, 'r');
        }
        return $data;
    }

    private function getType()
    {
        $type = $this->type;

        $visible = ['imagettd', 'qr', 'tagkoordinat'];
        if (in_array($type, $visible))
            $type = 'visible';

        return $type;
    }
}
