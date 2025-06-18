<?php

namespace App\Notifications;

use App\Http\Controllers\PublicOrderController;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class InvoiceEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $invoice;

    /**
     * Create a new notification instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusLabels = [
            'paid' => 'Paid',
            'pending' => 'Pending',
            'draft' => 'Draft',
            'cancelled' => 'Cancelled',
        ];

        $statusLabel = $statusLabels[$this->invoice->status] ?? ucfirst($this->invoice->status);
        $currencySymbol = Auth::user()->getCurrencySymbol();

        // Generate encrypted hash for the order ID
        $hash = PublicOrderController::generateHash($this->invoice->order_id);
        $publicUrl = route('orders.public', ['hash' => $hash]);

        return (new MailMessage)
            ->subject("Invoice #{$this->invoice->invoice_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Please find attached your invoice #{$this->invoice->invoice_number}.")
            ->line("Invoice Details:")
            ->line("Status: {$statusLabel}")
            ->line("Amount Due: {$currencySymbol}" . number_format($this->invoice->total_amount, 2))
            ->line("Due Date: " . ($this->invoice->due_date ? $this->invoice->due_date->format('F j, Y') : 'Not specified'))
            ->action('View Invoice Online', $publicUrl)
            ->line('Thank you for your business!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $statusLabels = [
            'paid' => 'Paid',
            'pending' => 'Pending',
            'draft' => 'Draft',
            'cancelled' => 'Cancelled',
        ];

        $statusLabel = $statusLabels[$this->invoice->status] ?? ucfirst($this->invoice->status);
        $currencySymbol = Auth::user()->getCurrencySymbol();

        // Store the mail message components
        $mailData = [
            'subject' => "Invoice #{$this->invoice->invoice_number}",
            'greeting' => "Hello {$notifiable->name},",
            'lines' => [
                "Please find attached your invoice #{$this->invoice->invoice_number}.",
                "Invoice Details:",
                "Status: {$statusLabel}",
                "Amount Due: {$currencySymbol}" . number_format($this->invoice->total_amount, 2),
                "Due Date: " . ($this->invoice->due_date ? $this->invoice->due_date->format('F j, Y') : 'Not specified'),
                'Thank you for your business!'
            ],
            'action' => [
                'text' => 'View Invoice Online',
                'url' => route('orders.public', ['hash' => PublicOrderController::generateHash($this->invoice->order_id)])
            ]
        ];

        return [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'status' => $this->invoice->status,
            'amount' => $this->invoice->total_amount,
            'mail' => $mailData
        ];
    }
}
