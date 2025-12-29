# MoonShine Import/Export Handlers

[Documentation](https://moonshine-laravel.com/docs/4.x/model-resource/import-export)

## Requirements

- MoonShine 4+
- Laravel 10+

| Version | PHP       |
|---------|-----------|
| 2.0     | PHP 8.2+  |
| 2.1     | PHP 8.3+  |

## Installation

```shell
composer require moonshine/import-export
```

## Usage

* In ModelResource add the `ImportExportConcern` trait and implement the `HasImportExportContract` interface.

```php
use MoonShine\ImportExport\Contracts\HasImportExportContract;
use MoonShine\ImportExport\Traits\ImportExportConcern;

class CategoryResource extends ModelResource implements HasImportExportContract
{
    use ImportExportConcern;
    
    // ...
}
```

* Define the fields that will be involved in import and export.

```php
protected function exportFields(): iterable
{
    return [
        ID::make(),
        Position::make(),
        Text::make('Name'),
    ];
}

protected function importFields(): iterable
{
    return [
        ID::make(),
        Text::make('Name'),
    ];
}
```

* Import Events.

```php
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
```

* Queue.

```php
protected function export(): ?Handler
{
    return ExportHandler::make(__('moonshine::ui.export'))
        ->when(
            $this->isExportToCsv(),
            static fn (ExportHandler $handler): ExportHandler => $handler->csv()
        )
        ->queue();
}

protected function import(): ?Handler
{
    return ImportHandler::make(__('moonshine::ui.import'))->queue();
}
```
