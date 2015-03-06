<?php

/**
 * Description of TopicalDomain
 *
 * @author sergio
 */
class TopicalDomain extends DatabaseTableWithId {
    
    // Both colors and icons are saved as number
    // in the database (their index)
    // Zero means nothing
    
    const COLOR_NONE = 0;
    const COLOR_RED = 1;
    const COLOR_GREEN = 2;
    const COLOR_BLUE = 3;
    const COLOR_BLACK = 4;
    
    public static $COLORS = array(
        0 => array( '', ''),
        1 => array( 'rosso', 'FF0000' ),
        2 => array( 'verde', '00FF00' ),
        3 => array( 'blu',   '0000FF' ),
        4 => array( 'nero',  '000000' ),
    );

    const ICON_NONE = 0;
    const ICON_DOLLAR = 1;
    const ICON_HOUSE = 2;
    const ICON_BILLS = 3;
    const ICON_MAIL = 4;
    
    public static $ICONS = array(
        0 => array( '', ''),
        1 => array( 'dollaro',      "path" ),
        2 => array( 'casa',         "path" ),
        3 => array( 'banconote',    "path" ),
        4 => array( 'lettera',      "path" ),
    );
    
    public static $ALLOWED_CODES = array(
        'A','B','C','D','E','F','G','H','I','J','K','L','M','N',
        'O','P','Q','R','S','T','U','V','W','X','Y','Z'
    );
    
    /**
     * @var array
     */
    protected $columns = array(
        'td_code',
        'td_name',
        'td_description',
        'td_active',
        'td_icon',
        'td_color',
        'td_eta',
        'td_next_generated_ticket',
    );
    
    /**
     * @var string
     */
    protected $idColumn = 'td_id';
    
    /**
     * @var string
     */
    protected $tableName = 'topical_domain';
    
    protected $td_id;
    protected $td_code;
    protected $td_name;
    protected $td_description; // Null
    protected $td_active;
    protected $td_icon; // Null
    protected $td_color; // Null
    protected $td_eta; // Null
    protected $td_next_generated_ticket = 1;
    
    
    
    protected function __construct() {
        // Private constructor (use static methods)
    }

    protected static function newFromDatabaseRow( $row ) {
        return parent::newFromDatabaseRowGeneric( $row, 'TopicalDomain' );
    }
    
    public static function fromDatabaseById( $td_id ) {
        return parent::fromDatabaseByParameterGeneric( 'td_id', $td_id, 'TopicalDomain' );
    }
    
    public static function fromDatabaseByCode( $td_code ) {
        $td_code = strtoupper( $td_code );
        return parent::fromDatabaseByParameterGeneric( 'td_code', $td_code, 'TopicalDomain' );
    }
    
    public static function fromDatabaseCompleteList( $activeOnly = true ) {
        $sql = "SELECT * FROM topical_domain";
        if ( $activeOnly ) {
            $sql .= " WHERE td_active=1";
        }
        $sql .= " ORDER BY td_code";
        $stmt = Database::prepareStatement( $sql );
        if ( !$stmt->execute() ) {
            throw new Exception( __METHOD__ .
                " Error while reading topical domain from database" );
        }
        $output = array();
        $rows = $stmt->fetchall( PDO::FETCH_ASSOC );
        foreach ($rows as $row) {
            $output[] = self::newFromDatabaseRow( $row );
        }
        return $output;
    }
    
    /**
     * @return TopicalDomain
     */
    public static function newRecord() {
        $ret = new TopicalDomain();
        $ret->td_description = '';
        $ret->td_active = 1;
        $ret->td_icon = 0;
        $ret->td_color = 0;
        $ret->td_eta = null;
        $ret->td_next_generated_ticket = 1;
        
        $ret->saved = false;
        $ret->isNew = true;
        
        return $ret;
    }

    public function getId() {
        return $this->td_id;
    }

    public function getCode() {
        return $this->td_code;
    }

    public function getName() {
        return $this->td_name;
    }

    public function getDescription() {
        return $this->td_description;
    }

    public function getActive() {
        return $this->td_active;
    }

    public function getIcon() {
        return $this->td_icon;
    }

    public function getColor() {
        return $this->td_color;
    }

    public function getEta() {
        return $this->td_eta;
    }

    public function getNextGeneratedTicket() {
        return $this->td_next_generated_ticket;
    }

    public function setCode( $td_code ) {
        $td_code = strtoupper( $td_code );
        if ( strlen( $td_code ) > 1 ) {
            // Use only first letter
            $td_code = substr( $td_code, 0, 1 );
        }
        if ( $this->td_code != $td_code ) {
            $this->saved = false;
            $this->td_code = $td_code;
        }
    }

    public function setName( $td_name ) {
        if ( $this->td_name != $td_name ) {
            $this->saved = false;
            $this->td_name = $td_name;
        }
    }

    public function setDescription( $td_description ) {
        if ( $this->td_description != $td_description ) {
            $this->saved = false;
            $this->td_description = $td_description;
        }
    }

    public function setActive( $td_active ) {
        if ( $this->td_active != $td_active ) {
            $this->saved = false;
            $this->td_active = $td_active;
        }
    }

    public function setIcon( $td_icon ) {
        $td_icon = $this->fixIconValue( $td_icon );
        if ( $this->td_icon != $td_icon ) {
            $this->saved = false;
            $this->td_icon = $td_icon;
        }
    }
    
    private function fixIconValue( $td_icon ) {
        if ( $td_icon === null ){
            return 0;
        }
        if ( !array_key_exists( (int) $td_icon, self::$ICONS ) ) {
            // Unrecognized icon
            return 0;
        }
        return (int) $td_icon;
    }

    public function setColor( $td_color ) {
        $td_color = $this->fixColorValue( $td_color );
        if ( $this->td_color != $td_color ) {
            $this->saved = false;
            $this->td_color = $td_color;
        }
    }
    
    private function fixColorValue( $td_color ) {
        if ( $td_color === null ){
            return 0;
        }
        if ( !array_key_exists( (int) $td_color, self::$COLORS ) ) {
            // Unrecognized icon
            return 0;
        }
        return (int) $td_color;
    }

    public function setEta( $td_eta ) {
        $td_eta = (int) $td_eta;
        if ( $this->td_eta != $td_eta ) {
            $this->saved = false;
            $this->td_eta = $td_eta;
        }
    }

    public function setNextGeneratedTicket( $td_next_generated_ticket ) {
        if ( $this->td_next_generated_ticket != $td_next_generated_ticket ) {
            $this->saved = false;
            $this->td_next_generated_ticket = $td_next_generated_ticket;
        }
    }
    
    public function canBeDeactivated() {
        $queueLength = Ticket::getNumberTicketInQueue( $this->td_code );
        return $queueLength === 0;
    }
    
    public function incrementNextGeneratedTicket() {
        $value = $this->getNextGeneratedTicket();
        $newValue = ( $value + 1 ) % 1000;
        $this->setNextGeneratedTicket( $newValue );
        return $value;
    }
    
    public static function getAvailableCodes() {
        $list = self::fromDatabaseCompleteList( false );
        $busyCodes = array();
        foreach ( $list as $td ) {
            $busyCodes[] = $td->getCode();
        }
        $availableCodes = array_diff( self::$ALLOWED_CODES, $busyCodes );
        return $availableCodes;
    }

}
