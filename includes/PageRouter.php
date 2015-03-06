<?php

/**
 * Description of PageRouter
 *
 * @author sergio
 */
class PageRouter {
    // URL name => Class name
    public static $ROUTER_MAP = array(
        // Paths beginning with => means redirect to that path
        "" => "=>application/loginPage",
        "index.php" => "=>application/loginPage",
        # Ajax
        "ajax/activateTopicalDomain" => "ActivateTopicalDomain",
        "ajax/removeRecord" => "RemoveRecord",
        # Application
        "application" => "=>application/loginPage",
        "application/adminDeskEdit" => "AdminDeskEdit",
        "application/adminDeskList" => "AdminDeskList",
        "application/adminDeviceEdit" => "AdminDeviceEdit",
        "application/adminDeviceList" => "AdminDeviceList",
        "application/adminOperatorEdit" => "AdminOperatorEdit",
        "application/adminOperatorList" => "AdminOperatorList",
        "application/adminPage" => "AdminPage",
        "application/adminSettings" => "AdminSettings",
        "application/adminStats" => "AdminStats",
        "application/adminTopicalDomainEdit" => "AdminTopicalDomainEdit",
        "application/adminTopicalDomainList" => "AdminTopicalDomainList",
        "application/loginPage" => "LoginPage",
        "application/logoutPage" => "LogoutPage",
        "application/opPage" => "OperatorPage",
        # Device
        "device" => "DeviceMainPage",
        "device/desk" => "DeviceDisplayDesk",
        "device/main" => "DeviceDisplayMain",
        # GlobalServer
        "globalServer" => "=>globalServer/homePage",
        "globalServer/homePage" => "GsHomePage",
        "globalServer/search" => "GsSearch",
        # Totem
        "totem/getOfficeInfo" => "GetOfficeInfo",
        "totem/getTopicalDomains" => "GetTopicalDomains",
        "totem/reserveTicket" => "ReserveTicket",
        # Web
        "web" => "=>web/tdSelection",
        "web/checkPhone" => "WebCheckPhone",
        "web/complete" => "WebComplete",
        "web/insertPhone" => "WebInsertPhone",
        "web/tdSelection" => "WebTdSelection",
    );
    
    public static function getClassOrRedirect( $pageRequested ) {
        // Handle api reguests
        if ( $pageRequested == "api" ) {
            // Log POST data
            gfDebug( print_r( $_POST, true ) );
            return "ApiPage";
        }

        if ( array_key_exists( $pageRequested, self::$ROUTER_MAP ) ) {
            $target = self::$ROUTER_MAP[$pageRequested];
            if ( strpos($target, "=>") === 0 ) {
                // Redirect
                global $gvPath;
                $target = substr( $target, 2 );
                $redirect = new RedirectOutput( "$gvPath/$target" );
                return $redirect;
            }
            return $target;
        }
        //TODO to implement generic error page
        die('Page unkown in PageRouter');
    }
}
