<?php

declare(strict_types=1);

namespace MoonShine\ImportExport\Traits;

use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Collections\Fields;
use Throwable;

trait ImportExportConcern
{
    /**
     * @return list<FieldContract>
     */
    protected function exportFields(): iterable
    {
        return [];
    }

    /**
     * @throws Throwable
     */
    public function getExportFields(): Fields
    {
        return Fields::make($this->exportFields())->ensure(FieldContract::class);
    }

    /**
     * @return list<FieldContract>
     */
    protected function importFields(): iterable
    {
        return [];
    }

    /**
     * @throws Throwable
     */
    public function getImportFields(): Fields
    {
        return Fields::make($this->importFields())->ensure(FieldContract::class);
    }

    public function beforeImportFilling(array $data): array
    {
        return $data;
    }

    public function beforeImported(mixed $item): mixed
    {
        return $item;
    }

    public function afterImported(mixed $item): mixed
    {
        return $item;
    }
}
