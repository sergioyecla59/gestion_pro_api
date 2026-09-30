<?php

namespace App\Services;

use App\Models\Pedido;

class FlujoService
{
    /*
    |--------------------------------------------------------------------------
    | ESTADOS DEL PEDIDO QUE SE PUEDEN PONER A MANO
    |--------------------------------------------------------------------------
    |
    | Los demás estados (Enviado, Completado) son
    | consecuencia de una acción en otro módulo, no
    | un botón. El controlador usa esta lista para
    | rechazar los que no se deben tocar a mano.
    |
    */
    public const ESTADOS_MANUALES = [
        'Preparando',
        'Anulado',
    ];

    /*
    |--------------------------------------------------------------------------
    | PEDIDO → ENVIADO
    |--------------------------------------------------------------------------
    |
    | Se llama al crear un albarán desde el pedido.
    | Si ya había albarán, no se toca nada.
    |
    */
    public function pedidoEnviado(Pedido $pedido): void
    {
        if ($pedido->estado === 'Completado') {
            return;
        }

        $pedido->estado = 'Enviado';
        $pedido->fecha_envio = now();
        $pedido->save();
    }

    /*
    |--------------------------------------------------------------------------
    | PEDIDO → COMPLETADO
    |--------------------------------------------------------------------------
    |
    | Se llama cuando el albarán se marca como
    | Entregado. Es la consecuencia del único hecho
    | físico que declara una persona.
    |
    */
    public function pedidoEntregado(Pedido $pedido): void
    {
        $pedido->estado = 'Completado';
        $pedido->fecha_completado = now();
        $pedido->save();
    }

    /*
    |--------------------------------------------------------------------------
    | ¿SE PUEDE PONER ESTE ESTADO A MANO?
    |--------------------------------------------------------------------------
    |
    */
    public function sePuedePonerAMano(string $estado): bool
    {
        return in_array(
            $estado,
            self::ESTADOS_MANUALES,
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ¿EL PEDIDO YA TIENE ALBARÁN?
    |--------------------------------------------------------------------------
    |
    | Un pedido con albarán ya salió de la empresa:
    | no puede retroceder a un estado anterior.
    |
    */
    public function tieneAlbaran(Pedido $pedido): bool
    {
        return $pedido->albaran()->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | ESTADOS QUE NO SE PUEDEN PONER SI YA HAY ALBARÁN
    |--------------------------------------------------------------------------
    |
    | Retroceder de estado después de generar la
    | entrega dejaría el pedido en una situación
    | incoherente: "Preparando" con mercancía ya
    | entregada.
    |
    */
    public const ESTADOS_BLOQUEADOS_CON_ALBARAN = [
        'Pendiente',
        'Preparando',
    ];

    /*
    |--------------------------------------------------------------------------
    | ¿SE PUEDE APLICAR ESTE ESTADO AL PEDIDO?
    |--------------------------------------------------------------------------
    |
    | Devuelve null si se permite, o el motivo del
    | rechazo si no.
    |
    */
    public function motivoRechazo(
        Pedido $pedido,
        string $estado
    ): ?string {
        // No se puede pulsar un estado automático.
        if (! $this->sePuedePonerAMano($estado)) {
            return 'El estado "' . $estado . '" no se puede establecer manualmente: lo asigna el sistema al crear el albarán o al marcarlo como entregado.';
        }

        // No se puede retroceder si ya hay albarán.
        if (
            $this->tieneAlbaran($pedido)
            && in_array(
                $estado,
                self::ESTADOS_BLOQUEADOS_CON_ALBARAN,
                true
            )
        ) {
            return 'No se puede devolver el pedido ' . $pedido->numero . ' al estado "' . $estado . '" porque ya tiene un albarán generado. Para deshacerlo, elimina primero el albarán.';
        }

        return null;
    }
}
