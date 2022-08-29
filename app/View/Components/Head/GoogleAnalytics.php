<?php

namespace App\View\Components\Head;

use Illuminate\View\Component;

class GoogleAnalytics extends Component
{
    public $tagId;
    public function __construct()
    {
        $this->tagId = env('ANALYTICS_TAG_ID', null);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.head.google-analytics');
    }
}
