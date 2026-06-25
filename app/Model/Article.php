<?php
declare (strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property string $content
 * @property string $cover
 * @property string $summary
 * @property string $create_time
 * @property string $update_time
 * @property int $status
 * @property int $views
 * @property int $sort
 */
class Article extends Model
{
    /**
     * @var string
     */
    protected $table = 'article';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $casts = ['id' => 'integer', 'status' => 'integer', 'views' => 'integer', 'sort' => 'integer'];
}
