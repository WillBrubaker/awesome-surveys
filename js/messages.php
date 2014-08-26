<?php
    error_log( 'in the dynamic php js file' );
    header("Content-type: application/javascript; charset: UTF-8");
    if ( defined( 'WPLANG' ) ) {
        echo 'alert("Hello ' . WPLANG . '")';
    }