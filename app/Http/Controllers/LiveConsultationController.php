<?php

namespace App\Http\Controllers;

use App\Models\ZoomOAuth;
use App\Repositories\ZoomRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class LiveConsultationController extends Controller
{
    public function zoomCallback(Request $request)
    {
        $zoomRepo = App::make(ZoomRepository::class);
        $zoomRepo->connectWithZoom($request->get('code'));

        return redirect(route('filament.doctor.resources.live-consultations.index'));
    }
}