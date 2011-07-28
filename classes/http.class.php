<?php
//v0.2
if (!class_exists('zHttpRequest')) {
	class zHttpRequest
	{
		var $_fp;        // HTTP socket
		var $_url;        // full URL
		var $_host;        // HTTP host
		var $_protocol;    // protocol (HTTP/HTTPS)
		var $_uri;        // request URI
		var $_port;        // port
		var $error;
		var $errno=false;
		var $post=array();	//post variables, defaults to $_POST
		var $redirect=false;
		var $errors=array();
		var $countRedirects=0;
		var $sid;

		// constructor
		function __construct($url="",$sid='')
		{
			if (!$url) return;
			$this->sid=$sid;
			$this->_url = $url;
			$this->_scan_url();
			$this->post=$_POST;
		}

		
		private function processHeaders($headers) {
			// split headers, one per array element
			if ( is_string($headers) ) {
				// tolerate line terminator: CRLF = LF (RFC 2616 19.3)
				$headers = str_replace("\r\n", "\n", $headers);
				// unfold folded header fields. LWS = [CRLF] 1*( SP | HT ) <US-ASCII SP, space (32)>, <US-ASCII HT, horizontal-tab (9)> (RFC 2616 2.2)
				$headers = preg_replace('/\n[ \t]/', ' ', $headers);
				// create the headers array
				$headers = explode("\n", $headers);
			}

			$response = array('code' => 0, 'message' => '');

			// If a redirection has taken place, The headers for each page request may have been passed.
			// In this case, determine the final HTTP header and parse from there.
			for ( $i = count($headers)-1; $i >= 0; $i-- ) {
				if ( !empty($headers[$i]) && false === strpos($headers[$i], ':') ) {
					$headers = array_splice($headers, $i);
					break;
				}
			}

			$cookies = '';
			$newheaders = array();
			foreach ( $headers as $tempheader ) {
				if ( empty($tempheader) )
				continue;

				if ( false === strpos($tempheader, ':') ) {
					list( , $response['code'], $response['message']) = explode(' ', $tempheader, 3);
					continue;
				}

				list($key, $value) = explode(':', $tempheader, 2);

				if ( !empty( $value ) ) {
					$key = strtolower( $key );
					if ( isset( $newheaders[$key] ) ) {
						if ( !is_array($newheaders[$key]) )
						$newheaders[$key] = array($newheaders[$key]);
						$newheaders[$key][] = trim( $value );
					} else {
						$newheaders[$key] = trim( $value );
					}
					if ( 'set-cookie' == $key ) {
						$cookies = $value;
					}
				}
			}

			return array('response' => $response, 'headers' => $newheaders, 'cookies' => $cookies);
		}
		
		// scan url
		private function _scan_url()
		{
			$req = $this->_url;

			$pos = strpos($req, '://');
			$this->_protocol = strtolower(substr($req, 0, $pos));

			$req = substr($req, $pos+3);
			$pos = strpos($req, '/');
			if($pos === false)
			$pos = strlen($req);
			$host = substr($req, 0, $pos);

			if(strpos($host, ':') !== false)
			{
				list($this->_host, $this->_port) = explode(':', $host);
			}
			else
			{
				$this->_host = $host;
				$this->_port = ($this->_protocol == 'https') ? 443 : 80;
			}

			$this->_uri = substr($req, $pos);
			if($this->_uri == '')
			$this->_uri = '/';
		}

		//check if server is live
		function live() {
			if (ip2long($this->_host)) return true; //in case using an IP instead of a host name
			$url=$this->_host;
			if (gethostbyname($url) == $url) return false;
			else return true;
		}

		//check if cURL installed
		function curlInstalled() {
			if (!function_exists('curl_init')) return false;
			else return true;
		}

		//check destination is reachable
		function checkConnection() {
			$output=$this->DownloadToString_curl();
			if ($output=='zingiri') return true;
			else return false;
		}

		//error logging
		function error($msg) {
			cc_whmcs_log('Error',$msg);
		}

		//notification logging
		function notify($msg) {
			cc_whmcs_log('Notification',$msg);
		}

		function getSid() {
			return md5(__FILE__);
		}
		
		// download URL to string
		function DownloadToString($withHeaders=true,$withCookies=false)
		{
			$newfiles=array();

			@session_start();
			$ch = curl_init();    // initialize curl handle
			$url=$this->_protocol.'://'.$this->_host.$this->_uri;
			//echo '<br />call:'.$url;
			curl_setopt($ch, CURLOPT_URL,$url); // set url to post to
			curl_setopt($ch, CURLOPT_FAILONERROR, 1);
			if ($withHeaders) curl_setopt($ch, CURLOPT_HEADER, 1);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60); // times out after 10s
			if ($this->_protocol == "https") {
				if (file_exists($cainfo)) {
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
					//curl_setopt($ch, CURLOPT_CAINFO, $cainfo);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
				} else {
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
					curl_setopt($ch, CURLOPT_CAINFO, NULL);
					curl_setopt($ch, CURLOPT_CAPATH, NULL);
				}
			}
			if ($withCookies && isset($_COOKIE)) {
				echo $cookies;die('with cookies');
				$cookies="";
				foreach ($_COOKIE as $i => $v) {
					if ($i=='WHMCSUID' || $i=="WHMCSPW") {
						if ($cookies) $cookies.=';';
						$cookies.=$i.'='.$v;
					}
				}
				curl_setopt($ch, CURLOPT_COOKIE, $cookies);
			}
			if (isset($_SESSION[$this->sid])) {
				curl_setopt($ch, CURLOPT_COOKIE, $_SESSION[$this->sid]);
			}
			if (count($_FILES) > 0) {
				foreach ($_FILES as $name => $file) {
					if (is_array($file['tmp_name']) && count($file['tmp_name']) > 0) {
						$c=count($file['tmp_name']);
						for ($i=0;$i<$c;$i++) {
							if ($file['tmp_name'][$i]) {
								$newfile=BLOGUPLOADDIR.$file['name'][$i];
								$newfiles[]=$newfile;
								copy($file['tmp_name'][$i],$newfile);
								if ($file['tmp_name'][$i]) $this->post[$name][$i]='@'.$newfile;
							}
						}
					} elseif ($file['tmp_name']) {
						$newfile=BLOGUPLOADDIR.$file['name'];
						$newfiles[]=$newfile;
						copy($file['tmp_name'],$newfile);
						if ($file['tmp_name']) $this->post[$name]='@'.$newfile;
					}
				}
			}
			if (count($this->post) > 0) {
				curl_setopt($ch, CURLOPT_POST, 1); // set POST method
				$post="";
				$apost=array();
				foreach ($this->post as $k => $v) {
					if (is_array($v)) {
						foreach ($v as $k2 => $v2) {
							if (is_array($v2)) {
								foreach ($v2 as $k3 => $v3) {
									if ($post) $post.='&';
									$post.=$k.'['.$k2.']'.'['.$k3.']'.'='.urlencode(stripslashes($v3));
									$apost[$k.'['.$k2.']'.'['.$k3.']']=stripslashes($v3);
								}
							} else {
								if ($post) $post.='&';
								$post.=$k.'['.$k2.']'.'='.urlencode(stripslashes($v2));
								$key='['.$k.']['.$k2.']';
								$apost[$k.'['.$k2.']']=stripslashes($v2);
							}
						}

					} else {
						if ($post) $post.='&';
						$post.=$k.'='.urlencode(stripslashes($v));
						$apost[$k]=stripslashes($v);
					}
				}
			}

			if (count($post) > 0) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $apost); // add POST fields
			}

			$data = curl_exec($ch); // run the whole process
			if (curl_errno($ch)) {
				$this->errno=curl_errno($ch);
				$this->error=curl_error($ch);
				$this->error('HTTP Error:'.$this->errno.'/'.$this->error.' at '.$this->_url);
				return false;
			}
			$info=curl_getinfo($ch);
			if ( !empty($data) ) {
				$headerLength = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
				$head = trim( substr($data, 0, $headerLength) );
				if ( strlen($data) > $headerLength ) $body = substr( $data, $headerLength );
				else $body = '';
				if ( false !== strpos($head, "\r\n\r\n") ) {
					$headerParts = explode("\r\n\r\n", $head);
					$head = $headerParts[ count($headerParts) -1 ];
				}
				$head = $this->processHeaders($head);
				$headers=$head['headers'];
				$cookies=$head['cookies'];
			} else {
				if ( $curl_error = curl_error($ch) )
				return new WP_Error('http_request_failed', $curl_error);
				if ( in_array( curl_getinfo( $ch, CURLINFO_HTTP_CODE ), array(301, 302) ) )
				return new WP_Error('http_request_failed', __('Too many redirects.'));

				$headers=array();
				$cookies='';
				$body = '';
			}

			if ($cookies) $_SESSION[$this->sid]=$cookies;
			curl_close($ch);

			//remove temporary upload files
			if (count($newfiles) > 0) {
				foreach ($newfiles as $file) {
					unlink($file);
				}
			}

			$this->$headers=$headers;
			$this->data=$data;
			$this->cookies=$cookies;
			$this->body=$body;
			if ($headers['location']) {
				$this->_uri='/'.$headers['location'];
				$this->post=array();
				$this->countRedirects++;
				if ($countRedirects < 10) {
					return $this->DownloadToString($withHeaders,$withCookies);
				} else {
					return 'ERROR: Too many redirects';
				}
			}
			return $body;
		}
	}
}
?>