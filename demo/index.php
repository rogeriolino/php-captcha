<?php

function __autoload($className) {
    require_once dirname(__DIR__) . '/src/' . str_replace("\\", "/", $className) . '.php';
}

use \RogerioLino\Captcha as Captcha;



$renderers = array('gd', 'svg', 'text');
$captchas = array('math', 'text');

function get($key, $arr) {
    $v = isset($_GET[$key]) ? $_GET[$key] : $arr[0];
    return isset($arr[$v]) ? $arr[$v] : $v;
}

$renderer = get('r', $renderers);
$captcha = get('c', $captchas);

$url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html>
<head>
<meta chartset="utf-8" />
<title>PHP Captcha</title>
<!-- <link href='http://fonts.googleapis.com/css?family=Scada' rel='stylesheet' type='text/css' /> -->
 <link href='style.css' rel='stylesheet' type='text/css' /> 
</style>
</head>
<body>
<div id="demo">
    <div id="header">
        <h1>PHP Captcha</h1>
    </div>
    <div id="content">
        <form method="get" action="<?php echo $url ?>">
            <div class="option">
                <h3>Captcha type:</h3>
                <ul>
                    <?php foreach ($captchas as $c): ?>
                    <li>
                        <label for="<?php echo $c ?>"><?php echo $c ?></label>
                        <input id="<?php echo $c ?>" type="radio" name="c" value="<?php echo $c ?>" <?php echo ($captcha == $c ? 'checked="checked"' : '') ?> onclick="this.form.submit()" />
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="option">
                <h3>Render mode:</h3>
                <ul>
                    <?php foreach ($renderers as $r): ?>
                    <li>
                        <label for="<?php echo $r ?>"><?php echo $r ?></label>
                        <input id="<?php echo $r ?>" type="radio" name="r" value="<?php echo $r; ?>" <?php echo ($renderer == $r ? 'checked="checked"' : ''); ?> onclick="this.form.submit()" />
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </form>
        <form method="post" action="<?php echo $url ?>">
            <div class="form">
                <h3>Form validation:</h3>
                <?php 

                    $message = '';
                    if (!empty($_POST)) {
                        $cform = Captcha\CaptchaForm::restore();
                        if ($cform->match()) {
                            $message = '<span class="message success">Success</span>';
                        } else {
                            $message = '<span class="message fail">Fail</span>';
                        }    
                    }

                    $cform = new Captcha\CaptchaForm();
                    switch ($captcha) {
                    case 'math':
                        $cform->setCaptcha(new Captcha\MathCaptcha());
                        break;
                    case 'text':
                        $cform->setCaptcha(new Captcha\TextCaptcha());
                        break;
                    }
                    // renderer choosed
                    switch ($renderer) {
                    case 'gd':
                        $cform->setRenderer(new Captcha\GdCaptchaRenderer());
                        break;
                    case 'svg':
                        $cform->setRenderer(new Captcha\SvgCaptchaRenderer());
                        break;
                    case 'text':
                        $cform->setRenderer(new Captcha\PlainTextCaptchaRenderer());
                        break;
                    }
                    
                    $cform->getCaptcha()->setCaseSensitive(false);
                    
                    echo $cform->create();
                ?>
                <div>
                    <input type="submit" /><?php echo $message ?>
                </div>
            </div>
        </form>
    </div>
    <div id="footer">
        <p><a href="https://github.com/rogeriolino/php-captcha">php-captcha</a></p>
    </div>
</div>
</body>
</html>
