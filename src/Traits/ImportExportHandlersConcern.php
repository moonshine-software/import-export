<?php

declare(strict_types=1);

namespace MoonShine\ImportExport\Traits;

use MoonShine\Crud\Contracts\Page\IndexPageContract;
use MoonShine\Crud\Handlers\Handler;
use MoonShine\ImportExport\ExportHandler;
use MoonShine\ImportExport\ImportHandler;
use MoonShine\Support\ListOf;

/**
 * @mixin IndexPageContract
 */
trait ImportExportHandlersConcern
{
    protected function isExportToCsv(): bool
    {
        return false;
    }

    protected function export(): ?Handler
    {
        return ExportHandler::make(__('moonshine::ui.export'))->when(
            $this->isExportToCsv(),
            static fn (ExportHandler $handler): ExportHandler => $handler->csv()
        );
    }

    protected function import(): ?Handler
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
}
