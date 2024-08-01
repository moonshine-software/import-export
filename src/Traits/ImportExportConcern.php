<?php

declare(strict_types=1);

namespace MoonShine\ImportExport\Traits;

use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Collections\Fields;
use MoonShine\Laravel\Handlers\Handler;
use MoonShine\ImportExport\ExportHandler;
use MoonShine\ImportExport\ImportHandler;
use MoonShine\Support\ListOf;
use Throwable;

trait ImportExportConcern
{
    public function isExportToCsv(): bool
    {
        return false;
    }

    public function export(): ?Handler
    {
        return ExportHandler::make(__('moonshine::ui.export'))->when(
            $this->isExportToCsv(),
            static fn (ExportHandler $handler): ExportHandler => $handler->csv()
        );
    }

    public function import(): ?Handler
    {
        return ImportHandler::make(__('moonshine::ui.import'));
    }

    /**
     * @return ListOf<Handler>
     */
    protected function handlers(): ListOf
    {
        return new ListOf(Handler::class, array_filter([
            $this->export(),
            $this->import(),
        ]));
    }

    /**
     * @return list<FieldContract>
     */
    public function exportFields(): array
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
    public function importFields(): array
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
}
