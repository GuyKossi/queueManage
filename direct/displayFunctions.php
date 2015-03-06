<?php

function getContentForRedirect( $dev, $currentDesk ) {
    global $gvPath;

    $content = array();
    if ( !$dev ) {
        $rand = rand( 1, 100 );
        $content['status'] = 'redirect';
        $content['location'] = "$gvPath/device?v=$rand";
    } elseif ( $dev->getDeskNumber() != $currentDesk ) {
        $content['status'] = 'redirect';
        if ( $dev->getDeskNumber() ) {
            $content['location'] =
                "$gvPath/device/desk?desk_number={$dev->getDeskNumber()}";
        } else {
            $content['location'] = getUrlForDisplayMain( $dev );
        }
    } elseif (
        $dev->getDeskNumber() == 0
        && $dev->getTdCode() != gfGetVar( 'td_code', '' )
    ) {
        $content['status'] = 'redirect';
        $content['location'] = getUrlForDisplayMain( $dev );
    }
    return $content;
}

function getUrlForDisplayMain( $dev ) {
    global $gvPath;

    if ( $dev->getTdCode() ) {
        $queryString = "?td_code=" . urlencode( $dev->getTdCode() );
    } else {
        $queryString = '';
    }
    return "$gvPath/device/main$queryString";
}
