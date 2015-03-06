<?php

class Autoloader {
    public static $CLASSES = array(
        # Default
        "AppPushSender" => "AppPushSender.php",
        "Database" => "Database.php",
        "EditableConf" => "EditableConf.php",
        "JobQueue" => "JobQueue.php",
        "LocalSettingsGenerator" => "LocalSettingsGenerator.php",
        "PageRouter" => "PageRouter.php",
        "Session" => "Session.php",
        "SmsSender" => "SmsSender.php",
        # Ajax
        "ActivateTopicalDomain" => "ajax/ActivateTopicalDomain.php",
        "RemoveRecord" => "ajax/RemoveRecord.php",
        # App
        "AppAction" => "app/AppAction.php",
        "AppCancelTicket" => "app/AppCancelTicket.php",
        "AppDigitalize" => "app/AppDigitalize.php",
        "AppGetDesksStatus" => "app/AppGetDesksStatus.php",
        "AppGetOfficeListByCity" => "app/AppGetOfficeListByCity.php",
        "AppGetOfficeListByGps" => "app/AppGetOfficeListByGps.php",
        "AppGetQueueStatus" => "app/AppGetQueueStatus.php",
        "AppGetTicketStatus" => "app/AppGetTicketStatus.php",
        "AppNewTicket" => "app/AppNewTicket.php",
        "AppNoSuchAction" => "app/AppNoSuchAction.php",
        "AppUpdateNotice" => "app/AppUpdateNotice.php",
        # App / exceptions
        "BadOwnershipException" => "app/exceptions/BadOwnershipException.php",
        "BannedDeviceException" => "app/exceptions/BannedDeviceException.php",
        "InvalidOfficeCodeException" => "app/exceptions/InvalidOfficeCodeException.php",
        "InvalidParamException" => "app/exceptions/InvalidParamException.php",
        "InvalidTicketException" => "app/exceptions/InvalidTicketException.php",
        "InvalidTokenException" => "app/exceptions/InvalidTokenException.php",
        # Entities
        "Ban" => "entities/Ban.php",
        "DatabaseTable" => "entities/DatabaseTable.php",
        "DatabaseTableWithId" => "entities/DatabaseTableWithId.php",
        "Desk" => "entities/Desk.php",
        "Device" => "entities/Device.php",
        "DisplayMain" => "entities/DisplayMain.php",
        "Office" => "entities/Office.php",
        "Operator" => "entities/Operator.php",
        "Ticket" => "entities/Ticket.php",
        "TicketStats" => "entities/TicketStats.php",
        "TopicalDomain" => "entities/TopicalDomain.php",
        # Output
        "ErrorPageOutput" => "output/ErrorPageOutput.php",
        "JsonOutput" => "output/JsonOutput.php",
        "Output" => "output/Output.php",
        "RedirectOutput" => "output/RedirectOutput.php",
        "WebPageOutput" => "output/WebPageOutput.php",
        # Pages
        "AdminDeskEdit" => "pages/AdminDeskEdit.php",
        "AdminDeskList" => "pages/AdminDeskList.php",
        "AdminDeviceEdit" => "pages/AdminDeviceEdit.php",
        "AdminDeviceList" => "pages/AdminDeviceList.php",
        "AdminOperatorEdit" => "pages/AdminOperatorEdit.php",
        "AdminOperatorList" => "pages/AdminOperatorList.php",
        "AdminPage" => "pages/AdminPage.php",
        "AdminSettings" => "pages/AdminSettings.php",
        "AdminStats" => "pages/AdminStats.php",
        "AdminTopicalDomainEdit" => "pages/AdminTopicalDomainEdit.php",
        "AdminTopicalDomainList" => "pages/AdminTopicalDomainList.php",
        "ApiPage" => "pages/ApiPage.php",
        "LoginPage" => "pages/LoginPage.php",
        "LogoutPage" => "pages/LogoutPage.php",
        "OperatorPage" => "pages/OperatorPage.php",
        "Page" => "pages/Page.php",
        # Pages / devices
        "DeviceMainPage" => "pages/device/DeviceMainPage.php",
        "DeviceDisplayDesk" => "pages/device/DeviceDisplayDesk.php",
        "DeviceDisplayMain" => "pages/device/DeviceDisplayMain.php",
        # Pages / exception
        "UnknownDeskException" => "pages/exceptions/UnknownDeskException.php",
        # Pages / globalServer
        "GsHomePage" => "pages/globalServer/GsHomePage.php",
        "GsSearch" => "pages/globalServer/GsSearch.php",
        # Pages / web
        "WebCheckPhone" => "pages/web/WebCheckPhone.php",
        "WebComplete" => "pages/web/WebComplete.php",
        "WebInsertPhone" => "pages/web/WebInsertPhone.php",
        "WebTdSelection" => "pages/web/WebTdSelection.php",
        # Totem
        "GetOfficeInfo" => "totem/GetOfficeInfo.php",
        "GetTopicalDomains" => "totem/GetTopicalDomains.php",
        "ReserveTicket" => "totem/ReserveTicket.php",
    );
    
    public static function autoload( $class ) {
        if ( array_key_exists( $class, self::$CLASSES ) ) {
            include self::$CLASSES[$class];
        }
    }
}
