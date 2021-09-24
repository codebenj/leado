<?php

use Illuminate\Database\Seeder;

class LeadEscalationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Customer::class, 3)
            ->create()
            ->each(function ($customer){
                $organisation = App\Organisation::inRandomOrder()->first();

                $lead = factory(App\Lead::class)->create([
                    'customer_id' => $customer->id,
                ]);

                factory(App\LeadEscalation::class)->create([
                    'lead_id' => $lead->id,
                    'organisation_id' => $organisation->id,
                ]);
            }
        );
    }
}
