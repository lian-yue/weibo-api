<?php
namespace LianYue\WeiboApi;


// http://open.weibo.com/wiki/%E5%BE%AE%E5%8D%9AAPI
class OAuth2
{

    protected $baseUri = 'https://api.weibo.com';

    protected $clientId;

    protected $clientSecret;

    protected $accessToken;

    protected $redirectUri;

    protected $requestOptions = array();

    public function __construct($clientId, $clientSecret, array $accessToken = null, array $requestOptions = array())
    {
        $this->setClientId($clientId);
        $this->setClientSecret($clientSecret);
        $this->setAccessToken($accessToken);
        $this->setRequestOptions($requestOptions);
    }


    public function getClientId()
    {
        return $this->clientId;
    }


    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function getClientSecret()
    {
        return $this->clientSecret;
    }


    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }


    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = (string) $redirectUri;
        return $this;
    }


    public function setRequestOptions(array $requestOptions = array())
    {
        $this->requestOptions =  $requestOptions;
        return $this;
    }




    public function getAccessToken(array $params = null)
    {
        if ($this->accessToken === null) {
            //  自动获取 access_token
            if ($params === null) {
                $params = $_GET;
            }
            if (empty($params['code'])) {
                throw new InvalidArgumentException('Code parameter is empty');
            }
            $request = $this->request('POST', 'oauth2/access_token', array(), array(), array(
                'grant_type' => 'authorization_code',
                'client_id' => $this->getClientId(),
                'client_secret' => $this->getClientSecret(),
                'code' => $params['code'],
                'redirect_uri' => empty($params['redirect_uri']) ? $this->getRedirectUri() : $params['redirect_uri'],
            ));
            $this->accessToken = $request->response()->getJson(true);
        }
        return $this->accessToken;
    }





    public function setAccessToken(array $accessToken = null)
    {
        $this->accessToken = $accessToken;
        return $this;
    }


    public function getAuthorizeUri(array $params = array())
    {
        if (!empty($params['redirect_uri'])) {
            $this->setRedirectUri($params['redirect_uri']);
        } else {
            $params['redirect_uri'] = $this->getRedirectUri();
            if (!$params['redirect_uri']) {
                throw new InvalidArgumentException('Not configuration redirect_uri');
            }
        }

        $params = array(
			'client_id' => $this->getClientId(),
		) + $params + array(
            'response_type'	=> 'code',
        );

        if (!empty($params['scope']) && is_array($params['scope'])) {
            $params['scope'] = implode(',', $params['scope']);
        }
        return $this->getUri('oauth2/authorize', $params);
    }




    public function getAccessTokenByRefreshToken(array $params = array())
    {
        $params = array(
            'grant_type' => 'refresh_token',
			'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
		) + $params + array(
            'redirect_uri' => $this->getRedirectUri(),
        );
        if (empty($params['refresh_token'])) {
            $accessToken = $this->getAccessToken();
            if (empty($accessToken['refresh_token'])) {
                throw new InvalidArgumentException('Not configuration refresh_token');
            }
            $params['refresh_token'] = $accessToken['refresh_token'];
        }
        $request = $this->request('POST', 'oauth2/access_token', array(), array(), $params);
        $accessToken = $request->response()->getJson(true);
        $accessToken['refresh_token'] = $params['refresh_token'];
        if (!$this->accessToken) {
            $this->accessToken = array();
        }
        $this->accessToken = $accessToken + $this->accessToken;
        return $this->accessToken;
    }




    public function getAccessTokenByPassowrd(array $params = array())
    {
        $params = array(
            'grant_type' => 'password',
			'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
		) + $params;
        if (empty($params['username'])) {
            throw new InvalidArgumentException('The user name is empty');
        }

        if (empty($params['password'])) {
            throw new InvalidArgumentException('The password is empty');
        }

        $request = $this->request('POST', 'oauth2/access_token', array(), array(), $params);
        $accessToken = $request->response()->getJson(true);
        $this->accessToken = $accessToken;
        return $this->accessToken;
    }




    public function getTokenInfo(array $params = array())
    {
        return $this->api('POST', 'oauth2/get_token_info', array(), array(), $params)->response();
    }

    public function revokeOAuth2(array $params = array())
    {
        return $this->api('GET', 'oauth2/revokeoauth2', $params)->response();
    }

    public function getUsersShow(array $params = array())
    {
        if (empty($params['uid']) && $this->accessToken && !empty($this->accessToken['uid'])) {
            $params['uid'] = $this->accessToken['uid'];
        }
        return $this->api('GET', 'users/show.json', $params)->response();
    }


    public function getUri($path, array $params = array())
    {
        if (substr($path, 0, 7) === 'http://' || substr($path, 0, 8) === 'https://') {
            $uri = $path;
        } else {
            $uri = $this->baseUri .'/' . ltrim($path, '/');
        }
        if ($params) {
            $uri .= '?' . http_build_query($params, null, '&');
        }
        return $uri;
    }


    public function request($method, $path, array $params = array(), array $headers = array(), $body = null, array $options = array())
    {
        $request = new Request($method, $this->getUri($path, $params), $headers, $body, $options + $this->requestOptions + array(CURLOPT_USERAGENT => 'OAuth/2.0 (LianYue; http://lianyue.org, https://github.com/lian-yue/weibo-api)'));
        return  $request->setResponseCallback(function(Response $response) {

            $json = $response->getJson();

            if (!empty($json->error_description)) {
                $error = $json->error_description;
            } elseif (!empty($json->error)) {
                $error = $json->error;
            } elseif (!empty($json->error_code)) {
                $error = sprintf('Error code %d', $json->error_code);
            }
            if (!empty($error)) {
                throw new ResponseException($error, empty($error->error_code) ? 0 : $error->error_code);
            }
            return $response;
        });
    }


    public function api($method, $path, array $params = array(), array $headers = array(), $body = null) {
        if (empty($params['access_token'])) {
            $accessToken = $this->getAccessToken();
            if (empty($accessToken['access_token'])) {
                throw new InvalidArgumentException('Not configuration access_token');
            }
            $params['access_token'] = $accessToken['access_token'];
        }
        return $this->request($method, $path, $params, $headers, $body);
    }
}
