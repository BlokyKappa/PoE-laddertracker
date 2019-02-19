<?php

namespace App\Repository;

use App\League;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Carbon\Carbon;
use function GuzzleHttp\Promise\settle;
use function GuzzleHttp\Promise\all;

class Ladder
{
    const CACHE_KEY = 'LADDER';

    public function all($league)
    {
        $key = "all . {$league}";

        $cacheKey = $this->getCacheKey($key);

        return cache()->remember($cacheKey, Carbon::now()->addMinutes(5), function () use ($league)  {
            return $this->getAll($league);
        });
    }

    private function storeLeague($leagueName)
    {
        League::updateOrCreate(['league' => $leagueName], ['cache_ends' => Carbon::now()->addMinutes('10')]);
    }

    public function getCacheKey($key)
    {
        $key = strtoupper($key);

        return self::CACHE_KEY . ".$key";
    }

    public function getAll($league)
    {
        $jsonLadder = $this->getLadder($league);

        if (!empty($jsonLadder)) {
            $ladder = json_decode($jsonLadder);

            foreach ($ladder->entries as $player) {
                $skills = $this->fetchActiveSkills($player->account->name, $player->character->name);
                $player->skills = $skills;
            }

            $this->storeLeague($league);
            return json_encode($ladder);
        } else {
            return [];
        }

    }

    private function fetchActiveSkills($accountName, $characterName)
    {
        $client = new Client();

        try {
           $response = $client->request(
                'post',
                'https://pathofexile.com/character-window/get-items', [
                    'form_params' => [
                        'accountName' => $accountName,
                        'character' => $characterName
                    ],
                ]
            );
        } catch (ServerException | ClientException $e) {
            return [];
        }

        $socketedGems = array();
        $responseObject = json_decode($response->getBody()->getContents());

        foreach ($responseObject->items as $item) {
            if (isset($item->socketedItems)) {

                /**
                 * Filter all support gems to check for links
                 */
                $filtered = array_filter($item->socketedItems, function ($value) {
                    if (isset($value->support))
                        return $value->support === true;
                    else
                        return false;
                });


                foreach ($item->socketedItems as $gem) {
                    if (isset($gem->support)) {
                        if ($gem->support === false) {
                            /**
                             * Insert the amount of links from the support gems into the damage skill object
                             */
                            $gem->links = count($filtered);

                            $socketedGems[] = $gem;
                        }
                    }
                }
            }
        }
        if (!empty($socketedGems)) {
            /**
             * Create Laravel collection for better methods
             */
            $collection = collect($socketedGems);

            /**
             * Check which column contains the highest value of links, which often is the main skill the player uses
             */
            $mainSkill = $collection->sortByDesc('links')->first();

            $skills = collect(['mainSkill' => $mainSkill, 'socketedGems' => $socketedGems]);

            return $skills;

        } else {
            $skills = collect(['mainSkill' => false, 'socketedGems' => false]);

            return $skills;
        }


    }

    private function getLadder($leagueName)
    {
        $client = new Client();
        try {
            $url = 'http://api.pathofexile.com/ladders/' . $leagueName . '?limit=30';
            $results = $client->get($url);
        } catch (ServerException | ClientException $e) {
            return ['Error' => 'Retrieving ladder from PoE API failed'];
        }

        $response = $results->getBody()->getContents();

        return $response;
    }
}
