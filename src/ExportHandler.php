<?php

declare(strict_types=1);

namespace MoonShine\ImportExport;

use Generator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use MoonShine\Contracts\Core\ResourceContract;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Core\Exceptions\ResourceException;
use MoonShine\Laravel\Handlers\Handler;
use MoonShine\ImportExport\Contracts\HasImportExportContract;
use MoonShine\ImportExport\Jobs\ExportHandlerJob;
use MoonShine\Laravel\MoonShineUI;
use MoonShine\Laravel\Notifications\MoonShineNotification;
use MoonShine\Laravel\Notifications\NotificationButton;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Exceptions\ActionButtonException;
use MoonShine\UI\Traits\WithStorage;
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Illuminate\Support\Str;

class ExportHandler extends Handler
{
    use WithStorage;

    protected ?string $icon = 'table-cells';

    protected bool $isCsv = false;

    protected string $csvDelimiter = ',';

    protected ?string $filename = null;

    protected bool $withConfirm = false;

    public function withConfirm(): static
    {
        $this->withConfirm = true;

        return $this;
    }

    public function isWithConfirm(): bool
    {
        return $this->withConfirm;
    }

    public function csv(): static
    {
        $this->isCsv = true;

        return $this;
    }

    public function delimiter(string $value): static
    {
        $this->csvDelimiter = $value;

        return $this;
    }

    public function filename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function hasFilename(): bool
    {
        return ! is_null($this->filename);
    }

    public function isCsv(): bool
    {
        return $this->isCsv;
    }

    public function getDelimiter(): string
    {
        return $this->csvDelimiter;
    }

    protected function generateFilePath(): string
    {
        $dir = $this->getDir();
        $filename = $this->hasFilename() ? $this->filename : $this->getDefaultFilename();
        $ext = $this->isCsv() ? 'csv' : 'xlsx';

        return sprintf('%s/%s.%s', $dir, $filename, $ext);
    }

    protected function getDefaultFilename(): string
    {
        return $this->getResource()->getUriKey() . '-' . Str::uuid();
    }

    /**
     * @throws ActionButtonException
     * @throws IOException
     * @throws WriterNotOpenedException
     * @throws UnsupportedTypeException
     * @throws InvalidArgumentException|Throwable
     */
    public function handle(): Response
    {
        $query = collect(
            request()->query()
        )->except(['_component_name', 'page'])->toArray();

        if (! $this->hasResource()) {
            throw ActionButtonException::resourceRequired();
        }

        $this->resolveStorage();

        $path = Storage::disk($this->getDisk())->path($this->generateFilePath());

        if ($this->isQueue()) {
            ExportHandlerJob::dispatch(
                $this->getResource()::class,
                $path,
                $query,
                $this->getDisk(),
                $this->getDir(),
                $this->getDelimiter(),
                $this->getNotifyUsers(),
            );

            MoonShineUI::toast(
                __('moonshine::ui.resource.queued')
            );

            return back();
        }

        return response()->download(
            self::process(
                $path,
                $this->getResource(),
                $query,
                $this->getDisk(),
                $this->getDir(),
                $this->getDelimiter(),
                $this->getNotifyUsers(),
            )
        );
    }

    /**
     * @throws WriterNotOpenedException
     * @throws IOException
     * @throws UnsupportedTypeException
     * @throws InvalidArgumentException
     * @throws ResourceException
     */
    public static function process(
        string $path,
        ResourceContract $resource,
        array $query,
        string $disk = 'public',
        string $dir = '/',
        string $delimiter = ',',
        array $notifyUsers = [],
    ): string {
        if(! $resource instanceof HasImportExportContract) {
            throw new ResourceException('The resource must implement the HasImportExportContract interface.');
        }

        $resource->setQueryParams($query);

        $items = static function (ResourceContract $resource): Generator {
            foreach ($resource->getQuery()->cursor() as $index => $item) {
                $row = [];

                $fields = $resource->getExportFields();

                $fields->fill(
                    $item->toArray(),
                    $resource->getCaster()->cast($item),
                    $index
                );

                foreach ($fields as $field) {
                    $row[$field->getLabel()] = $field
                        ->rawMode()
                        ->preview();
                }

                yield $row;
            }
        };

        $fastExcel = new FastExcel($items($resource));

        if (str($path)->contains('.csv')) {
            $fastExcel->configureCsv($delimiter);
        }

        $result = $fastExcel->export($path);

        $url = str($path)
            ->remove(Storage::disk($disk)->path($dir))
            ->value();

        MoonShineNotification::send(
            __('moonshine::ui.resource.export.exported'),
            new NotificationButton(
                label: __('moonshine::ui.download'),
                link: Storage::disk($disk)->url(trim($dir, '/') . $url)
            ),
            ids: $notifyUsers,
        );

        return $result;
    }

    /**
     * @throws ActionButtonException
     */
    public function getButton(): ActionButtonContract
    {
        if (! $this->hasResource()) {
            throw ActionButtonException::resourceRequired();
        }

        $query = Arr::query(request(['filter', 'sort', 'query-tag'], []));
        $url = $this->getUrl();
        $ts = "ts=" . time();

        $attributes = [
            'class' => 'js-change-query',
            'data-original-url' => $url,
            'data-original-query' => $ts,
        ];

        $button = ActionButton::make(
            $this->getLabel(),
            trim("$url?$ts&$query", '&')
        )
            ->primary()
            ->customAttributes($attributes)
            ->icon($this->getIconValue(), $this->isCustomIcon(), $this->getIconPath());

        if ($this->isWithConfirm()) {
            $button->withConfirm(
                formBuilder: static fn (FormBuilder $form): FormBuilder => $form->customAttributes($attributes)
            );
        }

        return $this->prepareButton($button);
    }
}
