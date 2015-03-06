<?php

/**
 * Description of GetOfficeInfo
 *
 * @author sergio
 */
class GetOfficeInfo extends Page {

    public function __construct() {
        Database::lockTables( false );
    }

    public function canUse( $userLevel ) {
        return true;
    }

    public function execute() {
        // Cannot be posted
        return true;
    }

    public function getOutput() {
        global $gvQrCodeMsg, $gvSpotTitle, $gvSpotBody,
            $gvOfficeName, $gvOfficeAddress, $gvOfficeCode,
            $gvOfficeSecret;
        $content = array();
        $content['qrCodeMsg'] = $gvQrCodeMsg;
        $content['spotTitle'] = $gvSpotTitle;
        $content['spotBody'] = $gvSpotBody;
        $content['officeName'] = $gvOfficeName;
        $content['officeAddress'] = $gvOfficeAddress;
        $content['officeCode'] = $gvOfficeCode;
        $content['officeSecret'] = $gvOfficeSecret;
        $jsonOutput = new JsonOutput();
        $jsonOutput->setContent( $content );

        return $jsonOutput;
    }

}
