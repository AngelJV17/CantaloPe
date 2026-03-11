<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('karaoke.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
