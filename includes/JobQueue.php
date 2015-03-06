<?php

/**
 * Description of JobQueue
 *
 * @author sergio
 */
class JobQueue {

    public static $instance = null;

    private $jobs = null;

    private function __construct() {
        // private constructor
        $this->jobs = array();
    }

    public static function getInstance() {
        if ( !self::$instance ) {
            self::$instance = new JobQueue();
        }
        return self::$instance;
    }

    public function addJob( $job ) {
        $this->jobs[] = $job;
    }

    public function getJobs() {
        return $this->jobs;
    }

    public function countJobs() {
        return count( $this->jobs );
    }

    public function executeJobs() {
        $num = count( $this->jobs );
        if ( $num ) {
            gfDebug( "Executing $num background jobs." );
            foreach ( $this->jobs as $job ) {
                $job();
            }
        }
    }

}
