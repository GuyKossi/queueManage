<?php

/**
 * Description of Page
 *
 * @author sergio
 */
abstract class Page {
    
    const NORMAL_USER = 0;
    const OPERATOR_USER = 1;
    const SYSADMIN_USER = 2;

    public abstract function getOutput();
    public abstract function execute();
    
    public abstract function canUse( $userLevel );
    
    public function afterPermissionCheck() {
        // No-op is default behavior
        ;
    }
    
    public static function getDelayedMsgBlock() {
        $ret = "";
        if ( $msg = gfGetDelayedMsg() ) {
            $type = htmlspecialchars( $msg['type'] );
            $text = htmlspecialchars( $msg['text'] );
            $ret = "\n<div class=\"message$type\">$text</div>";
        }
        return $ret;
    }
    
}
