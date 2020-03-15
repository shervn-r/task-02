<?php

namespace App\Jobs;

use App\Click;
use App\Url;
use Jenssegers\Agent\Agent;

class ProcessClick extends Job
{
    protected $agent;
    protected $url;

    /**
     * Create a new job instance.
     *
     * @param Agent $agent
     * @param Url $url
     */
    public function __construct(Agent $agent, Url $url)
    {
        $this->agent = $agent;
        $this->url = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $browser = $this->agent->browser();
        if ($this->agent->isDesktop()) {
            $device = 'desktop';
        } else if ($this->agent->isMobile()) {
            $device = 'mobile';
        }

        $click = new Click;

        $click->browser = $browser;
        $click->device = $device;

        $this->url->clicks()->save($click);
    }
}
