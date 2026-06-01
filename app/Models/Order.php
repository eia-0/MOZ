<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    /**
     * Поля, разрешённые для массового заполнения.
     */
    protected $fillable = [
        'customer_id',
        'store_id',
        'courier_id',
        'delivery_type',
        'delivery_address',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_instructions',
        'status',
        'total_price',
        'delivery_fee',
    ];

    /**
     * Связь с покупателем.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Связь с магазином.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Связь с курьером.
     */
    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    /**
     * Товары в заказе.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Русское название статуса.
     */
    public function statusLabel(): string
    {
        $labels = [
            'new'               => 'Новый',
            'accepted'          => 'Принят',
            'preparing'         => 'Готовится',
            'ready'             => 'Готов',
            'waiting_courier'   => 'Ожидает курьера',
            'courier_assigned'  => 'Курьер назначен',
            'in_transit'        => 'В пути',
            'delivered'         => 'Доставлен',
            'cancelled'         => 'Отменён',
        ];
        return $labels[$this->status] ?? $this->status;
    }
}