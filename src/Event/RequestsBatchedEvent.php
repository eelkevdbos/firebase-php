<?php namespace Firebase\Event;

use GuzzleHttp\Event\AbstractEvent;
use GuzzleHttp\Event\EventInterface;

class RequestsBatchedEvent extends AbstractEvent implements EventInterface {

    protected $requests;

    public function __construct($requests)
    {
        $this->requests = $requests;
    }

    public function getRequests()
    {
        return $this->requests;
    }

} 