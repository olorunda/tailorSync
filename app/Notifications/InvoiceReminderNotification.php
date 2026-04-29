<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $invoice;
    protected $isOverdue;

    /**
     * Create a new notification instance.
     */
    public function __construct(Invoice $invoice, bool $isOverdue = false)
    {
        $this->invoice = $invoice;
        $this->isOverdue = $isOverdue;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->isOverdue 
            ? "Overdue Invoice: #{$this->invoice->invoice_number}" 
            : "Reminder: Invoice #{$this->invoice->invoice_number} Due Soon";

        $message = $this->isOverdue
            ? "Our records show that your invoice is now past its due date. If you've already made the payment, please disregard this message."
            : "This is a friendly reminder that your invoice is due soon.";

        $currencySymbol = '$'; // Default or get from user if possible

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name},")
            ->line($message)
            ->line("**Invoice Details:**")
            ->line("**Invoice Number:** #{$this->invoice->invoice_number}")
            ->line("**Amount Due:** {$currencySymbol}" . number_format($this->invoice->total_amount, 2))
            ->line("**Due Date:** " . ($this->invoice->due_date ? $this->invoice->due_date->format('F j, Y') : 'Not specified'))
            ->action('View & Pay Invoice', url('/orders/public/' . \App\Http\Controllers\PublicOrderController::generateHash($this->invoice->order_id ?? $this->invoice->id.'_invoice')))
            ->line('Thank you for your prompt attention to this matter.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'total_amount' => $this->invoice->total_amount,
            'due_date' => $this->invoice->due_date ? $this->invoice->due_date->toIso8601String() : null,
            'is_overdue' => $this->isOverdue,
        ];
    }
}
