<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    /**
     * API: Return a list of drivers names and addresses with the maxium Suitability Score.
     *  @param Request $request
     */
    public function index(Request $request)
    {

        $request->validate([
            'drivers' => 'required',
            'addresses' => 'required',
        ]);
        $drivers = explode(',', $request->drivers);
        $addresses = explode(',', $request->addresses);
        $response = $this->assignShipment($drivers, $addresses);
        return response($response);
    }

    /**
     * Return a list of drivers names and addresses with the maxium Suitability Score.
     *
     * @param array $listNames array of drivers names
     * @param array $listAddresses array of addresses to ship
     */

    public function assignShipment(array $listNames, array $listAddresses): array
    {
        $rawSuitabilityScore = array();

        # creating a list with all the possibilities of Suitability Score
        foreach ($listNames as $name) {
            $name = trim($name);
            foreach ($listAddresses as $address) {
                $address = trim($address);
                $suitabilityScore = $this->calculateSuitabilityScore(trim($name), trim($address));
                array_push($rawSuitabilityScore, array(
                    'address' => $address,
                    'driver' => $name,
                    'ss' => $suitabilityScore
                ));
            }
        }
        #sorting the Suitability Score List
        array_multisort(array_column($rawSuitabilityScore, "ss"), SORT_DESC, $rawSuitabilityScore);
        #run recursion
        return $this->pickTheBestSuitabilityScore($rawSuitabilityScore);
    }

    /**
     * This function calculates the Suitability Score based on the driver's name and the address
     *
     * @param string $driverName driver name 
     * @param string $address   address to ship
     */

    private function calculateSuitabilityScore(string $driverName, string  $address): float
    {
        $even = strlen($address) % 2 ? false : true;
        $suitabilityScore = 0.0;
        if ($even) {
            $vowels = preg_match_all('/[aeiou]/i', $driverName);
            $suitabilityScore = $vowels * 1.5;
        } else {
            $suitabilityScore = preg_match_all('/[bcdfghjklmnpqrstvwxyz]/i', $driverName);
        }
        #calculate the common factor (gcd =  greatest common divisor)
        $commonFactors = gmp_gcd(strlen($driverName), strlen($address));
        if ($commonFactors > 1) {
            $suitabilityScore = $suitabilityScore * 1.5;
        }
        return $suitabilityScore;
    }

    /**
     * This function perform a recursion to create a list of the best Suitability Score.
     * That list should be ordered previously, descending sorted by the Suitability Score.
     *
     * @param array $suitabilityScoreList a list with the combination of driver name and address Suitability Score
     */

    private function pickTheBestSuitabilityScore(array $suitabilityScoreList, $bestSuitabilityScoreList = array(), $totalSS = 0){
        #if there aren't more items on $suitabilityScoreList stop the recursion
        if (count($suitabilityScoreList) == 0) {
            return array('shipmentList' => $bestSuitabilityScoreList, 'totalSuitabilityScore' => $totalSS);
        }
        $bestInList = $suitabilityScoreList[0];
        $totalSS += $bestInList['ss'];
        array_push($bestSuitabilityScoreList, $bestInList);
        $newSuitabilityScoreList = array();
        #create new array of drivers, addresses and SS without the best match "$bestInList"
        foreach ($suitabilityScoreList as $ss) {
            if ($ss['address'] != $bestInList['address'] && $ss['driver'] != $bestInList['driver']) {
                array_push($newSuitabilityScoreList, $ss);
            }
        }
        #perform a recursion again
        return $this->pickTheBestSuitabilityScore($newSuitabilityScoreList, $bestSuitabilityScoreList, $totalSS);
    }
}
