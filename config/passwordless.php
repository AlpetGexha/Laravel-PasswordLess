<?php

return [
    // how much second need to wait after max attemps
    'rate_limit' => 120, // 2 minute

    // how much request can user make for limited time
    'max_attempts' => 2,

    // how much second link is valid
    'expired_time' => 900  //now()->addMinutes(15)
];
