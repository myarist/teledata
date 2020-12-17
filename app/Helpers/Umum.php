<?php
namespace App\Helpers;

class WebApiBps
{
    private $cookie; // cookies
    private $ch; // curl
    private $webapi;

	// CONSTRUCTOR
	function __construct(){
		$this->cookie = "cookie.txt";
        $this->ch = curl_init();
        $this->webapi = env('WEBAPI_BPS');
        $this->tgapi = env('TELEGRAM_BOT_TOKEN');
	}

	// DESTRUCTOR
	function __destruct() {
        if($this->ch) curl_close($this->ch);
    }

    private function connectcurl($ch, $url){

		// set url
        curl_setopt($ch, CURLOPT_URL, $url);

        // set user agent
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

        // return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


		return $ch;
    }
    public function caripublikasi($keyword,$page)
    {
        //$getdata = "?model=publication&domain=5200&key=".$this->webapi."&keyword=".$keyword;
        if ($page>1)
        {
            $url="https://webapi.bps.go.id/v1/api/list/?model=publication&domain=5200&key=".$this->webapi."&keyword=".$keyword."&page=".$page;
        }
        else {
            $url="https://webapi.bps.go.id/v1/api/list/?model=publication&domain=5200&key=".$this->webapi."&keyword=".$keyword;
        }

		$ch = $this->connectcurl($this->ch, $url);
        $result = curl_exec ($ch);
        $result = json_decode($result, TRUE);
		return $result;
    }
    public function caristatistik($keyword,$page)
    {
        //$getdata = "?model=publication&domain=5200&key=".$this->webapi."&keyword=".$keyword;
        if ($page>1)
        {
            $url="https://webapi.bps.go.id/v1/api/list/?model=statictable&domain=5200&key=".$this->webapi."&keyword=".$keyword."&page=".$page;
        }
        else {
            $url="https://webapi.bps.go.id/v1/api/list/?model=statictable&domain=5200&key=".$this->webapi."&keyword=".$keyword;
        }

		$ch = $this->connectcurl($this->ch, $url);
        $result = curl_exec ($ch);
        $result = json_decode($result, TRUE);
		return $result;
    }
    public function carilain($keyword,$page)
    {
        //$getdata = "?model=publication&domain=5200&key=".$this->webapi."&keyword=".$keyword;
        if ($page>1)
        {
            $url="https://webapi.bps.go.id/v1/api/list/?model=news&domain=5200&key=".$this->webapi."&keyword=".$keyword."&page=".$page;
        }
        else {
            $url="https://webapi.bps.go.id/v1/api/list/?model=news&domain=5200&key=".$this->webapi."&keyword=".$keyword;
        }

		$ch = $this->connectcurl($this->ch, $url);
        $result = curl_exec ($ch);
        $result = json_decode($result, TRUE);
		return $result;
    }
    public function caribrs($keyword,$page)
    {
        //$getdata = "?model=publication&domain=5200&key=".$this->webapi."&keyword=".$keyword;
        if ($page>1)
        {
            $url="https://webapi.bps.go.id/v1/api/list/?model=pressrelease&domain=5200&key=".$this->webapi."&keyword=".$keyword."&page=".$page;
        }
        else {
            $url="https://webapi.bps.go.id/v1/api/list/?model=pressrelease&domain=5200&key=".$this->webapi."&keyword=".$keyword;
        }

		$ch = $this->connectcurl($this->ch, $url);
        $result = curl_exec ($ch);
        $result = json_decode($result, TRUE);
		return $result;
    }
    public function webinfo()
    {
        $url = 'https://api.telegram.org/bot'.$this->tgapi.'/getWebhookInfo';

		$ch = $this->connectcurl($this->ch, $url);
        $result = curl_exec ($ch);
        $result = json_decode($result, TRUE);
		return $result;
    }
    public function resetwebhook()
    {
        $url = 'https://api.telegram.org/bot'.$this->tgapi.'/deleteWebhook?drop_pending_updates=true';

		$ch = $this->connectcurl($this->ch, $url);
        $result = curl_exec ($ch);
        $result = json_decode($result, TRUE);
		return $result;
    }
}
