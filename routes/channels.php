<?php

use Illuminate\Support\Facades\Broadcast;


Broadcast::channel('image-processing', function ($user) {
    return true;
});
