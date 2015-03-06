<?php
if ( !defined( 'ENTRY_POINT' ) ) {
    die('This is not an entry point. GeneralFunctions' );
}

function gfGetDelayedMsg() {
    if ( isset( $_SESSION['delayedMsg'] ) ) {
        $msg = $_SESSION['delayedMsg'];
        unset( $_SESSION['delayedMsg'] );
        return $msg;
    } else {
        return false;
    }
}

function gfGetDelayedText() {
    if ( isset( $_SESSION['delayedMsg'] ) ) {
        $msg = $_SESSION['delayedMsg']['text'];
        unset( $_SESSION['delayedMsg'] );
        return $msg;
    } else {
        return false;
    }
}

function gfSetDelayedMsg( $text, $type = 'Ok' ) {
    $arr = array();
    $arr['text'] = $text;
    $arr['type'] = $type;
    $_SESSION['delayedMsg'] = $arr;
}

function gfPostVar( $key, $default = null ) {
    if ( isset( $_POST[$key] ) ) {
        if ( is_int( $default ) ) {
            return (int) $_POST[$key];
        }
        return $_POST[$key];
    }
    return $default;
}

function gfGetVar( $key, $default = null ) {
    if ( isset( $_GET[$key] ) ) {
        if ( is_int( $default ) ) {
            return (int) $_GET[$key];
        }
        return $_GET[$key];
    }
    return $default;
}

function gfSessionVar( $key, $default = null ) {
    if ( isset( $_SESSION[$key] ) ) {
        if ( is_int( $default ) ) {
            return (int) $_SESSION[$key];
        }
        return $_SESSION[$key];
    }
    return $default;
}

function gfDebug( $message ) {
    global $gvDebug;

    if ( !$gvDebug['active'] ) {
        return;
    }

    static $socket = null;
    if ( !$socket ) {
        $socket = socket_create( AF_INET, SOCK_DGRAM, SOL_UDP );
        if ( !$socket ) {
            // Unable to create socket, discard message
            return;
        }
    }
    socket_sendto(
        $socket,
        $message,
        strlen( $message ),
        0,
        $gvDebug['destinationHost'],
        $gvDebug['destinationPort']
    );
}
