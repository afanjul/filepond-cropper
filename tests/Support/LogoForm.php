<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Tests\Support;

use yii\base\Model;

final class LogoForm extends Model
{
    public array $logo_file = [];

    public function formName(): string
    {
        return 'Model';
    }
}
