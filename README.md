

# Composer 安装

    composer require lianyue/weibo-api



# 微博 OAuth2

### 申请应用
http://open.weibo.com/webmaster/add

Client Id
    就是你的  **Api Key**

Client Secret
    就是你的  **App Secret**




### OAuth2 api 列表
http://open.weibo.com/wiki/%E5%BE%AE%E5%8D%9AAPI





### Oauth2使用方法

    namespace LianYue\WeiboApi;

    $oauth2 = new OAuth2(CLIENT_ID, CLIENT_KEY);
    $oauth2->setRedirectUri(CALLBACK_URI);
    try {
        // 设置 state
        if (!empty($_COOKIE['weibo_api_state'])) {
            $oauth2->setState($_COOKIE['weibo_api_state']);
        }

        // 取得令牌
        $accessToken = $oauth2->getAccessToken();

        // 访问令牌
        print_r($accessToken);

        // 用户信息
        print_r($oauth2->getUsersShow()->getJson(true));

        // 其他api调用
        print_r($this->api('GET', '/statuses/mentions.json')->response()->getJson(false));
    } catch (BaiduApiException $e) {

        // 获取重定向链接
        $uri = $oauth2->getAuthorizeUri(['display' => 'pc']);

        // 储存 state
        setcookie('weibo_api_state', $oauth2->getState(), time() + 86400, '/');

        // 重定向
        header('Location: ' . $uri);
    }
