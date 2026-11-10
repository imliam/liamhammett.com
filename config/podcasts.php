<?php

return [
    // Enable only after installing the documented internal Nginx location.
    'x_accel_redirect' => (bool) env('PODCAST_X_ACCEL_REDIRECT', false),
];
