<?php
namespace Media\Multi;

use Media;

class Part
{
    public $name = null;
    public $data = null;
    public $filename = null;
    public $mime = null;

    public function __construct(string $name = '', string $data = '', string $filename = null)
    {
        $this->name = $name;
        $this->data = $data;
        if ($filename) {
            $this->filename = $filename;
            $this->mime = Media::mime($filename);
        }
    }

    public function __toString()
    {
$s = 'Content-Disposition: form-data; name="'.$this->name.'"';
if ($this->filename) $s.= sprintf("; filename=\"%s\"\r\nContent-Type: %s", $this->filename, $this->mime);
$s.= "\r\n\r\n";
$s.= $this->data;
        return $s;
    }

    public function parse($part)
    {
        list($header, $this->data) = explode("\r\n\r\n", $part);
        $header = explode("\r\n", $header);

        foreach ($header as $h) {
            list($k, $v) = explode(": ", $h);
            if ($k == 'Content-Type') {
                $this->mime = $v;
            } elseif ($k == 'Content-Disposition') {
                $params = explode('; ', $v);
                array_shift($params);
                foreach ($params as $param) {
                    list($name, $value) = explode('=', $param);
                    $this->$name = substr($value, 1, -1);
                }
            }
        }
        return $this;
    }
}
