<?php
global $post;
$results = get_post_meta( $post->ID, '_response', false );
var_dump( $results );