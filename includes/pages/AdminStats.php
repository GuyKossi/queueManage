<?php

/**
 * Description of AdminStats
 *
 * @author sergio
 */
class AdminStats extends Page {

    public static $MIN_DATE = 1; // Jan 1st 1970
    public static $MAX_DATE = 2145916800; // Jan 1st 2038

    protected $dateFrom;
    protected $dateTo;

    public function __construct() {
        Database::lockTables( false );
        $this->dateFrom = self::$MIN_DATE;
        $this->dateTo = self::$MAX_DATE;
    }

    public function canUse( $userLevel ) {
        return $userLevel === Page::SYSADMIN_USER;
    }

    public function execute() {
        global $gvTimeZone;
        if ( !empty( $_POST['from'] )
            && !empty( $_POST['to'] )
        ) {
            $dateFrom = strtotime( $_POST['from'] . " $gvTimeZone" );
            $dateTo = strtotime( $_POST['to'] . " $gvTimeZone" );
            if ( $dateTo && $dateFrom && $dateTo > $dateFrom ) {
                $this->dateTo = $dateTo;
                $this->dateFrom = $dateFrom;
            }
        }
        return true;
    }

    public function getOutput() {
        global $gvPath;

        $page = new WebPageOutput();
        $page->setHtmlPageTitle( $this->getPageTitle() );
        $page->linkStyleSheet( "$gvPath/assets/css/style.css");
        $page->loadJqueryUi();
        $page->addJavascript( "$gvPath/assets/js/adminStats.js" );
        $page->addJavascript( "$gvPath/assets/js/vendor/Chart.min.js" );
        $page->setHtmlBodyHeader( $this->getPageHeader() );
        $page->setHtmlBodyContent( $this->getPageContent() );
        
        return $page;
    }
    
    private function getTdStatsRows() {
        $rows = '';
        $tdList = TopicalDomain::fromDatabaseCompleteList( false );
        foreach ( $tdList as $td ) {
            $tickets = TicketStats::fromDatabaseListByCode(
                $td->getCode(),
                $this->dateFrom,
                $this->dateTo
            );
            $accumulatorWait = 0;
            $accumulatorService = 0;
            $counter = 0;
            foreach ( $tickets as $ticket ) {
                // Do not count canceled ticket
                if ( !$ticket->getTimeExec() ) {
                    continue;
                }
                $wait = $ticket->getTimeExec() - $ticket->getTimeIn();
                $accumulatorWait += $wait;
                $service = $ticket->getTimeOut() - $ticket->getTimeExec();
                $accumulatorService += $service;
                $counter++;
            }
            if ( $counter ) {
                $averageWait = (int) ( $accumulatorWait / $counter / 60 );
                $averageService = (int) ( $accumulatorService / $counter / 60 );
            } else {
                $averageWait = 0;
                $averageService = 0;
            }
            
            $rows .= <<<EOS
<tr>
    <td>{$td->getCode()}</td>
    <td>$counter</td>
    <td>$averageWait min</td>
    <td>$averageService min</td>
</tr>
EOS;
        }
        if ( !$rows ) {
            $rows = '<tr><td colspan="3">Nessuna statistica disponibile</td></tr>';
        }
        return $rows;

    }
    
    public function getPageContent() {
        global $gvPath;

        if ( $this->dateFrom == self::$MIN_DATE
            && $this->dateTo == self::$MAX_DATE ) {
            $timeSpan = 'completo';
        } else {
            global $gvTimeZone;
            $timezone = new DateTimeZone( $gvTimeZone );

            $from = new DateTime( '@' . $this->dateFrom );
            $from->setTimeZone( $timezone );
            $from = $from->format( 'd-m-Y' );

            $to = new DateTime( '@' . $this->dateTo );
            $to->setTimeZone( $timezone );
            $to = $to->format( 'd-m-Y' );

            $timeSpan = "dal $from al $to";
        }

        $content = '<h3>Imposta il periodo di ricerca</h2>' . PHP_EOL;
        $content .= $this->getForm();
        $content .= "\n<h2>Statistiche per il periodo $timeSpan</h2>\n";
        $content .= $this->getTdStatsTable();
        $content .= $this->getSourceStatsTable();
        $content .= "\n<h3>Statistiche per operatore</h2>\n";
        $content .= $this->getStatsOperatorTables();
        $content .= "<p><a href=\"$gvPath/application/adminPage\">Torna al menù principale</a></p>";
        return $content;
    }

    public function getTdStatsTable() {
        global $gvPath;

        $table = <<<EOS
<table id="listTable">
    <caption>Statistiche per area tematica</caption>
    <tr>
        <th>Area tematica</th>
        <th>Numero ticket</th>
        <th>Attesa media</th>
        <th>Tempo d'esecuzione medio</th>
    </tr>
    {$this->getTdStatsRows()}
</table>

EOS;
        return $table;
    }

