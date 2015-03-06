<?php

/**
 * Description of Session
 *
 * @author sergio
 */
class Session {

    public static $webReservationSteps = array(
        0 => 'web/tdSelection',
        1 => 'web/insertPhone',
        2 => 'web/checkPhone',
        3 => 'web/complete',
    );
    
    public static function start() {
        global $gvSessionTimeout;
        
        if ( session_status() != PHP_SESSION_NONE ) {
            return;
        }
        
        session_start();
        if ( isset( $_SESSION['lastActivity'] ) ) {
            if ( time() - $_SESSION['lastActivity'] >= $gvSessionTimeout ) {
                if ( isset( $_SESSION['op_code'] ) ) {
                    Operator::clearTableForLogout( $_SESSION['op_code'] );
                }
                // Session expired
                session_destroy();
                unset( $_SESSION );
                session_start();
                session_regenerate_id();
                gfSetDelayedMsg( 'La sessione è scaduta.', 'Err' );
            }
        }
        $_SESSION['lastActivity'] = time();
        
        // Set userlevel
        if ( !isset( $_SESSION['userLevel'] ) ) {
            $_SESSION['userLevel'] = Page::NORMAL_USER;
        }

        if ( isset( $_SESSION['op_code'] ) ) {
            $op = Operator::fromDatabaseByCode( $_SESSION['op_code'] );
            if ( $op ) {
                // This is not really a session variable
                // It will be reloaded at every request
                // It's just to be used in other classes
                $_SESSION['operator'] = $op;
                if ( !isset( $_SESSION['td_served'] ) ) {
                    $_SESSION['td_served'] = array();
                }
            } else {
                // Operator deleted while still logged in?
                self::logoutOperator();
            }
        }

        if ( isset( $_SESSION['desk_number'] ) ) {
            $desk = Desk::fromDatabaseByNumber( $_SESSION['desk_number'] );
            if ( $desk ) {
                $_SESSION['desk'] = $desk;
                $desk->updateLastActivityTime();
                $desk->save();
            } else {
                self::logoutOperator();
            }
        }
    }

    public static function loginOperator( $code ) {
        $_SESSION['userLevel'] = Page::OPERATOR_USER;
        $_SESSION['op_code'] = $code;

        // Determine desk number
        $desk_ip_address = $_SERVER['REMOTE_ADDR'];
        $desk = Desk::fromDatabaseByIpAddress( $desk_ip_address );
        if ( !$desk ) {
            session_destroy();
            throw new UnknownDeskException();
        }
        $_SESSION['desk_number'] = $desk->getNumber();

        $desk->updateLastActivityTime();
        $desk->setOpCode( $code );
        $desk->save();
    }

    public static function logoutOperator() {
        $_SESSION['userLevel'] = Page::NORMAL_USER;
        Operator::clearTableForLogout( $_SESSION['op_code'] );
        unset( $_SESSION['op_code'] );
        unset( $_SESSION['desk_number'] );
        unset( $_SESSION['td_served'] );
    }

    public static function setupWebSession() {
        // Default values used by online reservation
        $_SESSION['step'] = 0;
        $_SESSION['td_code'] = '';
        $_SESSION['phone'] = '';
        $_SESSION['phone_code'] = '';
        $_SESSION['ticket'] = null;
    }

    // Return redirect object if a redirect is needed otherwise
    // return null
    public static function redirectLastStep( $realPageStep ) {
        global $gvPath;

        if ( !isset( $_SESSION['step'] ) ) {
            # New user, initalize session
            self::setupWebSession();
        }

        if ( $_SESSION['step'] != $realPageStep ) {
            $redirectPage = self::$webReservationSteps[$_SESSION['step']];
            $redirectPage = $gvPath . '/' . $redirectPage;
            return new RedirectOutput( $redirectPage );
        } else {
            return null;
        }
    }

}