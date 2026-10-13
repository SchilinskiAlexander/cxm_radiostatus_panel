<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2008 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Title: CxM Radiostatus Panel for PHP-Fusion
| Filename: cxm_radiostatus_captcha.php
| Author: Alexaxander Schilinski
| Homepage: https://schilinskialexander.com
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined('IN_FUSION')) { die('Access Denied'); }

// Ermittelt, welches Captcha-System die PHP-Fusion-Installation bereitstellt:
// 'fusion' = includes/captchas/<Einstellung>/ (PHP-Fusion 7.02+, z.B. securimage2, grecaptcha)
// 'legacy' = includes/securimage/ (ältere PHP-Fusion 7 Versionen)
function cxm_rs_captcha_mode() {
    global $settings;

    $captcha = !empty($settings['captcha']) ? preg_replace('/[^a-z0-9_\-]/i', '', $settings['captcha']) : '';
    if ($captcha !== '' && file_exists(INCLUDES.'captchas/'.$captcha.'/captcha_check.php')) {
        return 'fusion';
    }
    if (file_exists(INCLUDES.'securimage/securimage.php')) {
        return 'legacy';
    }
    return '';
}

// Gibt das Captcha samt Eingabefeld (name="captcha_code") als HTML zurück.
function cxm_rs_captcha_html($input_id) {
    global $settings, $locale, $userdata, $aidlink;

    $mode = cxm_rs_captcha_mode();
    $input = '<input type="text" id="'.$input_id.'" name="captcha_code" class="textbox cxm-rs-form-wide" autocomplete="off" />';

    if ($mode === 'fusion') {
        $_CAPTCHA_HIDE_INPUT = false;
        ob_start();
        include INCLUDES.'captchas/'.preg_replace('/[^a-z0-9_\-]/i', '', $settings['captcha']).'/captcha_display.php';
        $html = ob_get_clean();
        if (empty($_CAPTCHA_HIDE_INPUT)) {
            $html .= '<br />'.$input;
        }
        return $html;
    }

    if ($mode === 'legacy') {
        $path = INCLUDES.'securimage/';
        return '<img src="'.$path.'securimage_show.php" alt="" class="cxm-rs-captcha-img" /><br />'
            .'<a href="#" onclick=\'var i=this.parentNode.getElementsByTagName("img")[0];i.src="'.$path.'securimage_show.php?sid="+Math.random();return false;\'>'
            .'<img src="'.$path.'images/refresh.gif" alt="" /></a><br />'
            .$input;
    }

    return '';
}

// Prüft die Captcha-Eingabe des aktuellen POST-Requests.
function cxm_rs_captcha_valid() {
    global $settings, $locale, $userdata;

    $mode = cxm_rs_captcha_mode();

    if ($mode === 'fusion') {
        $_CAPTCHA_IS_VALID = false;
        include INCLUDES.'captchas/'.preg_replace('/[^a-z0-9_\-]/i', '', $settings['captcha']).'/captcha_check.php';
        return $_CAPTCHA_IS_VALID == true;
    }

    if ($mode === 'legacy') {
        if (!isset($_POST['captcha_code']) || $_POST['captcha_code'] === '') {
            return false;
        }
        require_once INCLUDES.'securimage/securimage.php';
        $securimage = new Securimage();
        return $securimage->check($_POST['captcha_code']) == true;
    }

    return false;
}

// JavaScript, das nach dem Absenden ein neues Captcha lädt (Bild bzw. reCAPTCHA).
function cxm_rs_captcha_reset_js($form_selector) {
    return 'if(window.grecaptcha&&grecaptcha.reset){try{grecaptcha.reset();}catch(e){}}'
        .'$('.$form_selector.').find("img").each(function(){'
        .'var s=$(this).attr("src")||"";'
        .'if(s.indexOf("securimage_show")>-1){$(this).attr("src",s.split("?")[0]+"?sid="+Math.random());}'
        .'});'
        .'$('.$form_selector.').find("input[name=captcha_code]").val("");';
}
