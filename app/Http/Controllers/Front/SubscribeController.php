<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\AppBaseController;
use App\Models\Subscribe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscribeController extends AppBaseController
{
    public function store(Request $request): JsonResponse
    {
        $input = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        Subscribe::firstOrCreate(
            ['email' => setEmailLowerCase($input['email'])],
            ['subscribe' => Subscribe::SUBSCRIBE]
        );

        return $this->sendSuccess(__('messages.flash.subscriber_creat'));
    }
}
