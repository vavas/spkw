<?php

namespace App\Console\Commands;

use App\Campaign;
use Illuminate\Console\Command;
use App\InfluencersCampaign;
use SoapBox\Formatter\Formatter;
use Storage;

class CampaignGenerateCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaign:generate:csv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate csv file for end campaign';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Starting script');
        $model = new InfluencersCampaign();
        $infCampaigns = $this->groupingByCampaign($model->getEndedCampaigns()->toArray());

        foreach ($infCampaigns as $key => $infCampaign) {
            try {
                $this->info('Generate csv for campaign with id - '. $key);
                $this->generateCSV($key, $infCampaign);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }
        $this->info('Finish script');
    }

    /**
     * Generate csv file and store in storage/uploads/csv directory
     * @param string $key
     * @param array $infCampaign
     */
    private function generateCSV($key, $infCampaign)
    {
        $formatter = Formatter::make($infCampaign, Formatter::ARR);
        $model = new Campaign();

        $csv = $formatter->toCsv();
        $fileName = $key . '.csv';
        try {
            Storage::disk('csv')->put($fileName, $csv);
            $model->updateCampaignDetail($key, $fileName);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

    }

    /**
     * Grouping by campaign
     * @param array $infCampaigns
     * @return array
     */
    private function groupingByCampaign($infCampaigns)
    {
        $result = [];
        foreach ($infCampaigns as $campaign) {
            $result[$campaign['campaign_id']][] = array_merge($campaign, ['currency_type' => 'USD']);
        }

        return $result;
    }
}
