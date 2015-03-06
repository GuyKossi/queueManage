<?php

/**
 * Description of DeviceMainPage
 *
 * @author sergio
 */
class DeviceMainPage extends Page {

    public function canUse( $userLevel ) {
        return true;
    }

    public function execute() {
        // Cannot be posted
        return true;
    }

    public function getOutput() {
        global $gvPath;

        $ip = $_SERVER['REMOTE_ADDR'];
        
        //Check whether the address is registered
        $device = Device::fromDatabaseByIpAddress( $ip );
        if ( !$device ) {
            $page = new WebPageOutput();
            $page->setHtmlPageTitle( 'Dispositivo non riconosciuto' );
            $page->addHtmlHeader( '<meta http-equiv="refresh" content="5">' );
            $page->setHtmlBodyContent( $this->getPageContentForUnknown( $ip ) );
            return $page;
        }
        if ( (int) $device->getDeskNumber() === 0 ) {
            // DisplayMain
            $td_code = $device->getTdCode();
            if ( $td_code ) {
                $td_code = "?td_code=" . urlencode( $td_code );
            } else {
                $td_code = '';
            }
            $redirect = new RedirectOutput( "$gvPath/device/main$td_code" );
            return $redirect;
        }
        $num = $device->getDeskNumber();
        $redirect = new RedirectOutput( "$gvPath/device/desk?desk_number=$num" );
        return $redirect;
    }
    
    public function getPageContentForUnknown( $ip ) {
        $ret = <<<EOS
<h1>Il dispositivo non è stato riconosciuto</h1>
<h1>$ip</h1>
EOS;
        return $ret;
    }

}
