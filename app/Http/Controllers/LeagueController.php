<?php

namespace App\Http\Controllers;

use App\League;
use Facades\App\Repository\Ladder;
use Illuminate\Support\Facades\Input;

class LeagueController extends Controller
{
    public function index()
    {
        $get = Input::get('league');

        $leagueName = json_encode($get);

        return view('welcome')->with('leagueName', $leagueName);
    }

    public function fetchLadder()
    {
        $league = Input::get('league');

        $ladder = Ladder::all($league);

        if (!empty($ladder)) {
            return response()->json($ladder);
        } else {
            return response()->json([
                'message' => 'League not found',
                'status' => 404
            ], 404);
        }
    }

    public function knownLeagues()
    {
        $leagues = League::all();

        if (!empty($leagues)) {
            return response()->json($leagues);
        } else {
            return response()->json(['message' => 'No known leagues']);
        }
    }


}
