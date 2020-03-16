<?php

namespace App\Jobs;

use App\Click;
use App\Url;
use Jenssegers\Agent\Agent;

class ProcessClick extends Job
{
    protected $agent;
    protected $fingerprint;
    protected $url;

    /**
     * Create a new job instance.
     *
     * @param Agent $agent
     * @param string $fingerprint
     * @param Url $url
     */
    public function __construct(Agent $agent, string $fingerprint, Url $url)
    {
        $this->agent = $agent;
        $this->fingerprint = $fingerprint;
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
        } else if ($this->agent->isMobile() || $this->agent->isPhone()) {
            $device = 'mobile';
        } else if ($this->agent->isTablet()) {
            $device = 'tablet';
        } else {
            $device = null;
        }

        $click = new Click;

        $click->browser = $browser;
        $click->device = $device;
        $click->fingerprint = $this->fingerprint;

        $this->url->clicks()->save($click);
    }
}
