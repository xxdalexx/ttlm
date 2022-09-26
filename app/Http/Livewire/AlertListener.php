<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Session;
use Livewire\Component;

class AlertListener extends Component
{
    use SendsAlerts;

    protected array $alert = [];

    public function mount()
    {
        if (Session::has('failedApiAlert')) {
            $this->alert = [
                'title' => 'You can not make API Calls',
                'message' => 'Either your Private Key or TT user id is invalid. Please update it here and try again.',
                'type' => 'error'
            ];
        }
        if (Session::has('noCapacitiesSetAlert')) {
            $this->alert = [
                'title' => 'Capacities Needed',
                'message' => 'That page requires a Trucking Capacity and Pocket Capacity to function correctly. Please update it here and try again.',
                'type' => 'error'
            ];
        }
    }

    public function render()
    {
        return view('livewire.alert-listener')->with(['alert' => $this->alert]);
    }

}
