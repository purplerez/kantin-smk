<?php

namespace App\Livewire;

use App\Models\Notification;
use Livewire\Component;

class NotificationsBell extends Component
{
    public function markAllRead(): void
    {
        Notification::where('user_id', auth()->id())->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function markRead(int $id): void
    {
        Notification::where('user_id', auth()->id())->whereKey($id)->update(['read_at' => now()]);
    }

    public function render()
    {
        return view('livewire.notifications-bell', [
            'items' => Notification::where('user_id', auth()->id())->orderByDesc('created_at')->limit(12)->get(),
            'unread' => Notification::where('user_id', auth()->id())->whereNull('read_at')->count(),
        ]);
    }
}
