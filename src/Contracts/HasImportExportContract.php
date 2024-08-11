<?php

declare(strict_types=1);

namespace MoonShine\ImportExport\Contracts;

use MoonShine\Laravel\Collections\Fields;
use Throwable;

interface HasImportExportContract
{
    /**
     * @throws Throwable
     */
    public function getExportFields(): Fields;

    /**
     * @throws Throwable
     */
    public function getImportFields(): Fields;
}
