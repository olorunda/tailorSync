<?php

namespace App\Livewire\Settings;

use App\Models\SubscriptionHistory as SubscriptionHistoryModel;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SubscriptionHistory extends Component
{
    /**
     * Mount the component.
     *
     * @return void
     */
    public function mount()
    {
        // Check if user has permission to view subscription history
        if (!Auth::user()->hasPermission('view_profile')) {
            session()->flash('error', 'You do not have permission to view subscription history.');
            return redirect()->route('dashboard');
        }
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $user = Auth::user();
        $businessDetail = $user->businessDetail;

        // Get subscription history records for the business
        $subscriptionHistory = collect([]);

        if ($businessDetail) {
            $subscriptionHistory = $businessDetail->subscriptionHistories()
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('livewire.settings.subscription-history', [
            'subscriptionHistory' => $subscriptionHistory
        ]);
    }
}
