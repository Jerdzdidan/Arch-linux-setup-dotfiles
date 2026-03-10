<?php

namespace App\View\Components\Table;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProgressCard extends Component
{
    /**
     * Create a new component instance.
     */
    public $numeratorId, $denominatorId, $progressBarId, $percentageId, $icon, $bgColor, $title, $class;

    public function __construct($numeratorId, $denominatorId, $progressBarId, $percentageId, $icon, $bgColor, $title, $class = '')
    {
        $this->numeratorId = $numeratorId;
        $this->denominatorId = $denominatorId;
        $this->progressBarId = $progressBarId;
        $this->percentageId = $percentageId;
        $this->icon = $icon;
        $this->bgColor = $bgColor;
        $this->title = $title;
        $this->class = $class;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.table.progress-card');
    }
}
