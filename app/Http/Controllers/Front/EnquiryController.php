<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\AppBaseController;
use App\Models\Enquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnquiryController extends AppBaseController
{
    public function store(Request $request): JsonResponse
    {
        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:121'],
            'message' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'region_code' => ['nullable', 'string', 'max:10'],
        ]);

        $input['email'] = setEmailLowerCase($input['email']);

        Enquiry::create($input);

        return response()->json([
            'success' => true,
            'message' => __('messages.flash.messages_sent')
        ]);
    }
}
