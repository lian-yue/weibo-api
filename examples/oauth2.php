<?php
namespace LianYue\WeiboApi;
require __DIR__ . '/config.php';

// 链接登录页面


$oauth2 = new OAuth2(CLIENT_ID, CLIENT_SELECT);

$oauth2->setRedirectUri(URI_BASE . 'callback.php');

$uri = $oauth2->getAuthorizeUri(['display' => 'pc']);



?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8" />
</head>
<body>

<a href="<?=$uri?>" >OAuth2登录</a>
</body>
</html>
