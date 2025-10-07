<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Ensayo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nro_medidor',
        'marca',
        'modelo',
        'calibre',
        'clase_metrologia',
        'ano_fabricacion',
        'banco_ensayo_id',
        'tecnico_id',
        'estado',
        'tipo_ensayo',
        'caudal_q1',
        'caudal_q2',
        'caudal_q3',
        'volumen_ensayo_q1',
        'volumen_medidor_q1',
        'error_q1',
        'volumen_ensayo_q2',
        'volumen_medidor_q2',
        'error_q2',
        'volumen_ensayo_q3',
        'volumen_medidor_q3',
        'error_q3',
        'temperatura',
        'presion',
        'humedad',
        'resultado_final',
        'observaciones',
        'defectos_encontrados',
        'fecha_inicio',
        'fecha_finalizacion',
        'tiempo_ensayo_minutos',
        'nro_certificado',
        'fecha_certificado'
    ];

    protected $casts = [
        'calibre' => 'decimal:2',
        'caudal_q1' => 'decimal:6',
        'caudal_q2' => 'decimal:6',
        'caudal_q3' => 'decimal:6',
        'volumen_ensayo_q1' => 'decimal:6',
        'volumen_medidor_q1' => 'decimal:6',
        'error_q1' => 'decimal:4',
        'volumen_ensayo_q2' => 'decimal:6',
        'volumen_medidor_q2' => 'decimal:6',
        'error_q2' => 'decimal:4',
        'volumen_ensayo_q3' => 'decimal:6',
        'volumen_medidor_q3' => 'decimal:6',
        'error_q3' => 'decimal:4',
        'temperatura' => 'decimal:2',
        'presion' => 'decimal:2',
        'humedad' => 'decimal:2',
        'fecha_inicio' => 'datetime',
        'fecha_finalizacion' => 'datetime',
        'fecha_certificado' => 'datetime'
    ];

    /**
     * Relación con el banco de ensayo
     */
    public function bancoEnsayo(): BelongsTo
    {
        return $this->belongsTo(BancoEnsayo::class);
    }

    /**
     * Relación con el técnico responsable
     */
    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'tecnico_id');
    }

    /**
     * Calcular error porcentual según NMP 005:2018
     */
    public function calcularError($volumenEnsayo, $volumenMedidor): float
    {
        if (!$volumenEnsayo || $volumenEnsayo == 0) {
            return 0;
        }
        
        return round((($volumenMedidor - $volumenEnsayo) / $volumenEnsayo) * 100, 4);
    }

    /**
     * Actualizar error Q1
     */
    public function actualizarErrorQ1(): void
    {
        if ($this->volumen_ensayo_q1 && $this->volumen_medidor_q1) {
            $this->error_q1 = $this->calcularError($this->volumen_ensayo_q1, $this->volumen_medidor_q1);
        }
    }

    /**
     * Actualizar error Q2
     */
    public function actualizarErrorQ2(): void
    {
        if ($this->volumen_ensayo_q2 && $this->volumen_medidor_q2) {
            $this->error_q2 = $this->calcularError($this->volumen_ensayo_q2, $this->volumen_medidor_q2);
        }
    }

    /**
     * Actualizar error Q3
     */
    public function actualizarErrorQ3(): void
    {
        if ($this->volumen_ensayo_q3 && $this->volumen_medidor_q3) {
            $this->error_q3 = $this->calcularError($this->volumen_ensayo_q3, $this->volumen_medidor_q3);
        }
    }

    /**
     * Actualizar todos los errores
     */
    public function actualizarTodosLosErrores(): void
    {
        $this->actualizarErrorQ1();
        $this->actualizarErrorQ2();
        $this->actualizarErrorQ3();
    }

    /**
     * Verificar si el ensayo cumple con NMP 005:2018
     */
    public function cumpleNorma(): bool
    {
        // Límites de error según NMP 005:2018 para medidores Clase B
        $limiteQ1 = 5.0; // ±5% para Q1
        $limiteQ2Q3 = 2.0; // ±2% para Q2 y Q3

        $errores = [];
        
        if ($this->error_q1 !== null) {
            $errores[] = abs((float)$this->error_q1) <= $limiteQ1;
        }
        
        if ($this->error_q2 !== null) {
            $errores[] = abs((float)$this->error_q2) <= $limiteQ2Q3;
        }
        
        if ($this->error_q3 !== null) {
            $errores[] = abs((float)$this->error_q3) <= $limiteQ2Q3;
        }

        return empty($errores) ? false : !in_array(false, $errores);
    }

    /**
     * Iniciar ensayo
     */
    public function iniciar(): void
    {
        $this->estado = 'en_proceso';
        $this->fecha_inicio = now();
        $this->save();
    }

    /**
     * Finalizar ensayo
     */
    public function finalizar(): void
    {
        $this->estado = 'completado';
        $this->fecha_finalizacion = now();
        
        if ($this->fecha_inicio) {
            $this->tiempo_ensayo_minutos = $this->fecha_inicio->diffInMinutes($this->fecha_finalizacion);
        }

        // Actualizar resultado final basado en cumplimiento de norma
        $this->resultado_final = $this->cumpleNorma() ? 'aprobado' : 'rechazado';
        
        $this->save();
    }

    /**
     * Generar número de certificado
     */
    public function generarNumeroCertificado(): string
    {
        $fecha = now()->format('Ymd');
        $numero = str_pad($this->id, 6, '0', STR_PAD_LEFT);
        return "CERT-{$fecha}-{$numero}";
    }

    /**
     * Scope para ensayos en proceso
     */
    public function scopeEnProceso($query)
    {
        return $query->where('estado', 'en_proceso');
    }

    /**
     * Scope para ensayos completados
     */
    public function scopeCompletados($query)
    {
        return $query->where('estado', 'completado');
    }

    /**
     * Scope para ensayos aprobados
     */
    public function scopeAprobados($query)
    {
        return $query->where('resultado_final', 'aprobado');
    }

    /**
     * Scope para ensayos rechazados
     */
    public function scopeRechazados($query)
    {
        return $query->where('resultado_final', 'rechazado');
    }

    /**
     * Obtener duración formateada del ensayo
     */
    public function getDuracionFormateadaAttribute(): string
    {
        if (!$this->tiempo_ensayo_minutos) {
            return 'N/A';
        }

        $horas = intval($this->tiempo_ensayo_minutos / 60);
        $minutos = $this->tiempo_ensayo_minutos % 60;

        if ($horas > 0) {
            return "{$horas}h {$minutos}min";
        }

        return "{$minutos}min";
    }
}
