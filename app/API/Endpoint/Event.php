<?php

namespace Statamic\API\Endpoint;

class Event
{
    /**
     * Fire an event
     *
     * @param mixed $event
     * @param mixed $payload
     * @return mixed
     */
    public function fire($event, $payload = [])
    {
        return event($event, $payload);
    }

    /**
     * Fire an event but only return the first response
     *
     * @param mixed $event
     * @param mixed $payload
     * @return mixed
     */
    public function fireFirst($event, $payload = [])
    {
        $response = self::fire($event, $payload);

        if (empty($response)) {
            return null;
        }

        return $response[0];
    }
}
