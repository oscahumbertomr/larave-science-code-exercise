<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ShipmentController;

class AssignShipment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipment:assign {driversFile} {addressesFile}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign a driver the best address based on suitability score algorithm';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $driversFile = $this->argument('driversFile');     
        $addressesFile = $this->argument('addressesFile');     
        $listNames = file($driversFile, FILE_IGNORE_NEW_LINES);
        $listAddresses = file($addressesFile, FILE_IGNORE_NEW_LINES);

        $shipmentController = new ShipmentController();
        $result = $shipmentController->assignShipment($listNames,$listAddresses);
        $newFile = 'shipmentFiles/results.json';
        file_put_contents('shipmentFiles/results.json', json_encode($result));

        $this->info("Total SS: {$result['totalSuitabilityScore']}");
        $this->info("A new file was created on $newFile");
        $this->info("");
        $this->info("This is the shipment list:");
        $this->info("address -> driver : suitability score ");
        $this->info("");
        foreach($result['shipmentList'] as $shipment){
            $this->info("{$shipment['address']} -> {$shipment['driver']} : {$shipment['ss']}");
        }

    }
}
