<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Address;

class StateLongToShortName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'state-long-name:short';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert state long name to short name';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // list all address
        $addresses = Address::all();

        foreach ($addresses as $address) {
            $addressState = $address->state;
            $shortToLong = $this->shortToLongName($addressState);

            $addressState = $shortToLong ? $shortToLong : $addressState;
            $addressState = ($shortToLong == null && $this->longToShortName($addressState))
                ? $this->longToShortName($addressState) 
                : $addressState;

            $address->update([
                'state' => $addressState
            ]);
        }

    }

    public function shortToLongName($longName) {
        $states = [
            'ACT' => 'Australian Capital Territory',
            'NSW' => 'New South Wales',
            'NT' => 'Northern Territory',
            'QLD' => 'Queensland',
            'SA' => 'South Australia',
            'TAS' => 'Tasmania',
            'VIC' => 'Victoria',
            'WA' => 'Western Australia',
        ];

        return isset($states[$longName]) ? $longName : null;
    }

    public function longToShortName($longName) {
        $longName = strtolower($longName);

        $states = [
            'new south wales' => 'NSW',
            'queensland' => 'QLD',
            'victoria' => 'VIC',
            'south australia' => 'SA',
            'western australia' => 'WA',
            'australian capital territory' => 'ACT',
            'tasmania' => 'TAS',
            'northern territory' => 'NT',
        ];

        return $states[$longName] ?? null;
    }
}
