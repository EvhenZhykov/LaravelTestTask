<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\GenerateTokenService;
/**
 * Class AccessToken
 * @property string id
 * @property User user
 * @package App
 */
class AccessToken extends Model
{
    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var GenerateTokenService
     */
    protected $generateTokenService;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'user_id', 'expires_at', 'revoked'
    ];


    /**
     * AccessToken constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->generateTokenService = new GenerateTokenService();
        $this->id = $this->generateTokenService->generate();
    }

    /**
     * The User who is associated with the current token
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
