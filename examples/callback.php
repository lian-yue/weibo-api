<?php
namespace LianYue\WeiboApi;
require __DIR__ . '/config.php';


$oauth2 = new OAuth2(CLIENT_ID, CLIENT_SELECT);
$oauth2->setRedirectUri(URI_BASE . 'callback.php');
$accessToken = $oauth2->getAccessToken();
setcookie('weibo_oauth2_access_token', json_encode($accessToken), time() + 86400, '/');

?>

<pre>
访问令牌
<?=print_r($accessToken)?>

个人信息
<?=print_r($oauth2->getTokenInfo()->getJson())?>
<?=print_r($oauth2->getUsersShow()->getJson())?>
</pre>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8" />
</head>
<body>
<a href="./oauth2_test.php">测试 Api</a>
</body>
</html>
