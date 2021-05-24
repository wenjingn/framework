<?php
class Media extends Obj
{
    /**
     * @fields
     */
    protected $type   = null;
    protected $params = null;
    
    protected function initial() {}
    public function __construct(array $o = null)
    {
        parent::__construct($o);
        $this->params = new Obj;
        $this->initial();
    }

    public function getType()
    {
        return $this->type;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getContentType()
    {
        if (empty($this->params)) {
            return $this->type;
        }

        $type = $this->type.'; ';
        foreach ($this->params as $k => $v) {
            $type.= $k.'='.$v.'; ';
        }
        return substr($type, 0, -2);
    }

    /**
     * @static
     */
    private static $unknown = 'application/octet-stream';
    private static $fmap = [
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'jpeg'=> 'image/jpeg',
        'gif' => 'image/gif',
        'wav' => 'audio/wav',
        'mp3' => 'audio/mpeg3',
        'mov' => 'video/quicktime',
        'pdf' => 'application/pdf',
        'html'=> 'text/html',
        'txt' => 'text/plain',
        'xml' => 'application/xml',
        'json'=> 'application/json',
    ];

    private static $tmap = [
# short name map
        'form'  => 'Media\\FormData',
        'multi' => 'Media\\Multi',
        'json'  => 'Media\\Json',

# long name map
        'application/x-www-form-urlencoded' => 'Media\\FormData',
        'multipart/form-data'               => 'Media\\Multi',
        'application/json'                  => 'Media\\Json',
    ];

    public static function gen($type)
    {
        if ($type && isset(self::$tmap[$type])) {
            return new self::$tmap[$type];
        }
        return null;
    }

    public static function mime($file)
    {
        if (false === $pos = strrpos($file, '.'))
            return self::$unknown;
        $suffix = substr($file, $pos+1);
        if (isset(self::$fmap[$suffix])) {
            return self::$fmap[$suffix];
        }
        return self::$unknown;
    }
}
