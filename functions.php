<?php
function my_get_env($var) {
    if(strpos($_SERVER['HTTP_ORIGIN'], '//localhost:') !== false) {
        return defined($var) ? constant($var) : false;
    }
    return getenv($var);
}
