<?php
namespace Http;

use Dict;

class Client
{
    const DEBUG_CURLINFO = 0x8;
    const DEBUG_RESPONSE = 0x4;
    const DEBUG_REQHEAD  = 0x2;
    const DEBUG_REQDATA  = 0x1;

# single
    private $ch;
    private $debug;
    private $errno;
    private $error;

# multi
    private $mh;
    private $chs;
    private $reqs;
    private $maxproc;

    public function errno()
    {
        return $this->errno;
    }

    public function error()
    {
        return $this->error;
    }

    public static $options = [
        CURLOPT_HEADER         => true,
        CURLOPT_NOBODY         => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 3,
    ];

    public function __construct(int $maxproc = 1)
    {
        if ($maxproc > 1) {
            $this->maxproc = min($maxproc, 100);
            $this->mh   = curl_multi_init();
            $this->chs  = new Dict;
            $this->reqs = new Dict;
        } else {
            $this->maxproc = 1;
            $this->ch  = curl_init();
        }
    }

    public function __destruct()
    {
        if ($this->maxproc > 1) {
            curl_multi_close($this->mh);
            foreach ($this->chs as $ch) {
                curl_close($ch);
            }
        } else {
            curl_close($this->ch);
        }
    }

    public function setDebug($bits)
    {
        $this->debug = $bits;
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, (bool)($bits & self::DEBUG_CURLINFO));
    }

    private function debug($req, $res)
    {
        if ($this->debug == 0) return;
        if ($res === false) {
            echo sprintf('Err Info: [Errno: %d, Error%s]', $req->errno, $req->error);
            return;
        }

# CURLINFO
        if ($this->debug & self::DEBUG_CURLINFO) {
            $curlinfo = curl_getinfo($this->ch);
            echo str_pad('Curl Info: ', 80, '-')."\r\n";
            echo $curlinfo['request_header'];
        }
# RESPONSE
        if ($this->debug & self::DEBUG_RESPONSE) {
            echo str_pad('Res Info: ', 80, '-')."\r\n";
            echo $res."\r\n";
        }
# REQHEAD
        if ($this->debug & self::DEBUG_REQHEAD) {
            echo str_pad('Req Info: ', 80, '-')."\r\n";
            echo $req."\r\n";
        }
# REQDATA
        if ($this->debug & self::DEBUG_REQDATA) {
            if($req->hasContent()) echo $req->content."\r\n";
        }
# End
echo str_pad('=', 100, '=')."\r\n";
    }

    public static function prepare($ch, Request $req)
    {
        curl_setopt_array($ch, self::$options);
        $url = $req->url;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $req->getMethod());
        
        if ($req->getProtocol() == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        if ($req->hasContent()) {
            $req->header->set('Content-Type', $req->getContentType());
            curl_setopt($ch, CURLOPT_POSTFIELDS, $req->content);
        }
        if ($req->hasHeader()) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $req->header->toCurlOptions());
        }
        if ($req->hasCookie()) {
            curl_setopt($ch, CURLOPT_COOKIE, $req->cookies);
        }
    }

    public function send(Request $req)
    {
        self::prepare($this->ch, $req);
        $res = curl_exec($this->ch);

        if (false === $res) {
            $req->errno = curl_errno($this->ch);
            $req->error = curl_error($this->ch);
        } 

        $this->debug($req, $res);
        if ($res) {
            $res = (new Response)->parse($res);
            $req->trigger('receive', $res, $this);
        }
        return $res;
    }

    public function multi(iterable $reqs)
    {
        for ($i = 0; $i < $this->maxproc && $reqs->key() !== null; $i++) {
            $req = $reqs->current();
            
            $ch = curl_init();
            self::prepare($ch, $req);
            $this->chs->set((int)$ch, $ch);
            $this->reqs->set((int)$ch, $req);
            curl_multi_add_handle($this->mh, $ch);

            $reqs->next();
        }

        $active = null; $status = 0;
        do {
            if (curl_multi_select($this->mh) == -1) continue; # why call curl_multi_select?
            while (CURLM_CALL_MULTI_PERFORM == $status = curl_multi_exec($this->mh, $active));
            while ($info = curl_multi_info_read($this->mh)) {
                $ch = $info['handle'];
                $chno = (int)$ch;
                $res = (new Response)->parse(curl_multi_getcontent($ch));
                $req = $this->reqs->get($chno);
                curl_multi_remove_handle($this->mh, $ch);
                $trigger = $req->trigger('receive', $res, $this, $info);
                $this->afterReceive();

                if (false === $trigger) { # 为Request receive事件回调提供主动退出的方式
                    curl_close($ch);
                    $this->chs->offsetUnset($chno);
                    $this->reqs->offsetUnset($chno);
                    continue;
                }
                
                if ($reqs->key() != null) {
                    $req = $reqs->current();

                    $this->reqs->set($chno, $req);
                    curl_multi_add_handle($this->mh, $ch);
                    self::prepare($ch, $req);

                    $reqs->next();
                }
            }
        } while ($active && ($status == CURLM_OK || $status == CURLM_CALL_MULTI_PERFORM));
    }

    private function afterReceive()
    {
        printf("%s\n", json_encode($this->chs));
        printf("peak: %s usage: %s\n", memory_get_peak_usage(), memory_get_usage());
    }
}
