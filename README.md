# MoonShine Import/Export Handlers

[Documentation](https://moonshine-laravel.com/docs/4.x/model-resource/import-export)

## Requirements

- MoonShine 5.x
- Laravel 11–13

| Version | PHP       |
|---------|-----------|
| 2.0     | PHP 8.2+  |
| 2.1     | PHP 8.3+  |
| 3.0     | PHP 8.4+  |

## Installation

```shell
composer require moonshine/import-export:^3.0
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

* In the resource's `IndexPage`, add `ImportExportHandlersConcern` to register the buttons and handlers.

```php
use MoonShine\ImportExport\Traits\ImportExportHandlersConcern;
use MoonShine\Laravel\Pages\Crud\IndexPage;

class CategoryIndexPage extends IndexPage
{
    use ImportExportHandlersConcern;
}
```

Register `CategoryIndexPage::class` in the resource's `pages()` method alongside its form and detail pages.

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

* Queue (override these methods on the `IndexPage`).

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

## Upgrading from 2.x

Keep `ImportExportConcern`, `HasImportExportContract`, field definitions, and import events on the resource.
Add `ImportExportHandlersConcern` to its index page. Move any `export()`, `import()`,
`isExportToCsv()`, and `handlers()` overrides from the resource to that page.
From a page override, use `$this->getResource()` to access the resource (for example,
`->filename($this->getResource()->getUriKey())`). Handlers still receive the resource
through the index page's `getHandlers()`, including for queued import and export.