    public function getStatsOperatorTables() {
        global $gvPath;

        $tables = '';
        $operators = Operator::fromDatabaseCompleteList();
        foreach ( $operators as $operator ) {
            $tables .= <<<EOS
<table id="listTable">
<caption>{$operator->getFullName()} ({$operator->getCode()})</caption>
    <tr>
        <th>Area tematica</th>
        <th>Ticket serviti</th>
        <th>Percentuale</th>
        <th>Tempo medio d'esecuzione</th>
    </tr>
    {$this->getRowsForOperator( $operator )}
</table>
EOS;
        }

        if ( !$tables ) {
            $tables = "<p>Non sono disponibili statistiche per gli operatori</p>";
        }

        return $tables;
    }

    public function getRowsForOperator( $operator ) {
        /* Format for tdStats
        [
            'A' => [
                'count' => 10,
                'execTime' => 30
            ],
            'B' => [
                'count' => 10,
                'execTime' => 30
            ],

            ...
        ]
        */
        $tdStats = array();
        $tickets = TicketStats::fromDatabaseListByOperator(
            $operator->getCode(),
            $this->dateFrom,
            $this->dateTo
        );
        $totalCount = count( $tickets );
        $averageExecTime = 0;
        foreach ( $tickets as $ticket ) {
            $ticketCode = $ticket->getCode();
            if ( !isset( $tdStats[$ticketCode] ) ) {
                $tdStats[$ticketCode] = array(
                    'count' => 0,
                    'execTime' => 0
                );
            }
            $timeInExecution = $ticket->getTimeOut() - $ticket->getTimeExec();
            $tdStats[$ticketCode]['count'] += 1;
            $tdStats[$ticketCode]['execTime'] += $timeInExecution;
            $averageExecTime += $timeInExecution;
        }

        ksort( $tdStats );

        $rows = '';
        foreach ( $tdStats as $code => $values ) {
            // Division by zero should never occur
            $percentage = (int) ( ( $values['count'] / $totalCount ) * 100 );
            $averageTime = (int) ( $values['execTime'] / $values['count'] / 60 );
            $rows .= <<<EOS
<tr>
    <td>$code</td>
    <td>{$values['count']}</td>
    <td>$percentage %</td>
    <td>$averageTime min</td>
</tr>\n
EOS;
        }

        if ( !$rows ) {
            $rows = "<tr><td colspan=\"4\">Nessuna statistica disponibile</td></tr>";
        } else {
            $averageExecTime = (int) ( $averageExecTime / $totalCount / 60 );
            $rows .= <<<EOS
<tr>
    <td><b>TOTALE</b></td>
    <td><b>$totalCount</b></td>
    <td><b>100 %</b></td>
    <td><b>$averageExecTime min</b></td>
</tr>\n
EOS;
        }

        return $rows;
    }
    
    public function getSourceStatsTable() {
        $counters = array();
        $totalCount = 0;
        foreach ( array( 'app', 'totem', 'web' ) as $source ) {
            $tickets = TicketStats::fromDatabaseListBySource(
                $source,
                $this->dateFrom,
                $this->dateTo
            );
            $counters[$source] = array( "count" => count( $tickets ) );
            $totalCount += $counters[$source]['count'];
        }
        $rows = '';
        foreach ( $counters as $source => $value) {
            if ( $totalCount != 0 ) {
                $percentage = (int) ( ( $value['count'] / $totalCount ) * 100 );
            } else {
                $percentage = 0;
            }
            $counters[$source]['percentage'] = $percentage;
        }
        $table = <<<EOS
<table id="listTable">
<caption>Statistiche per sorgente</caption>
    <tr>
        <th />
        <th>App</th>
        <th>Totem</th>
        <th>Web</th>
    </tr>
    <tr>
        <th>Conteggio</th>
        <td>{$counters['app']['count']}</td>
        <td>{$counters['totem']['count']}</td>
        <td>{$counters['web']['count']}</td>
    </tr>
    <tr>
        <th>Percentuale</th>
        <td>{$counters['app']['percentage']} %</td>
        <td>{$counters['totem']['percentage']} %</td>
        <td>{$counters['web']['percentage']} %</td>
    </tr>
</table>
<canvas id="sourceChart" width="150" height="150"></canvas>
<script>
    var data = [
        {
            value: {$counters['app']['count']},
            label: "App",
            color:"#F7464A",
            highlight: "#FF5A5E"
        },
        {
            value: {$counters['totem']['count']},
            label: "Totem",
            color: "#46BFBD",
            highlight: "#5AD3D1"
        },
        {
            value: {$counters['web']['count']},
            label: "Web",
            color: "#FDB45C",
            highlight: "#FFC870",
        },
    ];
    var ctx = document.getElementById("sourceChart").getContext("2d");
    var myPieChart = new Chart(ctx).Pie(data);
</script>
EOS;
        return $table;
    }

    public function getPageTitle() {
        return 'Statistiche';
    }
    
    public function getPageHeader() {
        $ret = "<h1>{$this->getPageTitle()}</h1>";
        $ret .= Page::getDelayedMsgBlock();
        return $ret;
    }

    public function getForm() {
        return <<<EOS
<form method="post">
    <table class="adminStatsForm">
        <tr>
            <th>Inizio periodo</th>
            <th>Fine periodo</th>
            <th />
        </tr>
        <tr>
            <td><input type="text" name="from" class="datepicker"></td>
            <td><input type="text" name="to" class="datepicker"></td>
            <td><input type="submit" value="Aggiorna"></td>
        </tr>
    </table>
</form>
EOS;
    }
}
