<?php

namespace App\Mail;

use App\Models\Presupuesto;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PresupuestoMail extends Mailable
{
    use Queueable, SerializesModels;

    public Presupuesto $presupuesto;
    public string $urlPublica;
    public ?string $mensajePersonalizado;
    public bool $adjuntarPdf;
    public ?string $pdfBinario;
    public string $nombreArchivoPdf;
    public string $asuntoPersonalizado;

    /**
     * Create a new message instance.
     */
    public function __construct(
        Presupuesto $presupuesto,
        ?string $mensajePersonalizado = null,
        bool $adjuntarPdf = true,
        ?string $pdfBinario = null,
        ?string $asunto = null
    ) {
        $this->presupuesto = $presupuesto;
        $this->mensajePersonalizado = $mensajePersonalizado;
        $this->adjuntarPdf = $adjuntarPdf;
        $this->pdfBinario = $pdfBinario;

        $baseUrl = rtrim(config('services.frontend.url', 'http://localhost:3000'), '/');
        $this->urlPublica = "{$baseUrl}/presupuesto/{$presupuesto->token}";

        $nombreEmpresa = $presupuesto->empresa?->nombre_comercial ?? 'GestionPro';
        $this->asuntoPersonalizado = $asunto ?? "Presupuesto {$presupuesto->numero} - {$nombreEmpresa}";

        $numeroLimpio = preg_replace('/[^A-Za-z0-9_-]/', '_', $presupuesto->numero);
        $this->nombreArchivoPdf = "Presupuesto_{$numeroLimpio}.pdf";
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->asuntoPersonalizado,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.presupuesto',
            with: [
                'presupuesto' => $this->presupuesto,
                'cliente' => $this->presupuesto->cliente,
                'empresa' => $this->presupuesto->empresa,
                'urlPublica' => $this->urlPublica,
                'mensajePersonalizado' => $this->mensajePersonalizado,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if ($this->adjuntarPdf && $this->pdfBinario) {
            return [
                Attachment::fromData(fn () => $this->pdfBinario, $this->nombreArchivoPdf)
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
